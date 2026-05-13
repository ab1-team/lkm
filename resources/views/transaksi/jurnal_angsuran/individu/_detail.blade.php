@php
    use App\Utils\Tanggal;
    $t_pokok = 0;
    $t_jasa = 0;
    $t_denda = 0;
	$total_rencana_jasa = $nia->ra_i->sum('wajib_jasa');
@endphp

<form action="" method="post" id="FormCetakBuktiAngsuran" target="_blank">
    @csrf

    <table border="1" class="table table-striped midle">
        <thead class="bg-dark text-white">
            <tr>
                <td width="40" class="align-middle">
                    <div class="d-flex justify-content-center align-items-center">
                        <input class="form-check-input m-0" type="checkbox" value="true" id="checked" name="checked">
                    </div>
                </td>
                <th>Tanggal</th>
                <th>Keterangan</th>
                <th>Kode Kuitansi</th>
                <th>Pencairan</th>
                <th>Pokok</th>
                <th>Jasa</th>
                <th>Denda</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td colspan="4" align="center" style="text-transform: uppercase;">
                    <b>Target Pembayaran</b>
                </td>
                <td align="right"><b>{{ number_format($nia->alokasi) }}</b></td>
                <td align="right"><b>{{ number_format($nia->alokasi) }}</b></td>
                <td align="right"><b>{{ number_format($total_rencana_jasa) }}</b></td>
                <td align="right"><b>0</b></td>
            </tr>
            @foreach ($nia->real_i as $real_i)
                @php
                    $keterangan = '';
                    $denda = 0;
                    $idt = 0;
                @endphp
                @foreach ($real_i->trx as $trx)
                    @php
                        if (
                            $trx->rekening_kredit == '4.1.02.01' ||
                            $trx->rekening_kredit == '4.1.02.02' ||
                            $trx->rekening_kredit == '4.1.02.03' ||
                            $trx->rekening_kredit == '4.1.02.04' ||
                            $trx->rekening_kredit == '4.1.02.05'
                        ) {
                            $denda += $trx->jumlah;
                            $idt = $trx->idt;
                            continue;
                        }

                        $keterangan .= $trx->keterangan_transaksi . '<br>';
                        $idt = $trx->idt;
                    @endphp
                @endforeach
                <tr>
                    <td align="center" class="align-middle">
                        <div class="d-flex justify-content-center align-items-center">
                            <input class="form-check-input m-0" type="checkbox" value="{{ $idt }}"
                                id="{{ $idt }}" name="cetak[]" data-input="checked">
                        </div>
                    </td>
                    <td align="center">{{ Tanggal::tglIndo($real_i->tgl_transaksi) }}</td>
                    <td>{!! $keterangan !!}</td>
                    <td align="center">{{ $real_i->id }}</td>
                    <td align="right">0</td>
                    <td align="right">{{ number_format($real_i->realisasi_pokok) }}</td>
                    <td align="right">{{ number_format($real_i->realisasi_jasa) }}</td>
                    <td align="right">{{ number_format($denda) }}</td>
                </tr>

                @php
                    $t_pokok += $real_i->realisasi_pokok;
                    $t_jasa += $real_i->realisasi_jasa;
                    $t_denda += $denda;
                @endphp
            @endforeach

            <tr>
                <td colspan="4" align="right" style="text-transform: uppercase;">
                    <b>Total Transaksi</b>
                </td>
                <td align="right"><b>0</b></td>
                <td align="right"><b>{{ number_format($t_pokok) }}</b></td>
                <td align="right"><b>{{ number_format($t_jasa) }}</b></td>
                <td align="right"><b>{{ number_format($t_denda) }}</b></td>
            </tr>

            <tr>
                <td colspan="4" align="right" style="text-transform: uppercase;">
                    <b>Saldo</b>
                </td>
                <td align="right"><b>{{ number_format($nia->alokasi) }}</b></td>
                <td align="right"><b>{{ number_format($nia->alokasi - $t_pokok) }}</b></td>
                <td align="right"><b>{{ number_format(($total_rencana_jasa) - $t_jasa) }}</b></td>
                <td align="right"><b>{{ number_format($t_denda) }}</b></td>
            </tr>
        </tbody>
    </table>
</form>

<script>
    $(document).on('click', '#checked', function() {
        if ($(this)[0].checked == true) {
            $('[data-input=checked]').prop('checked', true)
        } else {
            $('[data-input=checked]').prop('checked', false)
        }
    })
</script>
