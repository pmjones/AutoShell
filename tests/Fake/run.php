<?php
use AutoShell\Console;

require dirname(__DIR__, 2) . '/vendor/autoload.php';

$console = Console::new(
    namespace: 'AutoShell\\Fake\\Command',
    directory: __DIR__ . '/Command'
);

$code = $console($_SERVER['argv']);
exit($code);
