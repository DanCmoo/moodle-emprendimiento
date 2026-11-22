#!/usr/bin/env bash
set -euo pipefail

repo_root="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
runtime="$repo_root/.moodle-runtime"
docker_repo="$runtime/moodle-docker"
moodle_repo="$runtime/moodle"

command -v git >/dev/null || { echo 'Instala Git antes de continuar.' >&2; exit 1; }
command -v docker >/dev/null || { echo 'Instala Docker antes de continuar.' >&2; exit 1; }
docker info >/dev/null 2>&1 || { echo 'Docker no está iniciado. Inícialo y vuelve a ejecutar este script.' >&2; exit 1; }

mkdir -p "$runtime"
if [[ ! -d "$docker_repo/.git" ]]; then
  git clone --depth 1 https://github.com/moodlehq/moodle-docker.git "$docker_repo"
fi
if [[ ! -d "$moodle_repo/.git" ]]; then
  git clone --depth 1 --branch MOODLE_501_STABLE https://github.com/moodle/moodle.git "$moodle_repo"
fi

if [[ ! -f "$moodle_repo/config.php" ]]; then
  cp "$docker_repo/config.docker-template.php" "$moodle_repo/config.php"
fi

export MOODLE_DOCKER_WWWROOT="$moodle_repo"
export MOODLE_DOCKER_DB=mariadb
export MOODLE_DOCKER_DB_VERSION=11.4
export MOODLE_DOCKER_PHP_VERSION=8.3
cd "$docker_repo"
./bin/moodle-docker-compose up -d
./bin/moodle-docker-wait-for-db

echo 'Moodle local está iniciado. Primera vez: sigue el comando de instalación en README.md. URL: http://localhost:8000'
