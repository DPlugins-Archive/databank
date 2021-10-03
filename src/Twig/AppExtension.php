<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class AppExtension extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction('md5', [$this, 'calculateMD5']),
        ];
    }

    public function calculateMD5(string $data, bool $binary = false): string
    {
        return md5($data, $binary);
    }
}
