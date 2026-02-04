<?php
$projectRoot = dirname(__DIR__);
require $projectRoot . '/vendor/autoload.php';
$app = require_once $projectRoot . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\AuditFinding;
$all = AuditFinding::all();
if ($all->isEmpty()) {
    echo "No findings found\n";
    exit;
}
foreach ($all as $f) {
    $path = $f->bukti_perbaikan;
    $exists = $path ? (file_exists($projectRoot . '/public/storage/' . $path) ? 'YES' : 'NO') : '-';
    echo $f->id . ' => ' . ($path ? $path : '(null)') . ' | file_exists(public/storage/...): ' . $exists . PHP_EOL;
}
