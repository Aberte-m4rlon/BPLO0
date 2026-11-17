const db = require('../config/db');

async function generateApplicationNumber() {
  const year = new Date().getFullYear();
  const [rows] = await db.execute(
    `SELECT COUNT(*) AS cnt FROM business_applications WHERE YEAR(created_at) = ?`,
    [year]
  );
  const seq = (rows[0].cnt + 1).toString().padStart(5, '0');
  return `APP-${year}-${seq}`;
}

async function generatePermitNumber() {
  const year = new Date().getFullYear();
  const [rows] = await db.execute(
    `SELECT COUNT(*) AS cnt FROM business_permits WHERE YEAR(created_at) = ?`,
    [year]
  );
  const seq = (rows[0].cnt + 1).toString().padStart(5, '0');
  return `BP-${year}-${seq}`;
}

module.exports = { generateApplicationNumber, generatePermitNumber };
