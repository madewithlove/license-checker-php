<?php

declare(strict_types=1);

namespace LicenseChecker\Output;

enum OutputFormat: string
{
    case TEXT = 'text';
    case JSON = 'json';
    case YAML = 'yaml';

    public static function tryFromInput(?string $input): self
    {
        return match ($input) {
            'json' => self::JSON,
             'yaml', 'yml' => self::YAML,
            default => self::TEXT,
        };
    }
}
