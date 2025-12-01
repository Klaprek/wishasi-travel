import React from 'react';
import { Link, useParams } from 'react-router-dom';
import useFetch from '../hooks/useFetch';
import { formatTanggalIndo } from '../utils/date';

const DetailPaketPage = () => {
    const { id } = useParams();
    const { data: paket, loading, error } = useFetch(`/api/paket/${id}`);

    if (loading) return <p className="text-slate-600">Memuat detail paket...</p>;
    if (error) return <p className="text-red-600">Gagal memuat paket</p>;
    if (!paket) return null;

    const banner = paket.banner_url || (paket.banner ? `/storage/${paket.banner}` : null);

    return (
        <div className="space-y-8">
            <div className="grid md:grid-cols-2 gap-6">
                <div className="bg-white border border-slate-200 rounded-3xl shadow overflow-hidden">
                    <div
                        className="h-72 bg-gradient-to-br from-indigo-100 to-indigo-200"
                        style={banner ? { backgroundImage: `url(${banner})`, backgroundSize: 'cover', backgroundPosition: 'center' } : {}}
                    />
                    <div className="p-6 space-y-3">
                        <h1 className="text-3xl font-bold text-slate-900">{paket.nama_paket}</h1>
                        <p className="text-slate-600">{paket.destinasi}</p>
                        <div className="flex flex-wrap gap-2 text-xs text-slate-600">
                            <span className="px-3 py-1 rounded-full bg-slate-50 border border-slate-200">
                                Jadwal: {formatTanggalIndo(paket.jadwal_keberangkatan)}
                            </span>
                            <span className="px-3 py-1 rounded-full bg-slate-50 border border-slate-200">
                                Durasi: {paket.durasi ? `${paket.durasi} hari` : '-'}
                            </span>
                            <span className="px-3 py-1 rounded-full bg-slate-50 border border-slate-200">
                                Kuota: {paket.kuota ?? '-'}
                            </span>
                            {paket.wajib_identitas && (
                                <span className="px-3 py-1 rounded-full bg-amber-50 border border-amber-200 text-amber-700">Wajib identitas</span>
                            )}
                            {paket.wajib_paspor && (
                                <span className="px-3 py-1 rounded-full bg-amber-50 border border-amber-200 text-amber-700">Wajib paspor</span>
                            )}
                        </div>
                        <div className="flex items-center justify-between pt-2">
                            <div>
                                <p className="text-sm text-slate-500">Harga per peserta</p>
                                <p className="text-2xl font-bold text-indigo-700">
                                    Rp {Number(paket.harga_per_peserta ?? 0).toLocaleString('id-ID')}
                                </p>
                            </div>
                            <div className="flex gap-2">
                                <Link
                                    to={`/paket/${paket.id}/pesan`}
                                    className="px-4 py-2 bg-indigo-600 text-white rounded-lg font-semibold hover:bg-indigo-700"
                                >
                                    Pesan Sekarang
                                </Link>
                                <Link
                                    to={`/paket/${paket.id}/rating`}
                                    className="px-4 py-2 border border-indigo-200 text-indigo-700 rounded-lg font-semibold hover:bg-indigo-50"
                                >
                                    Beri Rating
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>
                <div className="space-y-4">
                    <div className="bg-white border border-slate-200 rounded-3xl shadow p-6 space-y-3">
                        <h2 className="text-xl font-semibold text-slate-900">Rangkuman</h2>
                        <ul className="text-sm text-slate-600 space-y-2 list-disc list-inside">
                            <li>Pembayaran via Midtrans Snap, aman dan cepat.</li>
                            <li>Isi data peserta setelah pemesanan untuk verifikasi.</li>
                            <li>Hubungi admin jika membutuhkan penyesuaian jadwal.</li>
                        </ul>
                    </div>
                    <div className="bg-white border border-slate-200 rounded-3xl shadow p-6 space-y-3">
                        <h2 className="text-xl font-semibold text-slate-900">Include</h2>
                        <p className="text-sm text-slate-600 whitespace-pre-line">{paket.include}</p>
                    </div>
                </div>
            </div>
        </div>
    );
};

export default DetailPaketPage;
