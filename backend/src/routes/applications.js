const express = require('express');
const { body, validationResult } = require('express-validator');
const db = require('../config/db');
const { requireAuth } = require('../middleware/auth');
const { generateApplicationNumber } = require('../utils/appNumber');
const { logAudit } = require('../utils/auditLog');

const router = express.Router();
router.use(requireAuth);

const appValidation = [
  body('owner_full_name').trim().notEmpty().isLength({ max: 150 }),
  body('owner_contact').trim().notEmpty().isLength({ max: 30 }),
  body('owner_email').isEmail().normalizeEmail(),
  body('business_name').trim().notEmpty().isLength({ max: 200 }),
  body('business_address').trim().notEmpty(),
  body('business_type').trim().notEmpty().isLength({ max: 200 }),
  body('business_reg_number').optional().trim().isLength({ max: 100 }),
  body('application_type').isIn(['New', 'Renewal'])
];

// GET /api/applications  — applicant's own applications
router.get('/', async (req, res) => {
  try {
    const [rows] = await db.execute(
      `SELECT id, application_number, business_name, business_type, application_type,
              status, date_applied, submitted_at, created_at, remarks
       FROM business_applications
       WHERE user_id = ?
       ORDER BY created_at DESC`,
      [req.session.userId]
    );
    res.json(rows);
  } catch (err) {
    console.error(err);
    res.status(500).json({ message: 'Failed to fetch applications.' });
  }
});

// GET /api/applications/:id  — single application details
router.get('/:id', async (req, res) => {
  try {
    const [rows] = await db.execute(
      `SELECT a.*, u.full_name AS user_name
       FROM business_applications a
       JOIN users u ON u.id = a.user_id
       WHERE a.id = ?`,
      [req.params.id]
    );
    if (!rows.length) return res.status(404).json({ message: 'Application not found.' });

    const app = rows[0];
    // Applicants can only see their own
    if (req.session.role !== 'admin' && app.user_id !== req.session.userId)
      return res.status(403).json({ message: 'Access denied.' });

    // Fetch documents
    const [docs] = await db.execute(
      'SELECT * FROM application_documents WHERE application_id = ? ORDER BY uploaded_at',
      [req.params.id]
    );
    // Fetch status history
    const [history] = await db.execute(
      `SELECT h.*, u.full_name AS changed_by_name
       FROM application_status_history h
       LEFT JOIN users u ON u.id = h.changed_by
       WHERE h.application_id = ?
       ORDER BY h.changed_at`,
      [req.params.id]
    );
    res.json({ ...app, documents: docs, history });
  } catch (err) {
    console.error(err);
    res.status(500).json({ message: 'Failed to fetch application.' });
  }
});

// POST /api/applications  — create draft
router.post('/', appValidation, async (req, res) => {
  const errors = validationResult(req);
  if (!errors.isEmpty()) return res.status(400).json({ errors: errors.array() });

  const {
    owner_full_name, owner_contact, owner_email,
    business_name, business_address, business_type,
    business_reg_number, application_type
  } = req.body;

  try {
    const appNumber = await generateApplicationNumber();
    const [result] = await db.execute(
      `INSERT INTO business_applications
         (application_number, user_id, owner_full_name, owner_contact, owner_email,
          business_name, business_address, business_type, business_reg_number,
          application_type, status, date_applied)
       VALUES (?,?,?,?,?,?,?,?,?,?,'Draft', CURDATE())`,
      [appNumber, req.session.userId, owner_full_name, owner_contact, owner_email,
       business_name, business_address, business_type,
       business_reg_number || null, application_type]
    );

    await db.execute(
      `INSERT INTO application_status_history (application_id, new_status, remarks, changed_by)
       VALUES (?, 'Draft', 'Application draft created', ?)`,
      [result.insertId, req.session.userId]
    );

    await logAudit(req.session.userId, 'APPLICATION_CREATED', 'business_applications',
      result.insertId, `App #${appNumber} created as Draft`, req.ip);

    res.status(201).json({ message: 'Draft saved.', id: result.insertId, application_number: appNumber });
  } catch (err) {
    console.error(err);
    res.status(500).json({ message: 'Failed to create application.' });
  }
});

