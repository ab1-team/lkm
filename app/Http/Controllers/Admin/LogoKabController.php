<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Kabupaten;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class LogoKabController extends Controller
{
    /**
     * Folder tujuan di storage/app/public.
     *
     * Dipakai oleh dokumen Surat Rekomendasi Kredit:
     *   resources/views/perguliran/dokumen/rekomendasi_kredit.blade.php
     *   resources/views/perguliran_i/dokumen/rekomendasi_kredit.blade.php
     * yang memanggil "../storage/app/public/logo_kab/{id_kabupaten}.png".
     */
    private const FOLDER = 'logo_kab';

    /**
     * Ekstensi wajib hasil akhir. Kode dokumen selalu meminta ".png",
     * jadi file apa pun yang diunggah dikonversi menjadi PNG.
     */
    private const EKSTENSI = '.png';

    /**
     * Batas dimensi terpanjang (px). Gambar yang lebih besar diskala turun
     * supaya file tidak membengkak dan PDF tetap ringan.
     */
    private const MAKS_DIMENSI = 1200;

    public function index()
    {
        $title = 'Logo Kabupaten';

        $kabupaten = Kabupaten::orderBy('id')->paginate(15)->through(function ($kab) {
            $kab->file_logo = $this->namaFile($kab->id);
            $kab->ada_logo = $this->adaFile($kab->id);

            return $kab;
        });

        // File yang ada di folder tapi sudah tidak punya baris kabupaten
        // (mis. kabupaten dihapus, atau file diunggah manual).
        $namaDiketahui = Kabupaten::pluck('id')
            ->map(fn ($id) => $this->namaFile($id))
            ->all();

        $orphan = collect(Storage::disk('public')->files(self::FOLDER))
            ->map(fn ($path) => basename($path))
            ->filter(fn ($file) => str_ends_with($file, self::EKSTENSI))
            ->reject(fn ($file) => in_array($file, $namaDiketahui, true))
            ->values();

        return view('admin.logo-kab.index', compact('kabupaten', 'orphan', 'title'));
    }

    public function create()
    {
        $title = 'Unggah Logo Kabupaten';

        return view('admin.logo-kab.form', [
            'title' => $title,
            'edit' => false,
            'kabupaten' => null,
            'daftarKabupaten' => Kabupaten::orderBy('id')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);

        $id = (int) $data['nama_file'];
        $namaFile = $this->namaFile($id);

        if (! $this->simpan($request->file('file'), $id)) {
            return back()->withInput()->with('error', "Logo {$namaFile} gagal disimpan. Gambar tidak bisa dikonversi ke PNG.");
        }

        return redirect()->route('admin.logoKab.index')
            ->with('status', "Logo {$namaFile} berhasil disimpan.");
    }

    public function edit(Kabupaten $kabupaten)
    {
        $title = 'Ganti Logo Kabupaten';

        return view('admin.logo-kab.form', [
            'title' => $title,
            'edit' => true,
            'kabupaten' => $kabupaten,
            'adaLogo' => $this->adaFile($kabupaten->id),
            'daftarKabupaten' => Kabupaten::orderBy('id')->get(),
        ]);
    }

    public function update(Request $request, Kabupaten $kabupaten)
    {
        // Nama file mengikuti kabupaten yang sedang diedit.
        $request->merge(['nama_file' => $kabupaten->id]);

        $this->validated($request);

        $namaFile = $this->namaFile($kabupaten->id);

        if (! $this->simpan($request->file('file'), $kabupaten->id)) {
            return back()->withInput()->with('error', "Logo {$namaFile} gagal disimpan. Gambar tidak bisa dikonversi ke PNG.");
        }

        return redirect()->route('admin.logoKab.index')
            ->with('status', "Logo {$namaFile} berhasil diperbarui.");
    }

    public function destroy(Kabupaten $kabupaten)
    {
        $namaFile = $this->namaFile($kabupaten->id);

        Storage::disk('public')->delete($this->path($kabupaten->id));

        return redirect()->route('admin.logoKab.index')
            ->with('status', "Logo {$namaFile} berhasil dihapus.");
    }

    /**
     * Validasi input form.
     */
    private function validated(Request $request): array
    {
        return $request->validate([
            'nama_file' => ['required', 'integer', 'min:1', Rule::exists('kabupaten', 'id')],
            'file' => ['required', 'image', 'mimes:jpg,jpeg,png,gif,webp,bmp', 'max:4096'],
        ], [
            'nama_file.required' => 'Nama file wajib diisi dengan ID kabupaten.',
            'nama_file.integer' => 'Nama file harus berupa angka (ID kabupaten).',
            'nama_file.exists' => 'ID kabupaten tidak ditemukan.',
            'file.required' => 'File gambar wajib diunggah.',
            'file.image' => 'File yang diunggah harus berupa gambar.',
            'file.mimes' => 'Format gambar harus JPG, PNG, GIF, WEBP, atau BMP.',
            'file.max' => 'Ukuran gambar maksimal 4 MB.',
        ]);
    }

    /**
     * Path relatif file di disk public, contoh: logo_kab/20.png
     */
    private function path(int $id): string
    {
        return self::FOLDER.'/'.$this->namaFile($id);
    }

    /**
     * Nama file hasil akhir, contoh: 20 -> 20.png
     */
    private function namaFile(int $id): string
    {
        return $id.self::EKSTENSI;
    }

    /**
     * Cek apakah file logo sudah ada.
     */
    private function adaFile(int $id): bool
    {
        return Storage::disk('public')->exists($this->path($id));
    }

    /**
     * Simpan file gambar sebagai PNG dengan nama {id}.png
     */
    private function simpan(UploadedFile $file, int $id): bool
    {
        $disk = Storage::disk('public');
        $target = $this->path($id);

        $image = $this->bacaGambar($file->get());

        if (! $image) {
            return false;
        }

        $image = $this->skalaTurun($image);

        ob_start();
        $berhasil = imagepng($image);
        $isi = ob_get_clean();

        imagedestroy($image);

        if (! $berhasil || $isi === false) {
            return false;
        }

        return $disk->put($target, $isi);
    }

    /**
     * Baca gambar dari string binary.
     *
     * imagecreatefromstring() mendeteksi format otomatis (JPG, PNG, GIF,
     * WEBP, BMP) sehingga tidak perlu cabang per format.
     */
    private function bacaGambar(string $isi)
    {
        if ($isi === '' || ! function_exists('imagecreatefromstring')) {
            return false;
        }

        return @imagecreatefromstring($isi);
    }

    /**
     * Perkecil gambar yang melebihi batas dimensi.
     */
    private function skalaTurun(\GdImage $image): \GdImage
    {
        $width = imagesx($image);
        $height = imagesy($image);

        if ($width <= self::MAKS_DIMENSI && $height <= self::MAKS_DIMENSI) {
            return $image;
        }

        $skala = min(self::MAKS_DIMENSI / $width, self::MAKS_DIMENSI / $height);
        $newWidth = max(1, (int) round($width * $skala));
        $newHeight = max(1, (int) round($height * $skala));

        $resized = imagecreatetruecolor($newWidth, $newHeight);
        imagealphablending($resized, false);
        imagesavealpha($resized, true);
        imagecopyresampled($resized, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

        imagedestroy($image);

        return $resized;
    }
}
