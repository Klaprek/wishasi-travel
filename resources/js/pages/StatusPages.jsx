import React from 'react';
import StatusScreen from '../components/StatusScreen';

export function HalamanStatusVerifikasi() {
    return (
        <StatusScreen
            title="Menunggu Verifikasi"
            description="Data peserta sedang diperiksa admin. Kamu akan mendapat notifikasi ketika sudah siap dibayar."
            hint="Pastikan kontak dan email aktif agar update status tidak terlewat."
            actionLabel="Kembali ke pesanan"
            actionHref="/pesanan-saya"
        />
    );
}

export function HalamanMenungguPembayaran() {
    return (
        <StatusScreen
            title="Menunggu Pembayaran"
            description="Data sudah diverifikasi. Lanjutkan pembayaran sesuai metode yang tersedia."
            hint="Gunakan Midtrans untuk pembayaran cepat dan aman."
            actionLabel="Pilih metode pembayaran"
            actionHref="/pesanan-saya"
        />
    );
}

export function HalamanStatusPembayaran() {
    return (
        <StatusScreen
            title="Status Pembayaran"
            description="Transaksi sedang diproses. Jika kamu sudah membayar, status akan otomatis berubah ke pembayaran selesai."
            hint="Jika status tidak berubah dalam 5 menit, hubungi admin dengan bukti bayar."
            actionLabel="Kembali"
            actionHref="/pesanan-saya"
        />
    );
}

export function HalamanPembayaranSelesai() {
    return (
        <StatusScreen
            title="Pembayaran Selesai"
            description="Terima kasih! Pembayaranmu sudah diterima. Tim kami akan menyiapkan perjalananmu."
            actionLabel="Lihat pesanan"
            actionHref="/pesanan-saya"
        />
    );
}

export function HalamanPesananSelesai() {
    return (
        <StatusScreen
            title="Pesanan Selesai"
            description="Perjalananmu telah berakhir. Bagikan pengalaman melalui rating untuk membantu traveler lain."
            actionLabel="Beri rating"
            actionHref="/pesanan-saya"
        />
    );
}
