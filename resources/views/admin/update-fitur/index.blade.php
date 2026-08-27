@extends('admin.layout.base')

@section('content')
    <div class="row">
        <div class="col-12">
            @if (session('status'))
                <div class="alert alert-success">{{ session('status') }}</div>
            @endif

            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Daftar Update Fitur</h5>
                    <a href="{{ route('admin.updateFitur.create') }}" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus"></i> Tambah
                    </a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-flush table-hover">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Tanggal</th>
                                    <th>Judul</th>
                                    <th>Jenis</th>
                                    <th>Foto</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($items as $i => $item)
                                    <tr>
                                        <td>{{ $items->firstItem() + $i }}</td>
                                        <td>{{ $item->tanggal->translatedFormat('d F Y, H:i') }}</td>
                                        <td>{{ $item->judul }}</td>
                                        <td>
                                            <span class="badge bg-{{ config("update_fitur.jenis.{$item->jenis}.badge", 'secondary') }}">
                                                {{ config("update_fitur.jenis.{$item->jenis}.label", $item->jenis) }}
                                            </span>
                                        </td>
                                        <td>
                                            @if ($item->foto)
                                                <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url('update-fitur/' . $item->foto) }}"
                                                     alt="" style="height: 40px;">
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td>
                                            <a href="{{ route('admin.updateFitur.edit', $item->id) }}"
                                               class="btn btn-sm btn-warning">Edit</a>
                                            <form action="{{ route('admin.updateFitur.destroy', $item->id) }}"
                                                  method="post" class="d-inline"
                                                  onsubmit="return confirm('Yakin hapus?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger">Hapus</button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center text-muted">Belum ada data.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{ $items->links() }}
                </div>
            </div>
        </div>
    </div>
@endsection