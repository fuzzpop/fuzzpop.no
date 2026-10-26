# Fuzz Pop

Source repository for the record label website at <https://fuzzpop.no>.

## Import baseline

The website was imported from the live Host1 document root on October 26, 2026.
The baseline contains the PHP pages, styles, scripts, release metadata, tracklists
and images. Server error logs and certificate-validation files are excluded.

This import preserves the existing website rather than changing its behavior.
Automatic deployment is not configured. Do not upload repository-only files to
Host1; `.ftpignore` records those exclusions for future deployment tooling.

## Local preview

From the repository directory, run:

```powershell
php -S localhost:8001 -t .
```

Open <http://localhost:8001>. Use a different available port if necessary.
PHP's development server does not apply `.htaccess`; the production HTTPS
redirect remains an Apache setting.

## Known baseline issues

- Some release metadata omits the Spotify URL line. Existing direct array access
  can emit PHP 8 warnings, including in `disco_details.php` and `disco.php`.
- The legacy `disco_table.php` references an undefined `$map` when constructing
  artist links and emits PHP warnings.
- The existing `disco_test.php` contains a syntax error and does not pass PHP
  linting. It is retained as part of the original import, not as a passing test.

These issues predate the import and have not been repaired in the baseline.

## Licence

This repository uses the custom **Source Available - All Rights Reserved
License** in [LICENCE](./LICENCE). Public visibility does not grant permission
to reuse its contents. See that file for the copyright holder and permission
contact.

The existing `sort_table.js` is Stuart Langridge's SortTable version 2 and retains
its upstream X11/MIT terms. The custom repository licence does not replace those
third-party permissions. See [THIRD-PARTY-LICENCES](./THIRD-PARTY-LICENCES).
