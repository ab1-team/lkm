<form action="/pengaturan/spk/{{ $kec->id }}" method="post" id="FormSPK">
    @csrf
    @method('PUT')

    <textarea name="editor_spk" id="editor_spk">{!! json_decode($kec->redaksi_spk, true) !!}</textarea>
    <textarea name="spk" id="spk" class="d-none"></textarea>
</form>

<div class="d-flex justify-content-end mt-3 align-items-center gap-2">
    <button type="button" data-bs-toggle="modal" data-bs-target="#keyword" class="btn btn-info btn-sm text-nowrap"
        style="height: 36px; border-radius: 8px !important; margin: 0 !important; display: inline-flex; align-items: center; font-size: 0.75rem !important; padding: 0 12px !important;">
        Kata Kunci
    </button>
    <button type="button" id="SimpanSPK" data-target="#FormSPK" class="btn btn-dark btn-sm text-nowrap btn-simpan"
        style="height: 36px; border-radius: 8px !important; margin: 0 !important; display: inline-flex; align-items: center; font-size: 0.75rem !important; padding: 0 12px !important;">
        Simpan Perubahan
    </button>
</div>

@section('modal')
    <div class="modal fade" id="keyword" tabindex="-1" aria-labelledby="keywordLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="keywordLabel">Kata Kunci</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-7">
                            <div class="alert alert-info text-center">
                                <b>Kata Kunci</b> yang digunakan dalam redaksi SPK
                            </div>
                            <table class="table table-striped midle">
                                <thead class="bg-dark text-white">
                                    <tr>
                                        <th width="10">No</th>
                                        <th width="100">Kata Kunci</th>
                                        <th>Keterangan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($keywordSPK as $keyword => $value)
                                        <tr>
                                            <td>{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}.</td>
                                            <td><code>{{ $keyword }}</code></td>
                                            <td>{{ $value['desc'] }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="col-md-5">
                            <div class="alert alert-info text-center">
                                <b>Fungsi</b>
                            </div>
                            <div class="alert alert-info">
                                <div style="text-align: justify;">
                                    Penulisan fungsi berada diantara <code>{</code> dan <code>}</code> dan diawali dengan
                                    tanda
                                    sama dengan (<code>=</code>) diikuti dengan nama fungsi. Contoh :
                                    <code>{=terbilang(10000)}</code>
                                    akan menghasilkan teks <code>Sepuluh Ribu</code>.
                                </div>
                                <div style="text-align: justify;">
                                    Anda juga dapat memasukkan kata kunci/fungsi kedalam fungsi.
                                    <div>
                                        Contoh :
                                        <ul>
                                            <li><code>{=terbilang({alokasi})}</code></li>
                                            <li><code>{=terbilang({=(10000*(10/100))})}</code></li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            <div class="accordion accordion-flush" id="accordionFlushExample">
                                @foreach ($fungsiSPK as $fungsi => $value)
                                    <div class="accordion-item">
                                        <h2 class="accordion-header" id="toggle{{ $fungsi }}">
                                            <button class="accordion-button collapsed" type="button"
                                                data-bs-toggle="collapse" data-bs-target="#content{{ $fungsi }}"
                                                aria-expanded="false" aria-controls="content{{ $fungsi }}">
                                                <b>{{ $fungsi }}</b>
                                            </button>
                                        </h2>
                                        <div id="content{{ $fungsi }}" class="accordion-collapse collapse"
                                            aria-labelledby="toggle{{ $fungsi }}"
                                            data-bs-parent="#accordionFlushExample">
                                            <div class="accordion-body">
                                                <blockquote class="blockquote">
                                                    <code>{{ $value['fungsi'] }}</code>
                                                </blockquote>

                                                <div style="text-align: justify;">
                                                    {!! $value['desc'] !!}.
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger btn-sm" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>
@endsection
