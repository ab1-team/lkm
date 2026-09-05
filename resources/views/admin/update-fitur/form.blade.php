@extends('admin.layout.base')

@section('content')
    <style>
        .form-uf .form-control,
        .form-uf .form-select {
            border: 1px solid #0d6efd;
        }
        .form-uf .form-control:focus,
        .form-uf .form-select:focus {
            border-color: #0d6efd;
            box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.15);
        }
    </style>

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        {{ $item->exists ? 'Edit' : 'Tambah' }} Update Fitur
                    </h5>
                </div>
                <div class="card-body">
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form class="form-uf"
                        action="{{ $item->exists ? route('admin.updateFitur.update', $item->id) : route('admin.updateFitur.store') }}"
                        method="post" enctype="multipart/form-data">
                        @csrf
                        @if ($item->exists)
                            @method('PUT')
                        @endif

                        <div class="mb-3">
                            <label class="form-label">Tanggal</label>
                            <input type="datetime-local" name="tanggal"
                                   class="form-control @error('tanggal') is-invalid @enderror"
                                   value="{{ old('tanggal', $item->exists ? $item->tanggal->format('Y-m-d\TH:i') : now()->format('Y-m-d\TH:i')) }}"
                                   required>
                            @error('tanggal')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Judul (maks 30 karakter)</label>
                            <input type="text" name="judul"
                                   class="form-control @error('judul') is-invalid @enderror"
                                   value="{{ old('judul', $item->judul) }}" maxlength="30" required>
                            @error('judul')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Jenis</label>
                            <select name="jenis" class="form-select @error('jenis') is-invalid @enderror" required>
                                <option value="">-- Pilih Jenis --</option>
                                @foreach (config('update_fitur.jenis') as $key => $val)
                                    <option value="{{ $key }}"
                                        {{ old('jenis', $item->jenis) == $key ? 'selected' : '' }}>
                                        {{ $val['label'] }}
                                    </option>
                                @endforeach
                            </select>
                            @error('jenis')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Deskripsi</label>
                            <textarea name="deskripsi" rows="5"
                                      class="form-control @error('deskripsi') is-invalid @enderror"
                                      required>{{ old('deskripsi', $item->deskripsi) }}</textarea>
                            @error('deskripsi')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">
                                Foto {{ $item->exists ? '(kosongkan jika tidak diganti)' : '' }}
                            </label>
                            <input type="file" name="foto"
                                   class="form-control @error('foto') is-invalid @enderror"
                                   accept="image/*">
                            @error('foto')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror

                            @if ($item->exists && $item->foto)
                                <div class="mt-2">
                                    <img src="{{ asset('uploads/update-fitur/' . $item->foto) }}"
                                         alt="" style="max-height: 120px;" class="rounded">
                                </div>
                            @endif
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('admin.updateFitur.index') }}" class="btn btn-secondary">Batal</a>
                            <button type="submit" class="btn btn-primary">Simpan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection