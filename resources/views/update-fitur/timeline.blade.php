@extends('layouts.base')

@section('content')
<style>
    .timeline-wrap {
        position: relative;
        padding-left: 56px;
    }
    .timeline-wrap .timeline-item {
        position: relative;
        padding-bottom: 1.25rem;
    }
    .timeline-wrap .timeline-item:last-child {
        padding-bottom: 0;
    }
    .timeline-wrap .timeline-item::before {
        content: "";
        position: absolute;
        left: -38px;
        top: 26px;
        bottom: -2px;
        width: 2px;
        background: #0d6efd;
    }
    .timeline-wrap .timeline-item:last-child::before {
        display: none;
    }
    .timeline-wrap .timeline-icon {
        position: absolute;
        left: -56px;
        top: 4px;
        width: 36px;
        height: 36px;
        border-radius: 50%;
        background: #fff;
        border: 2px solid #0d6efd;
        color: #0d6efd;
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 1;
    }
    .timeline-wrap .timeline-card {
        background: #fff;
        border: 3px solid #0d6efd;
        border-radius: 8px;
        padding: 14px 18px;
        box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04);
        transition: box-shadow .15s ease, transform .15s ease;
        color: inherit;
        text-decoration: none;
        display: block;
    }
    .timeline-wrap .timeline-card:hover {
        box-shadow: 0 6px 18px rgba(13, 110, 253, 0.15);
        transform: translateY(-1px);
        color: inherit;
        text-decoration: none;
    }
    .timeline-wrap .timeline-judul {
        font-weight: 600;
        color: #1e293b;
        margin-bottom: 2px;
    }
    .timeline-wrap .timeline-tanggal {
        font-size: .8rem;
        color: #64748b;
    }
</style>

<div class="app-main__inner">
    <div class="main-card mb-3 card">
        <div class="card-body">
            <h5 class="card-title mb-4">Riwayat Pembaruan</h5>

            @if ($items->count() === 0)
                <div class="text-center text-muted py-5">Belum ada riwayat pembaruan.</div>
            @else
                <div class="timeline-wrap">
                    @foreach ($items as $item)
                        @php
                            $icon = config("update_fitur.jenis.{$item->jenis}.icon", 'fa-circle-info');
                        @endphp
                        <div class="timeline-item">
                            <div class="timeline-icon">
                                <i class="fa {{ $icon }}"></i>
                            </div>
                            <a href="{{ route('notif.show', $item) }}" class="timeline-card">
                                <div class="timeline-judul">{{ $item->judul }}</div>
                                <div class="timeline-tanggal">
                                    {{ $item->tanggal->translatedFormat('d M Y, H:i') }}
                                </div>
                            </a>
                        </div>
                    @endforeach
                </div>

                <div class="mt-4 d-flex justify-content-center">
                    {{ $items->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
