<?php

require __DIR__.'/vendor/autoload.php';

use Carbon\Carbon;

$start = Carbon::parse('2026-02-20 08:00:00');
$end = Carbon::parse('2026-02-20 18:00:00');

echo "Start: {$start->format('Y-m-d H:i:s')}\n";
echo "End: {$end->format('Y-m-d H:i:s')}\n\n";

echo "end->diffInSeconds(start): " . $end->diffInSeconds($start) . "\n";
echo "start->diffInSeconds(end): " . $start->diffInSeconds($end) . "\n";
echo "end - start (manual): " . ($end->timestamp - $start->timestamp) . "\n";
