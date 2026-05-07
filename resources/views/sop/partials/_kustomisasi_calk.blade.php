<form action="/pengaturan/kustomisasi_calk/{{ $kec->id }}" method="post" id="FormCalk">
    @csrf
    @method('PUT')

    <textarea name="editor_calk" id="editor_calk">{!! json_decode($kec->custom_calk, true) !!}</textarea>
    <textarea name="custom_calk" id="custom_calk" class="d-none"></textarea>
</form>

<div class="d-flex justify-content-end mt-3 align-items-center gap-2">
    <button type="button" data-bs-toggle="modal" data-bs-target="#keyword" class="btn btn-info btn-sm text-nowrap"
        style="height: 36px; border-radius: 8px !important; margin: 0 !important; display: inline-flex; align-items: center; font-size: 0.75rem !important; padding: 0 12px !important;">
        Kata Kunci
    </button>
    <button type="button" id="SimpanCalk" data-target="#FormCalk" class="btn btn-dark btn-sm text-nowrap btn-simpan"
        style="height: 36px; border-radius: 8px !important; margin: 0 !important; display: inline-flex; align-items: center; font-size: 0.75rem !important; padding: 0 12px !important;">
        Simpan Perubahan
    </button>
</div>
