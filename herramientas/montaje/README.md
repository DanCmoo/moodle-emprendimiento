# Montaje de las entregas en Moodle

Scripts que aplican las entregas de los grupos al curso **restaurado en el Moodle local** (Docker). Cada script corresponde a un grupo, usa la API de Moodle y se puede volver a ejecutar sin duplicar contenido.

## Cómo se usa

1. Levanta el Moodle local (ver `README.md` de la raíz) con el formato **Tiles** instalado en `public/course/format/tiles`.
   - **Importante en Windows:** pon el código de Moodle en un volumen de Docker y no en la carpeta de Windows; si no, la instalación tarda más de una hora. Se hace con `.moodle-runtime/moodle-docker/local.yml` (ver abajo).
2. Deja la copia del curso real (`.mbz`, sin usuarios) en `Copia moodle/`.
3. Desde la raíz del repositorio (Git Bash):

```bash
REINICIAR=1 bash herramientas/montaje/montar.sh  # borra el curso local, restaura la copia limpia y monta todo
bash herramientas/montaje/montar.sh g07_foros    # vuelve a ejecutar solo un grupo
CURSO=4 bash herramientas/montaje/montar.sh      # monta sobre un curso ya restaurado
```

`montar.sh` regenera el HTML (`generar_html.py`), copia scripts y adjuntos al contenedor con `docker cp` y los ejecuta como `www-data`. `REINICIAR=1` solo borra cursos del Moodle **local**.

### Código de Moodle en un volumen de Docker

```bash
docker volume create eidti_moodle_code
cd .moodle-runtime/moodle
tar --exclude=./.git -cf - . | docker run --rm -i -v eidti_moodle_code:/dst moodlehq/moodle-php-apache:8.3 \
  sh -c "tar -xf - -C /dst && chown -R www-data:www-data /dst"
```

y en `.moodle-runtime/moodle-docker/local.yml`:

```yaml
services:
  webserver:
    volumes:
      - eidti_moodle_code:/var/www/html
volumes:
  eidti_moodle_code:
    external: true
```

Con eso la instalación tarda unos 2 minutos y restaurar el curso, unos 5 segundos.

## Errores encontrados al probar y cómo se resolvieron

| Error | Causa | Solución |
|---|---|---|
| Texto basura tipo «35-2.107-.692…» en «Material complementario» | El filtro de emoticonos convertía «8-)» dentro del dibujo SVG del ícono en una carita y rompía el HTML | Los íconos SVG se cambian por íconos Font Awesome (los de la guía del grupo 2) |
| Páginas nuevas vacías | `page_add_instance` solo guarda el contenido si recibe el formulario | `mt_create_module` copia el contenido al crear la página |
| Barra azul a la izquierda de títulos y banners | Estilo del formato Tiles para h2–h5 | Estilo en línea `border-left:0 !important` (`MT_NOBAR`) |
| Banner desbordado y etiquetas anidadas en el índice | Sangría heredada del curso original | Sangría en 0 para todas las actividades |
| Tabla de «Objetivos» desbordada en celular | Ancho fijo de 1035 px | Texto con lista |
| Títulos repetidos («Contenido temático» + «Temas») | Cajas de título viejas junto a los subtítulos nuevos | Las cajas pasan a títulos pequeños y «Contenido temático» queda bajo «Temas» |
| Videos de Perspectiva holista sin título («Play Video») | El filtro multimedia convierte los enlaces de YouTube en reproductores | Enlaces con `class="nomediaplugin"` |

## Scripts

