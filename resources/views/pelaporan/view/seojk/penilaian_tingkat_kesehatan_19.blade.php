@php
    use App\Utils\Tanggal;
    $a = $analisis;
@endphp

@extends('pelaporan.layout.base')

@section('content')
    <table border="0" width="100%" cellspacing="0" cellpadding="2" style="font-size: 10px;">
        <tr>
            <td colspan="3" align="center" style="font-size: 14px;">
                <b>LAPORAN PENILAIAN TINGKAT KESEHATAN LKM</b>
            </td>
        </tr>
        <tr>
            <td colspan="3" align="center" style="font-size: 12px;">
                <b>(Berdasarkan POJK No. 19/POJK.05/2021)</b>
            </td>
        </tr>
        <tr>
            <td colspan="3" align="center" style="font-size: 11px;">
                <b>{{ strtoupper($sub_judul) }}</b>
            </td>
        </tr>
    </table>
    <br>

    <table border="0" width="100%" cellspacing="0" cellpadding="2" style="font-size: 10px;">
        <tr>
            <td width="3%" valign="top">1.</td>
            <td width="35%" valign="top">Nama LKM</td>
            <td width="62%" valign="top">: <b>{{ $kec->nama_lembaga_long }}</b></td>
        </tr>
        <tr>
            <td valign="top">2.</td>
            <td valign="top">Sandi LKM</td>
            <td valign="top">: {{ $kec->sandi_lkm }}</td>
        </tr>
        <tr>
            <td valign="top">3.</td>
            <td valign="top">Nomor & Tanggal Izin Usaha</td>
            <td valign="top">: {{ $kec->ijin_usaha ?? '-' }}</td>
        </tr>
        <tr>
            <td valign="top">4.</td>
            <td valign="top">Alamat</td>
            <td valign="top">: {{ $kec->alamat_kec }}, {{ $kec->nama_kec }}</td>
        </tr>
        <tr>
            <td valign="top">5.</td>
            <td valign="top">Tanggal Cetak</td>
            <td valign="top">: {{ $tanggal_kondisi }}</td>
        </tr>
    </table>
    <br>

    <div style="font-size: 11px; font-weight: bold; background: rgb(232,232,232); padding: 4px;">
        I. INFORMASI NERACA UTAMA (otomatis dihitung dari database per {{ $tgl_kondisi }})
    </div>
    <table border="0" width="100%" cellspacing="0" cellpadding="3" style="font-size: 10px; border: 1px solid #000;">
        <thead>
            <tr style="background: rgb(245,245,245);">
                <th class="t l b r" width="5%" align="center">No</th>
                <th class="t l b r" width="55%" align="left">Parameter</th>
                <th class="t l b r" width="40%" align="right">Nilai (Rp)</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="t l b r" align="center">1</td>
                <td class="t l b r">Kas dan Setara Kas</td>
                <td class="t l b r" align="right">{{ number_format($a['kas_setara_kas'], 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td class="t l b r" align="center">2</td>
                <td class="t l b r">Total Aset</td>
                <td class="t l b r" align="right">{{ number_format($a['total_aset'], 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td class="t l b r" align="center">3</td>
                <td class="t l b r">Liabilitas Lancar</td>
                <td class="t l b r" align="right">{{ number_format($a['liabilitas_lancar'], 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td class="t l b r" align="center">4</td>
                <td class="t l b r">Total Liabilitas</td>
                <td class="t l b r" align="right">{{ number_format($a['total_liabilitas'], 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td class="t l b r" align="center">5</td>
                <td class="t l b r">Modal Disetor</td>
                <td class="t l b r" align="right">{{ number_format($a['modal_disetor'], 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td class="t l b r" align="center">6</td>
                <td class="t l b r">Total Ekuitas</td>
                <td class="t l b r" align="right">{{ number_format($a['total_ekuitas'], 0, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>
    <br>

    <div style="font-size: 11px; font-weight: bold; background: rgb(232,232,232); padding: 4px;">
        II. ANALISIS RASIO KEUANGAN &amp; KEPATUHAN
    </div>
    <table border="0" width="100%" cellspacing="0" cellpadding="3" style="font-size: 10px; border: 1px solid #000;">
        <thead>
            <tr style="background: rgb(245,245,245);">
                <th class="t l b r" width="5%" align="center">No</th>
                <th class="t l b r" width="30%" align="left">Indikator Rasio</th>
                <th class="t l b r" width="30%" align="left">Formula</th>
                <th class="t l b r" width="15%" align="center">Batas POJK</th>
                <th class="t l b r" align="right">Hasil (%)</th>
                <th class="t l b r" width="12%" align="center">Status</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="t l b r" align="center">1</td>
                <td class="t l b r">Rasio Likuiditas</td>
                <td class="t l b r">Kas &amp; Setara Kas / Liabilitas Lancar</td>
                <td class="t l b r" align="center">Min. 4%</td>
                <td class="t l b r" align="right"><b>{{ number_format($a['rasio_likuiditas'], 2) }}%</b></td>
                <td class="t l b r" align="center">{{ $a['status_likuiditas'] }}</td>
            </tr>
            <tr>
                <td class="t l b r" align="center">2</td>
                <td class="t l b r">Rasio Solvabilitas</td>
                <td class="t l b r">Total Aset / Total Liabilitas</td>
                <td class="t l b r" align="center">Min. 110%</td>
                <td class="t l b r" align="right"><b>{{ number_format($a['rasio_solvabilitas'], 2) }}%</b></td>
                <td class="t l b r" align="center">{{ $a['status_solvabilitas'] }}</td>
            </tr>
            <tr>
                <td class="t l b r" align="center">3</td>
                <td class="t l b r">Rasio Ekuitas</td>
                <td class="t l b r">Total Ekuitas / Modal Disetor</td>
                <td class="t l b r" align="center">Min. 75%</td>
                <td class="t l b r" align="right"><b>{{ number_format($a['rasio_ekuitas'], 2) }}%</b></td>
                <td class="t l b r" align="center">{{ $a['status_ekuitas'] }}</td>
            </tr>
        </tbody>
    </table>
    <br>

    <div style="font-size: 11px; font-weight: bold; background: rgb(232,232,232); padding: 4px;">
        III. KESIMPULAN STATUS KESEHATAN
    </div>
    <table border="0" width="100%" cellspacing="0" cellpadding="4" style="font-size: 10px; border: 1px solid #000;">
        <tr style="background: {{ $a['warna_kesimpulan'] }}; color: #fff; font-weight: bold;">
            <td class="t l b r" width="5%" align="center">No</td>
            <td class="t l b r" width="25%" align="left">Status</td>
            <td class="t l b r" width="70%" align="left">Keterangan</td>
        </tr>
        <tr>
            <td class="t l b r" align="center">1</td>
            <td class="t l b r">SEHAT</td>
            <td class="t l b r">Likuiditas &ge; 4% dan Solvabilitas &ge; 110%.</td>
        </tr>
        <tr>
            <td class="t l b r" align="center">2</td>
            <td class="t l b r">KONDISI MEMBAHAYAKAN KELANGSUNGAN USAHA</td>
            <td class="t l b r">Likuiditas &lt; 3% dan Solvabilitas &lt; 100%.</td>
        </tr>
        <tr>
            <td class="t l b r" align="center" colspan="2"><b>STATUS SAAT INI</b></td>
            <td class="t l b r"><b>{{ $a['status_kesimpulan'] }}</b> &mdash; {{ $a['alasan_kesimpulan'] }}</td>
        </tr>
    </table>
    <br><br>

    <table border="0" width="100%" cellspacing="0" cellpadding="2" style="font-size: 11px;">
        <tr>
            <td width="50%"></td>
            <td width="50%" align="center">
                {{ $kec->nama_kec }}, {{ Tanggal::tglLatin($tgl_kondisi) }}<br>
                {{ $nama_lembaga }}<br><br><br><br><br>
                <strong><u>{{ $dir->namadepan ?? '' }} {{ $dir->namabelakang ?? '' }}</u></strong><br>
                <strong>
                    @if (!empty($dir) && isset($dir->jabatan))
                        {{ $dir->j->nama_jabatan ?? 'Direktur' }}
                    @else
                        Direktur
                    @endif
                </strong>
            </td>
        </tr>
    </table>
@endsection
