# Tattoo Studio Manager

A white-label SaaS platform for tattoo studios. One subscription, everything they need.

## Product Vision

Tattoo Studio Manager is a turnkey business platform sold to tattoo studios. Each studio gets their own branded experience—custom colors, logos, domain—while we handle the software. Studios focus on art, we handle the tech.

## Core Modules

### Customer-Facing (White-Label)
- **Booking Portal** - Beautiful, mobile-friendly appointment scheduling
- **Digital Waivers** - Clients sign consent forms before arrival
- **Studio Website** - Optional landing page with portfolio, pricing, contact

### Back Office (Filament Admin)
- **Appointment Management** - Calendar view, artist availability, status tracking
- **Client Database** - Profiles, tattoo history, preferences, contact info
- **Waiver Storage** - Searchable archive of signed consent forms
- **Inventory Tracking** - Supplies, low-stock alerts, usage logs
- **Staff Management** - Artists, schedules, permissions

### Future Modules
- Payment processing & invoicing
- Artist commission tracking
- Portfolio/gallery management
- Email/SMS notifications & reminders
- Analytics & reporting

## White-Label Features

Each studio tenant can customize:
- Logo & branding colors
- Custom domain (studio.tattoomanager.com or their own)
- Business info, hours, location
- Waiver text & terms
- Booking rules (deposit, cancellation policy)

## Tech Stack

- **Framework**: Laravel 12
- **Admin Panel**: Filament 3
- **Frontend**: Livewire + Blade (customer-facing)
- **Database**: SQLite (dev) / MySQL or PostgreSQL (prod)
- **PHP**: 8.2+

## Local Development

```bash
composer install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate
php artisan make:filament-user
php artisan serve
```

Admin panel: http://localhost:8000/admin

## Architecture

```
┌─────────────────────────────────────────────────────────┐
│                    Customer-Facing                      │
│  ┌─────────────┐  ┌─────────────┐  ┌─────────────┐     │
│  │   Booking   │  │   Waivers   │  │   Website   │     │
│  │   Portal    │  │    Form     │  │  (optional) │     │
│  └─────────────┘  └─────────────┘  └─────────────┘     │
└─────────────────────────────────────────────────────────┘
                          │
                          ▼
┌─────────────────────────────────────────────────────────┐
│                   Filament Admin                        │
│  ┌───────────┐ ┌───────────┐ ┌───────────┐ ┌─────────┐ │
│  │Appointments│ │  Clients  │ │  Waivers  │ │Inventory│ │
│  └───────────┘ └───────────┘ └───────────┘ └─────────┘ │
└─────────────────────────────────────────────────────────┘
                          │
                          ▼
┌─────────────────────────────────────────────────────────┐
│              Multi-Tenant Database                      │
│         (each studio = isolated tenant)                 │
└─────────────────────────────────────────────────────────┘
```

## License

Proprietary software. All rights reserved.

© 2025 Hexagon Storms
