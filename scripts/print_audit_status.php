<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Audit;
$id = $argv[1] ?? null;
if (!$id) {
    echo "Usage: php scripts/print_audit_status.php <id>\n";
    exit(1);
}
$a = Audit::find($id);
if (!$a) {
    echo "Audit not found\n";
    exit(1);
}
echo $a->id . ' ' . $a->status . "\n";
