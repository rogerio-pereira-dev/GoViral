# GoViral

**AI-powered TikTok profile analysis for beginner and small creators.**

GoViral is a micro SaaS product that analyzes user-provided TikTok profile information and delivers actionable recommendations plus a 30-day action plan. It is positioned as an affordable, impulse-buy entry product—fast AI-driven insight with no learning curve.

## Value proposition

- **Fast AI-driven analysis** — Get a structured growth report in minutes.
- **Clear, practical recommendations** — Profile score, niche, bio optimization, content ideas, viralization tips.
- **30-day action plan** — Step-by-step roadmap for growth and monetization.
- **No learning curve** — Simple form, single payment, report by email.

## Tech stack

| Layer      | Technology |
|-----------|------------|
| Backend   | PHP 8.5, Laravel 12 |
| Frontend  | Inertia.js, Vue 3, Vuetify |
| Database  | PostgreSQL (production/local) / SQLite (testing) |
| Queue     | Redis, Laravel Horizon |
| Payment   | Stripe (Laravel Cashier, Checkout + webhooks) |
| Email     | AWS SES |
| AI        | Laravel AI SDK (Gemini) |
| Captcha   | Cloudflare Turnstile |
| Dev/run   | Laravel Sail (Docker) |
| Tests     | Pest PHP (Browser + Feature + Unit), 90% coverage |
| Lint      | Laravel Pint |

## Requirements

- Docker and Docker Compose (for Sail).
- For first-time setup without Sail: PHP 8.2+ and Composer (see [Starting Containers](#starting-containers)).

## Quick start

All commands below assume you are in the project root. Use Sail for PHP/artisan/npm when containers are running.

### 1. Install dependencies

If `vendor` is missing, install PHP dependencies with Composer in Docker (no local PHP required):

```bash
test -d ./vendor || \
  docker run --rm -u "$(id -u):$(id -g)" \
    -v "$(pwd):/var/www/html" \
    -w /var/www/html \
    laravelsail/php85-composer:latest \
    composer install --ignore-platform-reqs --no-interaction --prefer-dist --optimize-autoloader
```

Then start Sail and install frontend dependencies:

```bash
./vendor/bin/sail up -d
./vendor/bin/sail npm install
```

### 2. Environment

```bash
test -f .env || cp .env.example .env
grep -q "^APP_KEY=" .env || ./vendor/bin/sail artisan key:generate
grep -q "^APP_KEY=$" .env && ./vendor/bin/sail artisan key:generate
```

Configure at least:

- `APP_URL` — Base URL (e.g. `http://localhost`).
- `DB_*` — Database (default SQLite for local).
- `REDIS_*` — Redis (Sail provides `redis` host).
- `STRIPE_KEY`, `STRIPE_SECRET`, `STRIPE_WEBHOOK_SECRET` — Stripe (see `docs/Setup/STRIPE_SETUP.md`).
- `MAIL_*` / `AWS_*` — AWS SES for report emails (see HLD).
- `GEMINI_API_KEY` — For AI report generation.
- `TURNSTILE_SITE_KEY`, `TURNSTILE_SECRET_KEY` — Cloudflare Turnstile (see `docs/Setup/TURNSTILE_SETUP.md`).

### 3. Database and frontend build

```bash
./vendor/bin/sail artisan migrate
./vendor/bin/sail npm run build
```

### 4. Run the app

- **Web:** Sail is already up; open `APP_URL` (e.g. `http://localhost`).
- **Queue worker:** The app uses **Laravel Horizon** for Redis queues. Horizon is started by Supervisor in the Sail container (`docker/8.5/supervisord.conf`). For manual run: `./vendor/bin/sail artisan horizon`.
- **Frontend dev:** Optional: `./vendor/bin/sail npm run dev` for Vite HMR.

**Main routes:**

- `/` — Landing page.
- `/start-growth` — Form (TikTok profile + payment).
- `/thank-you` — Post-payment thank you.
- `/horizon` — Queue dashboard (local; restricted by gate elsewhere).

## Testing

The test suite includes Browser tests. Ensure the frontend is built (or run `npm run dev`) before tests:

```bash
./vendor/bin/sail npm run build
./vendor/bin/sail artisan test --parallel --coverage --min=90
./vendor/bin/sail artisan test --type-coverage --min=90 --parallel
```

## Linting

```bash
./vendor/bin/sail exec laravel.test vendor/bin/pint --parallel
```

## Stopping containers

```bash
./vendor/bin/sail down
```

## Project structure and docs

- **Product & design:** `docs/01 - Product Requirement Document.md`, `docs/02 - High Level Design.md`, `docs/03 - Branding Manual.md`, `docs/04 - Features.md`.
- **Setup guides:** `docs/Setup/` (e.g. Stripe, Turnstile).
- **Architecture decisions:** `docs/ADRs/`.
- **Feature specs (FDRs):** `docs/FDRs/ToDo/` and `docs/FDRs/Done/`.
- **Contributor context:** `.cursor/AGENTS.md`, `.cursor/rules/starting-environment.mdc`.

## License

MIT (see repository or `composer.json`).
