# AGENTS

## Project Snapshot
- Stack: PHP 8.1+, server-rendered web app with planned JSON API backend.
- Entry point: [index.php](index.php).
- Shared web components and auth helpers: [lib/](lib/).
- Backend/API implementation requirements: [docs/plan.md](docs/plan.md).

## First Files To Read
- [docs/plan.md](docs/plan.md): Canonical backend/API scope and acceptance criteria.
- [index.php](index.php): Current landing page composition.
- [lib/login.php](lib/login.php): Google sign-in flow and session handling.
- [lib/composer.json](lib/composer.json): PHP dependencies used in this repo.

## Working Conventions
- Keep changes scoped and minimal; do not refactor unrelated areas.
- Preserve existing include patterns (`include`, `require_once`) and session-based flow unless the task requires API-level auth changes.
- Return JSON + HTTP status codes for API endpoints, as required by [docs/plan.md](docs/plan.md).
- Use PDO prepared statements for all SQL.
- Escape user-facing output in templates (`htmlspecialchars(..., ENT_QUOTES)`).

## Commands
- Install PHP dependencies from [lib/](lib/):
  - `cd lib && composer install`
- Run local PHP server from repo root:
  - `php -S localhost:8000`

## Pitfalls
- `composer.json` is in [lib/](lib/), not repository root.
- [lib/login.php](lib/login.php) currently contains a hardcoded Google client ID; prefer environment/config values for production-ready changes.
- [docs/plan.md](docs/plan.md) references `firebase/php-jwt`, but it is not yet present in [lib/composer.json](lib/composer.json).

## Documentation Policy
- Link to existing documentation instead of copying it into instruction files.
- Treat [docs/plan.md](docs/plan.md) as the source of truth for backend scope.