<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Preview Surat Keputusan</title>
    <style>
        body { background: #f2f3f7; margin: 0; padding: 24px 0; }
        .a4-sheet {
            width: 794px;
            min-height: 1123px;
            background: #fff;
            margin: auto;
            box-shadow: 0 0 10px #999;
            padding: 56px 56px 56px 56px;
            font-family: Times New Roman, Times, serif;
            color: #222;
        }
        .header-logo { display: flex; align-items: center; justify-content: space-between; }
        .header-title { text-align: center; margin-top: 8px; margin-bottom: 10px; }
        .fw-bold { font-weight: bold }
        .underline { text-decoration: underline }
        .mb-1 { margin-bottom: 0.7em }
        .mb-2 { margin-bottom: 1.5em }
        .mb-3 { margin-bottom: 2.3em }
        .mb-4 { margin-bottom: 3em }
        .mt-2 { margin-top: 1.5em }
        .mt-3 { margin-top: 2.5em }
        .mt-5 { margin-top: 3.5em }
        .text-center { text-align: center }
        .table-meta td { vertical-align: top; padding-right: 8px; }
        ol.alpha { list-style-type: lower-alpha; margin:0 0 0 1.7em; }
        ol.decimal { list-style-type: decimal; margin:0 0 0 1.7em; }
        .tembusan { margin-top: 3em; font-size: 15px; }
    </style>
</head>
<body>
<div class="a4-sheet">
    {{-- HEADER --}}
    <div class="header-logo">
        <div>
            <img src="{{ asset('img/logo-fakultas.png') }}" style="height:54px;">
        </div>
        <div style="text-align: right;">
            <img src="{{ asset('img/logo-unika.png') }}" style="height:48px;">
        </div>
    </div>
    <div class="header-title">
        <div class="fw-bold" style="font-size: 1.3em;">FAKULTAS ILMU KOMPUTER</div>
        <div style="font-size:13px;">
            Jl. Pawiyatan Luhur IV/1, Bendan Duwur, Semarang 50234<br>
            Telp. (024) 8441555, 8505030, Fax. (024) 8415479–8460255 <br>
            e-mail: unika@unika.ac.id | http://www.unika.ac.id
        </div>
    </div>
    <hr style="border:1px solid #222; margin:18px 0 18px 0;">
    <div class="text-center fw-bold underline mb-1" style="font-size:1.25em;">
        KEPUTUSAN DEKAN FAKULTAS ILMU KOMPUTER<br>
        UNIVERSITAS KATOLIK SOEGIJAPRANATA<br>
        NOMOR {{ $keputusan->nomor ?? '...' }}
    </div>
    <div class="text-center mb-2" style="font-size:1.1em;">
        tentang<br>
        <b>PENETAPAN VISI, MISI, TUJUAN FAKULTAS ILMU KOMPUTER UNIVERSITAS KATOLIK SOEGIJAPRANATA<br>
        DAN SELURUH PROGRAM STUDI YANG BERNAUNG DI BAWAHNYA</b>
    </div>
    <div class="text-center fw-bold mb-4">
        DEKAN FAKULTAS ILMU KOMPUTER<br>
        UNIVERSITAS KATOLIK SOEGIJAPRANATA
    </div>

    {{-- MENIMBANG --}}
    <table class="table-meta mb-1">
        <tr>
            <td style="width:100px;vertical-align:top;">Menimbang</td>
            <td style="width:15px;vertical-align:top;">:</td>
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

    <div class="text-center fw-bold mb-2" style="letter-spacing:0.13em;">
        M E M U T U S K A N
    </div>
    <table class="table-meta mb-3">
        <tr>
            <td style="width:100px;">Menetapkan</td>
            <td style="width:15px;">:</td>
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

    {{-- FOOTER & TTD --}}
    <div style="width:100%; display:flex; justify-content: flex-end; margin-top: 4em;">
        <div style="text-align:left; min-width:340px;">
            Semarang,<br>
            {{ isset($keputusan->tanggal) ? \Carbon\Carbon::parse($keputusan->tanggal)->translatedFormat('d F Y') : '-' }}<br>
            Dekan,<br><br><br><br>
            <span class="fw-bold">{{ $keputusan->dekan_nama ?? 'Prof. Dr. Ridwan Sanjaya, MS.IEC.' }}</span><br>
            NPP. {{ $keputusan->dekan_npp ?? '058.1.2002.255' }}
        </div>
    </div>
    <div class="tembusan mt-3">
        Tembusan:<br>
        1. Yth. Rektor<br>
        2. Yth. Ketua Prodi Teknik Informatika<br>
        3. Yth. Ketua Prodi Sistem Informasi
    </div>
</div>
</body>
</html>
