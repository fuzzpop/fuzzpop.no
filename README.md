# Fuzz Pop

The main navigation is **Home**, **Releases**, **Artists**, **About**, using `/`,
`/releases`, `/artists` and `/about`. The fixed header and responsive TOC and
Settings sidebars follow the sibling sites. Desktop sidebars open by default
and remember their state; phone menus open one at a time. Settings provides
Dark mode, Fullscreen, Share and Reset. Original bitmap colors are preserved
in Light mode.

Releases supports `?view=grid` (default), `?view=details` and `?view=table`,
plus `?artist=NAME`. The existing `/disco?a=SLUG`, `/disco_grid`,
`/disco_table` and `/disco_details` links remain supported. `.htaccess`
permanently redirects old PHP URLs, preserving query strings. Missing releases
return HTTP 404 and invalid view parameters return HTTP 400. PHP includes are
not extensionless public routes.

Artist records live in `data/artists.json`. Existing release and tracklist
text files remain authoritative and are read by `functions.php`; the public
release pages share that reader. Label registration and address information
from the original homepage now appear on About.

Source repository for the record label website at <https://fuzzpop.no>.

## Import baseline

The website was imported from the live Host1 document root on October 26, 2026.
The baseline contains the PHP pages, styles, scripts, release metadata, tracklists
and images. Server error logs and certificate-validation files are excluded.

This import preserves the existing website rather than changing its behavior.

On phones, release front covers fill the page width in Grid, Details and
individual release pages. Desktop thumbnails and layouts remain unchanged.

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

Use the shared Caddy/PHP startup command from this site's directory:

```powershell
powershell.exe -NoProfile -ExecutionPolicy Bypass -File "..\local-tools\startup.ps1"
```

Open <http://localhost:8004>. Port 8001 belongs to MC Peko.
See the shared [site register](../SITES.md) for all eight previews, routing
validation, browser groups, and icon synchronization. Localhost remains on HTTP;
production HTTPS redirects remain an Apache setting.

## Validation

With shared previews running:

```powershell
php -n tests/site.php --http http://localhost:8004
```

Check desktop and phone layouts in the browser, including both desktop
sidebars open, mobile menu switching, theme persistence and full-width covers.

## Known baseline issues

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
