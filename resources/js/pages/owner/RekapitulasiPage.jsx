import React from 'react';
import useFetch from '../../hooks/useFetch';

export default function RekapitulasiPage() {
    const { data: rekap, loading, error } = useFetch('/owner/rekapitulasi');

    return (
        <div className="space-y-6">
            <div>
                <p className="text-sm uppercase tracking-[0.2em] text-indigo-600 font-semibold">Halaman Rekapitulasi</p>
                <h1 className="text-3xl font-bold text-slate-900">Ringkasan pemesanan per paket</h1>
            </div>

            {loading && <p className="text-slate-600">Memuat rekap...</p>}
            {error && <p className="text-red-600">Gagal memuat data</p>}

            <div className="bg-white border border-slate-200 rounded-2xl shadow overflow-hidden">
                <table className="min-w-full divide-y divide-slate-200">
                    <thead className="bg-slate-50 text-left text-xs font-semibold text-slate-500 uppercase tracking-wide">
                        <tr>
                            <th className="px-6 py-3">Paket</th>
                            <th className="px-6 py-3 text-right">Total Pesanan</th>
                        </tr>
                    </thead>
                    <tbody className="divide-y divide-slate-100">
                        {(rekap ?? []).map((row) => (
                            <tr key={row.paket_id} className="hover:bg-slate-50/80">
                                <td className="px-6 py-4">
                                    <p className="font-semibold text-slate-900">{row.paket_tour?.nama_paket}</p>
                                </td>
                                <td className="px-6 py-4 text-right text-slate-700 font-semibold">{row.total}</td>
                            </tr>
                        ))}
                        {!loading && (rekap ?? []).length === 0 && (
                            <tr>
                                <td className="px-6 py-6 text-center text-slate-500" colSpan={2}>
                                    Belum ada data.
                                </td>
                            </tr>
                        )}
                    </tbody>
                </table>
            </div>
        </div>
    );
}
