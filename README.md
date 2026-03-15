# 📒 Contact Book — Web Application

A full-stack PHP + MySQL contact management system with approval workflow, WhatsApp OTP authentication, feature flags, and mobile-responsive UI.

**Live Demo:** [addressbook.free.nf/Test](https://addressbook.free.nf/Test/)

---

## ✨ Features

- **Contact Management** — Add, edit, delete contacts with full personal and address details
- **Dual Address Support** — Current address + Vatan (home village) address per contact
- **Approval Workflow** — Every change goes through admin review before going live
- **WhatsApp OTP** — Two-factor authentication for admin approve/reject actions via CallMeBot
- **Feature Flags** — Toggle functionality on/off from the admin UI without touching code
- **Field Validation** — Per-field required/optional rules controlled from `config.php`
- **Google Places** — Address autocomplete powered by Google Places API
- **Excel Export** — Select rows and export to `.xlsx` with one click
- **Mobile Responsive** — Table view on desktop, card view on mobile
- **Direct Call/Chat** — Clickable phone links to call or open WhatsApp directly

---

## 📁 Project Structure

```
Test/
├── index.php                      # Main contacts page (list, add, edit, delete, export)
├── approval.php                   # Admin approval page (review pending changes, flag toggles)
├── login.php                      # Admin login form
├── logout.php                     # Session destroy + redirect
├── api.php                        # REST API — all CRUD + approval endpoints
├── config.php                     # ⚙️ Central config — DB, feature flags, validation flags
├── auth.php                       # Session guard, OTP send/verify functions
├── otp.php                        # AJAX handler for WhatsApp OTP flow
├── flags.php                      # AJAX handler to read/write feature flags
├── debug.php                      # Diagnostic page (delete after setup)
├── alter_table.sql                # ALTER TABLE — adds new columns to existing DB
└── if0_41373306_contactbook.sql   # Full database dump
```

---

## 🗄️ Database Tables

| Table | Description |
|---|---|
| `contacts` | All contact records with personal info, current address, Vatan address |
| `contacts_pending` | Pending add/edit/delete requests awaiting admin approval |
| `users` | System users (reserved for future multi-user auth) |

### Key columns in `contacts`

| Column | Type | Notes |
|---|---|---|
| `approval_status` | ENUM | `pending` / `approved` / `rejected` |
| `statuz` | ENUM | `active` / `inactive` |
| `dob` | VARCHAR(10) | Format: `DD-MM-YYYY` |
| `mo_no` / `wp_no` | VARCHAR(20) | Mobile / WhatsApp numbers |
| `block_no` … `country` | VARCHAR / INT | Current address (7 fields) |
| `Vatan_vilage` … `Vatan_country` | VARCHAR / INT | Vatan address (8 fields) |

---

## ⚙️ Configuration (`config.php`)

All settings are controlled from a single file:

```php
// Admin credentials
define('ADMIN_USER', 'admin');
define('ADMIN_PASS', 'Admin@1234');

// WhatsApp OTP (CallMeBot)
define('WA_PHONE',   '919999999999');  // With country code, no +
define('WA_API_KEY', 'XXXXXXXX');

// Database
define('DB_HOST', 'your-db-host');
define('DB_USER', 'your-db-user');
define('DB_PASS', 'your-db-password');
define('DB_NAME', 'your-db-name');
```

### Feature Flags

| Flag | Default | Description |
|---|---|---|
| `FEATURE_WA_OTP` | `false` | Require WhatsApp OTP before approve/reject |
| `FEATURE_OTP_FALLBACK` | `false` | Show OTP on screen if WhatsApp fails |
| `FEATURE_EXPORT` | `false` | Excel export button on contacts page |
| `FEATURE_APPROVAL` | `false` | Enable approval workflow for all changes |
| `FEATURE_GOOGLE_PLACES` | `false` | Google Places address autocomplete |
| `FEATURE_COPY_ADDR` | `true` | Copy current address to Vatan address button |

> All flags can also be toggled live from the **Feature Flags panel** on `approval.php` — no file editing needed.

### Validation Flags

Control which fields are required (`true`) or optional (`false`):

```php
define('VALIDATE_FIRST_NAME',    true);
define('VALIDATE_MOBILE',        true);
define('VALIDATE_DOB',           false);   // optional
define('VALIDATE_FATHER_NAME',   false);   // optional
// ... etc
```

---

## 🚀 Installation

### 1. Import Database

Import `if0_41373306_contactbook.sql` into your MySQL database via phpMyAdmin or CLI:

```bash
mysql -u your_user -p your_database < if0_41373306_contactbook.sql
```

### 2. Configure

Edit `config.php` with your database credentials and admin password:

```php
define('DB_HOST', 'your-host');
define('DB_USER', 'your-user');
define('DB_PASS', 'your-password');
define('DB_NAME', 'your-database');
define('ADMIN_USER', 'admin');
define('ADMIN_PASS', 'YourSecurePassword');
```

### 3. Upload Files

Upload all PHP files to your web server via FTP or File Manager.

### 4. Set Permissions

```bash
chmod 666 config.php   # Required for live flag toggling from admin UI
```

### 5. (Optional) WhatsApp OTP Setup

1. Save `+34 644 59 77 30` in WhatsApp contacts as **CallMeBot**
2. Send: `I allow callmebot to send me messages`
3. Receive your API key by WhatsApp reply
4. Update `WA_PHONE` and `WA_API_KEY` in `config.php`
5. Set `FEATURE_WA_OTP` to `true`

### 6. (Optional) Google Places

1. Enable **Places API** in [Google Cloud Console](https://console.cloud.google.com)
2. Create an API key and add your domain to restrictions
3. Add key to `index.php`:
   ```js
   var GOOGLE_API_KEY = 'AIzaSyBxxxxxxxxxxxxxxxxxxxxxxxxxxxx';
   ```
4. Set `FEATURE_GOOGLE_PLACES` to `true` in `config.php`

---

## 🔌 API Endpoints

All requests to `api.php` — no authentication required for public endpoints.

| Action | Method | Auth | Description |
|---|---|---|---|
| `?action=list` | GET | — | List approved + active contacts |
| `?action=create` | POST | — | Submit new contact for approval |
| `?action=update` | POST | — | Submit edits for approval |
| `?action=delete` | POST | — | Submit delete request for approval |
| `?action=pending_list` | GET | Session | Get all pending changes with diff data |
| `?action=approve` | POST | Session + OTP | Approve a pending change |
| `?action=reject` | POST | Session + OTP | Reject a pending change |
| `?action=export` | POST | — | Export selected contacts as JSON for Excel |

---

## 🔄 Approval Workflow

```
User submits change
        ↓
Contact marked approval_status = pending
Change logged in contacts_pending
        ↓
Admin opens approval.php
        ↓
[If FEATURE_WA_OTP = true] → Verify WhatsApp OTP
        ↓
Admin reviews diff (old → new values highlighted)
        ↓
    Approve                     Reject
       ↓                           ↓
Change applied             Change discarded
approval_status            Contact reverts to
= approved                 previous state
```

---

## 🛡️ Security

- All SQL queries use **prepared statements** — protected against SQL injection
- Admin pages protected by **session guard** (`requireLogin()`)
- Approve/reject additionally protected by **OTP verification** (`requireOTP()`)
- Session auto-expires after **1 hour** of inactivity
- OTP rate-limited — 60-second cooldown between resend requests

> ⚠️ **Before production:** Hash the admin password using `password_hash()`, restrict the Google API key to your domain, and delete `debug.php` from the server.

---

## 🐛 Troubleshooting

| Issue | Fix |
|---|---|
| "Loading..." stuck on approval page | Upload latest `approval.php` (JS variable conflict fixed) |
| "Unknown column dob" fatal error | Run `alter_table.sql` in phpMyAdmin |
| "contacts_pending table not found" | Import `if0_41373306_contactbook.sql` |
| "Cannot write config.php" | `chmod 666 config.php` on server |
| "DB connection failed" | Check credentials in `config.php` |
| WhatsApp OTP not received | Verify CallMeBot registration, check `WA_PHONE` has no `+` |
| Google Places not working | Enable Places API, check API key restrictions |

Run `debug.php` in your browser for a full diagnostic:
```
https://your-domain/Test/debug.php
```
> Delete `debug.php` after fixing — it exposes DB credentials.

---

## 🛠️ Tech Stack

| Layer | Technology |
|---|---|
| Backend | PHP 7.4+ |
| Database | MySQL 5.7+ / MariaDB |
| Frontend | HTML5, CSS3, Vanilla JavaScript |
| Excel Export | SheetJS (xlsx.js) via CDN |
| Address Autocomplete | Google Places API |
| WhatsApp OTP | CallMeBot API (free) |
| Hosting | InfinityFree |

---

## 📄 License

This project is for personal/educational use.
