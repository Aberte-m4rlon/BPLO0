-- ============================================================
-- BPLO Bongabong - Database Schema
-- ============================================================

CREATE DATABASE IF NOT EXISTS bplo_bongabong;
USE bplo_bongabong;

-- ------------------------------------------------------------
-- Users
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS users (
  id            INT AUTO_INCREMENT PRIMARY KEY,
  full_name     VARCHAR(150) NOT NULL,
  email         VARCHAR(150) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  role          ENUM('applicant','admin') NOT NULL DEFAULT 'applicant',
  phone         VARCHAR(30),
  is_active     TINYINT(1) NOT NULL DEFAULT 1,
  created_at    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- ------------------------------------------------------------
-- Business Applications
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS business_applications (
  id                   INT AUTO_INCREMENT PRIMARY KEY,
  application_number   VARCHAR(30)  NOT NULL UNIQUE,
  user_id              INT          NOT NULL,
  -- Owner Info
  owner_full_name      VARCHAR(150) NOT NULL,
  owner_contact        VARCHAR(30)  NOT NULL,
  owner_email          VARCHAR(150) NOT NULL,
  -- Business Info
  business_name        VARCHAR(200) NOT NULL,
  business_address     TEXT         NOT NULL,
  business_type        VARCHAR(200) NOT NULL,
  business_reg_number  VARCHAR(100),
  application_type     ENUM('New','Renewal') NOT NULL DEFAULT 'New',
  -- Status & Dates
  status               ENUM('Draft','Submitted','Under Review','For Correction','Approved','Rejected','Ready for Release','Released')
                        NOT NULL DEFAULT 'Draft',
  remarks              TEXT,
  date_applied         DATE,
  submitted_at         DATETIME,
  created_at           DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at           DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_app_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- ------------------------------------------------------------
-- Application Documents
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS application_documents (
  id             INT AUTO_INCREMENT PRIMARY KEY,
  application_id INT          NOT NULL,
  document_type  VARCHAR(100) NOT NULL,
  file_name      VARCHAR(255) NOT NULL,
  file_path      VARCHAR(500) NOT NULL,
  file_size      INT,
  mime_type      VARCHAR(100),
  uploaded_at    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_doc_app FOREIGN KEY (application_id) REFERENCES business_applications(id) ON DELETE CASCADE
);

-- ------------------------------------------------------------
-- Application Status History
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS application_status_history (
  id             INT AUTO_INCREMENT PRIMARY KEY,
  application_id INT          NOT NULL,
  old_status     VARCHAR(50),
  new_status     VARCHAR(50)  NOT NULL,
  remarks        TEXT,
  changed_by     INT,
  changed_at     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_hist_app  FOREIGN KEY (application_id) REFERENCES business_applications(id) ON DELETE CASCADE,
  CONSTRAINT fk_hist_user FOREIGN KEY (changed_by)     REFERENCES users(id) ON DELETE SET NULL
);

-- ------------------------------------------------------------
-- Business Permits
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS business_permits (
  id             INT AUTO_INCREMENT PRIMARY KEY,
  application_id INT          NOT NULL UNIQUE,
  permit_number  VARCHAR(50)  NOT NULL UNIQUE,
  business_name  VARCHAR(200) NOT NULL,
  business_address TEXT       NOT NULL,
  permit_type    VARCHAR(100) NOT NULL DEFAULT 'Business Permit',
  owner_name     VARCHAR(150) NOT NULL,
  issued_by      INT,
  issue_date     DATE,
  expiry_date    DATE,
  status         ENUM('Active','Expired','Revoked') NOT NULL DEFAULT 'Active',
  qr_code_data   TEXT,
  revoke_reason  TEXT,
  revoked_by     INT,
  revoked_at     DATETIME,
  created_at     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_permit_app  FOREIGN KEY (application_id) REFERENCES business_applications(id),
  CONSTRAINT fk_permit_user FOREIGN KEY (issued_by)      REFERENCES users(id) ON DELETE SET NULL
);

-- ------------------------------------------------------------
-- Audit Logs
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS audit_logs (
  id          INT AUTO_INCREMENT PRIMARY KEY,
  user_id     INT,
  action      VARCHAR(200) NOT NULL,
  entity_type VARCHAR(100),
  entity_id   INT,
  details     TEXT,
  ip_address  VARCHAR(45),
  created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_audit_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

-- ------------------------------------------------------------
-- Document Requirements (configurable by admin)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS document_requirements (
  id           INT AUTO_INCREMENT PRIMARY KEY,
  doc_name     VARCHAR(150) NOT NULL,
  description  TEXT,
  is_required  TINYINT(1) NOT NULL DEFAULT 1,
  applies_to   ENUM('New','Renewal','Both') NOT NULL DEFAULT 'Both',
  sort_order   INT NOT NULL DEFAULT 0,
  is_active    TINYINT(1) NOT NULL DEFAULT 1,
  created_at   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- ------------------------------------------------------------
-- Indexes
-- ------------------------------------------------------------
CREATE INDEX idx_app_user_id   ON business_applications(user_id);
CREATE INDEX idx_app_status    ON business_applications(status);
CREATE INDEX idx_app_number    ON business_applications(application_number);
CREATE INDEX idx_permit_number ON business_permits(permit_number);
CREATE INDEX idx_audit_user    ON audit_logs(user_id);
