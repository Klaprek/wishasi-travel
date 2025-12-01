import React, { useEffect, useState } from 'react';
import { useParams } from 'react-router-dom';
import api from '../lib/api';
import useFetch from '../hooks/useFetch';

const SNAP_URL = 'https://app.sandbox.midtrans.com/snap/snap.js';

async function loadSnap(clientKey) {
    if (window.snap) return;
    await new Promise((resolve, reject) => {
        const script = document.createElement('script');
        script.src = `${SNAP_URL}?client-key=${clientKey}`;
        script.async = true;
        script.onload = resolve;
        script.onerror = reject;
        document.body.appendChild(script);
    });
}

export default function PaymentMethodPage() {
    const { orderId } = useParams();
    const { data: pesanan, loading } = useFetch(`/pesanan/${orderId}/peserta`);
    const [error, setError] = useState(null);
    const [processing, setProcessing] = useState(false);
    const [clientKey, setClientKey] = useState(
        document.head.querySelector('meta[name="midtrans-client-key"]')?.content ?? ''
    );

    useEffect(() => {
        if (clientKey) {
            loadSnap(clientKey).catch(() => setError('Gagal memuat Midtrans Snap'));
        }
    }, [clientKey]);

    const confirmStatus = async (status) => {
        try {
            await api.post(`/payments/${orderId}/confirm`, {
                status_pesanan: status === 'success' ? 'pembayaran_selesai' : 'menunggu_pembayaran',
                transaction_status: status,
            });
        } catch (err) {
            console.error(err);
        }
    };

    const startPayment = async () => {
        setProcessing(true);
        setError(null);
        try {
            const response = await api.post(`/payments/${orderId}/snap-token`);
            const token = response.data.token;
            const key = response.data.client_key || clientKey;
            if (key && !window.snap) {
                setClientKey(key);
                await loadSnap(key);
            }
            if (!window.snap) {
                throw new Error('Snap belum tersedia');
            }
            window.snap.pay(token, {
                onSuccess: () => confirmStatus('success'),
                onPending: () => confirmStatus('pending'),
                onError: () => setError('Terjadi kesalahan pada pembayaran'),
                onClose: () => setProcessing(false),
            });
        } catch (err) {
            setError(err.response?.data?.message || err.message || 'Gagal memulai pembayaran');
        } finally {
            setProcessing(false);
        }
    };

    if (loading) return <p className="text-slate-600">Memuat pembayaran...</p>;

    return (
        <div className="max-w-2xl mx-auto space-y-6">
            <div className="bg-white border border-slate-200 rounded-3xl shadow p-6 space-y-3">
                <p className="text-sm uppercase tracking-[0.2em] text-indigo-600 font-semibold">Halaman Metode Pembayaran</p>
                <h1 className="text-3xl font-bold text-slate-900">Pesanan #{pesanan?.id}</h1>
                <p className="text-slate-600">Pilih metode pembayaran Midtrans untuk paket {pesanan?.paket_tour?.nama_paket}.</p>
                {error && <div className="text-sm text-red-600 bg-red-50 border border-red-100 rounded-xl px-4 py-3">{error}</div>}
                <button
                    onClick={startPayment}
                    disabled={processing || !clientKey}
                    className="px-4 py-3 bg-indigo-600 text-white rounded-lg font-semibold hover:bg-indigo-700 disabled:opacity-60"
                >
                    {processing ? 'Menghubungkan Snap...' : 'Bayar dengan Midtrans'}
                </button>
                {!clientKey && (
                    <p className="text-sm text-amber-600 bg-amber-50 border border-amber-200 rounded-xl px-4 py-3">
                        Client key Midtrans belum diisi. Setel variabel MIDTRANS_CLIENT_KEY untuk mengaktifkan Snap.
                    </p>
                )}
            </div>
        </div>
    );
}
