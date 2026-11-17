const express = require('express');
const path = require('path');
const fs = require('fs');
const db = require('../config/db');
const { requireAuth } = require('../middleware/auth');
const upload = require('../middleware/upload');
const { logAudit } = require('../utils/auditLog');

const router = express.Router();
router.use(requireAuth);

// GET document requirements
router.get('/requirements', async (req, res) => {
  try {
    const appType = req.query.type || 'Both';
    const [rows] = await db.execute(
      `SELECT * FROM document_requirements
       WHERE is_active=1 AND (applies_to=? OR applies_to='Both')
       ORDER BY sort_order`,
      [appType]
    );
    res.json(rows);
  } catch (err) {
    res.status(500).json({ message: 'Failed to fetch requirements.' });
  }
});

// POST /api/documents/upload/:id  — upload document to application
router.post('/upload/:id', upload.single('file'), async (req, res) => {
  try {
    const [rows] = await db.execute(
      'SELECT id, user_id, status FROM business_applications WHERE id = ?',
      [req.params.id]
    );
    if (!rows.length) return res.status(404).json({ message: 'Application not found.' });
    const app = rows[0];
    if (app.user_id !== req.session.userId && req.session.role !== 'admin')
      return res.status(403).json({ message: 'Access denied.' });
    if (!['Draft','For Correction'].includes(app.status))
      return res.status(400).json({ message: 'Cannot upload documents in current status.' });

    if (!req.file) return res.status(400).json({ message: 'No file uploaded.' });

    const docType = req.body.document_type || 'Supporting Document';
    const filePath = req.file.path.replace(/\\/g, '/');
    const relPath = filePath.split('uploads/')[1];

    const [result] = await db.execute(
      `INSERT INTO application_documents
         (application_id, document_type, file_name, file_path, file_size, mime_type)
       VALUES (?,?,?,?,?,?)`,
      [req.params.id, docType, req.file.originalname, relPath,
       req.file.size, req.file.mimetype]
    );

    await logAudit(req.session.userId, 'DOCUMENT_UPLOADED', 'application_documents',
      result.insertId, `Uploaded: ${docType} for App ID ${req.params.id}`, req.ip);

    res.status(201).json({
      message: 'Document uploaded.',
      document: {
        id: result.insertId,
        document_type: docType,
        file_name: req.file.originalname,
        file_path: relPath
      }
    });
  } catch (err) {
    console.error(err);
    res.status(500).json({ message: err.message || 'Upload failed.' });
  }
});

// DELETE /api/documents/:docId
router.delete('/:docId', async (req, res) => {
  try {
    const [rows] = await db.execute(
      `SELECT d.*, a.user_id, a.status
       FROM application_documents d
       JOIN business_applications a ON a.id = d.application_id
       WHERE d.id = ?`,
      [req.params.docId]
    );
    if (!rows.length) return res.status(404).json({ message: 'Document not found.' });
    const doc = rows[0];
    if (doc.user_id !== req.session.userId && req.session.role !== 'admin')
      return res.status(403).json({ message: 'Access denied.' });
    if (!['Draft','For Correction'].includes(doc.status))
      return res.status(400).json({ message: 'Cannot delete documents in current status.' });

    // Delete physical file
    const fullPath = path.join(__dirname, '..', '..', 'uploads', doc.file_path);
    if (fs.existsSync(fullPath)) fs.unlinkSync(fullPath);

    await db.execute('DELETE FROM application_documents WHERE id = ?', [req.params.docId]);
    res.json({ message: 'Document deleted.' });
  } catch (err) {
    console.error(err);
    res.status(500).json({ message: 'Failed to delete document.' });
  }
});

module.exports = router;
