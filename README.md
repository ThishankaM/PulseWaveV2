<div align="center">

# 🎧 PulseWave

### Music that moves at your frequency.

PulseWave is a modern, database-driven music streaming experience built with PHP, MySQL, and vanilla JavaScript. Discover artists, explore genres, create playlists, save favourites, and control playback from a persistent responsive player.

<p>
  <img src="https://img.shields.io/badge/PHP-8.1%2B-777BB4?style=for-the-badge&logo=php&logoColor=white" alt="PHP 8.1+">
  <img src="https://img.shields.io/badge/MySQL-8.0-4479A1?style=for-the-badge&logo=mysql&logoColor=white" alt="MySQL 8">
  <img src="https://img.shields.io/badge/JavaScript-Vanilla-F7DF1E?style=for-the-badge&logo=javascript&logoColor=111111" alt="Vanilla JavaScript">
  <img src="https://img.shields.io/badge/UI-Responsive-A855F7?style=for-the-badge" alt="Responsive UI">
  <img src="https://img.shields.io/badge/Version-2.0.0-C084FC?style=for-the-badge" alt="Version 2.0.0">
</p>

<p>
  <a href="#-highlights">Highlights</a> •
  <a href="#-showcase">Showcase</a> •
  <a href="#-technology">Technology</a> •
  <a href="#-quick-start">Quick start</a> •
  <a href="#-project-structure">Structure</a>
</p>

<br>

<img src="Screenshot%202026-07-28%20233645%281%29.png" alt="PulseWave home page" width="100%">

</div>

## ✨ Highlights

<table>
  <tr>
    <td width="50%" valign="top">
      <h3>🎵 Immersive playback</h3>
      Persistent playback across pages with seek, volume, shuffle, repeat, previous, and next controls.
    </td>
    <td width="50%" valign="top">
      <h3>🔎 Music discovery</h3>
      Browse tracks by genre, search the catalog, explore artist profiles, and discover new favourites.
    </td>
  </tr>
  <tr>
    <td width="50%" valign="top">
      <h3>💜 Personal library</h3>
      Create playlists, like tracks, follow artists, and keep listening history tied to your account.
    </td>
    <td width="50%" valign="top">
      <h3>👤 Role-based experience</h3>
      Dedicated permissions and workflows for listeners, artists, and administrators.
    </td>
  </tr>
  <tr>
    <td width="50%" valign="top">
      <h3>📱 Responsive design</h3>
      A polished dark interface designed to feel consistent across desktop, tablet, and mobile screens.
    </td>
    <td width="50%" valign="top">
      <h3>🛡️ Safer foundations</h3>
      PDO prepared statements, CSRF-protected forms, session hardening, ownership checks, and validated uploads.
    </td>
  </tr>
</table>

## 🖼️ Showcase

<table>
  <tr>
    <td width="50%" align="center" valign="top">
      <strong>Explore the catalog</strong><br><br>
      <img src="README assets\Screenshot 2026-07-28 233659.png" alt="PulseWave Explore page" width="100%">
    </td>
    <td width="50%" align="center" valign="top">
      <strong>Discover artists</strong><br><br>
      <img src="README assets\Screenshot 2026-07-28 233708.png" alt="PulseWave Artists page" width="100%">
    </td>
  </tr>
  <tr>
    <td width="50%" align="center" valign="top">
      <strong>Artist profiles</strong><br><br>
      <img src="README assets\Screenshot 2026-07-28 233740.png" alt="PulseWave artist profile" width="100%">
    </td>
    <td width="50%" align="center" valign="top">
      <strong>Your personal library</strong><br><br>
      <img src="README assets\Screenshot 2026-07-28 233719.png" alt="PulseWave Library page" width="100%">
    </td>
  </tr>
  <tr>
    <td width="50%" align="center" valign="top">
      <strong>Flexible listening plans</strong><br><br>
      <img src="README assets\Screenshot 2026-07-28 233726.png" alt="PulseWave Upgrade page" width="100%">
    </td>
    <td width="50%" align="center" valign="top">
      <strong>Sign in and create an account</strong><br><br>
      <img src="README assets\Screenshot 2026-07-28 233820.png" alt="PulseWave authentication page" width="100%">
    </td>
  </tr>
</table>

> [!NOTE]
> Keep the seven supplied screenshot files beside `README.md`, using their original filenames, so the complete gallery renders on GitHub.

## 🚀 What you can do

