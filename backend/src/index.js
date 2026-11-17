require('dotenv').config();
const express = require('express');
const cors = require('cors');
const helmet = require('helmet');
const path = require('path');
const session = require('express-session');
const rateLimit = require('express-rate-limit');

const db = require('./config/db');
const authRoutes = require('./routes/auth');
const applicationRoutes = require('./routes/applications');
const adminRoutes = require('./routes/admin');
const permitRoutes = require('./routes/permits');
const verifyRoutes = require('./routes/verify');
const reportRoutes = require('./routes/reports');
const docsRoutes = require('./routes/documents');

const app = express();
const PORT = process.env.PORT || 5000;

// ── Security ──────────────────────────────────────────────
app.use(helmet({ crossOriginResourcePolicy: { policy: 'cross-origin' } }));

app.use(cors({
  origin: process.env.FRONTEND_URL || 'http://localhost:5173',
  credentials: true,
  methods: ['GET','POST','PUT','PATCH','DELETE','OPTIONS'],
  allowedHeaders: ['Content-Type','Authorization']
}));

// ── Rate limiting ─────────────────────────────────────────
const limiter = rateLimit({ windowMs: 15 * 60 * 1000, max: 200 });
app.use(limiter);

const authLimiter = rateLimit({ windowMs: 15 * 60 * 1000, max: 20 });

// ── Body parsing ──────────────────────────────────────────
app.use(express.json({ limit: '10mb' }));
app.use(express.urlencoded({ extended: true, limit: '10mb' }));

// ── Session ───────────────────────────────────────────────
app.use(session({
  secret: process.env.SESSION_SECRET || 'bplo_secret_key_change_me',
  resave: false,
  saveUninitialized: false,
  cookie: {
    secure: process.env.NODE_ENV === 'production',
    httpOnly: true,
    maxAge: parseInt(process.env.SESSION_MAX_AGE) || 86400000
  }
}));

// ── Static uploads ────────────────────────────────────────
app.use('/uploads', express.static(path.join(__dirname, '..', 'uploads')));

// ── Routes ────────────────────────────────────────────────
app.use('/api/auth',         authLimiter, authRoutes);
app.use('/api/applications', applicationRoutes);
app.use('/api/admin',        adminRoutes);
app.use('/api/permits',      permitRoutes);
app.use('/api/verify',       verifyRoutes);
app.use('/api/reports',      reportRoutes);
app.use('/api/documents',    docsRoutes);

// ── Health check ──────────────────────────────────────────
app.get('/api/health', (req, res) => res.json({ status: 'ok', time: new Date() }));

// ── 404 handler ───────────────────────────────────────────
app.use((req, res) => res.status(404).json({ message: 'Route not found' }));

// ── Error handler ─────────────────────────────────────────
app.use((err, req, res, next) => {
  console.error(err.stack);
  res.status(err.status || 500).json({
    message: err.message || 'Internal server error'
  });
});

// ── Start ─────────────────────────────────────────────────
db.getConnection().then(conn => {
  conn.release();
  console.log('✅ MySQL connected');
  app.listen(PORT, () => console.log(`🚀 Backend running on http://localhost:${PORT}`));
}).catch(err => {
  console.error('❌ MySQL connection failed:', err.message);
  process.exit(1);
});
