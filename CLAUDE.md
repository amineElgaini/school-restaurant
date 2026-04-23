# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

Laravel 12 API for a school restaurant management system with role-based access control (RBAC) and permission-based authorization.

## Development Commands

```bash
# Setup (install deps, migrate, build)
composer run setup

# Development (server, queue, logs, vite)
composer run dev

# Run tests
composer run test
php artisan test                    # All tests
php artisan test --filter=Name      # Single test

# Linting
./vendor/bin/pint                   # Auto-fix PHP code style
```

## Architecture

### Authentication & Authorization

- **Laravel Sanctum** for API token authentication
- **Custom RBAC system** with roles and permissions:
  - `Role` - Users have one role (hasMany relationship)
  - `Permission` - Can be assigned to roles (many-to-many) or users directly (many-to-many)
  - `assignable_role_permissions` - Defines which permissions a role can grant to its users
  - Middleware: `role:{slug}` and `permission:{slug}` for route protection

### Domain Models

- **User** - Students and admins with soft deletes, belongs to a Role
- **MealType** - Categories (e.g., breakfast, lunch, dinner)
- **Meal** - Individual meals belonging to a MealType
- **MenuMeal** - Meal scheduled for a specific date (served_at)
- **Reservation** - User's reservation for a MenuMeal
- **Complaint** - Student complaints with status tracking

### API Structure

- **Auth**: `/api/login`, `/api/logout`, `/api/me`
- **Admin** (`role:admin`): `/api/admin/users`, `/api/admin/roles`, `/api/admin/complaints`
- **Student** (`role:student`): `/api/student/reservations`, `/api/student/complaints`
- **Meal endpoints**: `/api/meal-types`, `/api/meals`, `/api/menu-meals`, `/api/reservations`

### Key Conventions

- Controllers use `HasMiddleware` interface for middleware registration
- API documentation via OpenAPI/Swagger annotations (L5-Swagger package)
- Soft deletes on User model for archiving
- JSON API responses with consistent structure
