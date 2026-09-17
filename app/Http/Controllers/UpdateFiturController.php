<?php

namespace App\Http\Controllers;

use App\Models\UpdateFitur;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class UpdateFiturController extends Controller
{
    private function readAt(Request $request): ?Carbon
    {
        $cookieName = config('update_fitur.cookie_name');
        $value = $request->cookie($cookieName);

        return $value ? Carbon::parse($value) : null;
    }

    public function dropdown(Request $request)
    {
        $items = UpdateFitur::dalamMasaNotif()->terbaruDulu()->get();
        $readAt = $this->readAt($request);

        $unreadCount = $readAt
            ? $items->where('tanggal', '>', $readAt)->count()
            : $items->count();

        return response()->json([
            'items'        => $items,
            'unread_count' => $unreadCount,
        ]);
    }

    public function tandaiDibaca(Request $request)
    {
        $cookieName = config('update_fitur.cookie_name');
        $days = config('update_fitur.cookie_days', 365);

        return back()->withCookie(
            cookie($cookieName, now()->toIso8601String(), 60 * 24 * $days)
        );
    }

    public function timeline()
    {
        $items = UpdateFitur::terbaruDulu()->paginate(15);
        $title = 'Riwayat Pembaruan';

        return view('update-fitur.timeline', compact('items', 'title'));
    }

    public function show(Request $request, UpdateFitur $update_fitur)
    {
        $title = $update_fitur->judul;

        $response = view('update-fitur.show', compact('update_fitur', 'title'));

        $currentReadAt = $this->readAt($request);
        $itemTanggal = $update_fitur->tanggal;

        if (! $currentReadAt || $itemTanggal->gt($currentReadAt)) {
            $days = config('update_fitur.cookie_days', 365);
            $response = $response->withCookie(
                cookie(config('update_fitur.cookie_name'), $itemTanggal->toIso8601String(), 60 * 24 * $days)
            );
        }

        return $response;
    }
}