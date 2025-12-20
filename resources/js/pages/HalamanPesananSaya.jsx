import React, { useEffect, useMemo, useState } from "react";
import { Link, useLocation } from "react-router-dom";
import api from "../lib/api";
import useFetch from "../hooks/useFetch";

const BADGES = {
    menunggu_verifikasi: "bg-amber-100 text-amber-800 border-amber-200",
    menunggu_pembayaran: "bg-indigo-100 text-indigo-800 border-indigo-200",
    pembayaran_selesai: "bg-emerald-100 text-emerald-800 border-emerald-200",
    pesanan_selesai: "bg-emerald-100 text-emerald-800 border-emerald-200",
    pesanan_ditolak: "bg-red-100 text-red-800 border-red-200",
};

const FILTERS = [
    { value: "menunggu_verifikasi", label: "Menunggu Verifikasi" },
    { value: "menunggu_pembayaran", label: "Menunggu Pembayaran" },
    { value: "pembayaran_selesai", label: "Pembayaran Selesai" },
    { value: "pesanan_selesai", label: "Pesanan Selesai" },
    { value: "pesanan_ditolak", label: "Pesanan Ditolak" },
];

export default function HalamanPesananSaya() {
    const {
        data: pesanan,
        loading,
        error,
        refetch,
    } = useFetch("/pesanan-saya");
    const { search } = useLocation();
    const queryStatus = useMemo(
        () => new URLSearchParams(search).get("status"),
        [search]
    );
    const [filter, setFilter] = useState(() => {
        return FILTERS.some((f) => f.value === queryStatus)
            ? queryStatus
            : "menunggu_verifikasi";
    });
    const [completing, setCompleting] = useState(null);
    const [actionError, setActionError] = useState("");

    useEffect(() => {
        if (FILTERS.some((f) => f.value === queryStatus)) {
            setFilter(queryStatus);
        }
    }, [queryStatus]);

    const filteredOrders = useMemo(() => {
        return (pesanan ?? []).filter(
            (order) => order.status_pesanan === filter
        );
    }, [pesanan, filter]);

    const markSelesai = async (orderId) => {
        setCompleting(orderId);
        setActionError("");
        try {
            await api.post(`/pesanan/${orderId}/selesai`);
            refetch();
        } catch (err) {
            setActionError(
                err.response?.data?.message || "Gagal menandai pesanan selesai"
            );
        } finally {
            setCompleting(null);
        }
    };

    const downloadKartu = (order) => {
        const escapeHtml = (value) => {
            return String(value)
                .replace(/&/g, "&amp;")
                .replace(/</g, "&lt;")
                .replace(/>/g, "&gt;")
                .replace(/"/g, "&quot;")
                .replace(/'/g, "&#39;");
        };

        const orderId = order.kode ?? order.id ?? "-";
        const paketTitle = order.paket_tour?.nama_paket ?? "Paket Tour";
        const pesertaIds = (order.pesertas ?? [])
            .map((p) => p.kode ?? p.id)
            .filter((id) => id != null);
        const pesertaHtml = pesertaIds.length
            ? pesertaIds
                  .map((id) => `<span class="badge">${escapeHtml(id)}</span>`)
                  .join("")
            : '<span class="empty">-</span>';
        const logoUrl = `${window.location.origin}/images/logo.webp`;

        const html = `<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Kartu Pesanan ${escapeHtml(orderId)}</title>
<style>
* { box-sizing: border-box; }
@page { size: A4 landscape; margin: 12mm; }
body { margin: 0; padding: 0; font-family: Arial, sans-serif; background: #f8fafc; color: #0f172a; }
.ticket { display: flex; gap: 24px; align-items: center; border: 2px dashed #94a3b8; border-radius: 16px; padding: 24px; background: #ffffff; }
.logo { width: 90px; height: 90px; object-fit: contain; }
.title { font-size: 22px; font-weight: 700; margin: 4px 0 8px; }
.label { font-size: 12px; text-transform: uppercase; letter-spacing: 0.18em; color: #475569; }
.row { margin-top: 6px; font-size: 14px; }
.badge { display: inline-flex; align-items: center; padding: 4px 10px; border-radius: 999px; border: 1px solid #cbd5f5; background: #eef2ff; font-size: 12px; margin: 2px; }
.empty { color: #94a3b8; font-size: 12px; }
.footer { margin-top: 10px; font-size: 11px; color: #64748b; }
@media print { body { background: #ffffff; } }
</style>
</head>
<body>
<div class="ticket">
  <img class="logo" src="${logoUrl}" alt="Wishasi Logo">
  <div class="content">
    <div class="label">Kartu Pesanan</div>
    <div class="title">${escapeHtml(paketTitle)}</div>
    <div class="row"><strong>ID Pesanan:</strong> ${escapeHtml(orderId)}</div>
    <div class="row"><strong>ID Peserta:</strong> ${pesertaHtml}</div>
    <div class="footer">Bawa kartu ini saat keberangkatan.</div>
  </div>
</div>
<script>
window.onload = function () { window.print(); };
</script>
</body>
</html>`;

        const printWindow = window.open("", "_blank", "width=1000,height=700");
        if (!printWindow) {
            window.alert("Pop-up diblokir. Izinkan pop-up untuk mencetak.");
            return;
        }
        printWindow.document.open();
        printWindow.document.write(html);
        printWindow.document.close();
    };

    const tampilFilterBelumDiverifikasi = (order) => {
        const jumlahPeserta = order.jumlah_peserta ?? 0;
        const jumlahTerkirim = (order.pesertas ?? []).length;
        const sudahKirim = jumlahTerkirim >= jumlahPeserta && jumlahPeserta > 0;

        return (
            <div className="border border-slate-200 rounded-xl p-4 bg-slate-50 flex items-center justify-between gap-4">
                <p className="text-sm text-slate-500">Lengkapi data peserta</p>
                <div className="flex gap-2 mt-2 flex-wrap">
                    {sudahKirim ? (
                        <button
                            type="button"
                            disabled
                            className="px-3 py-2 bg-slate-200 text-slate-500 rounded-lg text-sm font-semibold cursor-not-allowed"
                        >
                            Lengkapi Peserta
                        </button>
                    ) : (
                        <Link
                            to={`/pesanan/${order.id}/data-peserta`}
                            state={{ pesanan: order }}
                            className="px-3 py-2 bg-indigo-600 text-white rounded-lg text-sm font-semibold hover:bg-indigo-700"
                        >
                            Lengkapi Peserta
                        </Link>
                    )}
                </div>
            </div>
        );
    };

    const tampilFilterMenungguPembayaran = (order) => (
        <div className="border border-slate-200 rounded-xl p-4 bg-slate-50 flex flex-col md:flex-row md:items-center md:justify-between gap-3">
            <div className="space-y-1">
                <p className="text-xs text-slate-500">Lakukan pembayaran</p>
                <p className="font-semibold text-slate-800">
                    Total: Rp{" "}
                    {(
                        Number(order.paket_tour?.harga_per_peserta ?? 0) *
                        (order.jumlah_peserta ?? 1)
                    ).toLocaleString("id-ID")}
                </p>
            </div>
            <Link
                to={`/pembayaran/${order.id}/metode`}
                className="inline-flex px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm font-semibold hover:bg-indigo-700"
            >
                Bayar Sekarang
            </Link>
        </div>
    );

    const tampilFilterPembayaranSelesai = (order) => (
        <div className="border border-slate-200 rounded-xl p-4 bg-slate-50 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <p className="text-sm text-slate-600 mb-4">
                    Pembayaran telah selesai
                </p>
                <p className="text-sm text-slate-600">
                    Tekan pesanan selesai jika sudah melakukan perjalanan tour
                </p>
            </div>
            <div className="flex flex-wrap gap-2">
                <button
                    type="button"
                    onClick={() => downloadKartu(order)}
                    className="px-4 py-2 bg-white text-slate-700 border border-slate-200 rounded-lg text-sm font-semibold hover:bg-slate-100"
                >
                    Cetak Kartu
                </button>
                <button
                    onClick={() => markSelesai(order.id)}
                    disabled={completing === order.id}
                    className="px-4 py-2 bg-emerald-600 text-white rounded-lg text-sm font-semibold hover:bg-emerald-700 disabled:opacity-60"
                >
                    {completing === order.id
                        ? "Memproses..."
                        : "Pesanan Selesai"}
                </button>
            </div>
        </div>
    );

    const tampilFilterPesananSelesai = (order) => (
        <div className="border border-slate-200 rounded-xl p-4 bg-slate-50 flex items-center justify-between gap-4">
            <p className="text-sm text-slate-600 mb-2">
                {order.rating
                    ? "Rating sudah dikirim"
                    : "Berikan ulasan perjalananmu"}
            </p>
            {order.rating ? (
                <button
                    type="button"
                    disabled
                    className="inline-flex px-4 py-2 bg-slate-200 text-slate-500 rounded-lg text-sm font-semibold cursor-not-allowed"
                    title="Rating sudah dikirim"
                >
                    Beri Rating
                </button>
            ) : (
                <Link
                    to={`/pesanan/${order.id}/rating`}
                    className="inline-flex px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm font-semibold hover:bg-indigo-700"
                >
                    Beri Rating
                </Link>
            )}
        </div>
    );

    const tampilFilterPesananDitolak = (order) => (
        <div className="border border-red-200 rounded-xl p-4 bg-red-50 text-red-800 text-sm">
            {order.alasan_penolakan ? `Alasan: ${order.alasan_penolakan}` : ""}
        </div>
    );

    const tampilHalamanPesananSaya = () => (
        <div className="space-y-6 pt-6 pb-6">
            <div className="bg-white border border-slate-200 shadow rounded-3xl p-6">
                <p className="text-sm uppercase tracking-[0.2em] text-indigo-600 font-semibold">
                    Pesanan Saya
                </p>
                <h1 className="text-3xl font-bold text-slate-900">
                    Pantau semua perjalananmu
                </h1>
                <p className="text-slate-600">
                    Isi data peserta, lakukan pembayaran, dan cek status
                    verifikasi.
                </p>
            </div>

            <div className="overflow-x-auto -mx-1">
                <div className="flex flex-nowrap gap-2 px-1 pb-2">
                    {FILTERS.map((f) => (
                        <button
                            key={f.value}
                            onClick={() => setFilter(f.value)}
                            className={`flex-shrink-0 whitespace-nowrap px-3 py-2 rounded-lg text-sm font-semibold border transition ${
                                filter === f.value
                                    ? "bg-indigo-600 text-white border-indigo-600"
                                    : "bg-white text-slate-700 border-slate-200 hover:border-indigo-200"
                            }`}
                        >
                            {f.label}
                        </button>
                    ))}
                </div>
            </div>

            {actionError && (
                <div className="text-sm text-red-700 bg-red-50 border border-red-100 rounded-xl px-4 py-3">
                    {actionError}
                </div>
            )}

            {loading && <p className="text-slate-600">Memuat pesanan...</p>}
            {error && (
                <div className="text-sm text-red-600 bg-red-50 border border-red-100 rounded-xl px-4 py-3">
                    Gagal memuat pesanan
                    <button
                        onClick={refetch}
                        className="ml-3 text-indigo-700 font-semibold"
                    >
                        Coba lagi
                    </button>
                </div>
            )}

            <div className="space-y-4">
                {filteredOrders.map((order) => {
                    const badge =
                        BADGES[order.status_pesanan] ??
                        "bg-slate-100 text-slate-700 border-slate-200";
                    return (
                        <div
                            key={order.id}
                            className="bg-white border border-slate-200 rounded-2xl shadow-sm p-6 space-y-4"
                        >
                            <div className="flex flex-col md:flex-row md:items-start md:justify-between gap-4">
                                <div className="space-y-1">
                                    <p className="text-xs text-slate-500">
                                        ID #{order.kode ?? order.id}
                                    </p>
                                    <h2 className="text-xl font-semibold text-slate-900">
                                        {order.paket_tour?.nama_paket}
                                    </h2>
                                    <p className="text-sm text-slate-600">
                                        Jumlah peserta: {order.jumlah_peserta}
                                    </p>
                                </div>
                                <span
                                    className={`px-3 py-1 rounded-full border text-xs font-semibold self-start ${badge}`}
                                >
                                    {order.status_pesanan?.replaceAll("_", " ")}
                                </span>
                            </div>
                            {filter === "menunggu_verifikasi" &&
                                tampilFilterBelumDiverifikasi(order)}
                            {filter === "menunggu_pembayaran" &&
                                tampilFilterMenungguPembayaran(order)}
                            {filter === "pembayaran_selesai" &&
                                tampilFilterPembayaranSelesai(order)}
                            {filter === "pesanan_selesai" &&
                                tampilFilterPesananSelesai(order)}
                            {filter === "pesanan_ditolak" &&
                                tampilFilterPesananDitolak(order)}
                        </div>
                    );
                })}

                {!loading && filteredOrders.length === 0 && (
                    <div className="bg-white border border-dashed border-slate-200 rounded-2xl p-10 text-center text-slate-500">
                        Belum ada pesanan untuk filter ini. Mulai dengan memilih
                        paket di katalog.
                    </div>
                )}
            </div>
        </div>
    );

    return tampilHalamanPesananSaya();
}
