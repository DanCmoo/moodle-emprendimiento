#!/usr/bin/env bash
# Diseño del Moodle de PRUEBA inspirado en udistrital.edu.co: tema Moove (el mismo del aula real),
# color vino, Montserrat y Lora. Solo toca el Moodle local de Docker.
# Uso, desde la raíz del repositorio:  bash herramientas/montaje/tema_ud.sh
set -euo pipefail
dk() { MSYS_NO_PATHCONV=1 docker "$@"; }
c="moodle-docker-webserver-1"
cfg() { dk exec -u www-data "$c" php /var/www/html/admin/cli/cfg.php "$@"; }

# 1. Tema Moove 5.1.2 desde el directorio oficial de plugins de moodle.org (se verifica el MD5).
if ! dk exec "$c" test -d /var/www/html/public/theme/moove; then
  tmp="$(mktemp -d)"
  curl -sSL -m 300 -o "$tmp/moove.zip" "https://marketplace.moodle.com/api/plugins/theme_moove/versions/2025093001/download"
  echo "942b0d12856567b13b3ade8f36a86a05  $tmp/moove.zip" | md5sum -c -
  python -I -c "import zipfile,sys; zipfile.ZipFile(sys.argv[1]).extractall(sys.argv[2])" "$tmp/moove.zip" "$tmp"
  ruta="$tmp/moove"; command -v cygpath >/dev/null && ruta="$(cygpath -m "$ruta")"
  dk cp "$ruta" "$c:/var/www/html/public/theme/moove"
  dk exec "$c" chown -R www-data:www-data /var/www/html/public/theme/moove
  dk exec -u www-data "$c" php /var/www/html/admin/cli/upgrade.php --non-interactive >/dev/null
fi

# 2. Ajustes del tema (son los mismos que hay que poner a mano en el aula real; ver README).
cfg --name=theme --set=moove
cfg --component=theme_moove --name=brandcolor --set="#8C1919"
cfg --component=theme_moove --name=fontsite --set="Montserrat"
cfg --name=additionalhtmlhead --set='<link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin><link href="https://fonts.googleapis.com/css2?family=Lora:ital,wght@0,400;0,700;1,400&display=swap" rel="stylesheet">'
cfg --component=theme_moove --name=scss --set="$(cat "$(dirname "${BASH_SOURCE[0]}")/tema_ud.scss")"
dk exec -u www-data "$c" php /var/www/html/admin/cli/purge_caches.php
echo "Tema UD aplicado en el Moodle de prueba."
