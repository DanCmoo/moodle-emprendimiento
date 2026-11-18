$ErrorActionPreference = 'Stop'
$repoRoot = (Resolve-Path (Join-Path $PSScriptRoot '..')).Path
$runtime = Join-Path $repoRoot '.moodle-runtime'
$dockerRepo = Join-Path $runtime 'moodle-docker'
$moodleRepo = Join-Path $runtime 'moodle'

if (-not (Get-Command git -ErrorAction SilentlyContinue)) { throw 'Instala Git antes de continuar.' }
if (-not (Get-Command docker -ErrorAction SilentlyContinue)) { throw 'Instala Docker Desktop y verifica que esté iniciado.' }
if (-not (Get-Command bash -ErrorAction SilentlyContinue)) { throw 'Instala Git for Windows con Git Bash y agrega bash al PATH, o ejecuta docker/setup.sh desde Git Bash.' }
docker info *> $null
if ($LASTEXITCODE -ne 0) { throw 'Docker no está iniciado. Abre Docker Desktop y vuelve a ejecutar este script.' }

New-Item -ItemType Directory -Force -Path $runtime | Out-Null
if (-not (Test-Path (Join-Path $dockerRepo '.git'))) {
    git clone --depth 1 https://github.com/moodlehq/moodle-docker.git $dockerRepo
    if ($LASTEXITCODE -ne 0) { throw 'No se pudo clonar moodle-docker.' }
}
if (-not (Test-Path (Join-Path $moodleRepo '.git'))) {
    git clone --depth 1 --branch MOODLE_501_STABLE https://github.com/moodle/moodle.git $moodleRepo
    if ($LASTEXITCODE -ne 0) { throw 'No se pudo clonar Moodle 5.1 estable.' }
}

$config = Join-Path $moodleRepo 'config.php'
if (-not (Test-Path $config)) {
    Copy-Item (Join-Path $dockerRepo 'config.docker-template.php') $config
}

$env:MOODLE_DOCKER_WWWROOT = $moodleRepo
$env:MOODLE_DOCKER_DB = 'mariadb'
$env:MOODLE_DOCKER_DB_VERSION = '11.4'
$env:MOODLE_DOCKER_PHP_VERSION = '8.3'
Push-Location $dockerRepo
try {
    bash ./bin/moodle-docker-compose up -d
    if ($LASTEXITCODE -ne 0) { throw 'No se pudo iniciar el entorno Docker.' }
    bash ./bin/moodle-docker-wait-for-db
    if ($LASTEXITCODE -ne 0) { throw 'La base de datos no quedó lista.' }
} finally {
    Pop-Location
}

Write-Host 'Moodle local está iniciado. Primera vez: sigue el comando de instalación en README.md. URL: http://localhost:8000'
