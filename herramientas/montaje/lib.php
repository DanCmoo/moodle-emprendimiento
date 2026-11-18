<?php
// Funciones comunes para montar las entregas en el curso restaurado.
// Se ejecutan dentro del contenedor de Moodle (ver herramientas/montaje/README.md).

define('CLI_SCRIPT', true);
require('/var/www/html/config.php');
require_once($CFG->libdir . '/clilib.php');
require_once($CFG->dirroot . '/course/lib.php');
require_once($CFG->dirroot . '/course/modlib.php');
require_once($CFG->dirroot . '/mod/label/lib.php');

// Carpeta con las entregas de los grupos, copiada junto a estos scripts.
define('MT_ENTREGAS', __DIR__ . '/entregas');

\core\session\manager::set_user(get_admin());

// Paleta y letras inspiradas en udistrital.edu.co. Las fuentes se cargan desde el tema (ver README).
const MT_VINO = '#8C1919';    // vino institucional
const MT_ROJO = '#950D16';    // rojo de botones y acentos
const MT_OSCURO = '#192129';  // franja oscura (barra superior y pie del portal)
const MT_FUENTE = "'Montserrat','Helvetica Neue',Arial,sans-serif";
const MT_SERIF = "Lora,Georgia,serif";

// El formato Tiles pone una barra azul a la izquierda de los h2-h5 del contenido; este estilo la anula.
const MT_NOBAR = 'border-left:0 !important;padding-left:0 !important;';

/** Íconos Font Awesome de la guía del grupo 2 para cada tipo de bloque. */
const MT_ICONOS = [
    'presentación' => 'fa-circle-play', 'mapa' => 'fa-map', 'contenido temático' => 'fa-book-open',
    'temas a desarrollar' => 'fa-book-open', 'actividades' => 'fa-list-check', 'resultado' => 'fa-bullseye',
    'material' => 'fa-paperclip', 'espacios' => 'fa-comments',
];

/** Título de bloque dentro de una unidad (reemplaza las cajas grises con ícono SVG del curso original). */
function mt_titulo(string $texto, ?string $icono = null): string {
    if ($icono === null) {
        $icono = 'fa-bookmark';
        foreach (MT_ICONOS as $clave => $fa) {
            if (str_starts_with(core_text::strtolower($texto), $clave)) {
                $icono = $fa;
                break;
            }
        }
    }
    return '<h5 class="eidti-titulo" style="color:#8C1919;font-family:\'Montserrat\',Arial,sans-serif;font-weight:700;font-size:18px;' .
        'margin:8px 0;' . MT_NOBAR . '"><i class="fa fa-solid ' . $icono . '" aria-hidden="true"></i>&nbsp; ' . s($texto) . '</h5>';
}

/** Separa el título inicial de una etiqueta (nuevo h5 o caja vieja) del resto del contenido. */
function mt_split_header(string $html): array {
    if (preg_match('~^\s*<h5 class="eidti-titulo".*?</h5>~s', $html, $m)) {
        return [$m[0], substr($html, strlen($m[0]))];
    }
    $fin = strpos($html, '</div>');
    return $fin === false ? ['', $html] : [substr($html, 0, $fin + 6), substr($html, $fin + 6)];
}

/** Curso de trabajo: el restaurado con nombre corto EIDTI (o el id que se pase con --curso=N). */
function mt_course(): stdClass {
    global $DB;
    $opts = getopt('', ['curso::']);
    if (!empty($opts['curso'])) {
        return get_course((int)$opts['curso']);
    }
    $c = $DB->get_records_select('course', 'fullname LIKE ?', ['%EMPRENDIMIENTO E IDEAS DE NEGOCIO%'], 'id DESC', '*', 0, 1);
    if (!$c) {
        cli_error('No encontré el curso restaurado. Pasa --curso=ID.');
    }
    return reset($c);
}

