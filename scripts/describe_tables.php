<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
$tables = ['audit_findings','audits','audit_standards'];
foreach($tables as $t) {
    echo "Table: $t\n";
    $cols = DB::select("SELECT column_name, data_type FROM information_schema.columns WHERE table_name = ? ORDER BY ordinal_position", [$t]);
    foreach($cols as $c) {
        echo " - {$c->column_name} ({$c->data_type})\n";
    }
    echo "\n";
}
