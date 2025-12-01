import React from 'react';
import { Link } from 'react-router-dom';
import useFetch from '../hooks/useFetch';

const BADGES = {
    menunggu_verifikasi: 'bg-amber-100 text-amber-800 border-amber-200',
    menunggu_pembayaran: 'bg-indigo-100 text-indigo-800 border-indigo-200',
    pembayaran_selesai: 'bg-emerald-100 text-emerald-800 border-emerald-200',
    pesanan_selesai: 'bg-emerald-100 text-emerald-800 border-emerald-200',
};

export default function PesananSayaPage() {
    const { data: pesanan, loading, error, refetch } = useFetch('/pesanan-saya');

    return (
        <div className="space-y-6">
            <div className="bg-white border border-slate-200 shadow rounded-3xl p-6">
                <p className="text-sm uppercase tracking-[0.2em] text-indigo-600 font-semibold">Pesanan Saya</p>
                <h1 className="text-3xl font-bold text-slate-900">Pantau semua perjalananmu</h1>
                <p className="text-slate-600">Isi data peserta, lakukan pembayaran, dan cek status verifikasi.</p>
            </div>

            {loading && <p className="text-slate-600">Memuat pesanan...</p>}
            {error && (
                <div className="text-sm text-red-600 bg-red-50 border border-red-100 rounded-xl px-4 py-3">
                    Gagal memuat pesanan
                    <button onClick={refetch} className="ml-3 text-indigo-700 font-semibold">
                        Coba lagi
                    </button>
                </div>
            )}

            <div className="space-y-4">
                {(pesanan ?? []).map((order) => {
                    const badge = BADGES[order.status_pesanan] ?? 'bg-slate-100 text-slate-700 border-slate-200';
                    return (
                        <div key={order.id} className="bg-white border border-slate-200 rounded-2xl shadow-sm p-6 space-y-4">
                            <div className="flex flex-col md:flex-row md:items-start md:justify-between gap-4">
                                <div className="space-y-1">
                                    <p className="text-sm text-slate-500">Paket</p>
                                    <h2 className="text-xl font-semibold text-slate-900">{order.paket_tour?.nama_paket}</h2>
                                    <p className="text-sm text-slate-600">Jumlah peserta: {order.jumlah_peserta}</p>
                                </div>
                                <span className={`px-3 py-1 rounded-full border text-xs font-semibold ${badge}`}>
                                    {order.status_pesanan?.replaceAll('_', ' ')}
                                </span>
                            </div>
                            <div className="grid md:grid-cols-3 gap-3">
                                <div className="border border-slate-200 rounded-xl p-4 bg-slate-50">
                                    <p className="text-xs text-slate-500 uppercase">Data Peserta</p>
                                    <div className="flex gap-2 mt-2 flex-wrap">
                                        <Link
                                            to={`/pesanan/${order.id}/data-peserta`}
                                            className="px-3 py-2 bg-indigo-600 text-white rounded-lg text-sm font-semibold hover:bg-indigo-700"
                                        >
                                            Lengkapi Peserta
                                        </Link>
                                        <Link
                                            to={`/paket/${order.paket_id}`}
                                            className="px-3 py-2 border border-indigo-200 text-indigo-700 rounded-lg text-sm font-semibold hover:bg-indigo-50"
                                        >
                                            Detail Paket
                                        </Link>
                                    </div>
                                </div>
                                <div className="border border-slate-200 rounded-xl p-4 bg-slate-50">
                                    <p className="text-xs text-slate-500 uppercase">Pembayaran</p>
                                    <p className="font-semibold text-slate-800 mt-2">
                                        Total estimasi: Rp{' '}
                                        {(
                                            Number(order.paket_tour?.harga_per_peserta ?? 0) * (order.jumlah_peserta ?? 1)
                                        ).toLocaleString('id-ID')}
                                    </p>
                                    <Link
                                        to={`/pembayaran/${order.id}/metode`}
                                        className="mt-2 inline-flex px-3 py-2 bg-white border border-indigo-200 text-indigo-700 rounded-lg text-sm font-semibold hover:bg-indigo-50"
                                    >
                                        Pilih Metode
                                    </Link>
                                </div>
                                <div className="border border-slate-200 rounded-xl p-4 bg-slate-50">
                                    <p className="text-xs text-slate-500 uppercase">Rating</p>
                                    <p className="text-sm text-slate-600">Berikan ulasan setelah perjalanan selesai.</p>
                                    <Link
                                        to={`/paket/${order.paket_id}/rating`}
                                        className="mt-2 inline-flex px-3 py-2 bg-white border border-slate-200 text-slate-700 rounded-lg text-sm font-semibold hover:bg-slate-50"
                                    >
                                        Tulis Ulasan
                                    </Link>
                                </div>
                            </div>
                        </div>
                    );
                })}

                {!loading && (pesanan ?? []).length === 0 && (
                    <div className="bg-white border border-dashed border-slate-200 rounded-2xl p-10 text-center text-slate-500">
                        Belum ada pesanan. Mulai dengan memilih paket di katalog.
                    </div>
                )}
            </div>
        </div>
    );
}
