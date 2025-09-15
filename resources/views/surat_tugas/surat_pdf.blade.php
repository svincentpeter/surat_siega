{{-- Surat Tugas - PDF (satu render, tanpa page-break tambahan) --}}
@php
  $context = 'pdf';
  // Ambil nilai ukuran & opacity yang sudah tersimpan di database untuk tugas ini
  $ttdW = $ttdW ?? ($tugas->ttd_w_mm ?? null);
  $capW = $capW ?? ($tugas->cap_w_mm ?? null);
  $capOpacity = $capOpacity ?? ($tugas->cap_opacity ?? null);

  // Controller harus menyediakan gambar TTD & Cap dalam format base64
  // karena Dompdf lebih stabil menanganinya.
  // Contoh di Controller:
  // $data['ttdImageB64'] = Helper::pathToBase64(storage_path('app/public/' . $tugas->ttd_path));
  // $data['capImageB64'] = Helper::pathToBase64(storage_path('app/public/' . $tugas->cap_path));
@endphp

@include('surat_tugas.partials._core', [
  'context'      => $context,
  'tugas'        => $tugas,
  'kop'          => $kop ?? null,
  'penerimaList' => $penerimaList ?? null,
  'ttdW'         => $ttdW,
  'capW'         => $capW,
  'capOpacity'   => $capOpacity,
  'ttdImageB64'  => $ttdImageB64 ?? null, // Pastikan dikirim dari controller
  'capImageB64'  => $capImageB64 ?? null, // Pastikan dikirim dari controller
])