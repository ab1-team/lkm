<?php
// Cek setiap view() catatan bimbingan di controller individu/lmpok ada filenya.
require __DIR__.'/../../vendor/autoload.php';
$app = require_once __DIR__.'/../../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$fail = 0;

foreach ([
    'App\Http\Controllers\PinjamanKelompokController',
    'App\Http\Controllers\PinjamanIndividuController',
] as $class) {
    $src = file_get_contents((new ReflectionClass($class))->getFileName());

    // ambil nama view dari setiap view('...catatan...')
    preg_match_all("/view\('([^']*catatan_[a-z_]*)'/", $src, $m);

    foreach ($m[1] as $view) {
        $path = resource_path('views/'.str_replace('.', '/', $view).'.blade.php');
        $ok = file_exists($path);

        printf("%-9s %-52s %s\n", $ok ? 'ADA' : 'HILANG', $view, $ok ? '' : $path);

        if (! $ok) {
            $fail++;
        }
    }
}

// Nama kolom harus identik di kedua controller.
$cols = [];
foreach ([
    'App\Http\Controllers\PinjamanKelompokController',
    'App\Http\Controllers\PinjamanIndividuController',
] as $class) {
    $src = file_get_contents((new ReflectionClass($class))->getFileName());
    preg_match_all("/catatan_b[a-z_]*'/", $src, $m);
    $cols[$class] = array_values(array_unique($m[0]));
}
echo "\nnama kolom:\n";
foreach ($cols as $c => $v) {
    printf("  %-52s %s\n", class_basename($c), implode(', ', array_map(fn ($x) => $x . ' (len ' . strlen($x) . ')', $v)));
}

$uniq = [];
foreach ($cols as $v) {
    foreach ($v as $x) {
        $uniq[$x] = true;
    }
}
echo '  kolom unik lintas controller: '.count($uniq)." (harus 1)\n";

exit($fail === 0 && count($uniq) === 1 ? 0 : 1);