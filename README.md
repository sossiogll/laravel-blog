# gllHub

gllHub is a content hub: it keeps what has no other home, brings in what lives elsewhere (GitHub, Medium, ...),
curates it and serves it. The core is a Laravel 12 application with a Filament admin panel; every kind of content
and every integration comes from a package, and the core knows none of them by name. The architecture and the rules
for writing code are in [`CLAUDE.md`](CLAUDE.md). The packages live in
[`sossiogll/gllhub-packages`](https://github.com/sossiogll/gllhub-packages).

## Run it with Docker

Clone this repository and `gllhub-packages` side by side (the stack mounts the sibling folder):

```sh
git clone https://github.com/sossiogll/laravel-blog.git
git clone https://github.com/sossiogll/gllhub-packages.git
cd laravel-blog
git checkout next
docker compose up
```

The first start copies `.env.example` to `.env`, installs the Composer dependencies, generates the application key
and migrates the database; it takes a few minutes. Then open <http://localhost:8000/admin> and create an admin user:

```sh
docker compose exec app php artisan make:filament-user
```

`make:filament-user` is Filament's own command (it also takes `--name`, `--email` and `--password` to skip the
prompts), so the core has no command of its own for it.

The admin draws everything from the installed packages. With none (see "Add packages") the Content and Sources menus
are empty and System > Packages says so. Syncs run on the `queue` service (Horizon); "Sync now" only queues them.

| Service | URL |
| --- | --- |
| App | <http://localhost:8000> |
| Vite | <http://localhost:5173> |
| Mailpit (outgoing mail) | <http://localhost:8025> |

On Linux, build with your own ids so the files the containers create belong to you:
`HOST_UID=$(id -u) HOST_GID=$(id -g) docker compose up --build`.

## Add packages

The core's `composer.json` names no package. An instance lists its own in `composer.local.json`, which is
git-ignored and merged by `wikimedia/composer-merge-plugin`:

```sh
cp composer.local.json.example composer.local.json
docker compose exec app composer update gllhub/portfolio gllhub/medium
```

The example points at `/gllhub-packages` inside the container, the sibling repository. `composer update` also
rewrites `composer.lock`; do not commit that change.

## Tests

```sh
docker compose exec app php artisan test
docker compose exec app sh -c 'cd contracts && composer install && vendor/bin/phpunit'
```

CI runs both, and starts the Docker stack, on every push to `next` and every pull request.
