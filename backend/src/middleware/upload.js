const multer = require('multer');
const path = require('path');
const fs = require('fs');

const UPLOAD_DIR = path.join(__dirname, '..', '..', 'uploads');
if (!fs.existsSync(UPLOAD_DIR)) fs.mkdirSync(UPLOAD_DIR, { recursive: true });

const ALLOWED_TYPES = [
  'image/jpeg', 'image/png', 'image/gif', 'image/webp',
  'application/pdf'
];
const MAX_SIZE_MB = parseInt(process.env.MAX_FILE_SIZE_MB) || 10;

const storage = multer.diskStorage({
  destination: (req, file, cb) => {
    const appDir = path.join(UPLOAD_DIR, `app_${req.params.id || 'tmp'}`);
    if (!fs.existsSync(appDir)) fs.mkdirSync(appDir, { recursive: true });
    cb(null, appDir);
  },
  filename: (req, file, cb) => {
    const ext = path.extname(file.originalname).toLowerCase();
    const safeName = `${Date.now()}_${Math.random().toString(36).substr(2, 8)}${ext}`;
    cb(null, safeName);
  }
});

const fileFilter = (req, file, cb) => {
  if (ALLOWED_TYPES.includes(file.mimetype)) cb(null, true);
  else cb(new Error('Only PDF, JPG, PNG, GIF, and WEBP files are allowed'), false);
};

const upload = multer({
  storage,
  fileFilter,
  limits: { fileSize: MAX_SIZE_MB * 1024 * 1024 }
});

module.exports = upload;
