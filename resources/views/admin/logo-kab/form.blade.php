@extends('admin.layout.base')

@section('content')
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">{{ $title }}</h5>
                </div>
                <div class="card-body">
                    @if (session('error'))
                        <div class="alert alert-danger">
                            {{ session('error') }}
                        </div>
                    @endif

                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    @if ($edit)
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i>
                            File akan disimpan sebagai <strong>{{ $kabupaten->id }}.png</strong>
                            (ID kabupaten <strong>{{ $kabupaten->id }}</strong> &mdash; {{ $kabupaten->nama_kab }}).
                        </div>
                    @endif

                    <form action="{{ $edit ? route('admin.logoKab.update', $kabupaten->id) : route('admin.logoKab.store') }}"
                        method="post" enctype="multipart/form-data">
                        @csrf
                        @if ($edit)
                            @method('PUT')
                        @endif

                        <div class="mb-3">
                            <label class="form-label" for="nama_file">Nama File</label>
                            <input type="number" name="nama_file" id="nama_file" min="1"
                                   class="form-control @error('nama_file') is-invalid @enderror"
                                   value="{{ old('nama_file', $edit ? $kabupaten->id : '') }}"
                                   {{ $edit ? 'readonly' : '' }} required>
                            @error('nama_file')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">
                                Isi dengan ID kabupaten. Hasil akhir: <code>20.png</code>.
                                @if (! $edit)
                                    <select id="pilih-kabupaten" class="form-control form-control-sm mt-2">
                                        <option value="">-- Pilih ID kabupaten (opsional) --</option>
                                        @foreach ($daftarKabupaten as $kab)
                                            <option value="{{ $kab->id }}">
                                                {{ $kab->id }} &mdash; {{ $kab->nama_kab }}
                                            </option>
                                        @endforeach
                                    </select>
                                @endif
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label" for="file">File Gambar</label>
                            <input type="file" name="file" id="file"
                                   class="form-control @error('file') is-invalid @enderror"
                                   accept="image/*" required>
                            @error('file')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">
                                Format JPG, PNG, GIF, WEBP, atau BMP (maks 4 MB). File otomatis
                                dikonversi menjadi PNG dengan nama <code>&lt;id&gt;.png</code>.
                            </div>
                        </div>

                        @if ($edit)
                            <div class="mb-3">
                                <label class="form-label">Logo Saat Ini</label>
                                <div>
                                    @if ($adaLogo)
                                        <img src="{{ asset('storage/logo_kab/' . $kabupaten->id . '.png') }}"
                                             alt="{{ $kabupaten->nama_kab }}" style="max-height: 120px;"
                                             class="rounded">
                                    @else
                                        <span class="text-muted">Belum ada logo untuk kabupaten ini.</span>
                                    @endif
                                </div>
                            </div>
                        @endif

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('admin.logoKab.index') }}" class="btn btn-secondary">Batal</a>
                            <button type="submit" class="btn btn-primary">Simpan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">Cara Pakai</h6>
                </div>
                <div class="card-body">
                    <ol class="mb-0 ps-3">
                        <li class="mb-2">Pilih ID kabupaten pada kolom <strong>Nama File</strong>.</li>
                        <li class="mb-2">Unggah file gambar logo.</li>
                        <li class="mb-2">Klik <strong>Simpan</strong>.</li>
                        <li>Logo tersimpan sebagai <code>&lt;id&gt;.png</code> dan langsung dipakai
                            pada dokumen <strong>Surat Rekomendasi Kredit</strong>.</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        (function() {
            var selectKab = document.getElementById('pilih-kabupaten');
            var inputNama = document.getElementById('nama_file');

            if (selectKab && inputNama) {
                selectKab.addEventListener('change', function() {
                    if (this.value !== '') {
                        inputNama.value = this.value;
                    }
                });
            }
        })();
    </script>
@endsection
