<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\UpdateFitur;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class UpdateFiturController extends Controller
{
    private const FOTO_DIR = 'update-fitur';
    private const FOTO_DISK = 'public_uploads';

    private function fotoDisk()
    {
        $disk = Storage::disk(self::FOTO_DISK);

        if (!$disk->exists(self::FOTO_DIR)) {
            $disk->makeDirectory(self::FOTO_DIR);
        }

        return $disk;
    }

    public function index()
    {
        $items = UpdateFitur::terbaruDulu()->paginate(10);
        $title = 'Update Fitur';

        return view('admin.update-fitur.index', compact('items', 'title'));
    }

    public function create()
    {
        $title = 'Tambah Update Fitur';

        return view('admin.update-fitur.form', ['item' => new UpdateFitur(), 'title' => $title]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['foto'] = $this->simpanFoto($request);

        UpdateFitur::create($data);

        return redirect()->route('admin.updateFitur.index')->with('status', 'Update fitur ditambahkan.');
    }

    public function edit(UpdateFitur $update_fitur)
    {
        $title = 'Edit Update Fitur';

        return view('admin.update-fitur.form', ['item' => $update_fitur, 'title' => $title]);
    }

    public function update(Request $request, UpdateFitur $update_fitur)
    {
        $data = $this->validated($request);

        if ($request->hasFile('foto')) {
            $this->hapusFotoLama($update_fitur->foto);
            $data['foto'] = $this->simpanFoto($request);
        } else {
            unset($data['foto']);
        }

        $update_fitur->update($data);

        return redirect()->route('admin.updateFitur.index')->with('status', 'Update fitur diperbarui.');
    }

    public function destroy(UpdateFitur $update_fitur)
    {
        $this->hapusFotoLama($update_fitur->foto);
        $update_fitur->delete();

        return redirect()->route('admin.updateFitur.index')->with('status', 'Update fitur dihapus.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'tanggal'    => ['required', 'date'],
            'judul'      => ['required', 'string', 'max:30'],
            'deskripsi'  => ['required', 'string'],
            'jenis'      => ['required', Rule::in(array_keys(config('update_fitur.jenis')))],
            'foto'       => [$request->isMethod('post') ? 'required' : 'nullable', 'image', 'max:2048'],
        ]);
    }

    private function simpanFoto(Request $request): string
    {
        $disk = $this->fotoDisk();
        $file = $request->file('foto');
        $filename = $file->hashName();
        $file->storeAs(self::FOTO_DIR, $filename, self::FOTO_DISK);

        return $filename;
    }

    private function hapusFotoLama(?string $filename): void
    {
        if ($filename) {
            Storage::disk(self::FOTO_DISK)->delete(self::FOTO_DIR . '/' . $filename);
        }
    }
}