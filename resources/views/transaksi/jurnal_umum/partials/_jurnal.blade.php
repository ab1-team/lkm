@php
    use App\Utils\Tanggal;

    if ($rek->jenis_mutasi == 'debet') {
        $saldo_awal_tahun = $saldo['debit'] - $saldo['kredit'];
        $saldo_awal_bulan = $d_bulan_lalu - $k_bulan_lalu;
    } else {
        $saldo_awal_tahun = $saldo['kredit'] - $saldo['debit'];
        $saldo_awal_bulan = $k_bulan_lalu - $d_bulan_lalu;
    }

    $total_saldo = $transaksi->isNotEmpty() ? (float) $transaksi->last()->saldo_running : (float)($saldo_awal_tahun + $saldo_awal_bulan);
@endphp

<form action="/transaksi/dokumen/cetak" method="post" id="FormCetakDokumenTransaksi" target="_blank">
    @csrf

    <table border="0" width="100%" cellspacing="0" cellpadding="0" class="table table-striped midle">
        <thead class="bg-dark text-white">
            <tr>
                <td align="center" width="50">
                    <div class="form-check d-flex justify-content-center align-items-center mb-0">
                        <input class="form-check-input" type="checkbox" value="true" id="checked" name="checked" style="width: 18px; height: 18px; cursor: pointer;" title="Pilih Semua">
                    </div>
                </td>
                <td height="40" align="center" width="40">No</td>
                <td align="center" width="90">Tanggal</td>
                <td align="center" width="180">Kode Akun Debit (D)</td>
                <td align="center" width="180">Kode Akun Kredit (K)</td>
                <td align="center">Keterangan</td>
                <td align="center" width="70">Kode Trx.</td>
                <td align="center" width="130">Debit</td>
                <td align="center" width="130">Kredit</td>
                <td align="center" width="140">Saldo</td>
                <td align="center" width="40">Ins</td>
            </tr>
        </thead>

        <tbody>
            <tr>
                <td align="center"></td>
                <td align="center"></td>
                <td align="center">{{ Tanggal::tglIndo($tahun . '-01-01') }}</td>
                <td align="center"></td>
                <td align="center"></td>
                <td>Komulatif Transaksi Awal Tahun {{ $tahun }}</td>
                <td>&nbsp;</td>
                <td align="right">{{ number_format($saldo['debit'], 2) }}</td>
                <td align="right">{{ number_format($saldo['kredit'], 2) }}</td>
                <td align="right">{{ number_format($saldo_awal_tahun, 2) }}</td>
                <td align="center"></td>
            </tr>
            <tr>
                <td align="center"></td>
                <td align="center"></td>
                <td align="center">{{ Tanggal::tglIndo($tahun . '-' . $bulan . '-01') }}</td>
                <td align="center"></td>
                <td align="center"></td>
                <td>Komulatif Transaksi s/d Bulan Lalu</td>
                <td>&nbsp;</td>
                <td align="right">{{ number_format($d_bulan_lalu, 2) }}</td>
                <td align="right">{{ number_format($k_bulan_lalu, 2) }}</td>
                <td align="right">{{ number_format($saldo_awal_tahun + $saldo_awal_bulan, 2) }}</td>
                <td align="center"></td>
            </tr>

            @foreach ($transaksi as $trx)
                @php
                    $kuitansi = false;
                    $files = 'bm';
                    if ($keuangan->startWith($trx->rekening_debit, '1.1.01') && !$keuangan->startWith($trx->rekening_kredit, '1.1.01')) {
                        $files = 'bkm';
                        $kuitansi = true;
                    }
                    if (!$keuangan->startWith($trx->rekening_debit, '1.1.01') && $keuangan->startWith($trx->rekening_kredit, '1.1.01')) {
                        $files = 'bkk';
                        $kuitansi = true;
                    }
                    if ($keuangan->startWith($trx->rekening_debit, '1.1.01') && $keuangan->startWith($trx->rekening_kredit, '1.1.01')) {
                        $files = 'bm';
                        $kuitansi = false;
                    }
                    if ($keuangan->startWith($trx->rekening_debit, '1.1.02') && !($keuangan->startWith($trx->rekening_kredit, '1.1.01') || $keuangan->startWith($trx->rekening_kredit, '1.1.02'))) {
                        $files = 'bkm';
                        $kuitansi = true;
                    }
                    if ($keuangan->startWith($trx->rekening_debit, '1.1.02') && $keuangan->startWith($trx->rekening_kredit, '1.1.02')) {
                        $files = 'bm';
                        $kuitansi = false;
                    }
                    if ($keuangan->startWith($trx->rekening_debit, '1.1.02') && $keuangan->startWith($trx->rekening_kredit, '1.1.01')) {
                        $files = 'bm';
                        $kuitansi = false;
                    }
                    if ($keuangan->startWith($trx->rekening_debit, '1.1.01') && $keuangan->startWith($trx->rekening_kredit, '1.1.02')) {
                        $files = 'bm';
                        $kuitansi = false;
                    }
                    if ($keuangan->startWith($trx->rekening_debit, '5.') && !($keuangan->startWith($trx->rekening_kredit, '1.1.01') || $keuangan->startWith($trx->rekening_kredit, '1.1.02'))) {
                        $files = 'bm';
                        $kuitansi = false;
                    }
                    if (!($keuangan->startWith($trx->rekening_debit, '1.1.01') || $keuangan->startWith($trx->rekening_debit, '1.1.02')) && $keuangan->startWith($trx->rekening_kredit, '1.1.02')) {
                        $files = 'bm';
                        $kuitansi = false;
                    }
                    if (!($keuangan->startWith($trx->rekening_debit, '1.1.01') || $keuangan->startWith($trx->rekening_debit, '1.1.02')) && $keuangan->startWith($trx->rekening_kredit, '4.')) {
                        $files = 'bm';
                        $kuitansi = false;
                    }

                    $ins = '';
                    if (isset($trx->user->ins)) {
                        $ins = $trx->user->ins;
                    }
                @endphp


                <tr>
                    <td align="center">
                        <div class="form-check d-flex justify-content-center align-items-center mb-0">
                            <input class="form-check-input" type="checkbox" value="{{ $trx->idt }}"
                                id="chk_{{ $trx->idt }}" name="cetak[]" data-input="checked" style="width: 18px; height: 18px; cursor: pointer;">
                        </div>
                    </td>
                    <td align="center">{{ $loop->iteration }}.</td>
                    <td align="center">{{ Tanggal::tglIndo($trx->tgl_transaksi) }}</td>
                    <td align="left" style="font-size: 9pt;">(D) {{ $trx->rekening_debit_nama }}</td>
                    <td align="left" style="font-size: 9pt;">(K) {{ $trx->rekening_kredit_nama }}</td>
                    <td>{{ $trx->keterangan_transaksi }}</td>
                    <td align="center">{{ $trx->idt }}</td>
                    <td align="right">{{ number_format($trx->debit_baris, 2) }}</td>
                    <td align="right">{{ number_format($trx->kredit_baris, 2) }}</td>
                    <td align="right">{{ number_format($trx->saldo_running, 2) }}</td>
                    <td align="center">{{ $ins }}</td>
                </tr>
            @endforeach

            <tr>
                <td colspan="7">
                    <b>Total Transaksi {{ ucwords($sub_judul) }}</b>
                </td>
                <td align="right">
                    <b>{{ number_format($total_debit, 2) }}</b>
                </td>
                <td align="right">
                    <b>{{ number_format($total_kredit, 2) }}</b>
                </td>
                <td colspan="2" rowspan="3" align="center" style="vertical-align: middle">
                    <b>{{ number_format($total_saldo, 2) }}</b>
                </td>
            </tr>

            <tr>
                <td colspan="7">
                    <b>Total Transaksi sampai dengan {{ ucwords($sub_judul) }}</b>
                </td>
                <td align="right">
                    <b>{{ number_format($d_bulan_lalu + $total_debit, 2) }}</b>
                </td>
                <td align="right">
                    <b>{{ number_format($k_bulan_lalu + $total_kredit, 2) }}</b>
                </td>
            </tr>

            <tr>
                <td colspan="7">
                    <b>Total Transaksi Komulatif sampai dengan Tahun {{ $tahun }}</b>
                </td>
                <td align="right">
                    <b>{{ number_format($saldo['debit'] + $d_bulan_lalu + $total_debit, 2) }}</b>
                </td>
                <td align="right">
                    <b>{{ number_format($saldo['kredit'] + $k_bulan_lalu + $total_kredit, 2) }}</b>
                </td>
            </tr>
        </tbody>

    </table>
</form>

<script>
    $(document).on('click', '#checked', function() {
        var isChecked = $(this).prop('checked')
        $('[data-input="checked"]').prop('checked', isChecked).trigger('change')
    })

    $(document).on('change', '[data-input="checked"]', function() {
        var totalCheckboxes = $('[data-input="checked"]').length
        var checkedCheckboxes = $('[data-input="checked"]:checked').length
        $('#checked').prop('checked', totalCheckboxes === checkedCheckboxes && totalCheckboxes > 0)
    })
</script>
