<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Whatsapp;
use GuzzleHttp\Client;

class WhatsappReconcile extends Command
{
    protected $signature = 'whatsapp:reconcile
                            {--prune : Hapus instance yatim di gateway (tanpa flag ini = dry-run)}
                            {--keep-in-gateway= : Daftar instance_name di gateway yang TIDAK boleh dihapus (pisahkan dengan koma)}';

    protected $description = 'Bandingkan instance WhatsApp di Evolution gateway dengan tabel whatsapp lokal. Tampilkan & (opsional) hapus instance yatim.';

    public function handle()
    {
        $apiKey = env('WA_GATEWAY_API_KEY');
        $base = rtrim(env('WA_GATEWAY_BASE', 'https://wa-gateway.enpiistudio.com'), '/');

        if (! $apiKey) {
            $this->error('WA_GATEWAY_API_KEY belum di-set di .env');
            return 1;
        }

        $local = Whatsapp::pluck('instance_name')->filter()->values()->all();
        $keep = [];
        if ($this->option('keep-in-gateway')) {
            $keep = array_filter(array_map('trim', explode(',', $this->option('keep-in-gateway'))));
        }

        $this->info('Mengambil daftar instance dari gateway: '.$base);

        $client = new Client([
            'timeout' => 20,
            'http_errors' => false,
            'headers' => [
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36',
            ],
        ]);

        try {
            $res = $client->get($base.'/instance/fetchInstances', [
                'headers' => ['apikey' => $apiKey, 'Accept' => 'application/json'],
            ]);
        } catch (\Throwable $e) {
            $this->error('Gagal terhubung ke gateway: '.$e->getMessage());
            return 1;
        }

        $status = $res->getStatusCode();
        $body = json_decode((string) $res->getBody(), true);

        if ($status < 200 || $status >= 300 || ! is_array($body)) {
            $this->error('Response gateway tidak valid. HTTP '.$status);
            $this->line((string) $res->getBody());
            return 1;
        }

        $remoteNames = [];
        foreach ($body as $row) {
            if (isset($row['instance']['instanceName'])) {
                $remoteNames[] = $row['instance']['instanceName'];
            } elseif (isset($row['name'])) {
                $remoteNames[] = $row['name'];
            }
        }
        $remoteNames = array_values(array_unique($remoteNames));

        $orphans = array_values(array_diff($remoteNames, $local));
        $missing = array_values(array_diff($local, $remoteNames));

        $this->line('');
        $this->info('Ringkasan:');
        $this->line('  Instance di gateway  : '.count($remoteNames));
        $this->line('  Instance di DB lokal : '.count($local));
        $this->line('  Yatim (ada di gateway, tidak ada di DB) : '.count($orphans));
        $this->line('  Hilang (ada di DB, tidak ada di gateway) : '.count($missing));

        if (! empty($missing)) {
            $this->line('');
            $this->warn('Instance yang ada di DB tapi tidak ada di gateway:');
            foreach ($missing as $name) {
                $this->line('  - '.$name);
            }
        }

        if (! empty($orphans)) {
            $this->line('');
            $this->warn('Instance yatim di gateway:');
            foreach ($orphans as $name) {
                $tag = in_array($name, $keep, true) ? ' [DIPERTAHANKAN]' : '';
                $this->line('  - '.$name.$tag);
            }
        }

        $toDelete = array_values(array_diff($orphans, $keep));
        if (empty($toDelete)) {
            $this->line('');
            $this->info('Tidak ada instance yatim yang akan dihapus.');
            return 0;
        }

        if (! $this->option('prune')) {
            $this->line('');
            $this->info('Jalankan dengan --prune untuk menghapus instance yatim di atas.');
            return 0;
        }

        if (! $this->confirm('Hapus '.count($toDelete).' instance yatim dari gateway sekarang?', false)) {
            $this->info('Dibatalkan.');
            return 0;
        }

        $ok = 0;
        $fail = 0;
        foreach ($toDelete as $name) {
            try {
                $delRes = $client->delete($base.'/instance/delete/'.$name, [
                    'headers' => ['apikey' => $apiKey, 'Accept' => 'application/json'],
                ]);
                if ($delRes->getStatusCode() >= 200 && $delRes->getStatusCode() < 300) {
                    $this->line('  Hapus '.$name.' : OK');
                    $ok++;
                } else {
                    $this->line('  Hapus '.$name.' : GAGAL (HTTP '.$delRes->getStatusCode().')');
                    $fail++;
                }
            } catch (\Throwable $e) {
                $this->line('  Hapus '.$name.' : GAGAL ('.$e->getMessage().')');
                $fail++;
            }
        }

        $this->line('');
        $this->info('Selesai. Berhasil: '.$ok.', Gagal: '.$fail);
        return $fail > 0 ? 1 : 0;
    }
}