# JPKCom Gutenberg Bootstrap Optimizer – Developer Reference

## Plugin Overview

Deregisters the `areoi-bootstrap` stylesheet in the WordPress admin area so it does not interfere with the Gutenberg editor UI. Runs late (`admin_enqueue_scripts` priority 100) and only acts if that stylesheet is actually enqueued.

- **Text Domain:** `jpkcom-gutenberg-bs-optimize` (no header declared, defaults to slug; only used by the shared updater)
- **Min PHP:** 8.3 | **Min WP:** 6.9
- **Network:** not network-only (no `Network:` header)

---

## Architecture

```
Main file (jpkcom-gutenberg-bs-optimize.php)
├── declare(strict_types=1)
├── Plugin header
├── JPKCOM_GUTENBERG_BS_OPTIMIZE_VERSION constant
├── init @ priority 5: boot JPKComGitPluginUpdater
└── add_action admin_enqueue_scripts (prio 100)
    └── jpkcom_bs_deregister_plugin_admin_styles() : void
        └── if wp_style_is('areoi-bootstrap','enqueued') → wp_deregister_style()
```

---

## Behaviour

| Hook | Type | Effect |
|------|------|--------|
| `admin_enqueue_scripts` | action (prio 100) | Deregisters `areoi-bootstrap` if enqueued in wp-admin |

---

## Constants

| Constant | Value | Purpose |
|----------|-------|---------|
| `JPKCOM_GUTENBERG_BS_OPTIMIZE_VERSION` | matches the header `Version:` | Plugin version (sync with header/README/phpdoc.xml) |

---

## File Structure

```
jpkcom-gutenberg-bs-optimize/
├── jpkcom-gutenberg-bs-optimize.php ← Main: header, constant, admin styles fix, updater bootstrap
├── includes/
│   └── class-plugin-updater.php  ← GitHub auto-updater (namespace: JPKComGutenbergBsOptimizeGitUpdate)
├── .github/workflows/release.yml ← Build ZIP, manifest, PHPDoc, deploy to gh-pages (on tag push)
├── phpdoc.xml                    ← phpDocumentor config
├── README.md                     ← Public readme (source for the WP plugin modal)
├── CLAUDE.md                     ← This file
├── LICENSE                       ← GPL-2.0-or-later
└── .gitignore
```

---

## Plugin Updater

**Family updates (updater 1.5.0).** WordPress only runs the code of active plugins, so the updater copy inside an inactive JPKCom plugin never runs and that plugin was never offered an update. Since 1.5.0 every copy registers in a shared registry (`$GLOBALS['jpkcom_git_updater_registry']`); the copy with the highest `UPDATER_VERSION` is elected leader (ties: the first registered) and serves every installed JPKCom plugin, active or inactive. A plugin counts as JPKCom when its Plugin URI is `https://github.com/JPKCom/<slug>` and it sits in the canonical `<slug>/<slug>.php` layout. The leader provides the update rows with icons, "View details" and the SHA-256 gate (`upgrader_pre_download` at priority `PHP_INT_MAX - 100`, which also hashes a package an earlier filter supplied instead of trusting it). All other 1.5+ copies stay passive; a plugin whose pre-1.5 copy is loaded keeps serving itself and the leader leaves it alone.
- Package URLs are removed before the update transient is stored (`pre_set_site_transient_update_plugins`) and re-injected on read. A request in which no 1.5+ copy is loaded therefore finds an empty package and core refuses the update ("Update package not available") instead of installing an unverified ZIP.
- Version-control checkouts (`.git`, `.svn`, `.hg`, `.bzr`; core's `automatic_updates_is_vcs_checkout` filter applies) and renamed folders (e.g. `<slug>-main`) are told about a new release but never get a package (core shows "Automatic update is unavailable for this plugin", and the gate refuses an update through a stale row with core's `no_package` error): an update would wipe the working tree, or unpack into `<slug>/` and orphan the active plugin.
- Installs without a plugin name (`wp plugin install <url>`, the details modal) are claimed by the asset's file name `<slug>.zip`, as in 1.4, so every URL GitHub serves for a release asset (`releases/latest/download/`, any case, `www.`, `http`, a query string) must match the manifest checksum.
- Manifests are fetched while rendering update data only for users with `update_plugins`, in cron and in WP-CLI; an interactive request fetches at most 5 uncached manifests, and a host breaker (`jpk_git_update_host_fail`, 15 min) stops further reads after a transport error, a 429/5xx or a fetch slower than 3 s. Installs, updates and the details modal always may fetch. The manifest cache (`jpk_git_update_<md5(slug)>`, shared with older copies) lives 24–30 h, spread per plugin.
- Multisite: at least one JPKCom plugin with updater 1.5+ must be network-active or active on the main site. A plugin that is active only on a subsite cannot serve updates (nothing is offered; nothing unverified is installed).
- Covered by `tests/test-updater-family.php`, identical in every JPKCom plugin (170 checks, red against 1.4.0). Measured live on WordPress 7.1.3, single site and multisite.

