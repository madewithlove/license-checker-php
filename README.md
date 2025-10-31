# CLI Licence checker for composer dependencies
This library offers a simple CLI tool to show the licenses used by composer dependencies in your project.
These licenses can be verified against a list of allowed licenses to offer a way for your continuous integration
pipeline to block merging when a non-verified license is being introduced to the codebase.

## Installation
Installing should be a breeze thanks to `composer`:
Note that you need PHP 8.3 to install the latest version (2.x).
If you are using an older version of PHP, older versions can be installed.

```
composer require madewithlove/license-checker
```

## Configuration
To configure a list of allowed licenses, simply create an `.allowed-licenses` file in the root of your project (where `composer.json` is located).
The file could look like this:
```
# contents of .allowed-licenses
- MIT
- BSD-3-Clause
- New BSD License
```

It's possible to use a custom configuration file by passing the `--filename` (or `-f`) option to the CLI commands.

## Usage
These are the different CLI commands

### List used licenses
```
vendor/bin/license-checker used
```

### List allowed licenses
```
vendor/bin/license-checker allowed
```

### Check licenses
```
vendor/bin/license-checker check
```

### Automatically generate configuration
This command will automatically generate an `.allowed-licenses` configuration based on the currently used licenses.
```
vendor/bin/license-checker generate-config
```

### Excluding development dependencies
Passing the `--no-dev` option to the CLI commands will scope all checks to production dependencies only.
Checking production and development dependencies against separate configuration files is possible by passing options:
```
vendor/bin/license-checker check --no-dev --filename .allowed-licenses-production
vendor/bin/license-checker check --filename .allowed-licenses-including-dev
```

### Output Formats (--format option)
You can now choose how license information is displayed — either as a human-readable table (`text`), or in machine-readable formats (`json`, `yaml`).

```
vendor/bin/license-checker check --format=json
```

```json
{
    "laravel/framework": "MIT",
    "phpunit/phpunit": "BSD-3-Clause"
}

```

```
vendor/bin/license-checker check --format=text
```

```
✓  phpunit/phpunit [BSD-3-Clause]
✓  symfony/console [MIT]
✓  vimeo/psalm [MIT]
```

By default, results are printed as human-readable text.
Use --format=json for structured machine-readable output.

## Saving Output to File (--output option)

You can now save reports directly to a file instead of printing them to the console.
```
vendor/bin/license-checker check --format=json --output=licenses.json
vendor/bin/license-checker check --format=text --output=licenses.txt
```
If the `--output` option is omitted, the report will be printed to the console as before.