// PUT /api/applications/:id  — update draft
router.put('/:id', appValidation, async (req, res) => {
  const errors = validationResult(req);
  if (!errors.isEmpty()) return res.status(400).json({ errors: errors.array() });

  try {
    const [rows] = await db.execute(
      'SELECT id, user_id, status FROM business_applications WHERE id = ?',
      [req.params.id]
    );
    if (!rows.length) return res.status(404).json({ message: 'Not found.' });
    const app = rows[0];
    if (app.user_id !== req.session.userId && req.session.role !== 'admin')
      return res.status(403).json({ message: 'Access denied.' });
    if (!['Draft','For Correction'].includes(app.status))
      return res.status(400).json({ message: 'Application cannot be edited in current status.' });

    const {
      owner_full_name, owner_contact, owner_email,
      business_name, business_address, business_type,
      business_reg_number, application_type
    } = req.body;

    await db.execute(
      `UPDATE business_applications
       SET owner_full_name=?, owner_contact=?, owner_email=?,
           business_name=?, business_address=?, business_type=?,
           business_reg_number=?, application_type=?
       WHERE id=?`,
      [owner_full_name, owner_contact, owner_email,
       business_name, business_address, business_type,
       business_reg_number || null, application_type, req.params.id]
    );
    res.json({ message: 'Application updated.' });
  } catch (err) {
    console.error(err);
    res.status(500).json({ message: 'Failed to update application.' });
  }
});

// POST /api/applications/:id/submit  — submit draft
router.post('/:id/submit', async (req, res) => {
  try {
    const [rows] = await db.execute(
      'SELECT * FROM business_applications WHERE id = ?',
      [req.params.id]
    );
    if (!rows.length) return res.status(404).json({ message: 'Not found.' });
    const app = rows[0];
    if (app.user_id !== req.session.userId)
      return res.status(403).json({ message: 'Access denied.' });
    if (!['Draft','For Correction'].includes(app.status))
      return res.status(400).json({ message: 'Only Draft or For Correction applications can be submitted.' });

    const oldStatus = app.status;
    await db.execute(
      `UPDATE business_applications
       SET status='Submitted', submitted_at=NOW(), remarks=NULL
       WHERE id=?`,
      [req.params.id]
    );
    await db.execute(
      `INSERT INTO application_status_history
         (application_id, old_status, new_status, remarks, changed_by)
       VALUES (?,?,?,'Application submitted by applicant',?)`,
      [req.params.id, oldStatus, 'Submitted', req.session.userId]
    );
    await logAudit(req.session.userId, 'APPLICATION_SUBMITTED', 'business_applications',
      app.id, `App #${app.application_number} submitted`, req.ip);

    res.json({ message: 'Application submitted successfully.' });
  } catch (err) {
    console.error(err);
    res.status(500).json({ message: 'Failed to submit application.' });
  }
});

// DELETE /api/applications/:id  — delete draft only
router.delete('/:id', async (req, res) => {
  try {
    const [rows] = await db.execute(
      'SELECT id, user_id, status, application_number FROM business_applications WHERE id = ?',
      [req.params.id]
    );
    if (!rows.length) return res.status(404).json({ message: 'Not found.' });
    const app = rows[0];
    if (app.user_id !== req.session.userId && req.session.role !== 'admin')
      return res.status(403).json({ message: 'Access denied.' });
    if (app.status !== 'Draft')
      return res.status(400).json({ message: 'Only draft applications can be deleted.' });

    await db.execute('DELETE FROM business_applications WHERE id = ?', [req.params.id]);
    await logAudit(req.session.userId, 'APPLICATION_DELETED', 'business_applications',
      app.id, `App #${app.application_number} deleted`, req.ip);
    res.json({ message: 'Draft deleted.' });
  } catch (err) {
    console.error(err);
    res.status(500).json({ message: 'Failed to delete application.' });
  }
});

module.exports = router;
