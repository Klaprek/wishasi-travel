import React from 'react';
import { Link } from 'react-router-dom';

export default function StatusScreen({ title, description, hint, actionLabel, actionHref }) {
    return (
        <div className="max-w-3xl mx-auto space-y-6">
            <div className="rounded-3xl bg-white shadow border border-slate-200 p-8 space-y-4">
                <div className="inline-flex px-3 py-1 rounded-full bg-indigo-50 text-indigo-700 text-xs font-semibold">
                    Status Pemesanan
                </div>
                <h1 className="text-3xl font-bold text-slate-900">{title}</h1>
                <p className="text-slate-600">{description}</p>
                {hint && <div className="text-sm text-slate-500 bg-slate-50 border border-slate-200 rounded-xl px-4 py-3">{hint}</div>}
                {actionHref && (
                    <div className="pt-2">
                        <Link
                            to={actionHref}
                            className="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white rounded-lg font-semibold hover:bg-indigo-700"
                        >
                            {actionLabel ?? 'Kembali'}
                        </Link>
                    </div>
                )}
            </div>
        </div>
    );
}
