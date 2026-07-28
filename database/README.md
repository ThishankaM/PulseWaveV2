# Database files

- `schema.sql` creates the new normalized `pulsewave` database and inserts the
  supplied catalog, accounts, artists, playlists, and plan data.
- `legacy/PulseWave_original.sql` is the unmodified SQL file supplied with the
  original project.

Import `schema.sql` for the enhanced application. It creates a separate
database, so the old `project` database can remain available as a backup.

