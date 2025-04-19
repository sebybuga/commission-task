<?php

declare(strict_types=1);

include __DIR__ . '/vendor/autoload.php';

$finder = PhpCsFixer\Finder::create()
    ->in(['src'])
;

use PhpCsFixer\Config;
use PhpCsFixer\Finder;

$finder = Finder::create()
    ->in(['src']);

return (new Config())
    ->setRules([
        '@PSR2' => true,
        'array_syntax' => ['syntax' => 'short'],
    ])
    ->setFinder($finder);