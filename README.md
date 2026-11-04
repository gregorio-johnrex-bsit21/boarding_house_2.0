# BoardersHub for Sally Boarding House

BoardersHub is a web-based boarding-house management system for Sally Boarding House at CHMSU Binalbagan, Academic Year 2026-2027. It provides a landlord administration module for managing occupancy, billing, payments, devices, and maintenance, alongside a private Boarder Dashboard for each boarder.

## Features

### Landlord Administration Module

- Manage boarder accounts, room and bed assignments, and occupancy status.
- Configure rent settings, review payment submissions, record cash payments, and view billing information.
- Maintain the device catalog and review boarder device declarations.
- Review repair reports and update their urgency and status.
- View room and bed availability using the property map.

### Boarder Dashboard

- Review the current rent balance, bill, payment history, and payment-submission status.
- Submit payment details and a receipt image for landlord review.
- View the assigned room and bed on the interactive property map, with other beds shown by status only.
- View device availability and submit or cancel personal device declarations.
- Report repairs, track urgency and status, and update personal and emergency-contact details.
- Switch between saved dark and light themes.

## Technology

- Laravel and PHP
- Blade templates
- CSS and vanilla JavaScript
- SQLite
- Three.js-powered 3D property map

## Setup

From the project directory:

1. Install PHP dependencies: `composer install`
2. Copy the example environment file: `Copy-Item .env.example .env` (PowerShell) or `cp .env.example .env`.
3. Generate the application key: `php artisan key:generate`.
4. Ensure `.env` uses `DB_CONNECTION=sqlite`, then create the database file if it does not exist: `if (-not (Test-Path database\database.sqlite)) { New-Item -ItemType File database\database.sqlite }` (PowerShell) or `touch database/database.sqlite`.
5. Apply migrations: `php artisan migrate`.
6. Optionally load the demo data: `php artisan db:seed --class=BoardersHubSeeder`. The demo account passwords are defined in the seeder; do not use them in production.
7. Start the development server: `php artisan serve`.

The demo account emails are `admin@boardershub.test` and `tenant@boardershub.test`. Their passwords are in the seeder; no passwords are listed here.

## Tests

Run the test suite with:

```text
php artisan test
```

## Scope and limitations

- The system records payment submissions; it does not process online payments.
- Utility charges are not calculated from meters.
- Electricity, water, and Wi-Fi are not billed to boarders; electricity and water are included in rent.
- There are no announcements or messaging features and no native mobile app.
- Shared-device coordination, downloadable PDF receipts, daily-rate and move-out proration, advance-payment coverage dates, and payment-edit audit history are not implemented.

## Project map

- `app/` — application models, controllers, validation, and support logic.
- `routes/` — web and authentication routes.
- `resources/views/` — Blade views for administration, authentication, boarders, and shared layouts.
- `public/css/` and `public/js/` — stylesheets and browser-side behavior.
- `public/map/` — 3D map page and related assets.
- `docs/design/` — archived design prototype.
- `database/migrations/` and `database/seeders/` — database schema changes and optional demo data.
- `tests/` — automated feature and unit tests.

## 3D map

The interactive map is served from `public/map/` and uses `sally_bh.glb`. Rendering depends on the visitor's browser and graphics hardware.
