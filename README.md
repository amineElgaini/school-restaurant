# School Restaurant API

A Laravel-based API for managing school restaurant operations including meal planning, reservations, and user management with role-based access control.

## Features

- **Authentication**: JWT-style API tokens via Laravel Sanctum
- **Role-Based Access Control (RBAC)**: Custom permission system with roles and granular permissions
- **Meal Management**: Create and organize meals by type (breakfast, lunch, dinner)
- **Menu Planning**: Schedule meals for specific dates
- **Reservation System**: Students can reserve meals with conflict detection
- **Complaints**: Students can submit complaints for admin review
- **User Management**: Admins can create, update, and manage users with role/permission assignment

## Tech Stack

- **Framework**: Laravel 12
- **PHP**: 8.2+
- **Database**: SQLite (testing), MySQL/PostgreSQL (production)
- **Authentication**: Laravel Sanctum
- **API Documentation**: OpenAPI/Swagger (L5-Swagger)
- **Frontend Build**: Vite + Tailwind CSS 4

## Requirements

- PHP 8.2 or higher
- Composer
- Node.js & npm
- SQLite (for testing) or MySQL/PostgreSQL (for development)

## Installation

### Quick Setup

```bash
# Clone and install dependencies
composer run setup
```

### Manual Setup

```bash
# Install PHP dependencies
composer install

# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate

# Run database migrations
php artisan migrate

# Install and build frontend assets
npm install
npm run build
```

## Development

Start the development server with hot reloading:

```bash
composer run dev
```

This concurrently runs:
- Laravel development server
- Queue worker
- Log tail (Pail)
- Vite dev server

## Testing

```bash
# Run all tests
composer run test

# Run specific test
php artisan test --filter=TestName

# Run unit tests only
php artisan test --testsuite=Unit

# Run feature tests only
php artisan test --testsuite=Feature
```

## Code Style

```bash
# Auto-fix code style issues
./vendor/bin/pint
```

## API Endpoints

### Authentication

| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/api/login` | Login and receive API token |
| POST | `/api/logout` | Logout (invalidate current token) |
| GET | `/api/me` | Get current user details |

### Admin Routes (requires `role:admin`)

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/admin/users` | List all users |
| POST | `/api/admin/users` | Create new user |
| GET | `/api/admin/users/{user}` | Get user details with permissions |
| PUT | `/api/admin/users/{user}` | Update user |
| DELETE | `/api/admin/users/{user}` | Delete/Archive user |
| GET | `/api/admin/roles` | List all roles |
| GET | `/api/admin/roles/{role}/assignable-permissions` | Get assignable permissions for a role |
| GET | `/api/admin/complaints` | List all complaints |

### Student Routes (requires `role:student`)

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/student/reservations` | Get student's reservations for a date |
| POST | `/api/student/reservations` | Reserve a meal |
| DELETE | `/api/student/reservations/{reservation}` | Cancel a reservation |
| POST | `/api/student/complaints` | Submit a complaint |

### Meal Routes

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/meal-types` | List all meal types |
| GET | `/api/meals` | List all meals |
| POST | `/api/meals` | Create a meal |
| GET | `/api/meals/{meal}` | Get meal details |
| PUT | `/api/meals/{meal}` | Update a meal |
| DELETE | `/api/meals/{meal}` | Delete a meal |
| GET | `/api/menu-meals` | List menu meals |
| POST | `/api/menu-meals` | Add meal to menu |
| DELETE | `/api/menu-meals/{menuMeal}` | Remove meal from menu |
| GET | `/api/reservations/stats` | Get reservation statistics |

### Reservation Routes (requires `permission:view_reservations`)

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/reservations` | List users with reservations for a date |
| GET | `/api/reservations/{user}` | Get specific user's reservations |

## Database Schema

### Core Models

- **User** - Students, staff, and admins with soft deletes
- **Role** - User roles (admin, student, staff)
- **Permission** - Granular permissions assignable to roles or users
- **MealType** - Meal categories (breakfast, lunch, dinner)
- **Meal** - Individual meals belonging to a MealType
- **MenuMeal** - Meals scheduled for specific dates
- **Reservation** - User reservations for menu meals
- **Complaint** - Student complaints with status tracking

### Key Relationships

- User `belongsTo` Role
- User `belongsToMany` Permission (direct permissions)
- Role `belongsToMany` Permission
- Role `hasMany` User
- Meal `belongsTo` MealType
- Meal `hasMany` MenuMeal
- MenuMeal `belongsTo` Meal
- MenuMeal `hasMany` Reservation
- Reservation `belongsTo` User
- Reservation `belongsTo` MenuMeal
- Complaint `belongsTo` User

## Authorization

The application uses a custom RBAC system:

1. **Roles**: Each user has one role (admin, student, staff)
2. **Permissions**: Can be assigned to roles (inherited by all users with that role) or directly to users
3. **Assignable Permissions**: Each role defines which permissions it can grant to its users

### Middleware

- `role:{slug}` - Requires user to have the specified role
- `permission:{slug}` - Requires user to have the specified permission (via role or direct assignment)

## Project Structure

```
├── app/
│   ├── Http/
│   │   ├── Controllers/Api/   # API controllers
│   │   └── Middleware/        # Custom middleware (CheckRole, CheckPermission)
│   └── Models/                # Eloquent models
├── database/
│   ├── factories/             # Model factories for testing
│   ├── migrations/            # Database migrations
│   └── seeders/               # Database seeders
├── routes/
│   ├── api.php                # API routes
│   └── web.php                # Web routes
├── tests/
│   ├── Feature/               # Feature tests
│   └── Unit/                  # Unit tests
└── schemas/                   # UML diagrams
```

## License

MIT License - see [Laravel License](https://opensource.org/licenses/MIT)
