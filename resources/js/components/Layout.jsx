import React, { useEffect, useRef, useState } from 'react';
import { Link, NavLink, Outlet, useLocation, useNavigate } from 'react-router-dom';
import { useAuth } from '../providers/AuthProvider';

function NavItem({ to, label, end = false, isActiveOverride = false }) {
    return (
        <NavLink
            to={to}
            end={end}
            className={({ isActive }) => {
                const active = isActive || isActiveOverride;
                return `px-3 py-2 text-sm font-semibold transition ${
                    active ? 'text-purple-700' : 'text-slate-700/70 hover:text-purple-700'
                }`;
            }}
        >
            {label}
        </NavLink>
    );
}

function AnchorNavItem({ label, hash, isActive, onClick }) {
    return (
        <Link
            to={`/#${hash}`}
            onClick={(e) => onClick?.(e, hash)}
            className={`px-3 py-2 text-sm font-semibold transition ${
                isActive ? 'text-purple-700' : 'text-slate-700/70 hover:text-purple-700'
            }`}
        >
            {label}
        </Link>
    );
}

const ANCHOR_SECTION_IDS = ['home', 'katalog', 'rating', 'contact'];

export default function Layout() {
    const { user, logout } = useAuth();
    const isAdmin = user?.role === 'admin';
    const isOwner = user?.role === 'owner';
    const isAdminOrOwner = isAdmin || isOwner;
    const isCustomer = user?.role === 'customer';
    const location = useLocation();
    const navigate = useNavigate();
    const isLoginPage = location.pathname === '/login';
    const isHomePage = location.pathname === '/';
    const [activeSection, setActiveSection] = useState(() => {
        if (!isHomePage) return '';
        return location.hash?.replace('#', '') || 'home';
    });
    const isHomeActive = isHomePage && activeSection === 'home';
    const [openUserMenu, setOpenUserMenu] = useState(false);
    const [mobileOpen, setMobileOpen] = useState(false);
    const [isWide, setIsWide] = useState(typeof window !== 'undefined' ? window.innerWidth > 840 : true);
    const userMenuRef = useRef(null);

    const customerAnchorLinks = [
        { hash: 'katalog', label: 'Katalog' },
        { hash: 'rating', label: 'Rating' },
        { hash: 'contact', label: 'Contact Us' },
    ];

    const scrollToSection = (hash) => {
        const id = hash.replace('#', '');
        if (typeof document === 'undefined') return;
        const el = document.getElementById(id);
        if (!el) return;
        const header = document.querySelector('header');
        const offset = header?.offsetHeight ?? 0;
        const top = el.getBoundingClientRect().top + window.scrollY - offset;
        window.scrollTo({ top, behavior: 'smooth' });
    };

    const handleHomeClick = (e) => {
        e.preventDefault();
        navigate('/#home');
        requestAnimationFrame(() => scrollToSection('home'));
    };

    const handleAnchorClick = (e, hash) => {
        e.preventDefault();
        const target = `/#${hash}`;
        navigate(target);
        requestAnimationFrame(() => scrollToSection(hash));
    };

    useEffect(() => {
        if (!isHomePage || !location.hash) return;
        setActiveSection(location.hash.replace('#', ''));
        scrollToSection(location.hash);
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [isHomePage, location.hash]);

    const isSectionActive = (hash) => isHomePage && activeSection === hash;

    useEffect(() => {
        if (!isHomePage) {
            setActiveSection('');
            return;
        }
        let ticking = false;

        const updateActiveSection = () => {
            if (typeof document === 'undefined') return;
            const header = document.querySelector('header');
            const offset = header?.offsetHeight ?? 0;
            const scrollPosition = window.scrollY + offset + 4;
            let current = ANCHOR_SECTION_IDS[0];

            for (const id of ANCHOR_SECTION_IDS) {
                const el = document.getElementById(id);
                if (!el) continue;
                const top = el.getBoundingClientRect().top + window.scrollY;
                if (top <= scrollPosition) {
                    current = id;
                } else {
                    break;
                }
            }

            setActiveSection((prev) => (prev === current ? prev : current));
        };

        const handleScroll = () => {
            if (ticking) return;
            ticking = true;
            requestAnimationFrame(() => {
                ticking = false;
                updateActiveSection();
            });
        };

        updateActiveSection();
        window.addEventListener('scroll', handleScroll, { passive: true });
        window.addEventListener('resize', updateActiveSection);
        return () => {
            window.removeEventListener('scroll', handleScroll);
            window.removeEventListener('resize', updateActiveSection);
        };
    }, [isHomePage]);

    useEffect(() => {
        const handleClickOutside = (event) => {
            if (userMenuRef.current && !userMenuRef.current.contains(event.target)) {
                setOpenUserMenu(false);
            }
        };
        document.addEventListener('mousedown', handleClickOutside);
        return () => document.removeEventListener('mousedown', handleClickOutside);
    }, []);

    useEffect(() => {
        const handleResize = () => {
            setIsWide(window.innerWidth > 840);
        };
        handleResize();
        window.addEventListener('resize', handleResize);
        return () => window.removeEventListener('resize', handleResize);
    }, []);

    useEffect(() => {
        setMobileOpen(false);
        setOpenUserMenu(false);
    }, [location.pathname, location.hash]);

    const navLinks = (
        <>
            {!isAdminOrOwner && (
                <>
                    <AnchorNavItem hash="home" label="Home" isActive={isHomeActive} onClick={handleHomeClick} />
                    {customerAnchorLinks.map((link) => (
                        <AnchorNavItem
                            key={link.hash}
                            hash={link.hash}
                            label={link.label}
                            isActive={isSectionActive(link.hash)}
                            onClick={handleAnchorClick}
                        />
                    ))}
                </>
            )}
            {isCustomer && (
                <NavItem
                    to="/pesanan-saya"
                    label="Pesanan Saya"
                    isActiveOverride={
                        location.pathname.startsWith('/pesanan/') ||
                        location.pathname.startsWith('/pembayaran/')
                    }
                />
            )}
            {isAdmin && (
                <>
                    <NavItem to="/admin/paket" label="Kelola Paket" />
                    <NavItem to="/admin/pesanan" label="Kelola Pesanan" />
                </>
            )}
            {isOwner && <NavItem to="/owner/rekapitulasi" label="Rekap" />}
            {isAdminOrOwner && <NavItem to="/profil" label="Profil" />}
        </>
    );

    return (
        <div className="min-h-screen bg-gradient-to-br from-slate-50 via-white to-slate-100">
            <header className="bg-white border-b border-slate-200 sticky top-0 z-40">
                <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-3 flex items-center justify-between gap-6">
                    <Link to="/#home" onClick={handleHomeClick} className="flex items-center gap-3">
                        <img src="/images/logo.webp" alt="Wishasi" className="h-12 w-12 object-contain" />
                        <div className="leading-tight">
                            <p className="text-xl font-bold text-purple-900">Wishasi</p>
                            <p className="ps-2 text-sm text-purple-800">Tour and Travel</p>
                        </div>
                    </Link>

                    {!isLoginPage && isWide && (
                        <nav className="flex flex-wrap items-center justify-center gap-4 md:gap-6 lg:gap-8">
                            {navLinks}
                        </nav>
                    )}

                    <div className="flex items-center gap-3">
                        {!isLoginPage && !isWide && (
                            <button
                                type="button"
                                onClick={() => setMobileOpen((prev) => !prev)}
                                className="p-2 rounded-md border border-slate-200 text-slate-700 hover:border-purple-200 hover:text-purple-700 transition"
                                aria-label="Toggle navigation"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" className="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M4 6h16M4 12h16M4 18h16" />
                                </svg>
                            </button>
                        )}
                        {user ? (
                            <div className="relative" ref={userMenuRef}>
                                <button
                                    onClick={() => setOpenUserMenu((prev) => !prev)}
                                    className="flex items-center gap-2 text-left"
                                >
                                    <div className="hidden sm:flex flex-col text-right">
                                        <span className="text-sm font-semibold text-slate-900">{user.name}</span>
                                        <span className="text-xs text-slate-500 capitalize">{user.role}</span>
                                    </div>
                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        strokeWidth="2"
                                        className="h-4 w-4 text-slate-600"
                                    >
                                        <path d="M6 9l6 6 6-6" />
                                    </svg>
                                </button>
                                {openUserMenu && (
                                    <div className="absolute right-0 mt-2 w-40 rounded-lg border border-slate-200 bg-white shadow-md">
                                        <button
                                            onClick={() => {
                                                setOpenUserMenu(false);
                                                logout();
                                            }}
                                            className="w-full px-4 py-2 text-sm text-left text-slate-700 hover:bg-slate-50"
                                        >
                                            Logout
                                        </button>
                                    </div>
                                )}
                            </div>
                        ) : (
                            !isLoginPage && (
                                <Link to="/login" className="text-sm font-semibold text-slate-700 hover:text-purple-700">
                                    Login
                                </Link>
                            )
                        )}
                    </div>
                </div>
                {!isLoginPage && !isWide && mobileOpen && (
                    <div className="border-t border-slate-200 bg-white">
                        <div className="max-w-7xl mx-auto px-4 py-3 flex flex-col gap-2">{navLinks}</div>
                    </div>
                )}
            </header>

            <main className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pb-0">
                <Outlet />
            </main>
        </div>
    );
}
