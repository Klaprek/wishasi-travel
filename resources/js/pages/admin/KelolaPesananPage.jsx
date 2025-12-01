import React from 'react';
import { Link } from 'react-router-dom';
import useFetch from '../../hooks/useFetch';

export default function KelolaPesananPage() {
    const { data: pesanan, loading, error } = useFetch('/admin/pesanan');

    return (
        <div className="space-y-6">
            <div>
                <p className="text-sm uppercase tracking-[0.2em] text-indigo-600 font-semibold">Halaman Kelola Pesanan</p>
                <h1 className="text-3xl font-bold text-slate-900">Monitor pesanan customer</h1>
            </div>

            {loading && <p className="text-slate-600">Memuat pesanan...</p>}
            {error && <p className="text-red-600">Gagal memuat data</p>}

            <div className="bg-white border border-slate-200 shadow rounded-2xl overflow-hidden">
                <table className="min-w-full divide-y divide-slate-200">
                    <thead className="bg-slate-50 text-left text-xs font-semibold text-slate-500 uppercase tracking-wide">
                        <tr>
                            <th className="px-6 py-3">Pesanan</th>
                            <th className="px-6 py-3">Customer</th>
                            <th className="px-6 py-3">Status</th>
                            <th className="px-6 py-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody className="divide-y divide-slate-100">
                        {(pesanan ?? []).map((item) => (
                            <tr key={item.id} className="hover:bg-slate-50/80">
                                <td className="px-6 py-4">
                                    <p className="font-semibold text-slate-900">#{item.id}</p>
                                    <p className="text-sm text-slate-500">{item.paket_tour?.nama_paket}</p>
                                </td>
                                <td className="px-6 py-4 text-sm text-slate-700">{item.user?.name}</td>
                                <td className="px-6 py-4">
                                    <span className="px-3 py-1 rounded-full bg-slate-100 text-slate-700 text-xs font-semibold">
                                        {item.status_pesanan?.replaceAll('_', ' ')}
                                    </span>
                                </td>
                                <td className="px-6 py-4 text-right">
                                    <Link
                                        to={`/admin/pesanan/${item.id}`}
                                        className="px-3 py-2 text-sm font-semibold text-indigo-700 bg-indigo-50 rounded-lg hover:bg-indigo-100"
                                    >
                                        Detail
                                    </Link>
                                </td>
                            </tr>
                        ))}
                        {!loading && (pesanan ?? []).length === 0 && (
                            <tr>
                                <td className="px-6 py-6 text-center text-slate-500" colSpan={4}>
                                    Belum ada pesanan.
                                </td>
                            </tr>
                        )}
                    </tbody>
                </table>
            </div>
        </div>
    );
}