function mt_log(string $msg): void {
    mtrace('  · ' . $msg);
}

/** Módulo de la sección $sectionnum, del tipo $modname, cuyo nombre contiene $match (sin mayúsculas ni tildes). */
function mt_cm(stdClass $course, int $sectionnum, string $modname, string $match, bool $required = true): ?cm_info {
    $modinfo = get_fast_modinfo($course);
    $norm = fn($s) => core_text::strtolower(trim(preg_replace('/\s+/', ' ', strip_tags(
        iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $s)))));
    foreach ($modinfo->get_section_info($sectionnum)->get_sequence_cm_infos() as $cm) {
        if ($cm->modname === $modname && ($match === '' || str_contains($norm($cm->name), $norm($match)))) {
            return $cm;
        }
    }
    if ($required) {
        cli_error("No encontré $modname «{$match}» en la sección $sectionnum.");
    }
    return null;
}

/**
 * Nombre de una etiqueta a partir de su texto (como get_label_name, sin necesitar el contexto).
 * No usa html_to_text porque esa función pone los títulos en MAYÚSCULAS y con guiones bajos.
 */
function mt_label_name(string $html): string {
    $html = preg_replace('~<a class="eidti-ampliar nomediaplugin".*?</a>~s', '', $html);  // «Ampliar imagen» no es parte del nombre
    $name = html_entity_decode(strip_tags(str_replace('<', ' <', $html)), ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $name = preg_replace('/@@PLUGINFILE@@\/[[:^space:]]+/i', '', $name);
    $name = trim(preg_replace('/\s+/u', ' ', $name));
    if (core_text::strlen($name) > LABEL_MAX_NAME_LENGTH) {
        $name = core_text::substr($name, 0, LABEL_MAX_NAME_LENGTH) . '...';
    }
    return $name === '' ? get_string('modulename', 'label') : $name;
}

/**
 * Etiqueta de la sección cuyo contenido incluye alguno de $needles y ninguno de $exclude.
 * Se busca por contenido porque Moodle recalcula el nombre de una etiqueta cada vez que cambia su texto.
 */
function mt_label_with(stdClass $course, int $sectionnum, array $needles, array $exclude = ['eidti-subtitulo']): cm_info {
    foreach (get_fast_modinfo($course)->get_section_info($sectionnum)->get_sequence_cm_infos() as $cm) {
        if ($cm->modname !== 'label') {
            continue;
        }
        $html = mt_get_intro($cm);
        $ok = false;
        foreach ($needles as $n) {
            $ok = $ok || str_contains($html, $n);
        }
        foreach ($exclude as $x) {
            $ok = $ok && !str_contains($html, $x);
        }
        if ($ok) {
            return $cm;
        }
    }
    cli_error('No encontré en la sección ' . $sectionnum . ' una etiqueta con: ' . implode(' | ', $needles));
}

/** Reemplaza el contenido (intro) de un módulo: etiqueta, foro, cuestionario, recurso, etc. */
function mt_set_intro(cm_info $cm, string $html): void {
    global $DB;
    $rec = ['id' => $cm->instance, 'intro' => $html, 'introformat' => FORMAT_HTML, 'timemodified' => time()];
    if ($cm->modname === 'label') {
        $label = (object)['intro' => $html, 'introformat' => FORMAT_HTML, 'name' => ''];
        $rec['name'] = mt_label_name($html);
    }
    $DB->update_record($cm->modname, (object)$rec);
    mt_log("contenido actualizado: {$cm->modname} «" . shorten_text(strip_tags($cm->name), 50) . '»');
}

function mt_get_intro(cm_info $cm): string {
    global $DB;
    return (string)$DB->get_field($cm->modname, 'intro', ['id' => $cm->instance]);
}

/** Cambia el nombre visible de un módulo (no aplica a etiquetas, que toman el nombre del texto). */
function mt_rename(cm_info $cm, string $name): void {
    global $DB;
    $DB->set_field($cm->modname, 'name', $name, ['id' => $cm->instance]);
    mt_log("renombrado: «{$cm->name}» → «{$name}»");
}

/** Cambia nombre y/o resumen de una sección. */
function mt_set_section(stdClass $course, int $num, array $data): void {
    global $DB;
    $section = $DB->get_record('course_sections', ['course' => $course->id, 'section' => $num], '*', MUST_EXIST);
    $upd = ['id' => $section->id, 'timemodified' => time()];
    if (array_key_exists('name', $data)) {
        $upd['name'] = $data['name'];
    }
    if (array_key_exists('summary', $data)) {
        $upd['summary'] = $data['summary'];
        $upd['summaryformat'] = FORMAT_HTML;
    }
    $DB->update_record('course_sections', (object)$upd);
    mt_log("sección $num actualizada" . (isset($data['name']) ? ": «{$data['name']}»" : ''));
}

/** Opción de formato de una sección (por ejemplo el icono del mosaico en Tiles: 'tileicon'). */
function mt_section_option(stdClass $course, int $num, string $name, string $value): void {
    global $DB;
    $sectionid = $DB->get_field('course_sections', 'id', ['course' => $course->id, 'section' => $num], MUST_EXIST);
    $params = ['courseid' => $course->id, 'format' => $course->format, 'sectionid' => $sectionid, 'name' => $name];
    if ($id = $DB->get_field('course_format_options', 'id', $params)) {
        $DB->set_field('course_format_options', 'value', $value, ['id' => $id]);
    } else {
        $DB->insert_record('course_format_options', (object)($params + ['value' => $value]));
    }
    mt_log("sección $num: $name = $value");
}

/** Reemplaza el archivo principal de un recurso (mod_resource) por $path. */
function mt_replace_resource_file(cm_info $cm, string $path, ?string $filename = null): void {
    global $DB;
    if (!is_readable($path)) {
        cli_error("No existe el archivo $path");
    }
    $fs = get_file_storage();
    $context = context_module::instance($cm->id);
    $fs->delete_area_files($context->id, 'mod_resource', 'content');
    $fs->create_file_from_pathname([
        'contextid' => $context->id, 'component' => 'mod_resource', 'filearea' => 'content',
        'itemid' => 0, 'filepath' => '/', 'filename' => $filename ?? basename($path), 'sortorder' => 1,
    ], $path);
    $DB->set_field('resource', 'revision', $DB->get_field('resource', 'revision', ['id' => $cm->instance]) + 1,
        ['id' => $cm->instance]);
    mt_log('archivo de «' . $cm->name . '» reemplazado por ' . ($filename ?? basename($path)) .
        ' (' . round(filesize($path) / 1e6, 2) . ' MB)');
}

/** Icono del mosaico (formato Tiles). $icon es el nombre Font Awesome 4 sin «fa-», p. ej. 'university'. */
function mt_tile_icon(stdClass $course, int $num, string $icon): void {
    global $DB;
    $sectionid = $DB->get_field('course_sections', 'id', ['course' => $course->id, 'section' => $num], MUST_EXIST);
    \format_tiles\local\format_option::unset($course->id, \format_tiles\local\format_option::OPTION_SECTION_ICON, $sectionid);
    \format_tiles\local\format_option::set($course->id, \format_tiles\local\format_option::OPTION_SECTION_ICON,
        $sectionid, $icon);
    mt_log("sección $num: icono del mosaico = $icon");
}

/** Agrega un archivo al área de la intro de un módulo (para imágenes o PDF que se citan con @@PLUGINFILE@@). */
function mt_add_intro_file(cm_info $cm, string $path, ?string $filename = null): string {
    $fs = get_file_storage();
    $context = context_module::instance($cm->id);
    $filename = $filename ?? basename($path);
    $component = 'mod_' . $cm->modname;
    if ($old = $fs->get_file($context->id, $component, 'intro', 0, '/', $filename)) {
        $old->delete();
    }
    $fs->create_file_from_pathname(['contextid' => $context->id, 'component' => $component, 'filearea' => 'intro',
        'itemid' => 0, 'filepath' => '/', 'filename' => $filename], $path);
    return '@@PLUGINFILE@@/' . rawurlencode($filename);
}

/** Aplica reemplazos de texto en todas las etiquetas y resúmenes de sección del curso. Devuelve cuántos cambió. */
function mt_replace_everywhere(stdClass $course, array $pairs, bool $regex = false): int {
    global $DB;
    $n = 0;
    $apply = function(string $s) use ($pairs, $regex): string {
        foreach ($pairs as $from => $to) {
            if ($regex) {
                $s = is_callable($to) ? preg_replace_callback($from, $to, $s) : preg_replace($from, $to, $s);
            } else {
                $s = str_replace($from, $to, $s);
            }
        }
        return $s;
    };
    foreach ($DB->get_records('label', ['course' => $course->id]) as $l) {
        $new = $apply($l->intro);
        if ($new !== $l->intro) {
            $DB->update_record('label', (object)['id' => $l->id, 'intro' => $new,
                'name' => mt_label_name($new)]);
            $n++;
        }
    }
    foreach ($DB->get_records('course_sections', ['course' => $course->id]) as $s) {
        $new = $apply((string)$s->summary);
        if ($new !== (string)$s->summary) {
            $DB->set_field('course_sections', 'summary', $new, ['id' => $s->id]);
            $n++;
        }
    }
    return $n;
}

/** Crea un módulo nuevo (page, url, resource, glossary...) en la sección indicada, opcionalmente antes de otro. */
function mt_create_module(stdClass $course, int $sectionnum, string $modname, array $fields, ?cm_info $before = null): cm_info {
    global $DB;
    $moduleinfo = (object)array_merge([
        'modulename' => $modname, 'module' => $DB->get_field('modules', 'id', ['name' => $modname], MUST_EXIST),
        'section' => $sectionnum, 'visible' => 1, 'visibleoncoursepage' => 1, 'course' => $course->id,
        'introeditor' => ['text' => $fields['intro'] ?? '', 'format' => FORMAT_HTML, 'itemid' => 0],
        'cmidnumber' => '', 'groupmode' => 0, 'groupingid' => 0, 'completion' => 0,
    ], $fields);
    if ($modname === 'page' && isset($fields['page'])) {
        // page_add_instance solo copia el contenido cuando recibe el formulario; sin él la página queda vacía.
        $moduleinfo->content = $fields['page']['text'];
        $moduleinfo->contentformat = FORMAT_HTML;
    }
    $info = add_moduleinfo($moduleinfo, $course);
    if ($before) {
        $section = $DB->get_record('course_sections', ['course' => $course->id, 'section' => $sectionnum]);
        moveto_module(get_fast_modinfo($course)->get_cm($info->coursemodule), $section, $before->id);
    }
    rebuild_course_cache($course->id, true);
    $cm = get_fast_modinfo($course)->get_cm($info->coursemodule);
    mt_log("creado $modname «{$cm->name}» en la sección $sectionnum");
    return $cm;
}

/** Busca un módulo creado antes por nombre exacto (para que los scripts se puedan repetir sin duplicar). */
function mt_find_by_name(stdClass $course, int $sectionnum, string $modname, string $name): ?cm_info {
    foreach (get_fast_modinfo($course)->get_section_info($sectionnum)->get_sequence_cm_infos() as $cm) {
        if ($cm->modname === $modname && $cm->name === $name) {
            return $cm;
        }
    }
    return null;
}

/**
 * Envuelve cada imagen del curso en un enlace que la abre en tamaño completo en una pestaña nueva,
 * con un texto «Ampliar imagen» debajo (en celular nadie adivina que la imagen se puede tocar).
 * Se salta la foto del docente y las imágenes que ya están dentro de un enlace.
 */
function mt_imagenes_ampliables(string $html): string {
    // Texto alternativo: quita saltos de línea pegados («Mapa Unidad 1\n») y nombra la imagen de resultados.
    $html = preg_replace('~alt="Mapa Unidad (\d)\s*"~', 'alt="Mapa de la unidad $1"', $html);
    $html = str_replace('src="@@PLUGINFILE@@/Resultados.png" alt=""', 'src="@@PLUGINFILE@@/Resultados.png" alt="Resultados de aprendizaje de la unidad"', $html);
    $html = str_replace('alt="Resultados de aprendizaje de la unidad" width="1408" height="768" role="presentation"',
        'alt="Resultados de aprendizaje de la unidad" width="1408" height="768"', $html);
    return preg_replace_callback('~(<a\b[^>]*>\s*)?(<img\b[^>]*\bsrc="(@@PLUGINFILE@@/[^"]+)"[^>]*>)~i', function($m) {
        if ($m[1] !== '' || str_contains($m[2], 'Fotografía del docente') || str_contains($m[2], 'Foto Profesor')) {
            return $m[0];
        }
        $img = preg_replace('~\bstyle="~', 'style="cursor:zoom-in;', $m[2], 1, $n);
        if (!$n) {
            $img = str_replace('<img ', '<img style="cursor:zoom-in;" ', $img);
        }
        return '<a class="eidti-ampliar" href="' . $m[3] . '" target="_blank" rel="noopener" title="Abrir la imagen en tamaño completo">' . $img .
            '</a><br><a class="eidti-ampliar nomediaplugin" href="' . $m[3] . '" target="_blank" rel="noopener" style="font-size:14px;">' .
            '<i class="fa fa-solid fa-magnifying-glass-plus" aria-hidden="true"></i>&nbsp;Ampliar imagen (se abre en una pestaña nueva)</a>';
    }, $html);
}

function mt_done(stdClass $course): void {
    global $DB;
    // Imágenes ampliables en etiquetas, resúmenes de sección y páginas (se hace al final de cada script).
    foreach ($DB->get_records('label', ['course' => $course->id], '', 'id, intro') as $l) {
        $nuevo = mt_imagenes_ampliables($l->intro);
        if ($nuevo !== $l->intro) {
            $DB->set_field('label', 'intro', $nuevo, ['id' => $l->id]);
        }
    }
    foreach ($DB->get_records('course_sections', ['course' => $course->id], '', 'id, summary') as $s) {
        $nuevo = mt_imagenes_ampliables((string)$s->summary);
        if ($nuevo !== (string)$s->summary) {
            $DB->set_field('course_sections', 'summary', $nuevo, ['id' => $s->id]);
        }
    }
    foreach ($DB->get_records('page', ['course' => $course->id], '', 'id, content') as $p) {
        $nuevo = mt_imagenes_ampliables((string)$p->content);
        if ($nuevo !== (string)$p->content) {
            $DB->set_field('page', 'content', $nuevo, ['id' => $p->id]);
        }
    }
    // Recalcula el nombre de todas las etiquetas (es el texto que se ve en el índice del curso).
    foreach ($DB->get_records('label', ['course' => $course->id], '', 'id, intro, name') as $l) {
        $nombre = mt_label_name($l->intro);
        if ($nombre !== $l->name) {
            $DB->set_field('label', 'name', $nombre, ['id' => $l->id]);
        }
    }
    rebuild_course_cache($course->id, true);
    purge_caches(['muc' => true]);
    mtrace('Listo.');
}

/** Lee un archivo HTML de la carpeta html/ de estos scripts. */
function mt_html(string $file): string {
    $path = __DIR__ . '/html/' . $file;
    if (!is_readable($path)) {
        cli_error("Falta $path");
    }
    return file_get_contents($path);
}
