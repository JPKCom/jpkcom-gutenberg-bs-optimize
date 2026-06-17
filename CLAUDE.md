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
| `JPKCOM_GUTENBERG_BS_OPTIMIZE_VERSION` | `'2.0.3'` | Plugin version (sync with header/README/phpdoc.xml) |

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

- **Namespace:** `JPKComGutenbergBsOptimizeGitUpdate\JPKComGitPluginUpdater`
- **Manifest URL:** `https://jpkcom.github.io/jpkcom-gutenberg-bs-optimize/plugin_jpkcom-gutenberg-bs-optimize.json`
- Shared JPKCom updater (downstream copy of upstream `jpkcom-post-filter`; do not edit per-plugin). SHA256 verification, `wp_safe_remote_get()`, URL validation, race-condition lock, 24 h cache, timing-safe `hash_equals()`.
- Hooks: `plugins_api`, `site_transient_update_plugins`, `upgrader_process_complete`, `upgrader_pre_download`.

---

## Release Workflow

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
