<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Surat Keputusan - PDF</title>
    <style>
  @page { margin: 140px 40px 100px 40px; }

  body { font-family: "Times New Roman", Times, serif; }
  .judul { text-align: center; font-weight: bold; font-size: 20px; text-decoration: underline; margin-bottom:7px; }
  .nomor { text-align: center; margin-bottom: 18px; font-size:17px; }
  .subtitle { text-align: center; font-size:15px; margin-bottom:17px; }
  .isi { margin: 18px 0 0 36px; font-size: 15px; }
  .table-meta td { vertical-align: top; padding-right: 7px; }
  ol.alpha { list-style-type: lower-alpha; margin:0 0 0 1.6em; }
  ol.decimal { list-style-type: decimal; margin:0 0 0 1.5em; }
  .footer { text-align: right; margin-right: 80px; margin-top: 40px; font-size:15px; }
  .tembusan { margin-top: 3em; font-size: 15px; }

  .kop-footer { position: fixed; bottom: -80px; left: 0; right: 0; }

  /* Header baru: kop kanan-atas */
  .kop-kanan { position: fixed; top: -110px; right: 0; width: 420px; text-align: right; }
  .kop-kanan img.logo { height: 56px; vertical-align: middle; }
  .kop-kanan .title { font-weight: 700; letter-spacing: .02em; font-size: 16px; }
  .kop-kanan .meta { font-size: 11px; line-height: 1.25; }
</style>


</head>
<body>
   {{-- HEADER (kanan-atas) --}}
@if(($kop?->mode ?? 'image') === 'image' && !empty($kop?->header_path))
  <div class="kop-kanan">
    <img class="logo" src="{{ public_path('storage/'.$kop->header_path) }}">
  </div>
@elseif(($kop?->mode ?? 'image') === 'composed')
  <div class="kop-kanan">
    @if(($kop->tampilkan_logo_kanan ?? false) && $kop->logo_kanan_path)
      <img class="logo" src="{{ public_path('storage/'.$kop->logo_kanan_path) }}">
    @endif
    <div class="title">{{ strtoupper($kop->judul_atas ?? '') }}</div>
    <div class="meta">
      {{ $kop->subjudul }}<br>
      {{ $kop->alamat }}<br>
      Telp. {{ $kop->telepon }}@if($kop?->fax), Fax. {{ $kop->fax }}@endif<br>
      email: {{ $kop->email }}@if($kop?->website) | {{ $kop->website }}@endif
    </div>
  </div>
@else
  {{-- Fallback lama jika belum ada konfigurasi kop --}}
  <div style="text-align:center; margin-bottom:10px;">
    <span style="font-size:1.1em; font-weight: bold;">FAKULTAS ILMU KOMPUTER</span><br>
    <span style="font-size:12px;">
      Jl. Pawiyatan Luhur IV/1, Bendan Duwur, Semarang 50234<br>
      Telp. (024) 8441555, 8505030, Fax. (024) 8415479–8460255<br>
      e-mail: unika@unika.ac.id | http://www.unika.ac.id
    </span>
  </div>
  <hr style="border:1px solid #222; margin:14px 0 12px 0;">
@endif


{{-- FOOTER DINAMIS (opsional) --}}
@if(!empty($kop?->footer_path))
  <div class="kop-footer">
    <img src="{{ public_path('storage/'.$kop->footer_path) }}" style="width:100%;">
  </div>
@endif

    <div class="judul">
        KEPUTUSAN DEKAN FAKULTAS ILMU KOMPUTER<br>
        UNIVERSITAS KATOLIK SOEGIJAPRANATA
    </div>
    <div class="nomor">
        NOMOR {{ $keputusan->nomor ?? '...' }}
    </div>
    <div class="subtitle">
        tentang<br>
        <b>PENETAPAN VISI, MISI, TUJUAN FAKULTAS ILMU KOMPUTER UNIVERSITAS KATOLIK SOEGIJAPRANATA<br>
        DAN SELURUH PROGRAM STUDI YANG BERNAUNG DI BAWAHNYA</b>
    </div>
    <div class="text-center" style="font-weight:bold; margin-bottom:19px;">
        DEKAN FAKULTAS ILMU KOMPUTER<br>
        UNIVERSITAS KATOLIK SOEGIJAPRANATA
    </div>
    {{-- MENIMBANG --}}
    <table class="table-meta" style="margin-bottom:8px;">
        <tr>
            <td style="width:95px;vertical-align:top;">Menimbang</td>
            <td style="width:12px;vertical-align:top;">:</td>
            <td>
                <ol class="alpha">
                    @foreach($keputusan->menimbang as $mnt)
                        <li>{!! $mnt !!}</li>
                    @endforeach
                </ol>
            </td>
        </tr>
        <tr>
            <td style="vertical-align:top;">Mengingat</td>
            <td style="vertical-align:top;">:</td>
            <td>
                <ol class="decimal">
                    @foreach($keputusan->mengingat as $mgt)
                        <li>{!! $mgt !!}</li>
                    @endforeach
                </ol>
            </td>
        </tr>
    </table>

    <div style="text-align:center; font-weight:bold; margin-bottom:10px; letter-spacing:0.12em;">
        M E M U T U S K A N
    </div>
    <table class="table-meta" style="margin-bottom:16px;">
        <tr>
            <td style="width:92px;">Menetapkan</td>
            <td style="width:12px;">:</td>
            <td>
                <b>{{ $keputusan->menetapkan ?? 'KEPUTUSAN DEKAN TENTANG PENETAPAN VISI, MISI, DAN TUJUAN...' }}</b>
            </td>
        </tr>
        <tr>
            <td>KESATU</td>
            <td>:</td>
            <td>
                <b>Visi Fakultas Ilmu Komputer</b><br>
                {!! $keputusan->visi !!}
            </td>
        </tr>
        <tr>
            <td>KEDUA</td>
            <td>:</td>
            <td>
                <b>Misi Fakultas Ilmu Komputer:</b>
                <ol class="decimal" style="margin-top:5px;">
                    @foreach($keputusan->misi as $misi)
                        <li>{!! $misi !!}</li>
                    @endforeach
                </ol>
            </td>
        </tr>
        <tr>
            <td>KETIGA</td>
            <td>:</td>
            <td>
                <b>Tujuan Fakultas Ilmu Komputer:</b>
                <ol class="decimal" style="margin-top:5px;">
                    @foreach($keputusan->tujuan as $tujuan)
                        <li>{!! $tujuan !!}</li>
                    @endforeach
                </ol>
            </td>
        </tr>
        <tr>
            <td>KEEMPAT</td>
            <td>:</td>
            <td>
                Keputusan ini berlaku sejak tanggal ditetapkan.
            </td>
        </tr>
    </table>
    <div class="footer" style="text-align:right; margin-top:50px;">
        Semarang,<br>
        {{ ($keputusan->tanggal_surat ?? $keputusan->tanggal ?? null)
      ? \Carbon\Carbon::parse($keputusan->tanggal_surat ?? $keputusan->tanggal)->translatedFormat('d F Y')
      : '-' }}<br>
        Dekan,<br><br><br><br>
        <strong>{{ $keputusan->dekan_nama ?? 'Prof. Dr. Ridwan Sanjaya, MS.IEC.' }}</strong><br>
        NPP. {{ $keputusan->dekan_npp ?? '058.1.2002.255' }}
    </div>
    <div class="tembusan">
        Tembusan:<br>
        1. Yth. Rektor<br>
        2. Yth. Ketua Prodi Teknik Informatika<br>
        3. Yth. Ketua Prodi Sistem Informasi
    </div>
</body>
</html>
