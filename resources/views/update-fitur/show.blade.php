@extends('layouts.base')

@section('content')
@php
    $badge = config("update_fitur.jenis.{$update_fitur->jenis}.badge", 'secondary');
    $label = config("update_fitur.jenis.{$update_fitur->jenis}.label", $update_fitur->jenis);
    $icon  = config("update_fitur.jenis.{$update_fitur->jenis}.icon", 'fa-circle-info');
@endphp

<style>
    .detail-hero {
        border: 3px solid #0d6efd;
        border-radius: 8px;
    }
    .detail-icon {
        width: 56px;
        height: 56px;
        border-radius: 50%;
        background: #e0e7ff;
        color: #0d6efd;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.4rem;
    }
    .detail-foto {
        max-width: 100%;
        border-radius: 8px;
        margin-top: 1.25rem;
    }
</style>

<div class="app-main__inner">
    <div class="main-card mb-3 card">
        <div class="card-body">
            <div class="d-flex align-items-center mb-3">
                <a href="{{ route('notif.timeline') }}" class="btn btn-sm btn-outline-secondary me-3">
                    <i class="fa fa-arrow-left"></i>
                </a>
                <h5 class="card-title m-0">Detail Pembaruan</h5>
            </div>

            <div class="detail-hero p-4">
                <div class="d-flex align-items-start mb-3">
                    <div class="detail-icon me-3">
                        <i class="fa {{ $icon }}"></i>
                    </div>
                    <div class="flex-grow-1">
                        <span class="badge bg-{{ $badge }} mb-2">{{ $label }}</span>
                        <h4 class="m-0">{{ $update_fitur->judul }}</h4>
                        <div class="text-muted small mt-1">
                            <i class="fa fa-calendar me-1"></i>
                            {{ $update_fitur->tanggal->translatedFormat('d F Y, H:i') }}
                        </div>
                    </div>
                </div>

                <hr>

                <div class="detail-deskripsi" style="white-space: pre-line;">
                    {{ $update_fitur->deskripsi }}
                </div>

                @if ($update_fitur->foto)
                    <img src="{{ \Illuminate\Support\Facades\Storage::disk('public_uploads')->url('update-fitur/' . $update_fitur->foto) }}"
                         alt="{{ $update_fitur->judul }}" class="detail-foto">
                @endif
            </div>

            <div class="mt-3 text-end">
                <a href="{{ route('notif.timeline') }}" class="btn btn-sm btn-primary">
                    <i class="fa fa-arrow-left me-1"></i> Kembali ke Riwayat
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
