<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Formulir Simpanan Nasabah' }}</title>
    <style type="text/css">
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 11px;
            padding: 10px;
            background: white;
            line-height: 1.2;
        }

        .container {
            max-width: 210mm;
            margin: 0 auto;
            background: white;
            padding: 10px;
        }

        .header {
            text-align: right;
            margin-bottom: 8px;
            font-size: 10px;
        }

        .header-line {
            border-bottom: 2px solid #000;
            margin-bottom: 10px;
        }

        .title {
            text-align: center;
            font-size: 14px;
            font-weight: bold;
            margin: 10px 0;
            letter-spacing: 1px;
        }

        .title-line {
            border-bottom: 2px solid #000;
            margin-bottom: 15px;
        }

        .form-group {
            display: flex;
            margin-bottom: 5px;
            align-items: flex-start;
        }

        .form-label {
            width: 30%;
            padding-right: 10px;
            display: flex;
            align-items: center;
        }

        .form-number {
            width: 30px;
            text-align: left;
        }

        .form-colon {
            margin: 0 10px;
        }

        .form-value {
            flex: 1;
            border-bottom: 1px solid #000;
            min-height: 18px;
            padding: 2px 5px;
        }

        .checkbox-group {
            display: inline-flex;
            align-items: center;
            margin-right: 15px;
        }

        .checkbox {
            width: 14px;
            height: 14px;
            border: 1px solid #000;
            display: inline-block;
            margin: 0 5px;
            text-align: center;
            line-height: 12px;
            font-size: 10px;
        }

        .checkbox.checked::before {
            content: '✓';
        }

        .sub-section {
            margin-left: 30px;
            padding: 8px;
            border: 1px solid #000;
            margin-top: 4px;
            margin-bottom: 8px;
        }

        .sub-section-title {
            font-weight: bold;
            margin-bottom: 8px;
        }

        .pernyataan {
            margin-top: 15px;
            border-top: 2px solid #000;
            padding-top: 8px;
        }

        .pernyataan-title {
            font-weight: bold;
            margin-bottom: 5px;
        }

        .pernyataan-content {
            text-align: justify;
            line-height: 1.3;
            margin-bottom: 4px;
        }

        .signature-section {
            display: flex;
            justify-content: space-between;
            margin-top: 25px;
            margin-bottom: 20px;
        }

        .signature-box {
            text-align: center;
            width: 45%;
        }

        .footer {
            border-top: 2px solid #000;
            padding-top: 10px;
            font-size: 9px;
        }

        @media print {
            body {
                padding: 0;
            }
            .container {
                max-width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <div>CIF : {{ isset($simpanan) && $simpanan->jenis_simpanan && $simpanan->id ? $simpanan->jenis_simpanan . '.' . $simpanan->id : '_________________' }}</div>
            <div>No Rekening : {{ isset($simpanan) && $simpanan->nomor_rekening ? $simpanan->nomor_rekening : '_________________' }}</div>
        </div>
        <div class="header-line"></div>

        <!-- Title -->
        <div class="title">FORMULIR SIMPANAN NASABAH</div>
        <div class="title-line"></div>

        <!-- Form Fields -->
        <div class="form-group">
            <div class="form-label">
                <span class="form-number">1.</span>
                <span>N I K</span>
            </div>
            <span class="form-colon">:</span>
            <div class="form-value">{{ isset($simpanan->anggota->nik) ? $simpanan->anggota->nik : '' }}</div>
        </div>

        <div class="form-group">
            <div class="form-label">
                <span class="form-number">2.</span>
                <span>Nama Lengkap</span>
            </div>
            <span class="form-colon">:</span>
            <div class="form-value">{{ isset($simpanan->anggota->namadepan) ? $simpanan->anggota->namadepan : '' }}</div>
        </div>

        <div class="form-group">
            <div class="form-label">
                <span class="form-number">3.</span>
                <span>Nama Alias</span>
            </div>
            <span class="form-colon">:</span>
            <div class="form-value" style="width: 50%;">{{ isset($simpanan->anggota->nama_pangilan) ? $simpanan->anggota->nama_pangilan : '' }}</div>
            <div style="margin-left: 20px;">
                <span class="checkbox-group">
                    <span class="checkbox {{ isset($simpanan->anggota->jk) && $simpanan->anggota->jk == 'L' ? 'checked' : '' }}"></span>
                    <span>Laki Laki</span>
                </span>
                <span class="checkbox-group">
                    <span class="checkbox {{ isset($simpanan->anggota->jk) && $simpanan->anggota->jk == 'P' ? 'checked' : '' }}"></span>
                    <span>Perempuan</span>
                </span>
            </div>
        </div>

        <div class="form-group">
            <div class="form-label">
                <span class="form-number">4.</span>
                <span>Tempat, Tanggal Lahir</span>
            </div>
            <span class="form-colon">:</span>
            <div class="form-value">
                @if(isset($simpanan->anggota->tempat_lahir))
                    {{ $simpanan->anggota->tempat_lahir }}
                    @if(isset($simpanan->anggota->tgl_lahir))
                        , {{ \Carbon\Carbon::parse($simpanan->anggota->tgl_lahir)->format('d-m-Y') }}
                    @endif
                @endif
            </div>
        </div>

        <div class="form-group">
            <div class="form-label">
                <span class="form-number">5.</span>
                <span>Alamat</span>
            </div>
            <span class="form-colon">:</span>
            <div class="form-value">{{ isset($simpanan->anggota->alamat) ? $simpanan->anggota->alamat : '' }}</div>
        </div>

        <div class="form-group">
            <div class="form-label">
                <span class="form-number">6.</span>
                <span>No. Handphone</span>
            </div>
            <span class="form-colon">:</span>
            <div class="form-value">{{ isset($simpanan->anggota->hp) ? $simpanan->anggota->hp : '' }}</div>
        </div>

        <div class="form-group">
            <div class="form-label">
                <span class="form-number">7.</span>
                <span>Agama</span>
            </div>
            <span class="form-colon">:</span>
            <div class="form-value">{{ isset($simpanan->anggota->agama) ? $simpanan->anggota->agama : '' }}</div>
        </div>

        <div class="form-group">
            <div class="form-label">
                <span class="form-number">8.</span>
                <span>Status Pernikahan</span>
            </div>
            <span class="form-colon">:</span>
            <div class="form-value">
                <span class="checkbox-group">
                    <span class="checkbox {{ isset($simpanan->anggota->status_pernikahan) && $simpanan->anggota->status_pernikahan == 'Lajang' ? 'checked' : '' }}"></span>
                    <span>Lajang</span>
                </span>
                <span class="checkbox-group">
                    <span class="checkbox {{ isset($simpanan->anggota->status_pernikahan) && $simpanan->anggota->status_pernikahan == 'Menikah' ? 'checked' : '' }}"></span>
                    <span>Menikah</span>
                </span>
                <span class="checkbox-group">
                    <span class="checkbox {{ isset($simpanan->anggota->status_pernikahan) && $simpanan->anggota->status_pernikahan == 'Duda/Janda' ? 'checked' : '' }}"></span>
                    <span>Duda/Janda</span>
                </span>
            </div>
        </div>

        <div class="form-group">
            <div class="form-label">
                <span class="form-number">9.</span>
                <span>Pendidikan Terakhir</span>
            </div>
            <span class="form-colon">:</span>
            <div class="form-value">
                <span class="checkbox-group">
                    <span class="checkbox {{ isset($simpanan->anggota->pendidikan) && $simpanan->anggota->pendidikan == 'Tidak ada' ? 'checked' : '' }}"></span>
                    <span>Tidak ada</span>
                </span>
                <span class="checkbox-group">
                    <span class="checkbox {{ isset($simpanan->anggota->pendidikan) && $simpanan->anggota->pendidikan == 'SD' ? 'checked' : '' }}"></span>
                    <span>SD</span>
                </span>
                <span class="checkbox-group">
                    <span class="checkbox {{ isset($simpanan->anggota->pendidikan) && $simpanan->anggota->pendidikan == 'SLTP' ? 'checked' : '' }}"></span>
                    <span>SLTP</span>
                </span>
                <span class="checkbox-group">
                    <span class="checkbox {{ isset($simpanan->anggota->pendidikan) && $simpanan->anggota->pendidikan == 'SLTA' ? 'checked' : '' }}"></span>
                    <span>SLTA</span>
                </span>
                <span class="checkbox-group">
                    <span class="checkbox {{ isset($simpanan->anggota->pendidikan) && $simpanan->anggota->pendidikan == 'D3' ? 'checked' : '' }}"></span>
                    <span>D3</span>
                </span>
                <span class="checkbox-group">
                    <span class="checkbox {{ isset($simpanan->anggota->pendidikan) && $simpanan->anggota->pendidikan == 'S1' ? 'checked' : '' }}"></span>
                    <span>S1</span>
                </span>
                <span class="checkbox-group">
                    <span class="checkbox {{ isset($simpanan->anggota->pendidikan) && $simpanan->anggota->pendidikan == 'S2' ? 'checked' : '' }}"></span>
                    <span>S2</span>
                </span>
                <span class="checkbox-group">
                    <span class="checkbox {{ isset($simpanan->anggota->pendidikan) && $simpanan->anggota->pendidikan == 'S3' ? 'checked' : '' }}"></span>
                    <span>S3</span>
                </span>
                <span class="checkbox-group">
                    <span class="checkbox {{ isset($simpanan->anggota->pendidikan) && !in_array($simpanan->anggota->pendidikan, ['Tidak ada', 'SD', 'SLTP', 'SLTA', 'D3', 'S1', 'S2', 'S3', '']) ? 'checked' : '' }}"></span>
                    <span>Lainnya</span>
                </span>
            </div>
        </div>

        <div class="form-group">
            <div class="form-label">
                <span class="form-number">10.</span>
                <span>Pekerjaan</span>
            </div>
            <span class="form-colon">:</span>
            <div class="form-value">{{ isset($simpanan->anggota->usaha) ? $simpanan->anggota->usaha : '' }}</div>
        </div>

        <div class="form-group">
            <div class="form-label">
                <span class="form-number">11.</span>
                <span>Alamat Pekerjaan</span>
            </div>
            <span class="form-colon">:</span>
            <div class="form-value">{{ isset($simpanan->anggota->tempat_kerja) ? $simpanan->anggota->tempat_kerja : '' }}</div>
        </div>

        <div class="form-group">
            <div class="form-label">
                <span class="form-number">12.</span>
                <span>NPWP</span>
            </div>
            <span class="form-colon">:</span>
            <div class="form-value"></div>
        </div>

        <div class="form-group">
            <div class="form-label">
                <span class="form-number">13.</span>
                <span>Penghasilan per Bulan</span>
            </div>
            <span class="form-colon">:</span>
            <div class="form-value">
                <span class="checkbox-group">
                    <span class="checkbox"></span>
                    <span>&lt; 5 jt</span>
                </span>
                <span class="checkbox-group">
                    <span class="checkbox"></span>
                    <span>5 - 15 jt</span>
                </span>
                <span class="checkbox-group">
                    <span class="checkbox"></span>
                    <span>15 - 25 jt</span>
                </span>
                <span class="checkbox-group">
                    <span class="checkbox"></span>
                    <span>&gt; 25 jt</span>
                </span>
            </div>
        </div>

        <div class="form-group">
            <div class="form-label">
                <span class="form-number">14.</span>
                <span>Sumber Dana</span>
            </div>
            <span class="form-colon">:</span>
            <div class="form-value">
                <span class="checkbox-group">
                    <span class="checkbox"></span>
                    <span>Gaji/Upah</span>
                </span>
                <span class="checkbox-group">
                    <span class="checkbox"></span>
                    <span>Usaha</span>
                </span>
                <span class="checkbox-group">
                    <span class="checkbox"></span>
                    <span>Lainnya :</span>
                </span>
            </div>
        </div>

        <div class="form-group">
            <div class="form-label">
                <span class="form-number">15.</span>
                <span>Jenis Rekening</span>
            </div>
            <span class="form-colon">:</span>
            <div class="form-value">
                <span class="checkbox-group">
                    <span class="checkbox {{ isset($simpanan->jenis_simpanan) && $simpanan->jenis_simpanan == 'Simantera' ? 'checked' : '' }}"></span>
                    <span>Simantera</span>
                </span>
                <span class="checkbox-group">
                    <span class="checkbox {{ isset($simpanan->jenis_simpanan) && $simpanan->jenis_simpanan == 'Simantera Qurban' ? 'checked' : '' }}"></span>
                    <span>Simantera Qurban</span>
                </span>
                <span class="checkbox-group">
                    <span class="checkbox {{ isset($simpanan->jenis_simpanan) && $simpanan->jenis_simpanan == 'Simantera Hari raya' ? 'checked' : '' }}"></span>
                    <span>Simantera Hari raya</span>
                </span>
            </div>
        </div>

        <div class="form-group">
            <div class="form-label">
                <span class="form-number">16.</span>
                <span>Status Permohonan</span>
            </div>
            <span class="form-colon">:</span>
            <div class="form-value">
                <span class="checkbox-group">
                    <span class="checkbox"></span>
                    <span>Pribadi</span>
                </span>
                <span class="checkbox-group">
                    <span class="checkbox"></span>
                    <span>Kuasa</span>
                </span>
            </div>
        </div>

        <!-- Sub Section - Kuasa -->
        <div class="sub-section">
            <div class="sub-section-title">Isikan Jika Status Permohonan Kuasa</div>

            <div class="form-group" style="margin-bottom: 5px;">
                <div class="form-label" style="width: 40%;">
                    <span class="form-number">1.</span>
                    <span>Nama Lembaga</span>
                </div>
                <span class="form-colon">:</span>
                <div class="form-value">{{ isset($simpanan->lembaga) ? $simpanan->lembaga : '' }}</div>
            </div>

            <div class="form-group" style="margin-bottom: 5px;">
                <div class="form-label" style="width: 40%;">
                    <span class="form-number">2.</span>
                    <span>Jabatan</span>
                </div>
                <span class="form-colon">:</span>
                <div class="form-value">{{ isset($simpanan->jabatan) ? $simpanan->jabatan : '' }}</div>
            </div>
        </div>

        <div class="form-group">
            <div class="form-label">
                <span class="form-number">17.</span>
                <span>Pengampu</span>
            </div>
            <span class="form-colon">:</span>
            <div class="form-value">{{ isset($simpanan->pengampu) ? $simpanan->pengampu : '' }}</div>
        </div>

        <div class="form-group">
            <div class="form-label">
                <span class="form-number">18.</span>
                <span>Hubungan</span>
            </div>
            <span class="form-colon">:</span>
            <div class="form-value">{{ isset($simpanan->hubungan) ? $simpanan->hubungan : '' }}</div>
        </div>

        <div class="form-group">
            <div class="form-label">
                <span class="form-number">19.</span>
                <span>Nama Gadis Ibu Kandung</span>
            </div>
            <span class="form-colon">:</span>
            <div class="form-value">{{ isset($simpanan->anggota->nama_ibu) ? $simpanan->anggota->nama_ibu : '' }}</div>
        </div>

        <!-- Pernyataan Section -->
        <div class="pernyataan">
            <div class="pernyataan-title">PERNYATAAN</div>
            <div class="pernyataan-content">
                Bersama ini saya menyatakan bahwa
            </div>
            <div class="pernyataan-content">
                1. Semua data isian diatas adalah benar,
            </div>
            <div class="pernyataan-content">
                2. Menyetujui serta tunduk pada ketentuan dan syarat umum yang berlaku pada pembukaan rekening di <strong>{{ $kec->nama_lembaga_sort ?? 'Lembaga Keuangan Mikro' }}</strong>,
            </div>
            <div class="pernyataan-content">
                3. Dana yang saya setorkan dan pergunakan tidak berasal dari / untuk tujuan <i>money laundering</i> atau pencucian uang.
            </div>

            <!-- Signature Section -->
            <div class="signature-section">
                <div class="signature-box">
                    <div>Petugas Penerima</div>
                    <div style="height: 80px;"></div>
                    <div style="border-bottom: 1px solid #000; width: 80%; margin: 0 auto;"></div>
                </div>
                <div class="signature-box">
                    <div>Pemohon</div>
                    <div style="height: 80px;"></div>
                    <div style="border-bottom: 1px solid #000; width: 80%; margin: 0 auto;"></div>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="footer">
            Dicetak Oleh: {{ auth()->check() ? auth()->user()->name : '' }} ; pada: {{ now()->format('Y-m-d H:i:s') }}
        </div>
    </div>

    <script>
        window.onload = function() {
            window.print();
        }
    </script>
</body>
</html>
