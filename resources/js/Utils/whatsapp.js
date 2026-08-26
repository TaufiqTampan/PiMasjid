/**
 * Helper Utility for WhatsApp Link Formatting & Notifications
 */

/**
 * Format phone number to clean international standard (e.g. 0812... -> 62812...)
 */
export function formatWhatsAppPhone(phone) {
    if (!phone) return '';
    
    // Remove all non-digit characters
    let cleaned = phone.replace(/\D/g, '');

    // Convert local 08xx to 628xx
    if (cleaned.startsWith('0')) {
        cleaned = '62' + cleaned.substring(1);
    }
    // If starts with 8xx (missing leading 0/62), prepend 62
    else if (cleaned.startsWith('8')) {
        cleaned = '62' + cleaned;
    }

    return cleaned;
}

/**
 * Generate full WhatsApp Web/App URL
 */
export function getWhatsAppUrl(phone, text = '') {
    const cleanPhone = formatWhatsAppPhone(phone);
    if (!cleanPhone) return '#';

    const encodedText = encodeURIComponent(text);
    return `https://wa.me/${cleanPhone}?text=${encodedText}`;
}

/**
 * Open WhatsApp link directly in new tab
 */
export function openWhatsApp(phone, text = '') {
    const url = getWhatsAppUrl(phone, text);
    if (url !== '#') {
        window.open(url, '_blank', 'noopener,noreferrer');
    }
}

/**
 * Template: Donasi Lumbung Pangan (Jamaah -> Admin)
 */
export function buildDonationWAMessage({ siteName = 'Masjid', donorName, donorPhone, donationType, programTitle, amount, items }) {
    let msg = `Bismillah, Assalamu'alaikum Pengurus *${siteName}*.\n\n`;
    msg += `Saya ingin mengonfirmasi donasi Lumbung Pangan:\n`;
    msg += `👤 *Nama Donatur:* ${donorName}\n`;
    msg += `📞 *No. HP:* ${donorPhone}\n`;
    if (programTitle) msg += `📌 *Program:* ${programTitle}\n`;
    msg += `🎁 *Jenis Donasi:* ${donationType === 'uang' ? 'Transfer / Tunai' : 'Bahan Sembako'}\n`;
    if (donationType === 'uang' && amount) {
        msg += `💰 *Jumlah:* Rp ${new Intl.NumberFormat('id-ID').format(amount)}\n`;
    } else if (items) {
        msg += `📦 *Rincian Barang:* ${items}\n`;
    }
    msg += `\nMohon dapat diproses dan dikonfirmasi. Terima kasih. Jazaakallahu khairan.`;
    return msg;
}

/**
 * Template: Permohonan Sembako Lumbung Pangan (Jamaah -> Admin)
 */
export function buildRequestWAMessage({ siteName = 'Masjid', name, phone, programTitle, familyMembers, reason }) {
    let msg = `Bismillah, Assalamu'alaikum Pengurus *${siteName}*.\n\n`;
    msg += `Saya telah mengajukan permohonan bantuan sembako Lumbung Pangan:\n`;
    msg += `👤 *Nama Pemohon:* ${name}\n`;
    msg += `📞 *No. HP:* ${phone}\n`;
    if (programTitle) msg += `📌 *Program:* ${programTitle}\n`;
    msg += `👨‍👩‍👧‍👦 *Tanggungan:* ${familyMembers} Anggota Keluarga\n`;
    if (reason) msg += `📝 *Keterangan:* ${reason}\n`;
    msg += `\nMohon permohonan kami dapat dipertimbangkan. Terima kasih.`;
    return msg;
}

/**
 * Template: Booking Fasilitas Masjid (Jamaah -> Admin)
 */
export function buildBookingWAMessage({ siteName = 'Masjid', bookingCode, borrowerName, borrowerPhone, facilityName, eventName, startTime, endTime }) {
    let msg = `Bismillah, Assalamu'alaikum Admin *${siteName}*.\n\n`;
    msg += `Saya mengajukan peminjaman / booking fasilitas masjid:\n`;
    msg += `🔑 *Kode Booking:* *${bookingCode}*\n`;
    msg += `🏢 *Fasilitas:* ${facilityName}\n`;
    msg += `👤 *Nama Pemohon:* ${borrowerName}\n`;
    msg += `📞 *No. HP:* ${borrowerPhone}\n`;
    msg += `📋 *Nama Acara:* ${eventName}\n`;
    if (startTime) msg += `📅 *Waktu Mulai:* ${startTime}\n`;
    if (endTime) msg += `🏁 *Waktu Selesai:* ${endTime}\n`;
    msg += `\nMohon persetujuan dan konfirmasi jadwal dari pengurus masjid. Terima kasih.`;
    return msg;
}

/**
 * Template: Admin Status Update Booking (Admin -> Pemohon)
 */
export function buildAdminBookingStatusWAMessage({ borrowerName, bookingCode, facilityName, eventName, startTime, status, adminNotes }) {
    const isApproved = status === 'approved';
    let msg = `Assalamu'alaikum wr. wb. Sdr/i *${borrowerName}*,\n\n`;
    msg += `Mengenai pengajuan booking fasilitas masjid berikut:\n`;
    msg += `🔑 *Kode Booking:* ${bookingCode}\n`;
    msg += `🏢 *Fasilitas:* ${facilityName}\n`;
    msg += `📋 *Acara:* ${eventName}\n`;
    if (startTime) msg += `📅 *Jadwal:* ${startTime}\n\n`;
    msg += `Status Permohonan Anda: *${isApproved ? '✅ DISETUJUI' : '❌ DITOLAK'}*\n`;
    if (adminNotes) msg += `💬 *Catatan Pengurus:* ${adminNotes}\n`;
    msg += `\nTerima kasih. Semoga acara Anda berjalan lancar. Wassalamu'alaikum.`;
    return msg;
}
