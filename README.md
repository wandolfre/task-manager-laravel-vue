# Darient Task Manager

A full-stack Task Management application built with **Laravel 12** (REST API) and **Vue.js 3** (SPA frontend) as a technical assessment submission.

## Architecture Overview

```
┌──────────────────────────────────────────────────────┐
│                    Vue 3 SPA                         │
│  ┌──────────┐  ┌──────────┐  ┌───────────────────┐  │
│  │Vue Router│  │  Pinia   │  │ Axios (api.js)    │  │
│  │(7 routes)│  │(2 stores)│  │ Bearer Token Auth │  │
│  └──────────┘  └──────────┘  └─────────┬─────────┘  │
└────────────────────────────────────────┬┘            │
                                         │ JSON/REST   │
┌────────────────────────────────────────▼─────────────┐
│                 Laravel 12 API                        │
│  ┌──────────────┐  ┌──────────────┐  ┌────────────┐ │
│  │AuthController│  │TaskController│  │FormRequests│ │
│  │(Sanctum)     │  │(CRUD + Auth) │  │(Validation)│ │
│  └──────────────┘  └──────────────┘  └────────────┘ │
│  ┌──────────────┐  ┌──────────────┐                  │
│  │  User Model  │  │  Task Model  │                  │
│  │  (hasMany)   │  │  (belongsTo) │                  │
│  └──────────────┘  └──────────────┘                  │
└──────────────────────┬───────────────────────────────┘
                       │
              ┌────────▼────────┐
              │   MySQL 8.4     │
              │   (via Sail)    │
              └─────────────────┘
```

## Technology Versions

| Technology       | Version  |
|------------------|----------|
| PHP              | 8.4+     |
| Laravel          | 12.x     |
| Laravel Sanctum  | 4.x      |
| Vue.js           | 3.x      |
| Vue Router       | 4.x      |
| Pinia            | 2.x      |
| Tailwind CSS     | 4.x      |
| Vite             | 7.x      |
| MySQL            | 8.4      |
| PHPUnit          | 11.x     |
| Docker (Sail)    | Latest   |
| Node.js          | 20.x     |

## Local Setup (Laravel Sail)

### Prerequisites

- Docker Desktop installed and running
- Git

### Steps

```bash
# 1. Clone the repository
git clone https://github.com/wandolfre/task-manager-laravel-vue.git
cd darient-test

# 2. Copy environment file
cp .env.example .env

# 3. Install PHP dependencies (using Docker)
docker run --rm \
    -u "$(id -u):$(id -g)" \
    -v "$(pwd):/var/www/html" \
    -w /var/www/html \
    laravelsail/php84-composer:latest \
    composer install --ignore-platform-reqs

# 4. Start Sail containers
./vendor/bin/sail up -d

# 5. Generate application key
./vendor/bin/sail artisan key:generate

# 6. Run migrations and seed the database
./vendor/bin/sail artisan migrate --seed

# 7. Install frontend dependencies and build
./vendor/bin/sail npm install
./vendor/bin/sail npm run build

# 8. Visit the application
# http://localhost
```

### Seeded Data

The seeder creates **5 users** with **10 tasks each**. All seeded users have the password: `password`

## Running Tests

```bash
# Run the full test suite
./vendor/bin/sail artisan test

# Run specific test files
./vendor/bin/sail artisan test --filter=AuthTest
./vendor/bin/sail artisan test --filter=TaskCrudTest
./vendor/bin/sail artisan test --filter=TaskFilterTest
```

### Test Coverage

| Test File            | Tests | Category        | Description                                           |
|----------------------|-------|-----------------|-------------------------------------------------------|
| TaskModelTest        | 8     | Unit            | Relationships, casts, fillable, defaults, cascade      |
| UserModelTest        | 4     | Unit            | Password hashing, hidden fields, fillable, API tokens  |
| AuthTest             | 6     | Feature         | Register, login, logout (success + failure cases)      |
| TaskCrudTest         | 8     | Feature         | CRUD operations + 403 ownership enforcement            |
| TaskFilterTest       | 4     | Feature         | Filter by completed, title search, sorting             |
| TaskValidationTest   | 12    | Feature         | 401 unauthenticated access, 422 validation rules       |
| **Total**            | **42**| **117 assertions** |                                                    |

## API Endpoint Reference

### Authentication (Public)

| Method | Route           | Auth | Description                        |
|--------|-----------------|------|------------------------------------|
| POST   | `/api/register` | No   | Create a new user account          |
| POST   | `/api/login`    | No   | Authenticate and receive API token |

### Authentication (Protected)

