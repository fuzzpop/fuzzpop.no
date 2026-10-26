# Fuzz Pop

Source repository for the record label website at <https://fuzzpop.no>.

## Import baseline

The website was imported from the live Host1 document root on October 26, 2026.
The baseline contains the PHP pages, styles, scripts, release metadata, tracklists
and images. Server error logs and certificate-validation files are excluded.

This import preserves the existing website rather than changing its behavior.

## Deployment

The [Deploy site workflow](.github/workflows/deploy.yml) uses SFTP to deploy
website changes pushed to `main`, following the Pekosoft deployment approach.
Repository-only changes do not trigger deployment. Both incremental uploads
and full syncs use [.ftpignore](.ftpignore) to exclude development files,
documentation, secrets, logs and server-managed certificate-validation files.
Keep the workflow's `paths-ignore` list consistent when changing these exclusions.

Before the first deployment, configure these
[GitHub Actions secrets](https://github.com/fuzzpop/fuzzpop.no/settings/secrets/actions):

- `SFTP_HOST`: Host1 SFTP hostname.
- `SFTP_USERNAME`: account with access to the Fuzz Pop website directory.
- `SFTP_PASSWORD`: that account's password.

Configure these
[GitHub Actions variables](https://github.com/fuzzpop/fuzzpop.no/settings/variables/actions):

- `SFTP_REMOTE_DIR`: the absolute Fuzz Pop document root on Host1. Verify this
  against the hosting configuration; do not use the Pekosoft directory.
- `SFTP_PORT`: optional; defaults to `22`.

The workflow fails explicitly if required settings are missing. It learns the
SFTP host key with `ssh-keyscan`, matching Pekosoft's existing setup.

For the first deployment, open
[Actions](https://github.com/fuzzpop/fuzzpop.no/actions/workflows/deploy.yml),
select **Run workflow**, and choose **full**. A full sync uploads the website
and removes files absent from the repository inside `disco`, `disco_photos`
and `disco_photos_small`, including withdrawn releases still on the host.
It does not delete extra files elsewhere on the host, and exclusions remain
protected. Review the destination before starting a full sync.

Subsequent pushes deploy all website additions, changes, renames and deletions
in that push. Manual **changed** runs compare the latest commit with its parent;
use **full** to recover from a failed deployment or reconcile the entire site.
Deployments run one at a time. This workflow does not generate Pekosoft's
updates feed, which Fuzz Pop does not use.

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
