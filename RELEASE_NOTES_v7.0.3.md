# FMail v7.0.3

This maintenance release updates Composer dependency requirements and aligns the version metadata and documentation.

## Changes

- Raise the minimum PHP requirement from `>=5.1.0` to `>=7.0`.
- Change the `toropyga/base` dependency constraint from `^1.0.3` to `*`.
- Update the class version metadata, `X-Mailer` header, and README badges to v7.0.3.
- Correct the PHP requirements in both English and Russian documentation and list the required extensions.
- Add a changelog covering the 7.0.x series.

## Requirements

- PHP 7.0 or higher.
- PHP extensions: `imap`, `fileinfo`, `iconv`, and `mbstring`.
- The `toropyga/base` Composer package.

## Upgrade

Update the package through Composer:

```bash
composer require toropyga/fmail:^7.0.3
```

Check your PHP version and required extensions before upgrading. No public API changes are introduced in this release.

**Full changelog:** https://github.com/Toropyga/FMail/compare/v7.0.2...v7.0.3