| Method | Route           | Auth | Description                        |
|--------|-----------------|------|------------------------------------|
| POST   | `/api/logout`   | Yes  | Revoke the current API token       |
| GET    | `/api/user`     | Yes  | Get the authenticated user profile |

### Tasks (Protected — `auth:sanctum`)

| Method | Route             | Auth | Description                              |
|--------|-------------------|------|------------------------------------------|
| GET    | `/api/tasks`      | Yes  | List user's tasks (paginated, filterable) |
| POST   | `/api/tasks`      | Yes  | Create a new task                         |
| GET    | `/api/tasks/{id}` | Yes  | Show a specific task (owner only)         |
| PUT    | `/api/tasks/{id}` | Yes  | Update a task (owner only)                |
| DELETE | `/api/tasks/{id}` | Yes  | Delete a task (owner only)                |

### Task List Query Parameters

| Parameter    | Type    | Description                          | Example              |
|-------------|---------|--------------------------------------|----------------------|
| `completed` | boolean | Filter by completion status          | `?completed=true`    |
| `title`     | string  | Search by title (LIKE match)         | `?title=deploy`      |
| `sort_by`   | string  | Sort column: `due_date`, `created_at`| `?sort_by=due_date`  |
| `sort_order`| string  | Sort direction: `asc`, `desc`        | `?sort_order=asc`    |
| `page`      | integer | Pagination page number               | `?page=2`            |

### Error Responses

| Status | Description |
|--------|-------------|
| `401` | Missing or invalid authentication token |
| `403` | Attempting to access another user's task |
| `404` | Task not found |
| `422` | Validation failed — response includes `errors` object with field-specific messages |

## Project Structure

```
app/
├── Http/
│   ├── Controllers/Api/
│   │   ├── AuthController.php      # Register, login, logout
│   │   └── TaskController.php      # CRUD with ownership checks
│   └── Requests/
│       ├── StoreTaskRequest.php     # Create task validation
│       └── UpdateTaskRequest.php    # Update task validation
├── Models/
│   ├── User.php                    # HasApiTokens, hasMany(Task)
│   └── Task.php                    # belongsTo(User), casts
database/
├── factories/
│   ├── UserFactory.php             # Realistic user data
│   └── TaskFactory.php             # Realistic task data
├── migrations/                      # Users (with last_name) + Tasks
└── seeders/
    └── DatabaseSeeder.php          # 5 users × 10 tasks
resources/js/
├── api.js                          # Centralized Axios + Bearer token
├── App.vue                         # Root component with nav
├── router/index.js                 # 7 named routes + auth guards
├── stores/
│   ├── auth.js                     # Token + user state (Pinia)
│   └── tasks.js                    # Tasks + filters + pagination
└── views/
    ├── Login.vue                   # Sign in form
    ├── Register.vue                # Registration form
    ├── TaskIndex.vue               # Task board + filters + modal
    ├── TaskShow.vue                # Task detail view
    ├── TaskCreate.vue              # Create task form
    └── TaskEdit.vue                # Edit task form
tests/
├── Unit/
│   ├── TaskModelTest.php              # Model relationships, casts, defaults
│   └── UserModelTest.php              # Password hashing, hidden fields, tokens
└── Feature/
    ├── AuthTest.php                    # Auth endpoint tests
    ├── TaskCrudTest.php                # CRUD + 403 ownership tests
    ├── TaskFilterTest.php              # Filter + sort tests
    └── TaskValidationTest.php          # 401 + 422 validation tests
```

## Live Demo

**Production URL:** [https://task-manager-laravel-vue.onrender.com](https://task-manager-laravel-vue.onrender.com)

Deployed on Render using Docker containerization. The application supports MySQL, PostgreSQL, and SQLite through Laravel's database abstraction layer. For local development, Docker Compose (Laravel Sail) provides a MySQL 8.4 environment matching the production specification.

> **Note:** Free-tier instances spin down after periods of inactivity. The first request may take ~30 seconds while the container starts up.

## Notes for Reviewers

- **Ownership enforcement**: All task endpoints check `user_id` against the authenticated user, returning `abort(403, 'Unauthorized')` for violations.
- **Token-based auth**: Uses Sanctum personal access tokens (not cookie/session). Tokens are stored in `localStorage` and sent via `Authorization: Bearer` header.
- **Partial updates**: `PUT /api/tasks/{id}` supports partial updates — only send the fields you want to change.
- **SQL injection prevention**: Sort columns are whitelisted in the controller to prevent injection via column names.
- **Code documentation**: Every controller method has PHPDoc blocks, models have property annotations, FormRequests have rule comments, and Vue components have template comments.
