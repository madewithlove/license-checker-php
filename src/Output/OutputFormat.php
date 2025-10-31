<?php

declare(strict_types=1);

namespace LicenseChecker\Output;

enum OutputFormat: string
{
    case TEXT = 'text';
    case JSON = 'json';

    public static function tryFromInput(?string $input): self
    {
        return match ($input) {
            'json' => self::JSON,
            default => self::TEXT,
        };
    }
}
