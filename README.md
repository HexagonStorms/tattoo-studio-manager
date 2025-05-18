# Tattoo Studio Manager

A comprehensive backend solution for tattoo studios and artists to manage their entire business operations in one place.

## Overview

Tattoo Studio Manager is an all-in-one business management system designed specifically for tattoo studios. It streamlines daily operations, client management, and business processes while providing a polished and professional experience for both artists and clients.

## Key Features

### Appointment Management
- Intuitive booking calendar with artist availability
- Client scheduling with automatic confirmations
- Appointment history and status tracking

### Digital Waiver System
- Customizable waiver forms with legal compliance
- Digital signature collection
- Automatic storage and retrieval of client consent documents

### Inventory Management
- Track supplies (inks, needles, gloves, etc.)
- Low stock alerts and automatic reordering options
- Usage tracking tied to appointments

### Client Management
- Comprehensive client profiles
- Tattoo history and preferences
- Automated reminder notifications

### Financial Tools
- Payment processing and invoicing
- Artist commission tracking
- Financial reporting and analytics

### Portfolio Management
- Artist galleries and portfolios
- Before/after documentation
- Social media integration

## Technical Details

Built using:
- Laravel 12 framework
- Filament admin panel
- PHP 8.2
- SQLite database (configurable for production)

## Installation

1. Clone this repository
2. Run `composer install`
3. Configure your environment variables in `.env`
4. Run migrations: `php artisan migrate`
5. Create an admin user: `php artisan make:filament-user`
6. Start development server: `php artisan serve`
7. Access admin panel at `/admin`

## Development

### Requirements
- PHP 8.2 or higher
- Composer
- Node.js and NPM (for frontend assets)

### Useful Commands
- `php artisan serve` - Start the development server
- `php artisan make:filament-user` - Create an admin user account
- `php artisan make:model ModelName -mf` - Create model with migration and factory
- `php artisan test` - Run PHPUnit tests

## License

This project is proprietary software. All rights reserved.

© 2025 Hexagon Storms