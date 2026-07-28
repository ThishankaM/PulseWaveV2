# PulseWave Enhanced

PulseWave Enhanced is a cleaned and secured PHP/MySQL music-streaming project.
It preserves the original catalog and artwork while replacing the duplicated
pages with a responsive, database-driven application.

## Main improvements

- Clean `app`, `public`, `database`, and `storage` structure
- PDO database connection with environment configuration
- Prepared database queries and playlist ownership checks
- CSRF-protected forms and safer session handling
- User, artist, and administrator roles
- Dynamic artists, genres, tracks, playlists, likes, follows, and history
- Persistent responsive music player with seek, volume, shuffle, and repeat
- Safe JSON search for tracks, artists, and genres
- Mobile-friendly sign-in and registration
- Artist/admin upload dashboard with file validation
- Subscription requests without collecting card numbers or CVV values
- Shared navigation, player, footer, error messages, and reusable UI components

No Composer or Node.js dependencies are required.

## Requirements

- PHP 8.1 or newer
- MySQL 8 or MariaDB 10.4+
- Apache or another PHP-compatible web server
- PHP extensions: `pdo_mysql`, `mbstring`, and `fileinfo`

## Installation with XAMPP

1. Extract the project into:

   ```text
   C:\xampp\htdocs\PulseWave_Enhanced
   ```

2. Copy `.env.example` to `.env`.

3. Update the database values in `.env`. For a normal local XAMPP installation:

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

   This creates a new `pulsewave` database and does not overwrite the original
   `project` database.

5. Open:

   ```text
   http://localhost/PulseWave_Enhanced/public/
   ```

The application detects its URL automatically. Set `APP_URL` in `.env` only
when automatic detection is not suitable.

## Recommended database account for deployment

Do not use the MySQL `root` account outside a local development computer.

```sql
CREATE USER 'pulsewave_user'@'localhost' IDENTIFIED BY 'a-strong-password';
GRANT SELECT, INSERT, UPDATE, DELETE ON pulsewave.* TO 'pulsewave_user'@'localhost';
FLUSH PRIVILEGES;
```

Place the same username and password in `.env`.

## Audio files

Audio is intentionally excluded because it makes the ZIP too large. Copy the
files from the original `audio` folder into:

```text
public/uploads/audio/
```

Keep the original filenames. The enhanced database already uses the matching
paths. More information is available in
`public/uploads/audio/README.md`.

For large uploads, update `upload_max_filesize` and `post_max_size` in
`php.ini`, then restart Apache.

## Accounts

The user and artist accounts from the supplied SQL file are retained with their
existing password hashes.

A local administrator is included for testing:

```text
Email: admin@pulsewave.local
Password: PulseWave123!
```

Change or delete this account before publishing the application.

## Project structure

```text
PulseWave_Enhanced/
├── app/
│   ├── Core/             Database, authentication, and CSRF protection
│   ├── Repositories/     Catalog and library database operations
│   ├── Support/          Shared helper functions
│   └── Views/partials/   Shared header, search, player, and footer
├── database/
│   ├── schema.sql        Clean installation with migrated catalog data
│   └── legacy/           Original supplied SQL dump for reference
├── public/
│   ├── actions/          Validated POST request handlers
│   ├── api/              JSON catalog, search, and play-history endpoints
│   ├── assets/           Application CSS and JavaScript
│   ├── uploads/          Artwork and external audio storage
│   └── *.php             User-facing pages
├── storage/logs/         Application error logs
├── .env.example          Environment configuration template
└── README.md
```

Configure the web-server document root to `public/` for production. The root
`.htaccess` blocks direct access to application, database, and log directories
when the entire project is temporarily served through Apache.

## Subscription integration

Paid plan selection creates a `pending` subscription record. Connect that
record to a PCI-compliant hosted checkout provider before production. Never add
card-number or CVV fields to the PulseWave database.

## Production checklist

- Remove or change the demo administrator
- Use HTTPS and a restricted database account
- Set `APP_ENV=production`
- Set a fixed `APP_URL`
- Configure daily database and media backups
- Add email verification and provider-backed password reset
- Connect a hosted payment provider
- Confirm music and artwork licensing
- Review the privacy policy and terms with the real organization details

