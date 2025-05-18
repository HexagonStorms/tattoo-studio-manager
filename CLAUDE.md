# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

Tattoo Studio Manager is a Laravel 12 application with Filament admin panel that handles:

1. **Appointment Scheduling**: Booking and managing tattoo appointments
2. **Waiver Forms**: Digital releases and consent forms
3. **Inventory Management**: Tracking supplies (ink, needles, etc)
4. **Email Notifications**: Automated reminders and communications

## Development Commands

### Local Development (macOS)

- Using PHP 8.2 in this project
- `php artisan serve` - Start the development server
- `php artisan make:filament-user` - Create an admin user account
- `php artisan make:model ModelName -mf` - Create model with migration and factory

### Filament Admin Panel

- `/admin` - Access the Filament admin panel
- `php artisan make:filament-resource ModelName` - Create a Filament resource

### Testing

- `php artisan test` - Run PHPUnit tests

## Project Structure

The project follows standard Laravel directory structure with Filament components added.

## Common Issues

### PHP Version Management

- This project requires PHP 8.2
- We use direnv to automatically set the correct PHP version when entering this directory
- Run `direnv allow` if you see a message about direnv not being allowed

### Filament Admin Access

If you encounter a 403 Forbidden error when accessing the admin panel, ensure:
1. You've created a user with `php artisan make:filament-user`
2. The User model implements the FilamentUser contract