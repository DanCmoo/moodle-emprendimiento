#!/usr/bin/env bash
# Aplica las entregas al curso restaurado en el Moodle local (Docker).
# Uso, desde la raíz del repositorio:
#   bash herramientas/montaje/montar.sh                 # todos los grupos, en orden
#   bash herramientas/montaje/montar.sh g07_foros       # solo un script
# Opcional: CURSO=ID para indicar el id del curso restaurado.
set -euo pipefail
# Git Bash en Windows convierte /var/www/... en rutas de Windows: se desactiva solo para docker.
dk() { MSYS_NO_PATHCONV=1 docker "$@"; }
ruta_docker() { if command -v cygpath >/dev/null; then cygpath -m "$1"; else echo "$1"; fi; }

raiz="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
destino="$raiz/.moodle-runtime/montaje_emprendimiento"   # se arma aquí y se copia al contenedor
contenedor="moodle-docker-webserver-1"

# Orden: la identidad visual (g02) primero; los textos (g08) después de g04 y g06.
orden=(g02_identidad_visual g07_foros g06_syllabus_bibliografia g04_presentacion_curso g09_navegacion_glosario
       g05_videos g08_textos_enlaces g11_casos_exito g12_financiacion_legal)
if [[ $# -gt 0 ]]; then orden=("$@"); fi

python -I "$raiz/herramientas/montaje/generar_html.py" >/dev/null

# Copia scripts, HTML generado y los adjuntos que usan los scripts a la carpeta que ve el contenedor.
rm -rf "$destino"
mkdir -p "$destino/entregas"
cp "$raiz"/herramientas/montaje/*.php "$destino/"
cp -r "$raiz/herramientas/montaje/html" "$destino/html"
[[ -f "$raiz/herramientas/montaje/videos_youtube.json" ]] && cp "$raiz/herramientas/montaje/videos_youtube.json" "$destino/"
for g in Grupo_04_Tarea_03 Grupo_06_Tarea_05 Grupo_12_Tarea_10; do
  cp -r "$raiz/$g" "$destino/entregas/$g"
done

# El código de Moodle vive en un volumen de Docker (ver .moodle-runtime/moodle-docker/local.yml), así que se copia adentro.
dk exec "$contenedor" rm -rf /var/www/html/montaje_emprendimiento
dk cp "$(ruta_docker "$destino")" "$contenedor:/var/www/html/montaje_emprendimiento"
dk exec "$contenedor" chown -R www-data:www-data /var/www/html/montaje_emprendimiento

# REINICIAR=1: borra el curso local y restaura de nuevo la copia del curso real (Copia moodle/*.mbz).
if [[ "${REINICIAR:-0}" == "1" ]]; then
  mbz="$(ls "$raiz"/Copia\ moodle/*.mbz | head -1)"
  dk cp "$(ruta_docker "$mbz")" "$contenedor:/tmp/curso_real.mbz"
  dk exec "$contenedor" chown www-data /tmp/curso_real.mbz
  dk exec -u www-data "$contenedor" php /var/www/html/montaje_emprendimiento/reiniciar.php
  CURSO="$(dk exec -u www-data "$contenedor" php /var/www/html/admin/cli/restore_backup.php \
    --file=/tmp/curso_real.mbz --categoryid=1 | sed -n 's/.*Restored course ID: \([0-9]*\).*/\1/p')"
  echo "Curso restaurado con id $CURSO"
fi

extra=()
[[ -n "${CURSO:-}" ]] && extra=(--curso="$CURSO")
for s in "${orden[@]}"; do
  echo "== $s"
  dk exec -u www-data "$contenedor" php "/var/www/html/montaje_emprendimiento/$s.php" "${extra[@]}"
done
