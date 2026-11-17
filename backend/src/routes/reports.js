const express = require('express');
const { Parser } = require('json2csv');
const db = require('../config/db');
const { requireAdmin } = require('../middleware/auth');

const router = express.Router();
router.use(requireAdmin);

// GET /api/reports/applications/csv
router.get('/applications/csv', async (req, res) => {
  try {
    const { status, type, from, to } = req.query;
    const params = [];
    let where = 'WHERE 1=1';

    if (status) { where += ' AND status = ?'; params.push(status); }
    if (type)   { where += ' AND application_type = ?'; params.push(type); }
    if (from)   { where += ' AND DATE(submitted_at) >= ?'; params.push(from); }
    if (to)     { where += ' AND DATE(submitted_at) <= ?'; params.push(to); }

    const [rows] = await db.execute(
      `SELECT application_number, owner_full_name, owner_email, owner_contact,
              business_name, business_address, business_type, business_reg_number,
              application_type, status, date_applied, submitted_at, remarks
       FROM business_applications ${where}
       ORDER BY created_at DESC`,
      params
    );

    if (!rows.length) return res.status(404).json({ message: 'No data to export.' });

    const fields = [
      'application_number','owner_full_name','owner_email','owner_contact',
      'business_name','business_address','business_type','business_reg_number',
      'application_type','status','date_applied','submitted_at','remarks'
    ];
    const parser = new Parser({ fields });
    const csv = parser.parse(rows);

    res.setHeader('Content-Type', 'text/csv');
    res.setHeader('Content-Disposition', 'attachment; filename="applications_export.csv"');
    res.send(csv);
  } catch (err) {
    console.error(err);
    res.status(500).json({ message: 'Export failed.' });
  }
});

// GET /api/reports/permits/csv
router.get('/permits/csv', async (req, res) => {
  try {
    const [rows] = await db.execute(
      `SELECT p.permit_number, p.business_name, p.business_address, p.permit_type,
              p.owner_name, p.issue_date, p.expiry_date, p.status,
              a.application_number
       FROM business_permits p
       JOIN business_applications a ON a.id = p.application_id
       ORDER BY p.created_at DESC`
    );

    if (!rows.length) return res.status(404).json({ message: 'No data to export.' });

    const fields = [
      'permit_number','business_name','business_address','permit_type',
      'owner_name','issue_date','expiry_date','status','application_number'
    ];
    const parser = new Parser({ fields });
    const csv = parser.parse(rows);

    res.setHeader('Content-Type', 'text/csv');
    res.setHeader('Content-Disposition', 'attachment; filename="permits_export.csv"');
    res.send(csv);
  } catch (err) {
    res.status(500).json({ message: 'Export failed.' });
  }
});

// GET /api/reports/summary
router.get('/summary', async (req, res) => {
  try {
    const [statusBreakdown] = await db.execute(
      `SELECT status, COUNT(*) AS count FROM business_applications GROUP BY status`
    );
    const [typeBreakdown] = await db.execute(
      `SELECT application_type, COUNT(*) AS count FROM business_applications GROUP BY application_type`
    );
    const [monthly] = await db.execute(
      `SELECT DATE_FORMAT(submitted_at,'%Y-%m') AS month, COUNT(*) AS count
       FROM business_applications
       WHERE submitted_at IS NOT NULL
       GROUP BY month ORDER BY month DESC LIMIT 12`
    );
    res.json({ statusBreakdown, typeBreakdown, monthly });
  } catch (err) {
    res.status(500).json({ message: 'Failed.' });
  }
});

module.exports = router;
