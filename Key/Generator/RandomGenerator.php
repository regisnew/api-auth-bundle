<?php

declare(strict_types=1);

namespace Damax\Bundle\ApiAuthBundle\Key\Generator;

final class RandomGenerator implements Generator
{
    private $size;

    public function __construct(int $size = 20)
    {
        $this->size = $size;
    }

    public function generateKey(): string
    {
        return bin2hex(random_bytes($this->size));
    }
}
