import React, { useState } from 'react';
import { useNavigate, useParams } from 'react-router-dom';
import api from '../lib/api';
import useFetch from '../hooks/useFetch';

const HalamanFormPemesanan = () => {
    const { id } = useParams();
    const navigate = useNavigate();
    const { data: paket } = useFetch(`/api/paket/${id}`);
    const [jumlahPeserta, setJumlahPeserta] = useState('1');
    const [loading, setLoading] = useState(false);
    const [error, setError] = useState(null);

    const submit = async (e) => {
        e.preventDefault();
        const jumlahParsed = Number(jumlahPeserta);
        if (!jumlahPeserta || Number.isNaN(jumlahParsed) || jumlahParsed < 1) {
            setError('Jumlah peserta minimal 1');
            return;
        }
        setLoading(true);
        setError(null);
        try {
            await api.post(`/pesan/${id}`, { jumlah_peserta: jumlahParsed });
            navigate('/pesanan-saya');
        } catch (err) {
            setError(err.response?.data?.message || 'Gagal membuat pesanan');
        } finally {
            setLoading(false);
        }
    };

    const closeModal = () => navigate(`/paket/${id}`);

    return (
        <div
            className="fixed inset-0 z-40 flex items-center justify-center bg-slate-900/50 backdrop-blur-sm px-4 py-8"
            role="dialog"
            aria-modal="true"
            onClick={closeModal}
        >
            <div
                className="relative w-full max-w-2xl bg-white border border-slate-200 rounded-2xl shadow-xl p-6 sm:p-7 space-y-5"
                onClick={(e) => e.stopPropagation()}
            >
                <button
                    type="button"
                    onClick={closeModal}
                    className="absolute right-3 top-3 inline-flex h-9 w-9 items-center justify-center rounded-full bg-slate-100 text-slate-500 hover:bg-slate-200 hover:text-slate-700"
                    aria-label="Tutup"
                >
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        strokeWidth="1.8"
                        className="h-4 w-4"
                    >
                        <path strokeLinecap="round" strokeLinejoin="round" d="M6 6l12 12M18 6L6 18" />
                    </svg>
                </button>

                <div>
                    <p className="text-xs uppercase tracking-[0.2em] text-indigo-600 font-semibold">Form Pemesanan</p>
                    <h1 className="text-2xl font-bold text-slate-900">Pesan paket {paket?.nama_paket ?? ''}</h1>
                    <p className="text-slate-600">Isi jumlah peserta untuk melanjutkan.</p>
                </div>

                {error && <div className="text-sm text-red-600 bg-red-50 border border-red-100 rounded-xl px-4 py-3">{error}</div>}

                <form className="space-y-4" onSubmit={submit}>
                    <div>
                        <label className="text-sm font-semibold text-slate-700">Jumlah Peserta</label>
                        <input
                        type="number"
                        min={1}
                        value={jumlahPeserta}
                        onChange={(e) => setJumlahPeserta(e.target.value)}
                            className="mt-1 w-full border border-slate-200 rounded-lg px-3 py-2 focus:border-indigo-500 focus:ring-indigo-500"
                        />
                    </div>
                    <div className="flex items-center gap-3">
                        <button
                            type="button"
                            onClick={closeModal}
                            className="px-4 py-2 border border-slate-200 text-slate-700 rounded-lg font-semibold hover:bg-slate-50"
                        >
                            Batal
                        </button>
                        <button
                            type="submit"
                            disabled={loading}
                            className="px-5 py-2.5 bg-indigo-600 text-white rounded-lg font-semibold hover:bg-indigo-700 disabled:opacity-60"
                        >
                            {loading ? 'Memproses...' : 'Buat Pesanan'}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    );
};

export default HalamanFormPemesanan;
