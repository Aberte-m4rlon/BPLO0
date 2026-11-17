const express = require('express');
const db = require('../config/db');

const router = express.Router();

// Public endpoint — no authentication required
// GET /api/verify/:permitNumber
router.get('/:permitNumber', async (req, res) => {
  const { permitNumber } = req.params;

  // Sanitize
  if (!/^[A-Z0-9\-]+$/i.test(permitNumber) || permitNumber.length > 50) {
    return res.status(400).json({ result: 'Not Found', message: 'Invalid permit number format.' });
  }

  try {
    const [rows] = await db.execute(
      `SELECT p.permit_number, p.business_name, p.business_address,
              p.permit_type, p.owner_name, p.issue_date, p.expiry_date, p.status
       FROM business_permits p
       WHERE p.permit_number = ?`,
      [permitNumber]
    );

    if (!rows.length) {
      return res.json({
        result: 'Not Found',
        message: 'No permit record found for this QR code.',
        permit_number: permitNumber
      });
    }

    const permit = rows[0];
    const today  = new Date();
    const expiry = permit.expiry_date ? new Date(permit.expiry_date) : null;

    let result = 'Valid';
    if (permit.status === 'Revoked') {
      result = 'Revoked';
    } else if (expiry && expiry < today) {
      result = 'Expired';
    } else if (permit.status !== 'Active') {
      result = 'Not Found';
    }

    // Return only non-sensitive fields on public verification page
    res.json({
      result,
      permit_number:    permit.permit_number,
      business_name:    permit.business_name,
      business_address: permit.business_address,
      permit_type:      permit.permit_type,
      owner_name:       permit.owner_name,
      issue_date:       permit.issue_date,
      expiry_date:      permit.expiry_date
    });
  } catch (err) {
    console.error(err);
    res.status(500).json({ result: 'Error', message: 'Verification failed. Please try again.' });
  }
});

module.exports = router;
