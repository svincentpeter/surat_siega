@component('mail::message')
# Surat Tugas Disetujui

Yth. {{ $recipientName ?? 'Penerima' }},

Surat Tugas dengan nomor **{{ $tugas->nomor_surat ?? $tugas->nomor }}** telah disetujui.
Silakan cek lampiran PDF.

Terima kasih.
@endcomponent
