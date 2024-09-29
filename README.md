# SilverStripe AutoDoc

Automatically generate PHPDoc comments for DataObject subclasses based on fields and relations. Existing PHPDoc comments and directives are preserved at the beginning of comments.

## Requirements

* SilverStripe ^4.0

## Installation
```
composer require --dev atkinshealth/silverstripe-autodoc 1.x-dev
```

## License
See [License](LICENSE.md)

## Documentation
As this module directly modifies your source .php files, it is recommended that you only run it on projects in source control, with a clean working copy. To run the docblock generation, visit `/dev/autodoc` through sake

```
./vendor/bin/sake dev/autodoc module=app flush=1
```

If the module argument is left out, it will default to the `app` module. This can be changed through [yaml configuration](#example-configuration)

## Example configuration

This module by default generates docblocks for the `app` module, which is the default module for new SilverStripe projects. This default can be changed.

```yaml
AtkinsHealth\AutoDoc\AutoDocController:
  default_module: mysite
```

## Maintainers
 * Mason Dechaineux <mason@atkinshealth.com.au>

## Bugtracker
Bugs are tracked in the issues section of this repository. Before submitting an issue please read over
existing issues to ensure yours is unique.

If the issue does look like a new bug:

 - Create a new issue
 - Describe the steps required to reproduce your issue, and the expected outcome. Unit tests or command output can help here.
 - Describe your environment as detailed as possible: SilverStripe version, PHP version,
 Operating System, any installed SilverStripe modules.

Please report security issues to the module maintainers directly. Please don't file security issues in the bugtracker.

## Development and contribution
If you would like to make contributions to the module please ensure you raise a pull request and discuss with the module maintainers.
