<?php

declare(strict_types=1);

namespace LicenseChecker\Output;

enum OutputFormat: string
{
    case TEXT = 'text';
    case JSON = 'json';


    public static function tryFromInput(?string $value): self
    {
        if ($value === null) {
            return self::TEXT;
        }

        return match (strtolower($value)) {
            'text' => self::TEXT,
            'json' => self::JSON,
            default => self::TEXT,
        };
    }
}
