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

<style>
    .badge-pos-d { background-color: #2dce89; color: #fff; padding: 2px 6px; border-radius: 4px; font-size: 9pt; font-weight: 600; }
    .badge-pos-k { background-color: #f5365c; color: #fff; padding: 2px 6px; border-radius: 4px; font-size: 9pt; font-weight: 600; }
    .col-akun { font-size: 9pt; line-height: 1.3; }
</style>

<table border="0" width="100%" cellspacing="0" cellpadding="0" class="table table-striped midle">
    <thead class="bg-dark text-white">
        <tr>
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
            <td align="center" width="170">&nbsp;</td>
        </tr>
    </thead>

    <tbody>
        <tr>
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
            <td align="center"></td>
        </tr>
        <tr>
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
            <td align="center"></td>
        </tr>

        @foreach ($transaksi as $trx)
            @php
                $kuitansi = false;
                $files = 'bm';
                if (
                    $keuangan->startWith($trx->rekening_debit, '1.1.01') &&
                    !$keuangan->startWith($trx->rekening_kredit, '1.1.01')
                ) {
                    $files = 'bkm';
                    $kuitansi = true;
                }
                if (
                    !$keuangan->startWith($trx->rekening_debit, '1.1.01') &&
                    $keuangan->startWith($trx->rekening_kredit, '1.1.01')
                ) {
                    $files = 'bkk';
                    $kuitansi = true;
                }
                if (
                    $keuangan->startWith($trx->rekening_debit, '1.1.01') &&
                    $keuangan->startWith($trx->rekening_kredit, '1.1.01')
                ) {
                    $files = 'bm';
                    $kuitansi = false;
                }
                if (
                    $keuangan->startWith($trx->rekening_debit, '1.1.02') &&
                    !(
                        $keuangan->startWith($trx->rekening_kredit, '1.1.01') ||
                        $keuangan->startWith($trx->rekening_kredit, '1.1.02')
                    )
                ) {
                    $files = 'bkm';
                    $kuitansi = true;
                }
                if (
                    $keuangan->startWith($trx->rekening_debit, '1.1.02') &&
                    $keuangan->startWith($trx->rekening_kredit, '1.1.02')
                ) {
                    $files = 'bm';
                    $kuitansi = false;
                }
                if (
                    $keuangan->startWith($trx->rekening_debit, '1.1.02') &&
                    $keuangan->startWith($trx->rekening_kredit, '1.1.01')
                ) {
                    $files = 'bm';
                    $kuitansi = false;
                }
                if (
                    $keuangan->startWith($trx->rekening_debit, '1.1.01') &&
                    $keuangan->startWith($trx->rekening_kredit, '1.1.02')
                ) {
                    $files = 'bm';
                    $kuitansi = false;
                }
                if (
                    $keuangan->startWith($trx->rekening_debit, '5.') &&
                    !(
                        $keuangan->startWith($trx->rekening_kredit, '1.1.01') ||
                        $keuangan->startWith($trx->rekening_kredit, '1.1.02')
                    )
                ) {
                    $files = 'bm';
                    $kuitansi = false;
                }
                if (
                    !(
                        $keuangan->startWith($trx->rekening_debit, '1.1.01') ||
                        $keuangan->startWith($trx->rekening_debit, '1.1.02')
                    ) &&
                    $keuangan->startWith($trx->rekening_kredit, '1.1.02')
                ) {
                    $files = 'bm';
                    $kuitansi = false;
                }
                if (
                    !(
                        $keuangan->startWith($trx->rekening_debit, '1.1.01') ||
                        $keuangan->startWith($trx->rekening_debit, '1.1.02')
                    ) &&
                    $keuangan->startWith($trx->rekening_kredit, '4.')
                ) {
                    $files = 'bm';
                    $kuitansi = false;
                }

                $ins = '';
                if (isset($trx->user->ins)) {
                    $ins = $trx->user->ins;
                }
            @endphp


            <tr>
                <td align="center">{{ $loop->iteration }}.</td>
                <td align="center">{{ Tanggal::tglIndo($trx->tgl_transaksi) }}</td>
                <td align="left" class="col-akun">
                    <span class="badge-pos-d">D</span>
                    {{ $trx->rekening_debit_nama }}
                </td>
                <td align="left" class="col-akun">
                    <span class="badge-pos-k">K</span>
                    {{ $trx->rekening_kredit_nama }}
                </td>
                <td>{{ $trx->keterangan_transaksi }}</td>
                <td align="center">{{ $trx->idt }}</td>
                <td align="right" style="{{ $trx->posisi_baris == 'D' ? 'background:#e6f9f0;font-weight:600;' : '' }}">{{ number_format($trx->debit_baris, 2) }}</td>
                <td align="right" style="{{ $trx->posisi_baris == 'K' ? 'background:#fde2e6;font-weight:600;' : '' }}">{{ number_format($trx->kredit_baris, 2) }}</td>
                <td align="right">{{ number_format($trx->saldo_running, 2) }}</td>
                <td align="center">{{ $ins }}</td>
                <td align="right">
                    <div class="btn-group">
                        @if ($kuitansi)
                            @if ($trx->idtp > 0 && $trx->id_pinj != 0)
                                <button type="button" data-idtp="{{ $trx->idtp }}"
                                    class="btn btn-instagram btn-icon-only btn-tooltip" data-bs-toggle="dropdown"
                                    aria-expanded="false">
                                    <span class="btn-inner--icon"><i class="fas fa-file"></i></span>
                                </button>
                                <ul class="dropdown-menu px-2 py-3" aria-labelledby="dropdownMenuButton">
                                    <li>
                                        <a class="dropdown-item border-radius-md" target="_blank"
                                            href="/transaksi/dokumen/struk/{{ $trx->idtp }}">
                                            Kuitansi
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item border-radius-md" target="_blank"
                                            href="/transaksi/dokumen/struk_matrix/{{ $trx->idtp }}">
                                            Kuitansi Dot Matrix
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item border-radius-md" target="_blank"
                                            href="/transaksi/dokumen/struk_thermal/{{ $trx->idtp }}">
                                            Kuitansi Thermal
                                        </a>
                                    </li>
                                </ul>
                            @else
                                <button type="button" class="btn btn-instagram btn-icon-only btn-tooltip"
                                    data-bs-toggle="dropdown" aria-expanded="false">
                                    <span class="btn-inner--icon"><i class="fas fa-file"></i></span>
                                </button>
                                <ul class="dropdown-menu px-2 py-3" aria-labelledby="dropdownMenuButton">
                                    <li>
                                        <a class="dropdown-item border-radius-md" target="_blank"
                                            href="/transaksi/dokumen/kuitansi/{{ $trx->idt }}">
                                            Kuitansi
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item border-radius-md" target="_blank"
                                            href="/transaksi/dokumen/kuitansi_thermal/{{ $trx->idt }}">
                                            Kuitansi Thermal
                                        </a>
                                    </li>
                                </ul>
                            @endif
                        @endif

                        @if ($trx->idtp > 0 && $trx->id_pinj != 0)
                            <button type="button"
                                data-action="/transaksi/dokumen/{{ $files }}_angsuran/{{ $trx->idt }}"
                                class="btn btn-tumblr btn-icon-only btn-tooltip btn-link" data-bs-toggle="tooltip"
                                data-bs-placement="top" title="{{ $files }}" data-container="body"
                                data-animation="true">
                                <span class="btn-inner--icon"><i class="fas fa-file-circle-exclamation"></i></span>
                            </button>
                        @else
                            <button type="button"
                                data-action="/transaksi/dokumen/{{ $files }}/{{ $trx->idt }}"
                                class="btn btn-tumblr btn-icon-only btn-tooltip btn-link" data-bs-toggle="tooltip"
                                data-bs-placement="top" title="{{ $files }}" data-container="body"
                                data-animation="true">
                                <span class="btn-inner--icon"><i class="fas fa-file-circle-exclamation"></i></span>
                            </button>
                        @endif

                        @if ($is_dir)
                            <button type="button" data-idt="{{ $trx->idt }}"
                                class="btn btn-tumblr btn-icon-only btn-tooltip btn-reversal" data-bs-toggle="tooltip"
                                data-bs-placement="top" title="Reversal" data-container="body"
                                data-animation="true">
                                <span class="btn-inner--icon"><i class="fas fa-code-pull-request"></i></span>
                            </button>
                            @if (!$is_ben)
                                <button type="button" data-idt="{{ $trx->idt }}"
                                    class="btn btn-github btn-icon-only btn-tooltip btn-delete"
                                    data-bs-toggle="tooltip" data-bs-placement="top" title="Hapus"
                                    data-container="body" data-animation="true">
                                    <span class="btn-inner--icon"><i class="fas fa-trash-can"></i></span>
                                </button>
                            @endif
                        @endif
                    </div>
                </td>
            </tr>
        @endforeach

        <tr>
            <td colspan="6">
                <b>Total Transaksi {{ ucwords($sub_judul) }}</b>
            </td>
            <td align="right">
                <b>{{ number_format($total_debit, 2) }}</b>
            </td>
            <td align="right">
                <b>{{ number_format($total_kredit, 2) }}</b>
            </td>
            <td colspan="3" rowspan="3" align="center" style="vertical-align: middle">
                <b>{{ number_format($total_saldo, 2) }}</b>
            </td>
        </tr>

        <tr>
            <td colspan="6">
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
            <td colspan="6">
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

<script>
    $(document).ready(function() {
        initializeBootstrapTooltip()
    })
</script>
