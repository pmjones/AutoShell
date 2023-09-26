<?php
use PhpStyler\Config;
use PhpStyler\Files;
use PhpStyler\Styler;

return new Config(
    files: new Files(__DIR__ . '/src'),
    styler: new Styler(),
    cache: __DIR__ . '/.php-styler.cache',
);
