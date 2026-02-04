<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
$migs = DB::table('migrations')->orderBy('id')->get();
foreach ($migs as $m) {
    echo $m->id . ' | ' . $m->migration . ' | ' . $m->batch . PHP_EOL;
}
