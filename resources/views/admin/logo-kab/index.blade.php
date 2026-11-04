@extends('admin.layout.base')

@section('content')
    <div class="row">
        <div class="col-12">
            @if (session('status'))
                <div class="alert alert-success">{{ session('status') }}</div>
            @endif

            @if (session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif

            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-0">Logo Kabupaten</h5>
                        <small class="text-muted">
                            File disimpan di <code>storage/app/public/logo_kab/&lt;id&gt;.png</code> dan dipakai
                            pada Surat Rekomendasi Kredit.
                        </small>
                    </div>
                    <a href="{{ route('admin.logoKab.create') }}" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus"></i> Unggah
                    </a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-flush table-hover">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>ID</th>
                                    <th>Kabupaten</th>
                                    <th>Nama File</th>
                                    <th>Logo</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($kabupaten as $i => $kab)
                                    <tr>
                                        <td>{{ $kabupaten->firstItem() + $i }}</td>
                                        <td>{{ $kab->id }}</td>
                                        <td>{{ $kab->nama_kab }}</td>
                                        <td><code>{{ $kab->file_logo }}</code></td>
                                        <td>
                                            @if ($kab->ada_logo)
                                                <img src="{{ asset('storage/logo_kab/' . $kab->file_logo) }}"
                                                     alt="{{ $kab->nama_kab }}" style="height: 45px;">
                                            @else
                                                <span class="badge bg-secondary">Belum ada</span>
                                            @endif
                                        </td>
                                        <td>
                                            <a href="{{ route('admin.logoKab.edit', $kab->id) }}"
                                               class="btn btn-sm btn-warning">Ganti</a>
                                            @if ($kab->ada_logo)
                                                <form action="{{ route('admin.logoKab.destroy', $kab->id) }}"
                                                      method="post" class="d-inline"
                                                      onsubmit="return confirm('Yakin hapus logo {{ $kab->file_logo }}?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-danger">Hapus</button>
                                                </form>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center text-muted">Belum ada data kabupaten.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{ $kabupaten->links() }}
                </div>
            </div>

            @if (count($orphan) > 0)
                <div class="card mt-3">
                    <div class="card-header">
                        <h6 class="mb-0">File tanpa Kabupaten</h6>
                        <small class="text-muted">
                            File di folder <code>logo_kab</code> yang ID-nya tidak ada di tabel
                            <code>kabupaten</code>, sehingga tidak dipakai dokumen mana pun.
                        </small>
                    </div>
                    <div class="card-body">
                        @foreach ($orphan as $file)
                            <span class="badge bg-secondary me-2 mb-2">
                                <i class="fas fa-file-image"></i> {{ $file }}
                            </span>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>
@endsection
