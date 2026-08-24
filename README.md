# MSA Go — Short Link Manager

A bilingual Laravel application for branded short links, visit analytics, audit history and role-based access.

## Included

- English and Arabic interface with RTL support and Tajawal Arabic typography.
- Random unique six-digit codes or user-defined slugs.
- 302 redirects, active/inactive status and optional expiration.
- Visit, unique visitor, device, browser, platform and referrer analytics.
- Link creator/updater ownership and permanent audit history.
- Spatie roles and permissions with Super Admin, Administrator and Analyst defaults.

## Local setup with Laragon

1. Start Laragon and use **Reload** so `msa-short-links.test` is created.
2. Open `http://msa-short-links.test`.
3. Default local login: `admin@msago.local` / `ChangeMe123!`.
4. Change the default password immediately before exposing the app to a network.

The local build uses SQLite at `database/database.sqlite`. For production, configure MySQL in `.env`, set `APP_URL` to the final short domain, disable debug mode, run migrations and seed the initial roles/admin.

## Commands

```powershell
C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe artisan migrate --seed
C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe artisan test
```
