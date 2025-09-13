<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <title>Pratinjau Surat Tugas</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body style="margin:0; background:#f0f2f5;">
  @include('surat_tugas.partials._core', array_merge([
  'tugas'   => $tugas,
  'kop'     => $kop ?? null,
  'context' => 'web',
  'disable_sign_layer' => false,   // <- WAJIB: tampilkan TTD & Cap dari _core
], isset($ttdImageB64) ? [
  // ini variabel dari controller->buildSignAssets()
  'ttdImageB64' => $ttdImageB64 ?? null,
  'capImageB64' => $capImageB64 ?? null,
  'ttdLeft' => $ttdLeft ?? null, 'ttdTop' => $ttdTop ?? null, 'ttdW' => $ttdW ?? null, 'ttdH' => $ttdH ?? null,
  'capLeft' => $capLeft ?? null, 'capTop' => $capTop ?? null, 'capW' => $capW ?? null, 'capOpacity' => $capOpacity ?? null,
] : []))
</body>
</html>
