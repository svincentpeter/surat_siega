{{-- resources/views/surat_tugas/approve-preview.blade.php --}}
@php
  // File ini berfungsi sebagai wrapper untuk _core saat approval
  $context = 'web';

  // Ambil nilai dari array $preview yang dikirim Controller
  $ttdW       = $preview['ttd_w_mm'] ?? 42;
  $capW       = $preview['cap_w_mm'] ?? 35;
  $capOpacity = $preview['cap_opacity'] ?? 0.95;
@endphp

{{--
  Kita tidak butuh div atau container tambahan di sini.
  Cukup panggil _core agar pratinjau surat (elemen .sheet) menjadi elemen teratas.
  Ini penting agar live-preview (fetch HTML) bisa mengganti seluruh konten dengan benar.
--}}
@include('surat_tugas.partials._core', [
  'context'           => $context,
  'tugas'             => $tugas,
  'kop'               => $kop ?? null,
  'ttdW'              => $ttdW,
  'capW'              => $capW,
  'capOpacity'        => $capOpacity,
  'ttdImageB64'       => $preview['ttd_image_b64'] ?? null,
  'capImageB64'       => $preview['cap_image_b64'] ?? null,
])