-- ============================================================
-- BPLO Bongabong - Seed Data
-- ============================================================
USE bplo_bongabong;

-- ------------------------------------------------------------
-- Default Admin Accounts
-- Passwords are bcrypt hashes of: Admin@1234 and Staff@1234 and Test@1234
-- ------------------------------------------------------------
INSERT INTO users (full_name, email, password_hash, role, phone) VALUES
('BPLO Administrator',  'admin@bplo.bongabong.gov.ph', '$2b$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2uheWG/igi.', 'admin',     '09171234567'),
('BPLO Staff',          'staff@bplo.bongabong.gov.ph', '$2b$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2uheWG/igi.', 'admin',     '09181234567'),
('Juan dela Cruz',       'juan@example.com',             '$2b$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2uheWG/igi.', 'applicant', '09191234567'),
('Maria Santos',         'maria@example.com',             '$2b$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2uheWG/igi.', 'applicant', '09201234567');

-- Note: The hash above is for password "password" (bcrypt). 
-- Real passwords will be properly hashed by the registration endpoint.
-- To use the test accounts listed in README, run the backend seed script: npm run seed

-- ------------------------------------------------------------
-- Document Requirements
-- ------------------------------------------------------------
INSERT INTO document_requirements (doc_name, description, is_required, applies_to, sort_order) VALUES
('Barangay Business Clearance',      'Clearance from the Barangay where the business is located',        1, 'Both',    1),
('DTI / SEC / CDA Registration',     'Department of Trade and Industry or SEC Certificate of Registration',1, 'New',     2),
('Community Tax Certificate (CTC)',  'Cedula from the Municipal Hall',                                    1, 'Both',    3),
('Lease Contract / Property Title',  'Proof of business location ownership or lease',                    1, 'Both',    4),
('Fire Safety Inspection Certificate','Certificate from the Bureau of Fire Protection',                   1, 'Both',    5),
('Sanitary Permit',                  'Health and Sanitation clearance',                                   1, 'Both',    6),
('Previous Business Permit',         'Copy of last year\'s approved business permit',                    1, 'Renewal', 7),
('SSS / PhilHealth / Pag-IBIG Certificate','Proof of employee benefit contributions (if with employees)', 0, 'Both',   8);

-- ------------------------------------------------------------
-- Sample Applications (for development/testing)
-- ------------------------------------------------------------
INSERT INTO business_applications
  (application_number, user_id, owner_full_name, owner_contact, owner_email,
   business_name, business_address, business_type, business_reg_number,
   application_type, status, date_applied, submitted_at)
VALUES
('APP-2026-00001', 3, 'Juan dela Cruz', '09191234567', 'juan@example.com',
 'JDC General Merchandise', 'Brgy. Poblacion, Bongabong, Oriental Mindoro',
 'Retail - General Merchandise', 'DTI-123456', 'New', 'Approved',
 '2026-01-15', '2026-01-15 09:00:00'),

('APP-2026-00002', 4, 'Maria Santos', '09201234567', 'maria@example.com',
 'Santos Carinderia', 'Brgy. Hagan, Bongabong, Oriental Mindoro',
 'Food Service - Carinderia', NULL, 'New', 'Under Review',
 '2026-02-10', '2026-02-10 10:30:00'),

('APP-2026-00003', 3, 'Juan dela Cruz', '09191234567', 'juan@example.com',
 'JDC Repair Shop', 'Brgy. Labasan, Bongabong, Oriental Mindoro',
 'Services - Electronics Repair', NULL, 'New', 'For Correction',
 '2026-03-05', '2026-03-05 14:00:00');

-- Status history for sample applications
INSERT INTO application_status_history (application_id, old_status, new_status, remarks, changed_by) VALUES
(1, 'Draft',        'Submitted',      'Application submitted by applicant', 3),
(1, 'Submitted',    'Under Review',   'Documents received, under review',   1),
(1, 'Under Review', 'Approved',       'All requirements complete. Approved.',1),
(2, 'Draft',        'Submitted',      'Application submitted',               4),
(2, 'Submitted',    'Under Review',   'Under review by BPLO',               1),
(3, 'Draft',        'Submitted',      'Application submitted',               3),
(3, 'Submitted',    'Under Review',   'Under review',                       1),
(3, 'Under Review', 'For Correction', 'Please submit updated Fire Safety Inspection Certificate.', 1);

-- Set remarks on correction
UPDATE business_applications SET remarks = 'Please submit updated Fire Safety Inspection Certificate.' WHERE id = 3;

-- Sample approved permit
INSERT INTO business_permits
  (application_id, permit_number, business_name, business_address, permit_type,
   owner_name, issued_by, issue_date, expiry_date, status, qr_code_data)
VALUES
(1, 'BP-2026-00001', 'JDC General Merchandise',
 'Brgy. Poblacion, Bongabong, Oriental Mindoro', 'Business Permit',
 'Juan dela Cruz', 1, '2026-01-20', '2026-12-31', 'Active',
 'BP-2026-00001');

-- Audit log samples
INSERT INTO audit_logs (user_id, action, entity_type, entity_id, details) VALUES
(1, 'PERMIT_ISSUED',   'business_permits',       1, 'Permit BP-2026-00001 issued for JDC General Merchandise'),
(1, 'STATUS_CHANGED',  'business_applications',  1, 'Status changed to Approved'),
(1, 'STATUS_CHANGED',  'business_applications',  2, 'Status changed to Under Review'),
(1, 'STATUS_CHANGED',  'business_applications',  3, 'Status changed to For Correction');
