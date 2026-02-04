# CLAUDE.md

This file provides guidance to Claude Code when working with this repository.

## Product Overview

**Tattoo Studio Manager** is a white-label SaaS platform for tattoo studios.

Business model: Studios pay a subscription, get a fully branded booking/waiver/management system. They customize the look, we handle the software.

### Core Design Principles
- **White-label first**: Every customer-facing view must support tenant branding
- **Mobile-friendly**: Clients book and sign waivers on their phones
- **Simple for studios**: Non-technical shop owners need to use this easily

## Architecture

### Multi-Tenancy
- Each studio is a tenant with isolated data
- Tenant identification via subdomain or custom domain
- Shared codebase, per-tenant customization stored in DB

### Two Interfaces
1. **Customer-facing** (public, white-labeled)
   - Booking portal
   - Digital waiver signing
   - Optional studio website/landing page

2. **Admin panel** (Filament, staff only)
   - Manage appointments, clients, waivers, inventory
   - Configure studio branding & settings

## Tech Stack

- **PHP**: 8.2
- **Framework**: Laravel 12
- **Admin**: Filament 3
- **Frontend**: Livewire + Blade
- **Database**: SQLite (dev), MySQL/PostgreSQL (prod)

## Development Commands

```bash
php artisan serve                    # Start dev server
php artisan test                     # Run tests
php artisan make:model Name -mf      # Model + migration + factory
php artisan make:filament-resource Name  # Filament CRUD resource
php artisan migrate:fresh --seed     # Reset DB with seeds
```

## Project Structure

```
app/
├── Filament/
│   └── Resources/          # Admin panel resources
├── Models/                 # Eloquent models
├── Http/
│   └── Controllers/        # Customer-facing controllers
└── Providers/

resources/views/
├── filament/               # Admin panel customization
├── livewire/               # Livewire components
└── customer/               # Customer-facing views (white-label)

database/
├── migrations/
├── factories/
└── seeders/
```

## Current Status

### Built
- [x] Filament admin panel setup
- [x] User authentication
- [x] Waiver resource (CRUD in admin)

### In Progress
- [ ] Multi-tenancy (Studio model, tenant isolation)
- [ ] Customer-facing booking portal
- [ ] Customer-facing waiver form
- [ ] White-label theming system

### Planned
- [ ] Appointment/calendar system
- [ ] Inventory management
- [ ] Client profiles
- [ ] Email notifications
- [ ] Payment integration

## Module Specs

### Studios (Tenants)
- name, slug, custom_domain
- logo, primary_color, secondary_color
- business_hours, timezone
- contact info, address
- waiver_text, booking_rules

### Appointments
- studio_id, client_id, artist_id
- date, time, duration
- status (pending, confirmed, completed, cancelled, no-show)
- deposit_paid, notes, tattoo_description

### Clients
- studio_id (scoped to tenant)
- name, email, phone, DOB
- address, emergency_contact
- notes, preferences

### Waivers
- studio_id, client_id, appointment_id (optional)
- All current fields + tenant scoping
- signed_at, signature, ip_address

### Inventory
- studio_id, name, category
- quantity, unit, reorder_threshold
- supplier, cost, notes

### Artists (Staff)
- studio_id, user_id
- display_name, bio, specialties
- hourly_rate, commission_rate
- availability schedule

## Common Issues

### PHP Version
Project requires PHP 8.2. Use direnv (`direnv allow`) to auto-switch.

### Filament 403 Error
Ensure user implements `FilamentUser` contract and `canAccessPanel()` returns true.
