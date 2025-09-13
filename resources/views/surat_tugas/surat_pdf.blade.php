@php($context='pdf')
@include('surat_tugas.partials._core', [
  'tugas'   => $tugas,
  'kop'     => $kop ?? null,
  'context' => 'pdf',
  'disable_sign_layer' => false,
  // base64 & posisi yang sudah disiapkan di controller (renderTugasPdfWithSign)
  'ttdImageB64' => $ttdImageB64 ?? null,
  'capImageB64' => $capImageB64 ?? null,
  'ttdLeft' => $ttdLeft ?? null, 'ttdTop' => $ttdTop ?? null, 'ttdW' => $ttdW ?? null, 'ttdH' => $ttdH ?? null,
  'capLeft' => $capLeft ?? null, 'capTop' => $capTop ?? null, 'capW' => $capW ?? null, 'capOpacity' => $capOpacity ?? 0.95,
])