import { Routes, Route, Navigate } from 'react-router-dom';
import { AuthProvider, useAuth } from './context/AuthContext';

// Auth pages
import LoginPage    from './pages/auth/LoginPage';
import RegisterPage from './pages/auth/RegisterPage';

// Applicant pages
import DashboardPage       from './pages/applicant/DashboardPage';
import NewApplicationPage  from './pages/applicant/NewApplicationPage';
import ApplicationListPage from './pages/applicant/ApplicationListPage';
import ApplicationDetail   from './pages/applicant/ApplicationDetailPage';
import PermitViewPage      from './pages/applicant/PermitViewPage';

// Admin pages
import AdminDashboard      from './pages/admin/AdminDashboard';
import AdminApplications   from './pages/admin/AdminApplications';
import AdminApplicationDetail from './pages/admin/AdminApplicationDetail';
import AdminPermits        from './pages/admin/AdminPermits';
import AdminReports        from './pages/admin/AdminReports';
import AdminDocRequirements from './pages/admin/AdminDocRequirements';
import AuditLogsPage       from './pages/admin/AuditLogsPage';

// Public
import VerifyPage from './pages/public/VerifyPage';

function ProtectedRoute({ children, role }) {
  const { user, loading } = useAuth();
  if (loading) return <div className="loading-center"><div className="spinner"/></div>;
  if (!user) return <Navigate to="/login" replace />;
  if (role && user.role !== role) return <Navigate to={user.role === 'admin' ? '/admin' : '/dashboard'} replace />;
  return children;
}

function GuestRoute({ children }) {
  const { user, loading } = useAuth();
  if (loading) return <div className="loading-center"><div className="spinner"/></div>;
  if (user) return <Navigate to={user.role === 'admin' ? '/admin' : '/dashboard'} replace />;
  return children;
}

function AppRoutes() {
  return (
    <Routes>
      {/* Root redirect */}
      <Route path="/" element={<RootRedirect />} />

      {/* Auth */}
      <Route path="/login"    element={<GuestRoute><LoginPage /></GuestRoute>} />
      <Route path="/register" element={<GuestRoute><RegisterPage /></GuestRoute>} />

      {/* Public QR verify */}
      <Route path="/verify/:permitNumber" element={<VerifyPage />} />

      {/* Applicant */}
      <Route path="/dashboard"        element={<ProtectedRoute role="applicant"><DashboardPage /></ProtectedRoute>} />
      <Route path="/applications"     element={<ProtectedRoute role="applicant"><ApplicationListPage /></ProtectedRoute>} />
      <Route path="/applications/new" element={<ProtectedRoute role="applicant"><NewApplicationPage /></ProtectedRoute>} />
      <Route path="/applications/:id" element={<ProtectedRoute role="applicant"><ApplicationDetail /></ProtectedRoute>} />
      <Route path="/permits/:appId"   element={<ProtectedRoute role="applicant"><PermitViewPage /></ProtectedRoute>} />

      {/* Admin */}
      <Route path="/admin"                    element={<ProtectedRoute role="admin"><AdminDashboard /></ProtectedRoute>} />
      <Route path="/admin/applications"       element={<ProtectedRoute role="admin"><AdminApplications /></ProtectedRoute>} />
      <Route path="/admin/applications/:id"   element={<ProtectedRoute role="admin"><AdminApplicationDetail /></ProtectedRoute>} />
      <Route path="/admin/permits"            element={<ProtectedRoute role="admin"><AdminPermits /></ProtectedRoute>} />
      <Route path="/admin/reports"            element={<ProtectedRoute role="admin"><AdminReports /></ProtectedRoute>} />
      <Route path="/admin/doc-requirements"   element={<ProtectedRoute role="admin"><AdminDocRequirements /></ProtectedRoute>} />
      <Route path="/admin/audit-logs"         element={<ProtectedRoute role="admin"><AuditLogsPage /></ProtectedRoute>} />

      <Route path="*" element={<Navigate to="/" replace />} />
    </Routes>
  );
}

function RootRedirect() {
  const { user, loading } = useAuth();
  if (loading) return <div className="loading-center"><div className="spinner"/></div>;
  if (!user) return <Navigate to="/login" replace />;
  return <Navigate to={user.role === 'admin' ? '/admin' : '/dashboard'} replace />;
}

export default function App() {
  return (
    <AuthProvider>
      <AppRoutes />
    </AuthProvider>
  );
}
