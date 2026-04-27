# ZP BanList — phpBB 3.3.x Extension

A phpBB 3.3.x extension that adds a public page listing all active bans on the forum, with pagination, filtering, sorting, and inline management tools for administrators and global moderators.

---

## Features

### Ban list display
- Paginated list of all active bans (user, IP, email)
- Columns: **User / IP**, **Ban date**, **Expiry**, **Reason**, **Type**
- Visual badges for **Permanent** and **Temporary** bans with remaining time (e.g. `3d 14h`, `45m`)
- Tooltip showing exact expiry date on badge hover
- Alternating row colors (zebra striping) for easy reading
- Expired bans are automatically excluded from the list

### Filters and search
- Filter by type: **All** / **Temporary** / **Permanent**
- Search by username (partial match)
- Sort by **Ban date** and **Expiry** (ascending/descending)

### Inline editing (admins and global moderators)
Clicking the edit icon next to any ban opens an inline form with:
- **Message to banned user** — edit the text shown to the banned user
- **Ban expiry** — elegant date/time picker (Flatpickr) with day and time selection
- **Permanent checkbox** — enables/disables the date field; ban can be converted between temporary and permanent
- **Save** — saves via AJAX without page reload; the row updates live
- **Cancel** — closes the form without saving
- **Revoke ban** — immediately revokes the ban with a confirmation prompt; the row is removed from the list

### Logging
Every edit and revocation is recorded in the forum log (ACP log and MCP log):
- Message edit: `[ZP BanList] Ban edit for user: ...`
- Message + expiry edit: `[ZP BanList] Ban edit for user: ... | Expiry: old → new`
- Ban revocation: `[ZP BanList] Ban revoked for user: ...`

---

## ACP Control Panel

### Settings (`Manage`)
| Setting | Type | Default | Description |
|---------|------|---------|-------------|
| `zp_banlist_per_page` | Integer | 20 | Number of records per page |
| `zp_banlist_mod_edit` | Boolean | No | Allow inline editing for global moderators |

### Diagnostics
Panel that verifies installation integrity:
- Status of each migration
- Presence of configuration keys
- ACP module registration
- Extension activation status

---

## Requirements

| Requirement | Version |
|------------|---------|
| phpBB | ≥ 3.3.0 |
| PHP | ≥ 7.1.3 |

---

## Installation

1. Copy the `zp_banlist` folder into `ext/marcozp/` in your phpBB installation
2. Log into **ACP → Customise → Manage extensions**
3. Find **ZP BanList** and click **Enable**
4. The extension will automatically run its database migrations
5. Configure settings under **ACP → ZP BanList → Manage**

---

## File structure

```
ext/marcozp/zp_banlist/
├── acp/
│   ├── main_info.php               # ACP module definitions
│   └── main_module.php             # ACP panel logic
├── adm/style/
│   ├── acp_zp_banlist.html         # ACP manage template
│   └── acp_zp_banlist_diagnostics.html  # ACP diagnostics template
├── config/
│   ├── routing.yml                 # Route definitions
│   └── services.yml                # Dependency injection services
├── controller/
│   └── main_controller.php         # Public page + AJAX controller
├── event/
│   └── main_listener.php           # phpBB event listener
├── language/
│   ├── en/
│   │   ├── common.php
│   │   └── info_acp_zp_banlist.php
│   └── it/
│       ├── common.php
│       └── info_acp_zp_banlist.php
├── migrations/
│   ├── install_acp_module.php
│   ├── add_config.php
│   └── add_diagnostics_mode.php
└── styles/all/
    ├── template/
    │   ├── zp_banlist_body.html    # Ban list page template
    │   └── event/
    │       ├── overall_header_head_append.html   # Flatpickr asset injection
    │       └── overall_header_navigation_append.html  # Navigation menu link
    └── theme/
        ├── zp_banlist.css          # Extension stylesheet
        ├── flatpickr.min.css       # Flatpickr (bundled)
        ├── flatpickr.min.js        # Flatpickr (bundled)
        └── flatpickr.it.js         # Flatpickr Italian locale
```

---

## Supported languages

| Language | Code |
|----------|------|
| English | `en` |
| Italian | `it` |

---

## Required permissions

| Permission | Description |
|-----------|-------------|
| `a_ban` | View list and inline editing (administrators) |
| `m_` | Inline editing (global moderators, if enabled in ACP) |

---

## Screenshots

> *(add screenshots here before publishing)*

---

## License

GPL-2.0-only — see [LICENSE](LICENSE)

---

## Author

**marcozp**

---

## Changelog

See [EXTENSION_METADATA.md](EXTENSION_METADATA.md) for the full changelog.
# zp_banlist
