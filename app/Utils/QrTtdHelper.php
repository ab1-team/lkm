<?php

namespace App\Utils;

class QrTtdHelper
{
    public const EXTENSIONS = ['jpeg', 'jpg', 'png'];
    public const DIRECTORY  = 'qr';

    public static function findFile($lokasi): ?string
    {
        $lokasi = self::normalizeLokasi($lokasi);
        if ($lokasi === null) {
            return null;
        }

        $dir = storage_path('app/public/' . self::DIRECTORY);
        if (!is_dir($dir)) {
            return null;
        }

        foreach (self::EXTENSIONS as $ext) {
            $withName = $dir . DIRECTORY_SEPARATOR . $lokasi . self::NAME_SUFFIX . '.' . $ext;
            if (file_exists($withName)) {
                return $withName;
            }
        }

        foreach (self::EXTENSIONS as $ext) {
            $candidate = $dir . DIRECTORY_SEPARATOR . $lokasi . '.' . $ext;
            if (file_exists($candidate)) {
                return $candidate;
            }
        }

        return null;
    }

    public static function findExtension($lokasi): ?string
    {
        $path = self::findFile($lokasi);
        if ($path === null) {
            return null;
        }
        return strtolower(pathinfo($path, PATHINFO_EXTENSION));
    }

    public static function publicUrl($lokasi): ?string
    {
        $lokasi = self::normalizeLokasi($lokasi);
        if ($lokasi === null) {
            return null;
        }

        foreach (self::EXTENSIONS as $ext) {
            $withName = self::DIRECTORY . '/' . $lokasi . self::NAME_SUFFIX . '.' . $ext;
            $absolute = public_path('storage/' . $withName);
            if (file_exists($absolute)) {
                return '/storage/' . $withName;
            }
        }

        foreach (self::EXTENSIONS as $ext) {
            $relative = self::DIRECTORY . '/' . $lokasi . '.' . $ext;
            $absolute = public_path('storage/' . $relative);
            if (file_exists($absolute)) {
                return '/storage/' . $relative;
            }
        }

        $exists = self::absolutePath($lokasi);
        if ($exists !== null) {
            return route('qr.serve', ['filename' => basename($exists)]);
        }

        return null;
    }

    public static function absolutePath($lokasi): ?string
    {
        $lokasi = self::normalizeLokasi($lokasi);
        if ($lokasi === null) {
            return null;
        }

        $dir = storage_path('app/public/' . self::DIRECTORY);
        if (!is_dir($dir)) {
            return null;
        }

        foreach (self::EXTENSIONS as $ext) {
            $withName = $dir . DIRECTORY_SEPARATOR . $lokasi . self::NAME_SUFFIX . '.' . $ext;
            if (file_exists($withName)) {
                return $withName;
            }
        }

        foreach (self::EXTENSIONS as $ext) {
            $candidate = $dir . DIRECTORY_SEPARATOR . $lokasi . '.' . $ext;
            if (file_exists($candidate)) {
                return $candidate;
            }
        }

        return null;
    }

    public static function fileUrl($lokasi): ?string
    {
        $path = self::absolutePath($lokasi);
        if ($path === null || !is_readable($path)) {
            return null;
        }

        return 'file://' . $path;
    }

    public static function bestSrc($lokasi, bool $preferDataUri = false): ?string
    {
        $lokasi = self::normalizeLokasi($lokasi);
        if ($lokasi === null) {
            return null;
        }

        if ($preferDataUri) {
            $dataUri = self::inlineDataUri($lokasi);
            if ($dataUri !== null) {
                return $dataUri;
            }
            return self::fileUrl($lokasi);
        }

        $url = self::publicUrl($lokasi);
        if ($url !== null) {
            return $url;
        }

        $dataUri = self::inlineDataUri($lokasi);
        if ($dataUri !== null) {
            return $dataUri;
        }

        return self::fileUrl($lokasi);
    }

    public static function inlineDataUri($lokasi): ?string
    {
        $path = self::absolutePath($lokasi);
        if ($path === null || !is_readable($path)) {
            return null;
        }

        $contents = @file_get_contents($path);
        if ($contents === false) {
            return null;
        }

        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        $mime = $ext === 'png' ? 'image/png' : 'image/jpeg';

        return 'data:' . $mime . ';base64,' . base64_encode($contents);
    }

    public static function exists($lokasi): bool
    {
        return self::findFile($lokasi) !== null;
    }

    public const NAME_SUFFIX = '-name';

    public static function hasNameSuffix($lokasi): bool
    {
        $lokasi = self::normalizeLokasi($lokasi);
        if ($lokasi === null) {
            return false;
        }

        $dir = storage_path('app/public/' . self::DIRECTORY);
        if (!is_dir($dir)) {
            return false;
        }

        foreach (self::EXTENSIONS as $ext) {
            $withName = $dir . DIRECTORY_SEPARATOR . $lokasi . self::NAME_SUFFIX . '.' . $ext;
            if (file_exists($withName)) {
                return true;
            }
        }

        return false;
    }

    public static function basename($lokasi): ?string
    {
        $lokasi = self::normalizeLokasi($lokasi);
        if ($lokasi === null) {
            return null;
        }

        $dir = storage_path('app/public/' . self::DIRECTORY);
        if (!is_dir($dir)) {
            return null;
        }

        foreach (self::EXTENSIONS as $ext) {
            $withName = $dir . DIRECTORY_SEPARATOR . $lokasi . self::NAME_SUFFIX . '.' . $ext;
            if (file_exists($withName)) {
                return $lokasi . self::NAME_SUFFIX . '.' . $ext;
            }
        }

        foreach (self::EXTENSIONS as $ext) {
            $base = $dir . DIRECTORY_SEPARATOR . $lokasi . '.' . $ext;
            if (file_exists($base)) {
                return $lokasi . '.' . $ext;
            }
        }

        return null;
    }

    public static function displayHasName($lokasi): bool
    {
        $lokasi = self::normalizeLokasi($lokasi);
        if ($lokasi === null) {
            return false;
        }

        $dir = storage_path('app/public/' . self::DIRECTORY);
        if (!is_dir($dir)) {
            return true;
        }

        if (self::hasNameSuffix($lokasi)) {
            return true;
        }

        foreach (self::EXTENSIONS as $ext) {
            $base = $dir . DIRECTORY_SEPARATOR . $lokasi . '.' . $ext;
            if (file_exists($base)) {
                return false;
            }
        }

        return true;
    }

    private static function normalizeLokasi($lokasi): ?int
    {
        if ($lokasi === null || $lokasi === '') {
            return null;
        }

        if (is_numeric($lokasi)) {
            $int = (int) $lokasi;
            return $int > 0 ? $int : null;
        }

        return null;
    }
}
