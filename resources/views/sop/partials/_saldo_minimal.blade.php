<div class="row">
    <div class="col-12">
        <h6 class="mb-3">Pengaturan Saldo Minimal Simpanan</h6>
        <p class="text-muted small mb-3">
            Tentukan saldo minimal untuk setiap jenis simpanan. Penarikan tidak diizinkan jika saldo akhir
            setelah transaksi berada di bawah nilai ini. Default: Rp 20.000.
        </p>
    </div>
</div>

<form action="/pengaturan/saldo_minimal_simpanan" method="post" id="FormSaldoMinimal">
    @csrf
    @method('PUT')
    <div class="table-responsive">
        <table class="table table-sm table-bordered align-middle mb-0">
            <thead class="thead-light">
                <tr>
                    <th style="width: 50px;">No</th>
                    <th>Jenis Simpanan</th>
                    <th style="width: 240px;">Saldo Minimal (Rp.)</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($jenisSimpananList as $i => $js)
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td>{{ $js->nama_js }}</td>
                        <td>
                            <input type="number" min="0"
                                name="saldo_minimal[{{ $js->id }}]"
                                id="saldo_minimal_{{ $js->id }}"
                                class="form-control form-control-sm"
                                value="{{ $js->saldo_minimal ?? 20000 }}">
                            <small class="text-danger" id="msg_saldo_minimal_{{ $js->id }}"></small>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="text-center text-muted">Belum ada jenis simpanan.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</form>

<div class="d-flex justify-content-end mb-3 mt-3">
    <button type="button" id="SimpanSaldoMinimal" data-target="#FormSaldoMinimal"
        class="btn btn-sm btn-dark mb-0 btn-simpan">
        Simpan Saldo Minimal
    </button>
</div>