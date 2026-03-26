<?php

declare(strict_types=1);

namespace LicenseChecker\Output;

enum OutputFormat: string
{
    case TEXT = 'text';
    case JSON = 'json';
    case SARIF = 'sarif';

    public static function tryFromInput(?string $input): self
    {
        return match ($input) {
            'json' => self::JSON,
            'sarif' => self::SARIF,
            default => self::TEXT,
        };
    }
}
