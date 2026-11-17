const db = require('../config/db');

async function logAudit(userId, action, entityType, entityId, details, ip) {
  try {
    await db.execute(
      `INSERT INTO audit_logs (user_id, action, entity_type, entity_id, details, ip_address)
       VALUES (?, ?, ?, ?, ?, ?)`,
      [userId || null, action, entityType || null, entityId || null, details || null, ip || null]
    );
  } catch (err) {
    console.error('Audit log error:', err.message);
  }
}

module.exports = { logAudit };
