<?php

declare(strict_types=1);

$finder = (new PhpCsFixer\Finder())
    ->in('src')
    ->in('tests')
    ->append(['.php-cs-fixer.dist.php'])
;

return (new PhpCsFixer\Config())
    ->setRules([
        '@PER' => true,
        '@PER:risky' => true,
        '@PHP80Migration' => true,
        '@PHP80Migration:risky' => true,
        '@PHPUnit84Migration:risky' => true,
    ])
    ->setFinder($finder)
;
