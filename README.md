# Moodle de la clase

Repositorio del equipo para mantener una configuración común de Moodle, materiales del curso y su copia de seguridad exportada (`.mbz`). Cada integrante ejecuta una instancia local; las bases de datos no se sincronizan entre computadores. Para trabajar todos sobre la misma edición a la vez, haría falta una instancia compartida.

## Moodle local con Docker

Requisitos: Git y Docker Desktop (Windows/macOS) o Docker Engine + Compose (Linux). La configuración usa Moodle `MOODLE_503_STABLE`, PHP 8.3 y MariaDB 11.4, siguiendo el entorno de desarrollo de Moodle HQ. Confirmen con la universidad que su Moodle destino es compatible antes de entregar el curso.

Desde la raíz del repositorio, ejecuta el script de tu sistema:

```powershell
# Windows PowerShell
.\docker\setup.ps1
```

```bash
# macOS / Linux / Git Bash
bash docker/setup.sh
```

La primera ejecución descarga el código de Moodle y el entorno Docker oficial en `.moodle-runtime/` (no se suben a este repo), prepara la configuración local y levanta Moodle. En **la primera instalación** de cada integrante, ejecuta el comando mediante el wrapper incluido en Moodle HQ:

```powershell
Set-Location .moodle-runtime/moodle-docker
$env:MOODLE_DOCKER_WWWROOT = (Resolve-Path ..\moodle).Path
$env:MOODLE_DOCKER_DB = 'mariadb'
$env:MOODLE_DOCKER_DB_VERSION = '11.4'
$env:MOODLE_DOCKER_PHP_VERSION = '8.3'
bash ./bin/moodle-docker-compose exec webserver php admin/cli/install_database.php --agree-license --fullname="Curso de la clase" --shortname="curso-clase" --summary="Entorno local del equipo" --adminpass="TestLocal-2026!" --adminemail="admin@example.com"
```

En macOS/Linux/Git Bash, usa `cd .moodle-runtime/moodle-docker`, exporta las mismas cuatro variables (`MOODLE_DOCKER_WWWROOT` debe apuntar a la ruta absoluta `../moodle`) y ejecuta el mismo comando `./bin/moodle-docker-compose exec ...`.

Abre <http://localhost:8000> e inicia sesión como `admin` con la contraseña local indicada arriba. Es una clave de prueba; no la reutilices en un sitio público. Para arrancar de nuevo después, desde la carpeta `.moodle-runtime/moodle-docker` ejecuta `bash ./bin/moodle-docker-compose up -d`; para detener sin borrar datos, `bash ./bin/moodle-docker-compose stop`. Evita `down -v`, porque borra los volúmenes de datos locales.

## Copia del curso

Exporta desde Moodle: **Administración del curso → Copia de seguridad**. Publica la copia revisada como `backups/curso-latest.mbz`. El equipo puede descargar ese archivo y restaurarlo en su instancia. El `.mbz` es una instantánea; los cambios posteriores en cada Moodle local no se sincronizan automáticamente.

La skill del proyecto en `.agents/skills/publicar-respaldo-moodle/` ayuda a actualizar la copia en GitHub. Mantengan el archivo razonable en tamaño; si empieza a pesar mucho, usen Git LFS o Releases y dejen aquí el enlace e instrucciones. No incluyan datos personales o reales de estudiantes, contraseñas, `config.php`, bases de datos ni `moodledata`.

## Publicar cambios

Cada integrante trabaja en su rama, actualiza desde `main` y abre un Pull Request. Integren y validen el `.mbz` antes de reemplazar la copia compartida.
