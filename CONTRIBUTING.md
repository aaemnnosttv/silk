# Silk Contribution Guide

Hey there!  Thanks for taking the time to read this.  I really appreciate your interest to contribute to Silk!
Before submitting your contribution, please make sure to take a moment and read through the following guidelines.

## Issue Reporting Guidelines

- The issue list of this repo is **exclusively** for bug reports and feature requests.  For simple questions, please use [Gitter](https://gitter.im/aaemnnosttv/silk).

- Try to search for your issue, it may have already been answered.

- Check if the issue is reproducible with the latest stable version. If you are using a pre-release, please indicate the specific version you are using.

- If your issue is resolved but still open, don’t hesitate to close it. In case you found a solution by yourself, it could be helpful to explain how you fixed it.

## Pull Request Guidelines

- Checkout a topic branch from `master` and merge back against `master`.

- Follow the [code style](#code-style).

- Make sure `phpunit` passes. (see [development setup](#development-setup))

- If adding new feature:
    - Add accompanying test case.
    - Provide convincing reason to add this feature. Ideally you should open a suggestion issue first and have it greenlighted before working on it.

- If fixing a bug:
    - Provide detailed description of the bug in the PR.
    - Add appropriate test coverage if applicable.

## Code Style

- Use [PSR-2](http://www.php-fig.org/psr/psr-2/).  Just about every text editor has an extension for formatting your code this way.
_Note: This is NOT the same as the [WordPress Coding Standards](https://make.wordpress.org/core/handbook/best-practices/coding-standards/php/)._

- Code style violations are checked with [Nitpick CI](https://nitpick-ci.com/).

- Use a text editor that supports [EditorConfig](http://editorconfig.org/) (recommended).

- When in doubt, read the source code.

## Development Setup

You will need [Composer](https://getcomposer.org/download/).

- Fork the repository on GitHub if preparing to submit a Pull Request.

- Clone the repository.

- Run `composer install` within the project root.

- Install WordPress test suite
```
./tests/bin/install-wp-tests.sh {db-name} {db-user} {db-pass} {db-host}
```

## Tests

Contributions are expected to have test coverage where applicable. Tests are written using PHPUnit.
