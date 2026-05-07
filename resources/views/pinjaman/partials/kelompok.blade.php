@php
    use App\Models\PinjamanKelompok;

    $selected = false;
@endphp

<style>
    /* Modern Premium Select2 Styling */
    .select2-container--bootstrap-5 .select2-selection {
        border: 1px solid #d2d6da !important;
        border-radius: 8px !important;
        height: 40px !important;
        padding: 5px 12px !important;
        transition: all 0.2s ease-in-out !important;
        background-color: #fff !important;
        display: flex !important;
        align-items: center !important;
    }
    .select2-container--bootstrap-5 .select2-selection:focus,
    .select2-container--bootstrap-5.select2-container--focus .select2-selection {
        border-color: #5e72e4 !important;
        box-shadow: 0 0 0 3px rgba(94, 114, 228, 0.15) !important;
    }
    .select2-container--bootstrap-5 .select2-selection__rendered {
        color: #495057 !important;
        font-size: 0.875rem !important;
        line-height: normal !important;
        padding-left: 0 !important;
    }
    .select2-container--bootstrap-5 .select2-selection__arrow {
        height: 38px !important;
        right: 10px !important;
    }
    .select2-dropdown {
        border: 1px solid #e2e8f0 !important;
        border-radius: 10px !important;
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.08), 0 4px 6px -2px rgba(0, 0, 0, 0.04) !important;
        overflow: hidden !important;
    }
    .select2-results__option {
        padding: 8px 12px !important;
        font-size: 0.875rem !important;
    }
    .select2-container--bootstrap-5 .select2-results__option--highlighted[aria-selected] {
        background-color: #5e72e4 !important;
    }
</style>

<div class="d-flex align-items-center w-100 gap-2">
    <div class="flex-grow-1">
        <select class="js-example-basic-single form-select" name="kelompok" id="kelompok" style="width:100%">
            @foreach ($kelompok as $kel)
                @php
                    $pinjaman = 'N';
                    if ($kel->pinjaman_count > 0) {
                        $status = $kel->pinjaman->status;
                        $pinjaman = $status;
                    }

                    $select = false;
                    if (!($pinjaman == 'P' || $pinjaman == 'V' || $pinjaman == 'W') && !$selected) {
                        $select = true;
                        $selected = true;
                    }

                    if ($id_kel > 0) {
                        $select = false;
                    }

                    if ($kel->id == $id_kel) {
                        $select = true;
                    }
                @endphp
                <option {{ $select ? 'selected' : '' }} value="{{ $kel->id }}">
                    @if (isset($kel->d))
                        [{{ $pinjaman }}] [{{ $kel->kd_kelompok }}] {{ $kel->nama_kelompok }}
                        [{{ $kel->d->nama_desa }}]
                        [{{ $kel->ketua }}]
                    @else
                        [{{ $pinjaman }}] [{{ $kel->kd_kelompok }}] {{ $kel->nama_kelompok }} []
                        [{{ $kel->ketua }}]
                    @endif
                </option>
            @endforeach
        </select>
        <small class="text-danger" id="msg_kelompok"></small>
    </div>

    <div class="d-flex">
        <a href="/database/kelompok/register_kelompok" class="btn btn-primary btn-sm text-nowrap" style="height: 36px; border-radius: 8px !important; margin: 0 !important; display: inline-flex; align-items: center; font-size: 0.75rem !important; padding: 0 12px !important;">
            Register Kelompok
        </a>
    </div>
</div>

<script>
    $('.js-example-basic-single').select2({
        theme: 'bootstrap-5'
    });
</script>
