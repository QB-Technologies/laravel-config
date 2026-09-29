@verbatim
# QB Technologies coding rules

Apply these rules to new and edited code. Do not rewrite existing violations while doing unrelated work.

## Git

- Do not commit, push, tag, or skip hooks (`--no-verify`) unless the user explicitly asks.
- Do not commit `.env` or secrets.
- Do not commit commented-out code unless the user explicitly asks to keep it.
- Do not edit installed hook copies. Git keeps them outside the working tree in a worktree, so the path is not always `.git/hooks/`. Change hooks in `qb-technologies/laravel-config` and run `composer hooks:install`. Composer reinstalls them on every install and update.

## PHP

- New methods and variables use camelCase. Do not mass-rename existing snake_case.
- Return early when a branch does nothing further. Do not wrap the rest of the method in a positive `if`.
- Business code lives under `domain/`, using the existing Action classes. Tests are Pest. Put imports at the top of the file.
- The formatter is PHP-CS-Fixer, not Pint. Do not run Pint.

## Eloquent

- Do not call `withoutGlobalScopes()` in application code or in tests. It is allowed only in migrations and inside a scope that must see unscoped rows.
- Do not write `Model::query()->where(...)`. Use `Model::where(...)`. Do not use `Model::query()` in tests. Use `query()` only for a bare builder: Filament `->query(Model::query())`, a builder you constrain conditionally, or `DB::query()` for unions.
- Never concatenate request input into SQL. Use Eloquent or the query builder with bindings. `DB::raw`, `whereRaw`, and `selectRaw` are allowed only with bound parameters.

## HTTP and output

- Validate on the server and reject invalid input. Do not try to clean dangerous input into something safe.
- GET and HEAD must not create, update, or delete state. State-changing web routes keep Laravel's CSRF middleware.
- In Blade, output with `{{ }}`. Do not use `{!! !!}` unless the content is already trusted and there is no escaped alternative.
- Do not log, store, or return raw card numbers, passwords, tokens, or secrets.
- Do not set `APP_DEBUG=true` outside local.

## Static analysis

- Do not silence PHPStan with `phpstan-ignore`, `@phpstan-ignore`, or new baseline entries. Fix the type.
- After PHP changes, run `vendor/bin/phpstan analyse --memory-limit=1G` and `vendor/bin/php-cs-fixer fix --dry-run --diff`.
- Also run `vendor/bin/rector --dry-run` and `vendor/bin/phpcpd --min-lines 10 --min-tokens 70 --suffix .php domain/`.
- Use `./vendor/bin/sail` for Artisan, migrate, and tests.
- Migration filenames must match `YYYY_MM_DD_HHMMSS_*.php`.
- Do not add junk files (`.DS_Store`, `.idea/`, `.vscode/`, swap files, logs).
@endverbatim
