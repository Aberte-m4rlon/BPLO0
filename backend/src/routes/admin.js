const express = require('express');
const { body, validationResult } = require('express-validator');
const db = require('../config/db');
const { requireAdmin } = require('../middleware/auth');
const { logAudit } = require('../utils/auditLog');

const router = express.Router();
router.use(requireAdmin);

// ── Dashboard statistics ──────────────────────────────────
router.get('/dashboard', async (req, res) => {
  try {
    const [totalRows]    = await db.execute('SELECT COUNT(*) AS cnt FROM business_applications');
    const [pendingRows]  = await db.execute("SELECT COUNT(*) AS cnt FROM business_applications WHERE status='Submitted'");
    const [reviewRows]   = await db.execute("SELECT COUNT(*) AS cnt FROM business_applications WHERE status='Under Review'");
    const [approvedRows] = await db.execute("SELECT COUNT(*) AS cnt FROM business_applications WHERE status='Approved'");
    const [rejectedRows] = await db.execute("SELECT COUNT(*) AS cnt FROM business_applications WHERE status='Rejected'");
    const [releasedRows] = await db.execute("SELECT COUNT(*) AS cnt FROM business_applications WHERE status='Released'");
    const [permitRows]   = await db.execute("SELECT COUNT(*) AS cnt FROM business_permits WHERE status='Active'");
    const [corrRows]     = await db.execute("SELECT COUNT(*) AS cnt FROM business_applications WHERE status='For Correction'");

    // Recent applications
    const [recent] = await db.execute(
      `SELECT a.id, a.application_number, a.business_name, a.owner_full_name,
              a.status, a.submitted_at, a.application_type
       FROM business_applications a
       ORDER BY a.created_at DESC LIMIT 10`
    );

    res.json({
      stats: {
        total:       totalRows[0].cnt,
        pending:     pendingRows[0].cnt,
        under_review: reviewRows[0].cnt,
        approved:    approvedRows[0].cnt,
        rejected:    rejectedRows[0].cnt,
        released:    releasedRows[0].cnt,
        active_permits: permitRows[0].cnt,
        for_correction: corrRows[0].cnt
      },
      recent_applications: recent
    });
  } catch (err) {
    console.error(err);
    res.status(500).json({ message: 'Dashboard error.' });
  }
});

// ── List all applications (with search & filter) ──────────
router.get('/applications', async (req, res) => {
  try {
    const { search, status, type, page = 1, limit = 20, from, to } = req.query;
    const offset = (parseInt(page) - 1) * parseInt(limit);
    const params = [];
    let where = 'WHERE 1=1';

    if (search) {
      where += ' AND (a.application_number LIKE ? OR a.business_name LIKE ? OR a.owner_full_name LIKE ?)';
      params.push(`%${search}%`, `%${search}%`, `%${search}%`);
    }
    if (status) { where += ' AND a.status = ?'; params.push(status); }
    if (type)   { where += ' AND a.application_type = ?'; params.push(type); }
    if (from)   { where += ' AND DATE(a.submitted_at) >= ?'; params.push(from); }
    if (to)     { where += ' AND DATE(a.submitted_at) <= ?'; params.push(to); }

    const [countRows] = await db.execute(
      `SELECT COUNT(*) AS cnt FROM business_applications a ${where}`, params
    );
    const [rows] = await db.execute(
      `SELECT a.id, a.application_number, a.business_name, a.owner_full_name,
              a.owner_contact, a.owner_email, a.business_type, a.application_type,
              a.status, a.date_applied, a.submitted_at, a.remarks
       FROM business_applications a
       ${where}
       ORDER BY a.created_at DESC
       LIMIT ? OFFSET ?`,
      [...params, parseInt(limit), offset]
    );

    res.json({ total: countRows[0].cnt, page: parseInt(page), data: rows });
  } catch (err) {
    console.error(err);
    res.status(500).json({ message: 'Failed to fetch applications.' });
  }
});

// ── Get single application (admin view) ───────────────────
router.get('/applications/:id', async (req, res) => {
  try {
    const [rows] = await db.execute(
      `SELECT a.*, u.full_name AS user_name, u.email AS user_email
       FROM business_applications a
       JOIN users u ON u.id = a.user_id
       WHERE a.id = ?`,
      [req.params.id]
    );
    if (!rows.length) return res.status(404).json({ message: 'Not found.' });

    const [docs] = await db.execute(
      'SELECT * FROM application_documents WHERE application_id = ? ORDER BY uploaded_at',
      [req.params.id]
    );
    const [history] = await db.execute(
      `SELECT h.*, u.full_name AS changed_by_name
       FROM application_status_history h
       LEFT JOIN users u ON u.id = h.changed_by
       WHERE h.application_id = ?
       ORDER BY h.changed_at`,
      [req.params.id]
    );
    // Check if permit exists
    const [permits] = await db.execute(
      'SELECT * FROM business_permits WHERE application_id = ?',
      [req.params.id]
    );

    res.json({ ...rows[0], documents: docs, history, permit: permits[0] || null });
  } catch (err) {
    console.error(err);
    res.status(500).json({ message: 'Failed to fetch application.' });
  }
});

