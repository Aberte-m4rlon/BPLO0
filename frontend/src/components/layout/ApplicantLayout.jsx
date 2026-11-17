import { useState } from 'react';
import { NavLink, useNavigate } from 'react-router-dom';
import { useAuth } from '../../context/AuthContext';
import toast from 'react-hot-toast';
import {
  MdDashboard, MdListAlt, MdAddBox, MdLogout, MdMenu, MdClose,
  MdBusiness, MdPerson
} from 'react-icons/md';

export default function ApplicantLayout({ children }) {
  const { user, logout } = useAuth();
  const navigate = useNavigate();
  const [sidebarOpen, setSidebarOpen] = useState(false);

  const handleLogout = async () => {
    await logout();
    toast.success('Logged out.');
    navigate('/login');
  };

  return (
    <div className="page-layout">
      {/* Overlay (mobile) */}
      {sidebarOpen && (
        <div
          style={{ position:'fixed',inset:0,background:'rgba(0,0,0,0.4)',zIndex:99 }}
          onClick={() => setSidebarOpen(false)}
        />
      )}

      {/* Sidebar */}
      <aside className={`sidebar ${sidebarOpen ? 'open' : ''}`}>
        <div className="sidebar-logo">
          <div style={{ display:'flex', alignItems:'center', gap:8 }}>
            <MdBusiness size={24} color="#7dd3fc" />
            <div>
              <h2>BPLO Bongabong</h2>
              <p>Oriental Mindoro</p>
            </div>
          </div>
        </div>

        <nav className="sidebar-nav">
          <div className="nav-section-label">Main Menu</div>
          <NavLink to="/dashboard" className={({isActive}) => `nav-item ${isActive?'active':''}`} onClick={() => setSidebarOpen(false)}>
            <MdDashboard size={18}/> Dashboard
          </NavLink>
          <NavLink to="/applications" className={({isActive}) => `nav-item ${isActive?'active':''}`} end onClick={() => setSidebarOpen(false)}>
            <MdListAlt size={18}/> My Applications
          </NavLink>
          <NavLink to="/applications/new" className={({isActive}) => `nav-item ${isActive?'active':''}`} onClick={() => setSidebarOpen(false)}>
            <MdAddBox size={18}/> New Application
          </NavLink>
        </nav>

        <div className="sidebar-footer">
          <div style={{ display:'flex', alignItems:'center', gap:8, marginBottom:10 }}>
            <div style={{ width:32, height:32, borderRadius:'50%', background:'rgba(255,255,255,0.15)', display:'flex', alignItems:'center', justifyContent:'center' }}>
              <MdPerson size={18} color="#fff"/>
            </div>
            <div>
              <div style={{ color:'#fff', fontSize:13, fontWeight:600, lineHeight:1.2 }}>{user?.full_name}</div>
              <div style={{ color:'rgba(255,255,255,0.55)', fontSize:11 }}>Applicant</div>
            </div>
          </div>
          <button className="btn btn-secondary btn-sm" style={{ width:'100%' }} onClick={handleLogout}>
            <MdLogout size={15}/> Logout
          </button>
        </div>
      </aside>

      {/* Main */}
      <div className="main-content">
        <header className="topbar">
          <button className="btn btn-secondary btn-sm" style={{ display:'flex' }} onClick={() => setSidebarOpen(v => !v)} aria-label="Toggle menu">
            {sidebarOpen ? <MdClose size={20}/> : <MdMenu size={20}/>}
          </button>
          <div style={{ fontWeight:700, color:'var(--navy)', fontSize:16 }}>
            Business Permit Application System
          </div>
          <div style={{ color:'var(--gray-500)', fontSize:13 }}>
            Welcome, {user?.full_name}
          </div>
        </header>
        <main className="page-body">{children}</main>
      </div>
    </div>
  );
}