- Browse a dynamic catalog of tracks, artists, and genres
- Search tracks, artists, and genres through a JSON API
- Play music with a persistent cross-page audio controller
- Create and manage personal playlists
- Like favourite tracks and follow artists
- View artist profiles, follower counts, and available releases
- Maintain account-specific listening history
- Register, sign in, edit a profile, and sign out securely
- Upload tracks and artwork from artist and administrator dashboards
- Compare plans and submit subscription requests

## 🧰 Technology

| Layer | Technology | Purpose |
| --- | --- | --- |
| Front end | HTML5, CSS3, vanilla JavaScript | Responsive interface and player interactions |
| Back end | PHP 8.1+ | Routing, authentication, business logic, and APIs |
| Database | MySQL 8 / MariaDB 10.4+ | Catalog, accounts, playlists, likes, follows, and history |
| Data access | PDO | Prepared queries and consistent database access |
| Web server | Apache | Local XAMPP development and `.htaccess` protection |
| Configuration | `.env` | Environment-specific database and application settings |

There are no Composer or Node.js dependencies.

## ⚡ Quick start

### Requirements

- PHP 8.1 or newer
- MySQL 8 or MariaDB 10.4+
- Apache or another PHP-compatible web server
- PHP extensions: `pdo_mysql`, `mbstring`, and `fileinfo`

### XAMPP installation

1. Place the project in your XAMPP document root:

   ```text
   C:\xampp\htdocs\PulseWave_Enhanced
   ```

2. Copy the environment template:

   ```text
   .env.example → .env
   ```

3. Configure the local database in `.env`:

   ```env
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=pulsewave
   DB_USERNAME=root
   DB_PASSWORD=
   ```

4. Open phpMyAdmin and import:

   ```text
   database/schema.sql
   ```

5. Add the audio files as described in [Audio files](#-audio-files).

6. Start Apache and MySQL, then visit:

   ```text
   http://localhost/PulseWave_Enhanced/public/
   ```

The application detects its base URL automatically. Set `APP_URL` in `.env` only when automatic detection is unsuitable.

## 🔊 Audio files

Audio is intentionally excluded from the project archive to keep it lightweight. Copy the original audio files into:

```text
public/uploads/audio/
```

Keep the original filenames because the database already references the matching paths. For large uploads, increase `upload_max_filesize` and `post_max_size` in `php.ini`, then restart Apache.

## 🗂️ Project structure

```text
PulseWave_Enhanced/
├── app/
│   ├── Core/               # Database, authentication, and CSRF
│   ├── Repositories/       # Catalog and library data operations
│   ├── Support/            # Shared helper functions
│   └── Views/partials/     # Navigation, search, player, and footer
├── database/
│   ├── schema.sql          # Clean database installation
│   └── legacy/             # Original SQL dump for reference
├── public/
│   ├── actions/            # Validated POST request handlers
│   ├── api/                # Catalog, search, and history endpoints
│   ├── assets/             # Application CSS and JavaScript
│   ├── uploads/            # Artwork and external audio storage
│   └── *.php               # User-facing pages
├── storage/logs/           # Application error logs
├── .env.example            # Environment configuration template
└── README.md
```

For production, configure the web server document root to `public/`.

## 🔐 Accounts and roles

| Role | Capabilities |
| --- | --- |
| Listener | Browse, play, search, like, follow, and manage playlists |
| Artist | Listener features plus track and artwork uploads |
| Administrator | Catalog oversight, uploads, and application management |

A local administrator account is included for testing:

```text
Email: admin@pulsewave.local
Password: PulseWave123!
```

> Change or remove the demo administrator before publishing the application.

## 🛡️ Security

- Parameterized PDO queries help prevent SQL injection
- CSRF tokens protect state-changing forms
- Session regeneration and safer cookie settings strengthen authentication
- Playlist ownership checks prevent cross-account modifications
- Upload validation restricts file types, extensions, and destinations
- Sensitive configuration remains outside the public directory
- Apache rules block direct access to application, database, and log files

## 💳 Subscription flow

Paid plan selection creates a `pending` subscription request without storing card numbers or CVV values. Connect this workflow to a PCI-compliant hosted checkout provider before production.

## ✅ Before production

- Replace or remove the demo administrator
- Use HTTPS and a restricted database account
- Set `APP_ENV=production` and a fixed `APP_URL`
- Configure database and media backups
- Add email verification and provider-backed password reset
- Connect a hosted payment provider
- Confirm music and artwork licensing
- Publish organization-specific privacy terms

---

<div align="center">

Built for people who believe every moment deserves the right soundtrack.

**PulseWave — press play and find your frequency.**

</div>
