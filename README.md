# Fuzz Pop

The main navigation is **Home**, **Releases**, **Artists**, **About**, using `/`,
`/releases`, `/artists` and `/about`. The fixed header uses the Fuzz Pop logo as
its home link, and the responsive TOC and Settings sidebars follow the sibling
sites. Desktop sidebars open by default
and remember their state; phone menus open one at a time. Settings provides
Dark mode, Fullscreen, Share and Reset. Original bitmap colors are preserved
in Light mode.

Releases supports `?view=grid` (default), `?view=details` and `?view=table`,
plus `?artist=NAME`. The existing `/disco?a=SLUG`, `/disco_grid`,
`/disco_table` and `/disco_details` links remain supported. `.htaccess`
permanently redirects old PHP URLs, preserving query strings. Missing releases
return HTTP 404 and invalid view parameters return HTTP 400. PHP includes are
not extensionless public routes.

Artist records live in `data/artists.json`. Artists can be listed before they
have a Fuzz Pop release. Artist logos link to their website when one is known.
Existing release and tracklist text files remain authoritative and are read by
`functions.php`; the public release pages share that reader. Label registration
and address information from the original homepage now appear on About.
The Epileptic Passion logo uses outlined Edwardian Script ITC lettering,
matching the Meant To Be Shared cover. It is not linked because the artist
record has no verified website. The Silentlab Artists logo uses an SVG path
wordmark traced from the original artwork.
Arctica / Antarctica has an interim white-only SVG based on the provided logo.
Replace it with the original vector artwork when available.

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
website changes pushed to `main`, using the shared all-site deployment flow.
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

- `SFTP_PORT`: optional; defaults to `22`.

The workflow defines `SFTP_REMOTE_DIR` as `/home/flamenco/fuzzpop.no`.
The old destination variable and `SERVER_DIR` secret are not used.

The workflow fails explicitly if required settings are missing. It learns the
SFTP host key with `ssh-keyscan`, matching Pekosoft's existing setup.

Before migration or the first deployment, open
[Actions](https://github.com/fuzzpop/fuzzpop.no/actions/workflows/deploy.yml),
select **Run workflow**, and choose **check** to verify the SFTP login and
destination without changing files. Choose **full** only after that check.
A full sync uploads tracked runtime files
and removes files absent from the repository inside `disco`, `disco_photos`
and `disco_photos_small`, including withdrawn releases still on the host.
It does not delete extra files elsewhere on the host, and exclusions remain
protected. Review the destination before starting a full sync.

Both incremental and full uploads use lftp over SFTP. OpenSSH's batch client
rejects authentication on this account while lftp connects with the same secrets.
The read-only **Test deployment connection** workflow checks both clients without
uploading or deleting files.

Subsequent pushes and manual **changed** runs compare the latest commit with
the last successful deployment on the same branch, recovering changes from failed
runs. Successful **check** and **dry-run** runs are not upload baselines.
API errors or missing deployed ancestors stop deployment rather than
silently selecting a destructive full sync. Use **full** only after reviewing
the destination and its documented deletion behavior.
Deployments run one at a time. This workflow does not generate Pekosoft's
updates feed, which Fuzz Pop does not use.

Existing `FTP_SERVER`, `FTP_USERNAME` and `FTP_PASSWORD`
secrets are accepted as compatibility aliases when SFTP settings are absent.
They still use SFTP, not FTP or FTPS. Verify the destination in SFTP.
Run `node --test .github/tests/deployment.cjs` for deployment regression tests.

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
