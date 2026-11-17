const express = require('express');
const bcrypt = require('bcryptjs');
const { body, validationResult } = require('express-validator');
const db = require('../config/db');
const { logAudit } = require('../utils/auditLog');

const router = express.Router();

// ── Register ──────────────────────────────────────────────
router.post('/register', [
  body('full_name').trim().notEmpty().withMessage('Full name is required').isLength({ max: 150 }),
  body('email').isEmail().normalizeEmail().withMessage('Valid email required'),
  body('password')
    .isLength({ min: 8 }).withMessage('Password must be at least 8 characters')
    .matches(/[A-Z]/).withMessage('Password must contain an uppercase letter')
    .matches(/[0-9]/).withMessage('Password must contain a number'),
  body('phone').optional().trim().isLength({ max: 30 })
], async (req, res) => {
  const errors = validationResult(req);
  if (!errors.isEmpty()) return res.status(400).json({ errors: errors.array() });

  const { full_name, email, password, phone } = req.body;
  try {
    const [existing] = await db.execute('SELECT id FROM users WHERE email = ?', [email]);
    if (existing.length > 0) return res.status(409).json({ message: 'Email already registered.' });

    const hash = await bcrypt.hash(password, 12);
    const [result] = await db.execute(
      'INSERT INTO users (full_name, email, password_hash, role, phone) VALUES (?,?,?,?,?)',
      [full_name, email, hash, 'applicant', phone || null]
    );
    await logAudit(result.insertId, 'USER_REGISTERED', 'users', result.insertId, `New applicant: ${email}`, req.ip);
    res.status(201).json({ message: 'Account created successfully. Please log in.' });
  } catch (err) {
    console.error(err);
    res.status(500).json({ message: 'Server error during registration.' });
  }
});

// ── Login ─────────────────────────────────────────────────
router.post('/login', [
  body('email').isEmail().normalizeEmail(),
  body('password').notEmpty()
], async (req, res) => {
  const errors = validationResult(req);
  if (!errors.isEmpty()) return res.status(400).json({ errors: errors.array() });

  const { email, password } = req.body;
  try {
    const [rows] = await db.execute(
      'SELECT id, full_name, email, password_hash, role, is_active FROM users WHERE email = ?',
      [email]
    );
    if (rows.length === 0) return res.status(401).json({ message: 'Invalid email or password.' });

    const user = rows[0];
    if (!user.is_active) return res.status(403).json({ message: 'Account is disabled.' });

    const match = await bcrypt.compare(password, user.password_hash);
    if (!match) return res.status(401).json({ message: 'Invalid email or password.' });

    req.session.userId = user.id;
    req.session.role   = user.role;
    req.session.name   = user.full_name;

    await logAudit(user.id, 'USER_LOGIN', 'users', user.id, `Login: ${email}`, req.ip);

    res.json({
      message: 'Login successful',
      user: { id: user.id, full_name: user.full_name, email: user.email, role: user.role }
    });
  } catch (err) {
    console.error(err);
    res.status(500).json({ message: 'Server error during login.' });
  }
});

// ── Logout ────────────────────────────────────────────────
router.post('/logout', (req, res) => {
  req.session.destroy(err => {
    if (err) return res.status(500).json({ message: 'Logout failed.' });
    res.clearCookie('connect.sid');
    res.json({ message: 'Logged out successfully.' });
  });
});

// ── Me (current user) ─────────────────────────────────────
router.get('/me', (req, res) => {
  if (!req.session.userId) return res.status(401).json({ message: 'Not authenticated.' });
  res.json({
    id:        req.session.userId,
    full_name: req.session.name,
    role:      req.session.role
  });
});

module.exports = router;