- **Namespace:** `JPKComGutenbergBsOptimizeGitUpdate\JPKComGitPluginUpdater`
- **Manifest URL:** `https://jpkcom.github.io/jpkcom-gutenberg-bs-optimize/plugin_jpkcom-gutenberg-bs-optimize.json`
- Shared JPKCom updater (downstream copy of upstream `jpkcom-post-filter`; do not edit per-plugin). SHA256 verification, `wp_safe_remote_get()`, URL validation, race-condition lock, 24 h cache, timing-safe `hash_equals()`. Checksum verification is **mandatory**: a missing or unfetchable `checksum_sha256` aborts the update instead of installing unverified code. The verified temp file is returned from `upgrader_pre_download`, so WordPress installs exactly the bytes that were hashed (no second download). Failed manifest fetches are negatively cached for 1 h.
- Hooks: `plugins_api`, `site_transient_update_plugins`, `pre_set_site_transient_update_plugins` (priority `PHP_INT_MAX`), `upgrader_process_complete`, `deleted_plugin`, `upgrader_pre_download` (priority `PHP_INT_MAX - 100`).

---

## Release Workflow

**Actions are pinned to commit SHAs.** Every `uses:` line in `.github/workflows/` references a 40-character commit SHA instead of a tag (`@v4`), with the version as a trailing comment. A tag is a movable pointer and can be repointed; a SHA cannot. Since the release workflow builds the plugin ZIP **and** the SHA256 checksum the auto-updater trusts, a compromised action would ship a tampered ZIP together with a matching checksum — the checksum secures the transport, the pinning secures the build. `.github/dependabot.yml` keeps the pins current weekly in one combined PR; when updating, always change the SHA *and* the version comment together.

**CI** (`.github/workflows/ci.yml`) runs on every pull request *and* on every push to `main` — a required status check only covers pull requests, so a direct push with bypass rights would otherwise skip the checks entirely. It runs `php -l` over all PHP files; flags invalid named arguments to internal PHP functions (catches `sprintf(format:, values:)` → `ArgumentCountError`, which `php -l` does not see); validates the YAML of every `.github` file; asserts every action is pinned to a 40-character commit SHA; and executes `tests/test-*.php` where present.

**Dependabot auto-merge** (`.github/workflows/dependabot-auto-merge.yml`) merges only `semver-patch` and `semver-minor`, and only PRs from `dependabot[bot]` in this repo — never from forks. Major updates get a comment and stay manual. Two repo settings are prerequisites, otherwise this is useless or outright dangerous: "Allow auto-merge" must be enabled, and branch protection must list `CI / Lint & Guards` as a **required status check** — without it `gh pr merge --auto` merges *immediately*, since there is nothing left to wait for. Together with `cooldown: default-days: 7` no action release is adopted during its first week.

Triggered by **pushing a `v*` tag**; the workflow creates the GitHub release automatically. Pipeline: setup PHP/Python/Pandoc/GraphViz → README metadata → slug-named ZIP → SHA256 → upload ZIP + `.sha256` → `plugin_<slug>.json` manifest → PHPDoc → deploy to `gh-pages`.

---

## Security Checklist

- `declare(strict_types=1)` in every PHP file
- Admin-only action; no user input processed
- Updater: SHA256 verification + URL validation (audited separately)

---

## Release Checklist

1. Bump version in: header `Version:` + `Stable tag:`, `JPKCOM_GUTENBERG_BS_OPTIMIZE_VERSION`, `README.md`, `phpdoc.xml`
2. Add a `### x.y.z` block to `## Changelog` in `README.md`
3. Commit, tag `vx.y.z`, push the tag → the workflow builds and publishes everything
