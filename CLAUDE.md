# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## What this is

A small Laravel 10 app for listing/editing/deleting posts. It's the running example for a tutorial series (Tutorials 13–15): Tutorial 13 covers design docs, and this codebase is where the design gets implemented and later extended. Users can register/login (Laravel Fortify), see all posts (with author name and category), and edit/delete only their own posts (others' posts hide the edit/delete buttons and return 403 if the URL is opened directly).

## Setup and commands

Runs via Laravel Sail (Docker). Docker Desktop/Engine must be running first.

```bash
# install deps (first time, no Sail yet)
docker run --rm -u "$(id -u):$(id -g)" -v "$(pwd):/var/www/html" -w /var/www/html laravelsail/php82-composer:latest composer install

cp .env.example .env
./vendor/bin/sail up -d
./vendor/bin/sail artisan key:generate
./vendor/bin/sail artisan migrate --seed
```

App runs at `http://localhost`. If `migrate` fails with "Connection refused" right after `sail up -d`, MySQL isn't ready yet — wait and retry.

Seeded accounts (from `database/seeders/DatabaseSeeder.php`): `usera@example.com` / `userb@example.com`, password `password` for both.

```bash
./vendor/bin/sail artisan test                          # run all tests
./vendor/bin/sail artisan test --filter=test_name        # run a single test
./vendor/bin/sail artisan test tests/Feature/SomeTest.php # run one file
./vendor/bin/sail down                                    # stop containers
./vendor/bin/npm run dev    # or: npm run dev    (Vite dev server)
./vendor/bin/npm run build  # or: npm run build
./vendor/bin/pint           # code style (Laravel Pint)
```

PHP/MySQL versions are whatever the Sail containers pin — check with `./vendor/bin/sail php -v` and `./vendor/bin/sail mysql --version` rather than assuming.

## Architecture

Standard Laravel 10 MVC, intentionally minimal — this is a teaching app, so features are added incrementally as the tutorial progresses rather than all at once.

- **Models**: `Post` belongs to `User` and `Category`; `User` has many `Post`; `Category` has many `Post`. No soft deletes, no extra scopes — see `app/Models/`.
- **Authorization**: `PostPolicy` (`app/Policies/PostPolicy.php`) gates `update`/`delete` on `Post` to `user_id === auth user id`. It relies on Laravel's naming-convention auto-discovery (`Post` → `PostPolicy`) rather than explicit registration — `AuthServiceProvider::$policies` is left empty on purpose. `PostController` calls `$this->authorize(...)` before mutating.
- **Controller**: `app/Http/Controllers/PostController.php` is the only controller (`index`, `edit`, `update`, `destroy`) — no `store`/`create` yet, posts aren't created through the UI in the current tutorial stage.
- **Routes**: `routes/web.php` — all `/posts*` routes sit behind the `auth` middleware group. `RouteServiceProvider::HOME` is `/posts` (post-login redirect target).
- **Auth**: Laravel Fortify handles registration/login/logout. Custom Fortify views are wired in `app/Providers/FortifyServiceProvider.php` (`auth.login`, `auth.register` Blade views); actions live in `app/Actions/Fortify/`.
- **Views**: plain Blade, no frontend framework — `resources/views/posts/{index,edit}.blade.php`, `resources/views/auth/{login,register}.blade.php`.

## Repo layout gotcha: `docs/` vs `answers/`

These two directories are tutorial artifacts, not app code — don't treat them as part of the Laravel app when reasoning about behavior:

- `docs/` — the user's own design documents for this app, produced while working through Tutorial 13 (use-case diagrams, feature lists, etc., organized in `docs/13-N/` subfolders).
- `answers/` — reference/answer-key material for Tutorial 13's design exercises, covering both this post app (`post-*` files) and a separate café mobile-order app used for chapter exercises (`cafe-*` files) plus a registration test-design exercise (`register-*` files). It has no bearing on `app/`, `routes/`, etc. See `answers/README.md` for the full mapping of subfolder → design phase.

When asked to "catch up" a stage, the expected workflow (per `answers/README.md`) is copying an `answers/13-N/` folder's contents into `docs/13-N/`, not regenerating the design docs from scratch.
