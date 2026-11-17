const express = require('express');
const QRCode = require('qrcode');
const { body, validationResult } = require('express-validator');
const db = require('../config/db');
const { requireAuth, requireAdmin } = require('../middleware/auth');
const { generatePermitNumber } = require('../utils/appNumber');
const { logAudit } = require('../utils/auditLog');

const router = express.Router();

// ── Issue permit (admin only) ─────────────────────────────
router.post('/issue/:applicationId', requireAdmin, async (req, res) => {
  try {
    const [rows] = await db.execute(
      'SELECT * FROM business_applications WHERE id = ?', [req.params.applicationId]
    );
    if (!rows.length) return res.status(404).json({ message: 'Application not found.' });
    const app = rows[0];

    if (app.status !== 'Approved')
      return res.status(400).json({ message: 'Application must be Approved before issuing a permit.' });

    // Check not already issued
    const [existing] = await db.execute(
      'SELECT id FROM business_permits WHERE application_id = ?', [req.params.applicationId]
    );
    if (existing.length) return res.status(409).json({ message: 'Permit already issued for this application.' });

    const permitNumber = await generatePermitNumber();
    const issueDate    = new Date();
    const expiryDate   = new Date(issueDate.getFullYear(), 11, 31); // Dec 31 same year

    // Generate QR Code data URL
    const verifyUrl = `${process.env.FRONTEND_URL || 'http://localhost:5173'}/verify/${permitNumber}`;
    const qrDataUrl = await QRCode.toDataURL(verifyUrl, { width: 300, margin: 2 });

    const [result] = await db.execute(
      `INSERT INTO business_permits
         (application_id, permit_number, business_name, business_address,
          permit_type, owner_name, issued_by, issue_date, expiry_date, status, qr_code_data)
       VALUES (?,?,?,?,?,?,?,?,?,?,?)`,
      [app.id, permitNumber, app.business_name, app.business_address,
       'Business Permit', app.owner_full_name,
       req.session.userId,
       issueDate.toISOString().split('T')[0],
       expiryDate.toISOString().split('T')[0],
       'Active', qrDataUrl]
    );

    // Update application status to Ready for Release
    await db.execute(
      "UPDATE business_applications SET status='Ready for Release' WHERE id=?",
      [req.params.applicationId]
    );
    await db.execute(
      `INSERT INTO application_status_history
         (application_id, old_status, new_status, remarks, changed_by)
       VALUES (?,'Approved','Ready for Release','Permit issued and ready for release.',?)`,
      [req.params.applicationId, req.session.userId]
    );

    await logAudit(req.session.userId, 'PERMIT_ISSUED', 'business_permits',
      result.insertId, `Permit ${permitNumber} issued for ${app.business_name}`, req.ip);

    res.status(201).json({
      message: 'Permit issued successfully.',
      permit_number: permitNumber,
      permit_id: result.insertId
    });
  } catch (err) {
    console.error(err);
    res.status(500).json({ message: 'Failed to issue permit.' });
  }
});

// ── Get permit by application ID ──────────────────────────
router.get('/by-application/:applicationId', requireAuth, async (req, res) => {
  try {
    const [rows] = await db.execute(
      `SELECT p.*, a.user_id, a.application_number
       FROM business_permits p
       JOIN business_applications a ON a.id = p.application_id
       WHERE p.application_id = ?`,
      [req.params.applicationId]
    );
    if (!rows.length) return res.status(404).json({ message: 'No permit found.' });
    const permit = rows[0];

    // Access control: applicants can only see their own
    if (req.session.role !== 'admin' && permit.user_id !== req.session.userId)
      return res.status(403).json({ message: 'Access denied.' });

    res.json(permit);
  } catch (err) {
    res.status(500).json({ message: 'Failed to fetch permit.' });
  }
});

// ── Get permit by permit number ───────────────────────────
router.get('/:permitNumber', requireAuth, async (req, res) => {
  try {
    const [rows] = await db.execute(
      `SELECT p.*, a.application_number, a.user_id
       FROM business_permits p
       JOIN business_applications a ON a.id = p.application_id
       WHERE p.permit_number = ?`,
      [req.params.permitNumber]
    );
    if (!rows.length) return res.status(404).json({ message: 'Permit not found.' });
    const permit = rows[0];

    if (req.session.role !== 'admin' && permit.user_id !== req.session.userId)
      return res.status(403).json({ message: 'Access denied.' });

    res.json(permit);
  } catch (err) {
    res.status(500).json({ message: 'Failed.' });
  }
});

// ── Revoke permit (admin only) ────────────────────────────
router.patch('/:permitNumber/revoke', requireAdmin, [
  body('reason').trim().notEmpty().withMessage('Revocation reason is required.')
], async (req, res) => {
  const errors = validationResult(req);
  if (!errors.isEmpty()) return res.status(400).json({ errors: errors.array() });

  const { reason } = req.body;
  try {
    const [rows] = await db.execute(
      'SELECT * FROM business_permits WHERE permit_number = ?', [req.params.permitNumber]
    );
    if (!rows.length) return res.status(404).json({ message: 'Permit not found.' });
    if (rows[0].status === 'Revoked')
      return res.status(400).json({ message: 'Permit already revoked.' });

    await db.execute(
      `UPDATE business_permits
       SET status='Revoked', revoke_reason=?, revoked_by=?, revoked_at=NOW()
       WHERE permit_number=?`,
      [reason, req.session.userId, req.params.permitNumber]
    );

    await logAudit(req.session.userId, 'PERMIT_REVOKED', 'business_permits',
      rows[0].id, `Permit ${req.params.permitNumber} revoked. Reason: ${reason}`, req.ip);

    res.json({ message: 'Permit revoked.' });
  } catch (err) {
    res.status(500).json({ message: 'Failed to revoke permit.' });
  }
});

// ── Release permit (mark as Released) ────────────────────
router.patch('/release/:applicationId', requireAdmin, async (req, res) => {
  try {
    const [rows] = await db.execute(
      'SELECT * FROM business_applications WHERE id = ?', [req.params.applicationId]
    );
    if (!rows.length) return res.status(404).json({ message: 'Not found.' });
    if (rows[0].status !== 'Ready for Release')
      return res.status(400).json({ message: 'Application not ready for release.' });

    await db.execute(
      "UPDATE business_applications SET status='Released' WHERE id=?",
      [req.params.applicationId]
    );
    await db.execute(
      `INSERT INTO application_status_history
         (application_id, old_status, new_status, remarks, changed_by)
       VALUES (?,'Ready for Release','Released','Permit physically released to applicant.',?)`,
      [req.params.applicationId, req.session.userId]
    );
    await logAudit(req.session.userId, 'PERMIT_RELEASED', 'business_applications',
      req.params.applicationId, 'Permit released to applicant.', req.ip);

    res.json({ message: 'Application marked as Released.' });
  } catch (err) {
    res.status(500).json({ message: 'Failed.' });
  }
});

module.exports = router;
