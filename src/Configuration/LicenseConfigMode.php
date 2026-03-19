<?php

declare(strict_types=1);

namespace LicenseChecker\Configuration;

enum LicenseConfigMode: string
{
    case Allowed = 'allowed';
    case Denied = 'denied';
}
