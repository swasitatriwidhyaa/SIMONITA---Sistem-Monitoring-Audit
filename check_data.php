<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

$auditees = \App\Models\User::where('role', 'auditee')->orderBy('id')->get(['id', 'unit_kerja', 'email']);
echo "=== DAFTAR AUDITEE ===\n";
echo count($auditees) . " unit kerja ditemukan:\n\n";
foreach ($auditees as $a) {
    echo $a->id . ". " . $a->unit_kerja . " (" . $a->email . ")\n";
}

echo "\n=== DAFTAR STANDAR AUDIT ===\n";
$standards = \App\Models\AuditStandard::all(['id', 'kode', 'nama', 'jenis_audit']);
foreach ($standards as $s) {
    echo "- [" . $s->jenis_audit . "] " . $s->kode . " - " . $s->nama . "\n";
}
