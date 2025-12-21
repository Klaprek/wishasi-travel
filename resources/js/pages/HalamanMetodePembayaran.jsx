import React, { useState } from 'react';
import { useNavigate, useParams } from 'react-router-dom';
import api from '../lib/api';
import useFetch from '../hooks/useFetch';

const VA_METHODS = [
    { code: 'bca', name: 'BCA', color: '#0056a3' },
    { code: 'mandiri', name: 'Mandiri', color: '#f9b000' },
    { code: 'bni', name: 'BNI', color: '#006f61' },
    { code: 'bri', name: 'BRI', color: '#00529c' },
    { code: 'permata', name: 'Permata', color: '#007f5f' },
    { code: 'cimb', name: 'CIMB Niaga', color: '#a30034' },
    { code: 'bsi', name: 'BSI', color: '#0d9488' },
];

export default function HalamanMetodePembayaran() {
    const { orderId } = useParams();
    const navigate = useNavigate();
    const { data: pesanan, loading } = useFetch(`/pesanan/${orderId}/peserta`);
    const [error, setError] = useState(null);
    const [processing, setProcessing] = useState(false);
    const [selectedVa, setSelectedVa] = useState(VA_METHODS[0]?.code ?? 'bca');
    const [vaInfo, setVaInfo] = useState(null);
    const [paymentRequested, setPaymentRequested] = useState(false);
    const [paymentSuccess, setPaymentSuccess] = useState(false);
    const [transactionStatus, setTransactionStatus] = useState(null);
    const [midtransOrderId, setMidtransOrderId] = useState(null);

    const extractVaInfo = (data) => {
        const info = {};

        const vaNumber =
            data?.va_numbers?.[0]?.va_number ||
            data?.permata_va_number ||
            data?.bca_va_number ||
            data?.va_number ||
            data?.virtual_account_number ||
            data?.bill_key;

        if (vaNumber) info.vaNumber = vaNumber;

        const bank = data?.va_numbers?.[0]?.bank || data?.bank || selectedVa;
        if (bank) info.bank = bank;

        const companyCode = data?.biller_code || data?.company_code || data?.bill_key || null;
        if (companyCode) info.companyCode = companyCode;

        const amount = data?.gross_amount || data?.jumlah_pembayaran || data?.amount || null;
        if (amount) info.amount = amount;

        return info;
    };

    const startVaPayment = async () => {
        setProcessing(true);
        setError(null);
        setPaymentSuccess(false);
        setPaymentRequested(true);
        try {
            const response = await api.post(`/payments/${orderId}/snap-token`, {
                payment_type: 'bank_transfer',
                bank: selectedVa,
            });
            const info = extractVaInfo(response.data);
            setVaInfo(info);
            setMidtransOrderId(response.data.order_id || null);
            setTransactionStatus(response.data.transaction_status ?? 'pending');
        } catch (err) {
            setError(err.response?.data?.message || 'Gagal memulai pembayaran');
        } finally {
            setProcessing(false);
        }
    };

    const cekStatusPembayaran = async () => {
        if (!midtransOrderId) return;
        setProcessing(true);
        setError(null);
        try {
            const res = await api.get(`/payments/${orderId}/status`, { params: { order_id: midtransOrderId } });
            setTransactionStatus(res.data.transaction_status);
            const info = extractVaInfo(res.data);
            setVaInfo((prev) => {
                const next = { ...prev };
                if (info.vaNumber) next.vaNumber = info.vaNumber;
                if (info.bank) next.bank = info.bank;
                if (info.companyCode) next.companyCode = info.companyCode;
                if (info.amount) next.amount = info.amount;
                return next;
            });
            if (['settlement', 'capture'].includes(res.data.transaction_status)) {
                setPaymentSuccess(true);
            }
        } catch (err) {
            setError(err.response?.data?.message || 'Gagal mengambil status pembayaran');
        } finally {
            setProcessing(false);
        }
    };

    if (loading) return <p className="text-slate-600">Memuat pembayaran...</p>;

    const tampilDataPembayaran = () => {
        if (!paymentRequested) return null;

        if (paymentSuccess) {
            return (
                <div className="bg-emerald-50 border border-emerald-200 rounded-2xl shadow p-5 text-center space-y-3">
                    <div className="flex justify-center">
                        <div className="h-16 w-16 rounded-full bg-emerald-600 text-white flex items-center justify-center">
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                strokeWidth="2.5"
                                className="h-8 w-8"
                                aria-hidden="true"
                            >
                                <path strokeLinecap="round" strokeLinejoin="round" d="M5 13l4 4L19 7" />
                            </svg>
                        </div>
                    </div>
                    <h2 className="text-xl font-bold text-emerald-800">Pembayaran Berhasil</h2>
                    <p className="text-sm text-emerald-700">Terima kasih, pembayaran Anda telah kami terima.</p>
                    <button
                        type="button"
                        onClick={() => navigate('/pesanan-saya?status=pembayaran_selesai')}
                        className="mt-2 inline-flex px-4 py-2 text-sm font-semibold text-emerald-700 bg-white border border-emerald-200 rounded-lg hover:bg-emerald-100"
                    >
                        Kembali ke Pesanan
                    </button>
                </div>
            );
        }

        if (!vaInfo) return null;

        return (
            <div className="bg-white border border-slate-200 rounded-2xl shadow p-5 space-y-4">
                <div className="flex items-center justify-between">
                    <div>
                        <p className="text-xs uppercase tracking-[0.2em] text-indigo-600 font-semibold">Instruksi Pembayaran</p>
                        <h2 className="text-xl font-bold text-slate-900">Virtual Account {vaInfo.bank?.toUpperCase()}</h2>
                    </div>
                </div>
                <div className="grid gap-3">
                    <div className="border border-slate-200 rounded-xl px-4 py-3">
                        <p className="text-xs text-slate-500 uppercase">Jumlah yang harus dibayar</p>
                        <p className="text-lg font-semibold text-slate-900">
                            {vaInfo.amount ? Number(vaInfo.amount).toLocaleString('id-ID', { style: 'currency', currency: 'IDR' }) : '-'}
                        </p>
                    </div>
                    {vaInfo.companyCode && (
                        <div className="border border-slate-200 rounded-xl px-4 py-3">
                            <p className="text-xs text-slate-500 uppercase">Company Code</p>
                            <p className="text-lg font-semibold text-slate-900">{vaInfo.companyCode}</p>
                        </div>
                    )}
                    <div className="border border-slate-200 rounded-xl px-4 py-3">
                        <p className="text-xs text-slate-500 uppercase">Virtual Account Number</p>
                        <p className="text-lg font-semibold text-slate-900">{vaInfo.vaNumber ?? '-'}</p>
                    </div>
                </div>
                <div className="flex items-center justify-between">
                    <button
                        onClick={cekStatusPembayaran}
                        disabled={processing || !midtransOrderId}
                        className="px-4 py-2.5 bg-indigo-600 text-white rounded-lg text-sm font-semibold hover:bg-indigo-700 disabled:opacity-60"
                    >
                        {processing ? 'Memproses...' : 'Cek Status'}
                    </button>
                    <span className="text-sm text-slate-500">Status: {transactionStatus ?? 'pending'}</span>
                </div>
            </div>
        );
    };

    const tampilHalamanMetodePembayaran = () => (
        <div className="max-w-xl mx-auto space-y-5 pt-6 pb-10 px-4 sm:px-0">
            <div className="bg-white border border-slate-200 rounded-2xl shadow p-5 space-y-4">
                <div className="flex items-start justify-between gap-3">
                    <div className="space-y-1">
                        <p className="text-xs uppercase tracking-[0.2em] text-indigo-600 font-semibold">Halaman Metode Pembayaran</p>
                        <h1 className="text-2xl font-bold text-slate-900">Pesanan #{pesanan?.kode ?? pesanan?.id}</h1>
                        <p className="text-sm text-slate-600">
                            Pilih metode pembayaran untuk paket {pesanan?.paket_tour?.nama_paket}.
                        </p>
                        <p className="text-xs text-slate-500">Batas waktu pembayaran: 24 jam setelah VA dibuat.</p>
                    </div>
                    <button
                        type="button"
                        onClick={() => navigate(-1)}
                        className="inline-flex items-center justify-center h-10 w-10 rounded-full bg-slate-100 border border-slate-200 text-slate-600 hover:bg-slate-200"
                        aria-label="Kembali"
                    >
                        <svg viewBox="0 0 24 24" className="h-5 w-5" fill="none" stroke="currentColor" strokeWidth="2">
                            <path strokeLinecap="round" strokeLinejoin="round" d="M6 6l12 12M18 6L6 18" />
                        </svg>
                    </button>
                </div>
                <div className="space-y-3">
                    <p className="text-sm font-semibold text-slate-800">Virtual Account Bank</p>
                    <div className="grid md:grid-cols-2 gap-2.5">
                        {VA_METHODS.map((va) => (
                            <label
                                key={va.code}
                                className={`flex items-center gap-3 border rounded-xl px-4 py-3 cursor-pointer transition text-sm ${
                                    selectedVa === va.code ? 'border-indigo-400 bg-indigo-50 shadow-sm' : 'border-slate-200 bg-white'
                                }`}
                            >
                                <input
                                    type="radio"
                                    name="va"
                                    value={va.code}
                                    checked={selectedVa === va.code}
                                    onChange={() => setSelectedVa(va.code)}
                                    className="text-indigo-600"
                                />
                                <span
                                    className="inline-flex items-center justify-center h-10 w-10 rounded-md border border-slate-200 bg-white overflow-hidden"
                                >
                                    <img
                                        src={`/images/${va.code}.webp`}
                                        alt={`Logo ${va.name}`}
                                        className="h-8 w-8 object-contain"
                                    />
                                </span>
                                <span className="font-semibold text-slate-800">{va.name}</span>
                            </label>
                        ))}
                    </div>
                    {error && <div className="text-sm text-red-600 bg-red-50 border border-red-100 rounded-xl px-4 py-3">{error}</div>}
                    <button
                        onClick={startVaPayment}
                        disabled={processing}
                        className="px-4 py-2.5 bg-indigo-600 text-white rounded-lg text-sm font-semibold hover:bg-indigo-700 disabled:opacity-60"
                    >
                        {processing ? 'Memproses pembayaran...' : 'Bayar Sekarang'}
                    </button>
                    {transactionStatus && (
                        <p className="text-sm text-slate-600">
                            Status transaksi: <span className="font-semibold text-slate-800">{transactionStatus}</span>
                        </p>
                    )}
                </div>
            </div>

            {tampilDataPembayaran()}
        </div>
    );

    return tampilHalamanMetodePembayaran();
}
