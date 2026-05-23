# AGENTS

## Project Snapshot
- Stack: PHP 8.1+, server-rendered web app with JSON API backend.
- Entry point: [index.php](index.php).
- Shared web components and auth helpers: [lib/](lib/).
- Backend/API implementation details: [docs/API.md](docs/API.md).
- Backend/API implementation plan and constraints: [docs/plan.md](docs/plan.md).

## First Files To Read
- [docs/API.md](docs/API.md): Current API contract and Android integration details.
- [docs/plan.md](docs/plan.md): Backend scope and implementation checklist.
- [index.php](index.php): Current landing page composition.
- [dashboard.php](dashboard.php): DB-backed dashboard page and query patterns.
- [api/](api/): Auth, refresh, record entry, and health endpoints.
- [lib/composer.json](lib/composer.json): PHP dependencies used in this repo.

## Working Conventions
- Keep changes scoped and minimal; do not refactor unrelated areas.
- Preserve existing include patterns (`include`, `require_once`) and session-based flow unless the task requires API-level auth changes.
- Return JSON + HTTP status codes for API endpoints, as specified in [docs/API.md](docs/API.md).
- Use PDO prepared statements for all SQL.
- Escape user-facing output in templates (`htmlspecialchars(..., ENT_QUOTES)`).

## Commands
- Install PHP dependencies from [lib/](lib/) target:
  - `cd /var/www/lib && composer install`
- Run local PHP server from repo root:
  - `php -S localhost:8000`
- Apply schema and sample data:
  - `./scripts/apply_schema.sh`
  - `./scripts/seed_sample_data.sh`

## Pitfalls
- [lib/](lib/) is a symlink to `/var/www/lib`; repository-level changes to `lib/*` affect shared files outside repo root.
- `composer.json` is not in repository root; dependencies are managed via [lib/composer.json](lib/composer.json).
- [lib/login.php](lib/login.php) currently contains a hardcoded Google client ID; prefer environment/config values for production-ready changes.
- Scripts in [scripts/](scripts/) read `.env` for CLI usage; Apache vhost env vars are available to web requests only.
- [scripts/](scripts/) is blocked from web access via [scripts/.htaccess](scripts/.htaccess).

## Documentation Policy
- Link to existing documentation instead of copying it into instruction files.
- Treat [docs/API.md](docs/API.md) as the source of truth for endpoint contracts.
- Treat [docs/plan.md](docs/plan.md) as the source of truth for implementation scope.