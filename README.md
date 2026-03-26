# CLI Licence checker for composer dependencies
This library offers a simple CLI tool to show the licenses used by composer dependencies in your project.
These licenses can be verified against a list of allowed (or denied) licenses to offer a way for your continuous integration
pipeline to block merging when a non-verified license is being introduced to the codebase.

## Upgrading from 2.x

Version 3.x introduces a new structured configuration format. Run the migration command to upgrade:

```
vendor/bin/license-checker migrate-config --remove-old
```

This converts your `.allowed-licenses` file to the new `.license-checker.yml` format. See [full migration details](#migrating-from-2x) below.

## Installation
Installing should be a breeze thanks to `composer`:
Note that you need PHP 8.4 to install the latest version (3.x).

```
composer require madewithlove/license-checker
```

## Configuration
Create a `.license-checker.yml` file in the root of your project (where `composer.json` is located).

### Allowlist mode
Only the listed licenses are permitted. Any dependency using a license not on this list will be flagged:
```yaml
# .license-checker.yml
allowed:
  - MIT
  - BSD-3-Clause
  - Apache-2.0
```

### Denylist mode
All licenses are permitted **except** the ones listed. Use this when you want to block specific licenses:
```yaml
# .license-checker.yml
denied:
  - GPL-3.0
  - AGPL-3.0
```

> **Note:** `allowed` and `denied` are mutually exclusive — you must use one or the other, not both.

It's possible to use a custom configuration file by passing the `--filename` (or `-f`) option to the CLI commands.

## Usage
These are the different CLI commands:

### Check licenses
```
vendor/bin/license-checker check
```

### List used licenses
```
vendor/bin/license-checker used
```

### List configured licenses
Shows the configured allowed or denied licenses:
```
vendor/bin/license-checker list-config
```

### Count used licenses
```
vendor/bin/license-checker count
```

### Automatically generate configuration
This command will automatically generate a `.license-checker.yml` configuration in allowlist mode based on the currently used licenses:
```
vendor/bin/license-checker generate-config
```

### Excluding development dependencies
Passing the `--no-dev` option to the CLI commands will scope all checks to production dependencies only.
Checking production and development dependencies against separate configuration files is possible by passing options:
```
vendor/bin/license-checker check --no-dev --filename .license-checker-production.yml
vendor/bin/license-checker check --filename .license-checker-including-dev.yml
```

### Output Formats (--format option)
You can choose how license information is displayed — as a human-readable table (`text`), machine-readable JSON (`json`), or SARIF for GitHub Actions code scanning (`sarif`).

```
vendor/bin/license-checker check --format=json
```

```json
{
    "laravel/framework": {
        "license": "MIT",
        "is_allowed": true
    },
    "phpunit/phpunit": {
        "license": "BSD-3-Clause",
        "is_allowed": false
    }
}
```

```
vendor/bin/license-checker check --format=text
```

```
✓  phpunit/phpunit [BSD-3-Clause]
✓  symfony/console [MIT]
```

By default, results are printed as human-readable text.
Use `--format=json` for structured machine-readable output.
Use `--format=sarif` to generate a SARIF report for GitHub Actions integration (see below).

## GitHub Actions integration

The `--format=sarif` option outputs results in [SARIF 2.1.0](https://sarifweb.azurewebsites.net/) format, which GitHub can display as [code scanning alerts](https://docs.github.com/en/code-security/code-scanning/integrating-with-code-scanning/uploading-a-sarif-file-to-github) directly on pull requests.

Each root dependency that requires a disallowed license — either directly or through a transitive dependency — appears as a separate annotation pointing to your `composer.json`.

### Example workflow

```yaml
# .github/workflows/license-check.yml
name: License check

on: [push, pull_request]

jobs:
  license-check:
    runs-on: ubuntu-latest
    permissions:
      security-events: write  # required to upload SARIF results
    steps:
      - uses: actions/checkout@v4

      - name: Install dependencies
        run: composer install --no-interaction --prefer-dist

      - name: Check licenses
        id: license-check
        run: vendor/bin/license-checker check --format=sarif > license-results.sarif
        continue-on-error: true

      - name: Upload SARIF results
        uses: github/codeql-action/upload-sarif@v3
        with:
          sarif_file: license-results.sarif
          category: license-checker

      - name: Fail if license violations found
        if: steps.license-check.outcome == 'failure'
        run: exit 1
```

> **Note:** `continue-on-error: true` ensures the SARIF upload always runs even when violations are found. The final step then checks the original outcome and fails the build, so violations still block the pipeline.

### SARIF output example

```json
{
    "$schema": "https://raw.githubusercontent.com/oasis-tcs/sarif-spec/master/Schemata/sarif-schema-2.1.0.json",
    "version": "2.1.0",
    "runs": [
        {
            "tool": {
                "driver": {
                    "name": "license-checker",
                    "rules": [{ "id": "license-not-allowed" }]
                }
            },
            "results": [
                {
                    "ruleId": "license-not-allowed",
                    "level": "error",
                    "message": {
                        "text": "\"my/package\" requires \"bad/subdep\" which uses the disallowed license \"GPL-3.0\""
                    },
                    "locations": [
                        {
                            "physicalLocation": {
                                "artifactLocation": { "uri": "composer.json" }
                            },
                            "logicalLocations": [
                                { "name": "my/package", "kind": "package" }
                            ]
                        }
                    ]
                }
            ]
        }
    ]
}
```

## Migrating from 2.x

Version 3.x introduces a new structured configuration format. Here's what changed:

### Configuration file format
The old format was a plain YAML list in `.allowed-licenses`:
```yaml
# OLD format (.allowed-licenses) — no longer supported
- MIT
- BSD-3-Clause
```

The new format uses a structured YAML file (`.license-checker.yml`) with an explicit `allowed` or `denied` key:
```yaml
# NEW format (.license-checker.yml)
allowed:
  - MIT
  - BSD-3-Clause
```

### Automatic migration
Use the `migrate-config` command to convert your old configuration:
```
vendor/bin/license-checker migrate-config
```

This reads `.allowed-licenses` and writes `.license-checker.yml` with the `allowed:` key.

To also remove the old file:
```
vendor/bin/license-checker migrate-config --remove-old
```

### Renamed commands
| 2.x | 3.x |
|-----|-----|
| `allowed` | `list-config` |

### Other breaking changes
- Minimum PHP version is now 8.4 (was 8.3)
- Minimum Symfony version is now 7.4 (was 4.0)
