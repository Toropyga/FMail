# Changelog

Changes in the FMail 7.0.x series. Historical entries are based on the repository's tagged commits.

## [7.0.3] - 2026-10-31

### Changed

- Require PHP 7.0 or higher in Composer, replacing the previous `>=5.1.0` requirement.
- Allow any version of `toropyga/base` (`*`), replacing the `^1.0.3` constraint.
- Update the class version metadata, `X-Mailer` header, and README version badges to 7.0.3.

### Documentation

- Align the English and Russian README requirements with `composer.json`, including the required PHP extensions.
- Add this changelog for the 7.0.x series and release notes for v7.0.3.

## [7.0.2] - 2026-08-31

### Added

- Automatic SMTP STARTTLS negotiation when advertised by the server, with a `setStartTLS()` option to enable or disable it.

### Fixed

- Use CRLF message line endings and MIME version 1.0.
- Correct multipart boundaries, attachment formatting, and inline attachment disposition.
- Preserve detected attachment MIME types instead of overwriting them with `text/plain`.
- Correct the `To` header for messages sent to individual recipients through `mail()`.
- Accept numeric timeout values and tighten email validation character ranges.
- Guard calls to `Toropyga\Base` when the class is unavailable.
- Replace `utf8_encode()` with `mb_convert_encoding()` in subject decoding.

### Security

- Hide SMTP authentication credentials in authentication logs; remove the password and mask the login in IMAP connection errors.
- Escape error messages rendered as HTML in debug output.

### Changed

- Translate source comments to English and update author contact information.
- Set the Composer constraint for `toropyga/base` to `^1.0.3`.

## [7.0.1] - 2026-07-29

### Changed

- Update class version metadata and README badges to 7.0.1 following the Composer autoload fix.

## [7.0.0-fix] - 2026-07-29

### Fixed

- Align the Composer PSR-4 autoload namespace with `Toropyga\` and the `src/` directory.

## [7.0.0] - 2026-07-29

### Changed

- Rename the PHP namespace from `FYN` to `Toropyga`, including the imported `Base` class.
- Update documentation and examples to use `Toropyga\FMail`.

[7.0.3]: https://github.com/Toropyga/FMail/compare/v7.0.2...v7.0.3
[7.0.2]: https://github.com/Toropyga/FMail/compare/v7.0.1...v7.0.2
[7.0.1]: https://github.com/Toropyga/FMail/compare/v7.0.0-fix...v7.0.1
[7.0.0-fix]: https://github.com/Toropyga/FMail/compare/v7.0.0...v7.0.0-fix
[7.0.0]: https://github.com/Toropyga/FMail/compare/v6.0.6...v7.0.0
