/**
 * Run: node src/scripts/seedAdmin.js
 * Creates an admin account using credentials configured in backend/.env.
 */
require('dotenv').config({ path: require('path').join(__dirname,'../../.env') });
const bcrypt = require('bcryptjs');
const db = require('../config/db');

async function seed() {
  const { ADMIN_EMAIL, ADMIN_PASSWORD } = process.env;
  if (!ADMIN_EMAIL || !ADMIN_PASSWORD || ADMIN_PASSWORD.length < 12) {
    throw new Error('Set ADMIN_EMAIL and an ADMIN_PASSWORD of at least 12 characters in backend/.env.');
  }

  const fullName = process.env.ADMIN_NAME || 'BPLO Administrator';
  const phone = process.env.ADMIN_PHONE || null;
  const hash = await bcrypt.hash(ADMIN_PASSWORD, 12);
  await db.execute(
    `INSERT INTO users (full_name, email, password_hash, role, phone)
     VALUES (?,?,?,?,?)
     ON DUPLICATE KEY UPDATE password_hash=VALUES(password_hash), full_name=VALUES(full_name)`,
    [fullName, ADMIN_EMAIL, hash, 'admin', phone]
  );

  console.log(`Admin account seeded: ${ADMIN_EMAIL}`);
  process.exit(0);
}

seed().catch(err => {
  console.error('Seed failed:', err.message);
  process.exit(1);
});
