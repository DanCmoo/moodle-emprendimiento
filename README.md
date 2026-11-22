# Moodle de Emprendimiento e Ideas de Negocio en TI

Repositorio del equipo para crear y probar localmente una edición común del curso. Cada persona ejecuta su Moodle con Docker; los cambios hechos dentro de una instancia local no se sincronizan entre los cuatro computadores. Para integrar cambios del curso, se reconstruye y revisa una copia `.mbz`.

## Materiales privados

El repositorio no contiene las entregas originales ni el respaldo base. Cada integrante debe obtener del SharePoint del curso `Moodle-Copia.mbz` y la carpeta `contenido/`, y dejarlos en la raíz del repo con esa estructura. Estas rutas están excluidas de Git porque algunas entregas contienen nombres y datos de estudiantes. El `.mbz` generado también se mantiene local.

## Construir la copia del curso

Se requiere Python 3.10 o posterior; el generador usa solo bibliotecas incluidas con Python. Desde la raíz:

```powershell
python tools/build_course_mbz.py
```

El resultado es `Moodle-Emprendimiento-2026-3.mbz`. El script parte del respaldo Moodle 5.1.1, conserva la estructura de secciones y actividades existente, actualiza nombres y textos, incorpora el syllabus, mapas, lecturas, caso de financiación, paquete SCORM y materiales de cierre, y aplica los colores institucionales al contenido. Si falta un documento de entrada, se detiene indicando la ruta.

## Levantar Moodle con Docker

Requisitos: Git, Docker Desktop en Windows/macOS o Docker Engine + Compose en Linux; en Windows también se necesita Git Bash. El entorno usa Moodle `MOODLE_501_STABLE`, PHP 8.3 y MariaDB 11.4 para coincidir con la versión del respaldo.

Desde la raíz, ejecuta el script de tu sistema:

```powershell
# Windows PowerShell
.\docker\setup.ps1
```

```bash
# macOS / Linux / Git Bash
bash docker/setup.sh
```

La primera ejecución descarga Moodle y el entorno Docker oficial en `.moodle-runtime/`; no se suben al repo. El curso base usa el formato **Tiles**. Antes de restaurarlo, instala el formato Tiles compatible con Moodle 5.1 desde [Moodle Plugins](https://marketplace.moodle.com/plugins/format_tiles) en `moodle/course/format/tiles` y completa la instalación del plugin en Administración del sitio.

En la primera instalación de Moodle, desde PowerShell:

```powershell
Set-Location .moodle-runtime/moodle-docker
$env:MOODLE_DOCKER_WWWROOT = (Resolve-Path ..\moodle).Path
$env:MOODLE_DOCKER_DB = 'mariadb'
$env:MOODLE_DOCKER_DB_VERSION = '11.4'
$env:MOODLE_DOCKER_PHP_VERSION = '8.3'
bash ./bin/moodle-docker-compose exec webserver php admin/cli/install_database.php --agree-license --fullname="Moodle local de desarrollo" --shortname="moodle-dev" --summary="Entorno local del equipo" --adminpass="TestLocal-2026!" --adminemail="admin@example.com"
```

En macOS/Linux/Git Bash, cambia a `.moodle-runtime/moodle-docker`, exporta esas cuatro variables (con `MOODLE_DOCKER_WWWROOT` como ruta absoluta a `.moodle-runtime/moodle`) y ejecuta `./bin/moodle-docker-compose exec webserver php admin/cli/install_database.php ...` con los mismos argumentos.

Abre <http://localhost:8000> e inicia sesión como `admin` con la contraseña local anterior. Para iniciar el entorno más adelante, desde `.moodle-runtime/moodle-docker` ejecuta `bash ./bin/moodle-docker-compose up -d`; para detenerlo sin borrar los datos locales, `bash ./bin/moodle-docker-compose stop`. No uses `down -v`, porque borra los volúmenes locales.

## Restaurar el curso

En Moodle, ve a **Administración del sitio → Cursos → Restaurar curso**, selecciona `Moodle-Emprendimiento-2026-3.mbz` y restaura como curso nuevo. El `.mbz` es una instantánea: cambios posteriores hechos en Moodle deben exportarse y revisarse antes de compartirlos con el equipo.

## Trabajo en equipo

Cada integrante trabaja en su rama, actualiza desde `main` y abre un Pull Request. No agreguen `contenido/`, `Moodle-Copia.mbz`, respaldos que contengan datos personales, `moodledata/`, bases de datos ni `config.php` al repo público. La skill `.agents/skills/publicar-respaldo-moodle/` documenta el flujo de publicación de una copia ya revisada cuando el equipo decida compartir una.

## Alcance de la revisión local

La copia base y los documentos de entrada se pueden inspeccionar y transformar sin un servidor Moodle. La restauración visual y funcional debe revisarse en Moodle 5.1 con el plugin Tiles instalado; no se considera verificada hasta completar esa restauración.
