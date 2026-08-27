@extends('layouts.base')

@section('content')
<div class="container py-4">
    <h4 class="mb-3">Riwayat Pembaruan</h4>

    @foreach ($items as $item)
        <div class="card mb-3">
            <div class="card-body">
                <span class="badge bg-{{ config("update_fitur.jenis.{$item->jenis}.badge", 'secondary') }}">
                    {{ config("update_fitur.jenis.{$item->jenis}.label", $item->jenis) }}
                </span>
                <h5 class="mt-2">{{ $item->judul }}</h5>
                <div class="text-muted small mb-2">{{ $item->tanggal->translatedFormat('d F Y, H:i') }}</div>
                <p>{{ $item->deskripsi }}</p>
                @if ($item->foto)
                    <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url('update-fitur/' . $item->foto) }}"
                         class="img-fluid rounded" alt="{{ $item->judul }}">
                @endif
            </div>
        </div>
    @endforeach

    {{ $items->links() }}
</div>
@endsection