// ── Update application status ─────────────────────────────
const VALID_TRANSITIONS = {
  'Submitted':       ['Under Review', 'Rejected'],
  'Under Review':    ['Approved', 'For Correction', 'Rejected'],
  'For Correction':  ['Under Review', 'Rejected'],
  'Approved':        ['Ready for Release'],
  'Ready for Release': ['Released'],
  'Released':        [],
  'Rejected':        [],
  'Draft':           []
};

router.patch('/applications/:id/status', [
  body('status').notEmpty().isIn([
    'Under Review','For Correction','Approved','Rejected','Ready for Release','Released'
  ]),
  body('remarks').optional().trim().isLength({ max: 1000 })
], async (req, res) => {
  const errors = validationResult(req);
  if (!errors.isEmpty()) return res.status(400).json({ errors: errors.array() });

  const { status, remarks } = req.body;
  try {
    const [rows] = await db.execute(
      'SELECT * FROM business_applications WHERE id = ?', [req.params.id]
    );
    if (!rows.length) return res.status(404).json({ message: 'Not found.' });
    const app = rows[0];

    const allowed = VALID_TRANSITIONS[app.status] || [];
    if (!allowed.includes(status)) {
      return res.status(400).json({
        message: `Cannot change status from "${app.status}" to "${status}".`
      });
    }

    await db.execute(
      'UPDATE business_applications SET status=?, remarks=? WHERE id=?',
      [status, remarks || null, req.params.id]
    );
    await db.execute(
      `INSERT INTO application_status_history
         (application_id, old_status, new_status, remarks, changed_by)
       VALUES (?,?,?,?,?)`,
      [req.params.id, app.status, status, remarks || null, req.session.userId]
    );

    await logAudit(req.session.userId, 'STATUS_CHANGED', 'business_applications',
      app.id, `Status: ${app.status} → ${status}. Remarks: ${remarks || ''}`, req.ip);

    res.json({ message: `Status updated to "${status}".` });
  } catch (err) {
    console.error(err);
    res.status(500).json({ message: 'Failed to update status.' });
  }
});

// ── List all permits ──────────────────────────────────────
router.get('/permits', async (req, res) => {
  try {
    const { search, status, page = 1, limit = 20 } = req.query;
    const offset = (parseInt(page) - 1) * parseInt(limit);
    const params = [];
    let where = 'WHERE 1=1';

    if (search) {
      where += ' AND (p.permit_number LIKE ? OR p.business_name LIKE ? OR p.owner_name LIKE ?)';
      params.push(`%${search}%`, `%${search}%`, `%${search}%`);
    }
    if (status) { where += ' AND p.status = ?'; params.push(status); }

    const [countRows] = await db.execute(
      `SELECT COUNT(*) AS cnt FROM business_permits p ${where}`, params
    );
    const [rows] = await db.execute(
      `SELECT p.*, a.application_number, u.full_name AS issued_by_name
       FROM business_permits p
       JOIN business_applications a ON a.id = p.application_id
       LEFT JOIN users u ON u.id = p.issued_by
       ${where}
       ORDER BY p.created_at DESC
       LIMIT ? OFFSET ?`,
      [...params, parseInt(limit), offset]
    );
    res.json({ total: countRows[0].cnt, page: parseInt(page), data: rows });
  } catch (err) {
    res.status(500).json({ message: 'Failed to fetch permits.' });
  }
});

// ── Audit log ─────────────────────────────────────────────
router.get('/audit-logs', async (req, res) => {
  try {
    const { page = 1, limit = 50 } = req.query;
    const offset = (parseInt(page) - 1) * parseInt(limit);
    const [rows] = await db.execute(
      `SELECT al.*, u.full_name AS user_name
       FROM audit_logs al
       LEFT JOIN users u ON u.id = al.user_id
       ORDER BY al.created_at DESC
       LIMIT ? OFFSET ?`,
      [parseInt(limit), offset]
    );
    res.json(rows);
  } catch (err) {
    res.status(500).json({ message: 'Failed to fetch audit logs.' });
  }
});

// ── Manage document requirements ─────────────────────────
router.get('/doc-requirements', async (req, res) => {
  try {
    const [rows] = await db.execute(
      'SELECT * FROM document_requirements ORDER BY sort_order'
    );
    res.json(rows);
  } catch (err) {
    res.status(500).json({ message: 'Failed.' });
  }
});

router.post('/doc-requirements', [
  body('doc_name').trim().notEmpty(),
  body('applies_to').isIn(['New','Renewal','Both']),
  body('is_required').isBoolean()
], async (req, res) => {
  const errors = validationResult(req);
  if (!errors.isEmpty()) return res.status(400).json({ errors: errors.array() });
  const { doc_name, description, is_required, applies_to, sort_order } = req.body;
  try {
    await db.execute(
      'INSERT INTO document_requirements (doc_name, description, is_required, applies_to, sort_order) VALUES (?,?,?,?,?)',
      [doc_name, description || null, is_required ? 1 : 0, applies_to, sort_order || 0]
    );
    res.status(201).json({ message: 'Requirement added.' });
  } catch (err) {
    res.status(500).json({ message: 'Failed.' });
  }
});

router.patch('/doc-requirements/:id', async (req, res) => {
  const { is_active } = req.body;
  try {
    await db.execute('UPDATE document_requirements SET is_active=? WHERE id=?',
      [is_active ? 1 : 0, req.params.id]);
    res.json({ message: 'Updated.' });
  } catch (err) {
    res.status(500).json({ message: 'Failed.' });
  }
});

module.exports = router;
