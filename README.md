# Knowledge Learning

E-learning / e-commerce platform built with **Symfony 7** and **PHP 8.2+**.

Users can browse training courses (themes → cursus → lessons), purchase content via **Stripe Checkout** (sandbox mode), validate each lesson, and earn a **Knowledge Learning certification** once all lessons of a theme are completed.

> Repository: <https://github.com/EleaDSB/CEF-knowledge-learning>

---

## Prerequisites

| Tool | Minimum version |
|---|---|
| PHP | 8.2 |
| Composer | 2.x |
| MySQL | 8.0 |
| Symfony CLI | latest |
| Node / npm | optional (no front-end build step) |

---

## Installation

### 1. Clone the repository

```bash
git clone https://github.com/EleaDSB/CEF-knowledge-learning.git
cd CEF-knowledge-learning
```

### 2. Install PHP dependencies

```bash
composer install
```

### 3. Configure environment variables

Copy the template and fill in your values:

```bash
cp .env .env.local
```

Edit `.env.local`:

```dotenv
# MySQL — adapt credentials to your local setup
DATABASE_URL="mysql://root:YOUR_PASSWORD@127.0.0.1:3306/knowledge_learning?serverVersion=9.6.0&charset=utf8mb4"

# Stripe sandbox keys (get them at https://dashboard.stripe.com/test/apikeys)
STRIPE_PUBLIC_KEY=pk_test_...
STRIPE_SECRET_KEY=sk_test_...

# Local mail catcher (e.g. Mailpit on port 1025)
MAILER_DSN=smtp://localhost:1025
```

### 4. Create the database and run migrations

```bash
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate --no-interaction
```

### 5. Load fixtures (demo data + test accounts)

```bash
php bin/console doctrine:fixtures:load --no-interaction
```

---

## Launch

```bash
symfony serve
```

The application is available at <http://127.0.0.1:8000>.

---

## Test accounts

| Role | Email | Password |
|---|---|---|
| Administrator | `admin@knowledge-learning.fr` | `Admin1234!` |
| Client | `client@example.com` | `Client1234!` |

The client account is pre-verified and has a sample purchase (Cursus guitare).

---

## Running the test suite

### Configure the test environment

Create `.env.test.local` at the project root:

```dotenv
DATABASE_URL="mysql://root:YOUR_PASSWORD@127.0.0.1:3306/knowledge_learning?serverVersion=9.6.0&charset=utf8mb4"
STRIPE_PUBLIC_KEY=pk_test_fake
STRIPE_SECRET_KEY=sk_test_fake
MAILER_DSN=null://null
```

> Doctrine appends `_test` automatically (see `config/packages/doctrine.yaml`), so the actual test database will be `knowledge_learning_test`.

### Create and migrate the test database

```bash
php bin/console doctrine:database:create --env=test
php bin/console doctrine:migrations:migrate --env=test --no-interaction
```

### Run tests

```bash
php bin/phpunit
```

Expected output: **50 tests, 92 assertions, 0 failures**.

---

## Architecture

```
src/
├── Controller/     # HTTP layer (routes, request handling, responses)
├── Entity/         # Doctrine ORM entities
│   └── Traits/     # Reusable traits (TimestampableTrait)
├── EventSubscriber/# Doctrine lifecycle listeners (audit columns)
├── Form/           # Symfony form types
├── Repository/     # Data access layer (custom DQL/queries)
├── Security/       # Voters (access control per resource)
└── Service/        # Business services (MailerService)
```

The application follows the **MVC pattern** provided by Symfony:
- **Model** — Entities + Repositories (Doctrine ORM)
- **View** — Twig templates (`templates/`)
- **Controller** — Symfony controllers (`src/Controller/`)

---

## Key features

- Account registration with **email activation** (token-based, 24 h expiry)
- Stripe **Checkout sandbox** for cursus and individual lesson purchases
- Role-based access: `ROLE_USER` (client) and `ROLE_ADMIN` (backoffice)
- Lesson validation + **automatic certification** when all lessons of a theme are completed
- Full admin backoffice: users, themes, cursus, lessons, purchases
- Audit columns (`created_at`, `updated_at`, `created_by`, `updated_by`) on all entities
- CSRF protection on all forms
- PHPUnit test suite: functional tests (registration, login, purchase) + repository unit tests
