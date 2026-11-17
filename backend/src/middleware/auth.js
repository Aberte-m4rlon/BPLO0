// Middleware: require authenticated session
function requireAuth(req, res, next) {
  if (!req.session || !req.session.userId) {
    return res.status(401).json({ message: 'Not authenticated. Please log in.' });
  }
  next();
}

// Middleware: require admin role
function requireAdmin(req, res, next) {
  if (!req.session || !req.session.userId) {
    return res.status(401).json({ message: 'Not authenticated.' });
  }
  if (req.session.role !== 'admin') {
    return res.status(403).json({ message: 'Access denied. Admins only.' });
  }
  next();
}

// Middleware: require applicant role
function requireApplicant(req, res, next) {
  if (!req.session || !req.session.userId) {
    return res.status(401).json({ message: 'Not authenticated.' });
  }
  if (req.session.role !== 'applicant') {
    return res.status(403).json({ message: 'Access denied.' });
  }
  next();
}

module.exports = { requireAuth, requireAdmin, requireApplicant };