| Script | Grupo · tarea | Monta | Qué hace |
|---|---|---|---|
| `g02_identidad_visual.php` | 2 · Identidad visual | Felipe | Nombres de unidades, recursos y cuestionarios; iconos de mosaico; banner de bienvenida y de cada unidad; encabezados vino y dorado; subtítulos «Inicio de la unidad», «Temas» y «Evaluación y cierre». |
| `g04_presentacion_curso.php` | 4 · Presentación del curso | Felipe | Plan de trabajo con esquema nuevo, cronograma de 16 semanas, perfil docente, presentación de las cuatro unidades (objetivo, temas, actividad y preguntas). |
| `g06_syllabus_bibliografia.php` | 6 · Syllabus y bibliografía | Felipe | Reemplaza el syllabus de Cálculo, crea «Bibliografía del curso» y rehace el material complementario de las cuatro unidades. |
| `g07_foros.php` | 7 · Foros | Felipe | Textos de los foros de presentación, ayuda y novedades, y la actividad inicial (antes Lorem ipsum). |
| `g09_navegacion_glosario.php` | 9 · Navegación | Felipe | Glosario de 15 entidades de apoyo e índice de palabras clave con enlace a cada unidad. |
| `g05_videos.php` | 5 · Videos | Daniel | Guía de los seis videos de Perspectiva holista; depura esa etiqueta; cambia los MP4 por YouTube cuando exista `videos_youtube.json`. |
| `g08_textos_enlaces.php` | 8 · Textos y enlaces | Daniel | Frase por lectura, textos corregidos de «Contenido temático», correcciones del anexo B y limpieza de enlaces. |
| `g11_casos_exito.php` | 11 · Casos de éxito | Daniel | Páginas «Casos de éxito de emprendimiento en TI» y «Conceptos clave de la Unidad 1». |
| `g12_financiacion_legal.php` | 12 · Financiación y legal | Daniel | Página de financiación por etapas y entorno legal, y el caso de financiación en PDF. |

## Paleta y letras

Tomadas de [udistrital.edu.co](https://www.udistrital.edu.co/inicio) por indicación del equipo Moodle; reemplazan el dorado y Raleway/Open Sans de la guía original del grupo 2. Están definidas una sola vez en `lib.php`.

| Uso | Valor |
|---|---|
| Vino institucional (banners, títulos) | `#8C1919` |
| Rojo de acento (línea bajo subtítulos) | `#950D16` |
| Franja oscura (borde inferior de los banners) | `#192129` |
| Títulos y texto | Montserrat (respaldo: Helvetica Neue, Arial) |
| Acento serif (frase del banner) | Lora cursiva (respaldo: Georgia) |

Moodle limpia `<style>` y `<link>` de etiquetas y páginas, así que **las fuentes se cargan desde el tema**. En el aula real (tema Moove): *Administración del sitio → Apariencia → Temas → Moove → Avanzado → SCSS sin procesar*, agregar:

```scss
@import url("https://fonts.googleapis.com/css2?family=Lora:ital,wght@0,400;0,700;1,400&family=Montserrat:wght@300;400;600;700&display=swap");
```

Sin ese paso el contenido se ve igual pero con Helvetica/Arial y Georgia. En el Moodle local ya está puesto en Boost (`theme_boost | scss`), junto con el color de marca `#8C1919`.

## Decisiones tomadas al montar

- **Derechos de autor:** no se enlazan copias completas de libros comerciales alojadas en sitios de terceros (theoffice.pe, camarabaq.org.co, archive.org, elhacker.info, drives personales) ni Sci-Hub. Quedan la referencia y la indicación de consultar el Sistema de Bibliotecas UD.
- **Datos sin confirmar:** el perfil docente no publica los títulos ni el correo que el grupo 4 marcó «[confirmar]».
- **Fechas:** se quitan fechas fijas ya vencidas; las define el docente en el Foro de novedades.
- **Pendientes del docente:** asistente en Gemini (grupo 9, punto 8.3), reorganización del acordeón (8.4), fichas de invitados (3.3) y el enlace correcto de «Capital intelectual y gestión del conocimiento».

## Videos en YouTube

Cuando los cinco MP4 del curso estén en el canal (como «no listado»), crea `herramientas/montaje/videos_youtube.json`:

```json
{
  "Emprendimiento_en_TI.mp4": "ID",
  "Unidad_1__Marcos_en_TI.mp4": "ID",
  "Unidad_2__Emprendimiento_en_TI.mp4": "ID",
  "Validación_de_mercado.mp4": "ID",
  "Ideas_de_Negocios_en_TI.mp4": "ID"
}
```

y vuelve a ejecutar `g05_videos`. Los videos quedan incrustados y los MP4 se borran del curso (unos 54 MB menos).
