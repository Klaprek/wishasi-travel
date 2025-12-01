import React from 'react';
import { Navigate, useLocation } from 'react-router-dom';
import { useAuth } from '../providers/AuthProvider';

export default function RequireRole({ roles = [], children }) {
    const { user, loading } = useAuth();
    const location = useLocation();

    if (loading) {
        return (
            <div className="flex items-center justify-center py-20 text-slate-500">
                Memuat sesi...
            </div>
        );
    }

    if (!user) {
        return <Navigate to="/login" state={{ from: location.pathname }} replace />;
    }

    if (roles.length && !roles.includes(user.role)) {
        return <Navigate to="/" replace />;
    }

    return children;
}
