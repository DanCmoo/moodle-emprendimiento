<?php
// Grupo 5 · Tarea 4 · Videos y recursos multimedia (montaje: Daniel Sanabria).
// Fuente: Grupo_05_Tarea_04/Verificacion_y_entrega.docx (4.1 a 4.4). HTML generado con generar_html.py.
// Además:
//  - Se quita el video duplicado (Océano Azul aparecía dos veces), el enlace a Sci-Hub y el PDF no autorizado
//    de «El método Lean Startup» (theoffice.pe). El segundo enlace «Capital intelectual y gestión del conocimiento»
//    apuntaba al mismo video que «CBI Agro Fintech»; se retira hasta que el docente indique el enlace correcto.
//  - 4.3: los 5 MP4 del curso se reemplazan por videos de YouTube (no listados) cuando exista videos_youtube.json
//    con la forma {"Unidad_1__Marcos_en_TI.mp4": "ID_DE_YOUTUBE", ...}. Así se liberan unos 54 MB.
require(__DIR__ . '/lib.php');
global $DB;
$course = mt_course();
mtrace("Grupo 5 · videos en el curso {$course->id}");
$f = "font-family:'Montserrat',Arial,sans-serif;font-size:15px;line-height:1.5;";

// Guía de los seis videos (4.1 + 4.2), como página en Recursos.
$nombre = 'Guía de videos: resúmenes y preguntas de reflexión';
$guia = mt_html('g05_guia_videos.html');
$pg = mt_find_by_name($course, 7, 'page', $nombre);
if ($pg) {
    $DB->set_field('page', 'content', $guia, ['id' => $pg->instance]);
    mt_log("página «{$nombre}» actualizada");
} else {
    $pg = mt_create_module($course, 7, 'page', [
        'name' => $nombre, 'intro' => '', 'showdescription' => 0,
        'page' => ['text' => $guia, 'format' => FORMAT_HTML, 'itemid' => 0],
        'display' => 5, 'printintro' => 0, 'printlastmodified' => 0, 'printheading' => 1,
    ], mt_cm($course, 7, 'resource', 'Syllabus'));
}
$urlguia = (new moodle_url('/mod/page/view.php', ['id' => $pg->id]))->out(false);

// Etiqueta «Perspectiva holista» (4.4): videos ordenados por tema y el resto de recursos depurado.
// class="nomediaplugin": evita que el filtro multimedia cambie los enlaces de YouTube por reproductores sin título.
$li = fn(string $url, string $t, string $nota = '') => "<li><a class=\"nomediaplugin\" href=\"$url\" target=\"_blank\" rel=\"noopener\">$t</a>" . ($nota ? " <span class=\"text-muted\">($nota)</span>" : '') . '</li>';
$h5 = fn(string $t) => '<h5 style="color:#8C1919;font-family:\'Montserrat\',Arial,sans-serif;font-weight:700;margin-top:16px;' . MT_NOBAR . '">' . $t . '</h5>';
$perspectiva = '<h4 class="eidti-subtitulo" style="color:#8C1919;font-family:\'Montserrat\',Arial,sans-serif;font-weight:700;font-size:20px;border-bottom:2px solid #950D16;padding-bottom:6px;' . MT_NOBAR . '">' .
    '<i class="fa fa-solid fa-circle-play" aria-hidden="true"></i>&nbsp; Perspectiva holista</h4>' .
    '<div style="' . $f . '"><p>Recursos multimedia que conectan las teorías y conceptos del curso. Empieza por los seis videos comentados: ' .
    'en la <a href="' . $urlguia . '">guía de videos</a> encuentras el resumen y las preguntas de reflexión de cada uno.</p>' .
    $h5('Videos comentados') . '<ol>' . mt_html('g05_lista_videos.html') . '</ol>' .
    $h5('Lean Startup, MVP y pivoteo') . '<ul>' .
        $li('http://www.scielo.org.co/pdf/ean/n84/0120-8160-ean-84-00079.pdf', 'La metodología Lean Startup: desarrollo y aplicación para el emprendimiento', 'artículo, Revista EAN') .
        $li('https://robertotouza.com/lean-startup/que-es-el-mvp-o-producto-minimo-viable-en-las-startups/', 'MVP o producto mínimo viable en las startups') .
        $li('https://robertotouza.com/lean-startup/aprende-cuando-y-como-pivotar/', '¿Qué es pivotar en una startup?') . '</ul>' .
    $h5('Ejemplos de emprendimientos') . '<ul>' .
        $li('https://www.joyasdelavilla.com/', 'Joyas de la Villa') . $li('https://www.mielesdelavilla.com/', 'Mieles de la Villa') . '</ul>' .
    $h5('Fintech, inteligencia artificial y gestión del conocimiento') . '<ul>' .
        $li('https://www.youtube.com/watch?v=cjrnskr5E7E', '¿Qué es Fintech?', 'video') .
        $li('https://www.youtube.com/watch?v=k7M0RqYmj6Q', 'CBI Agro Fintech: inversiones', 'video') .
        $li('https://www.youtube.com/watch?v=6iVUKYgOihQ', 'Diferencia entre inteligencia artificial, machine learning y deep learning', 'video') .
        $li('https://www.youtube.com/watch?v=Es7HAzFsW1M', '¿Qué es una red neuronal?', 'video') .
        $li('https://protecciondatos-lopd.com/empresas/capital-intelectual-empresa/', 'Capital intelectual', 'artículo') .
        $li('https://www.youtube.com/watch?v=vgO-J_YMqMA', 'Módulo 4. Gestión del conocimiento', 'video') .
        $li('https://www.youtube.com/watch?v=h9LfuVZquhg', 'Cerebro digital: gestión del conocimiento personal', 'video') .
        $li('https://www.youtube.com/watch?v=MzKdUizTxBI', 'El Foro Informa (noviembre)', 'video') . '</ul>' .
    $h5('Normativa, comunidad y bases de datos') . '<ul>' .
        $li('https://minciencias.gov.co/sites/default/files/upload/reglamentacion/ley1838-2017.pdf', 'Ley 1838 de 2017', 'filosofía del curso') .
        $li('https://flisol.info/', 'FLISoL: Festival Latinoamericano de Instalación de Software Libre') .
        $li('https://drive.google.com/file/d/1oQkPwmbxA27q0AhJk47Wwjzwt0Y445OG/view', 'Matriz de formulación', 'ScienceDirect') .
        $li('https://www.youtube.com/watch?v=CY685luAFmo', 'Cómo buscar en Scopus', 'tutorial de Elsevier LATAM') .
        $li('http://bdigital.udistrital.edu.co:8080/login?url=http://www.scopus.com/home.url', 'Scopus con tu cuenta de la Universidad Distrital') . '</ul></div>';
mt_set_intro(mt_label_with($course, 7, ['Perspectiva holista'], []), $perspectiva);

// 4.3 · MP4 → YouTube.
$mapa = __DIR__ . '/videos_youtube.json';
if (!is_readable($mapa)) {
    mt_log('videos_youtube.json no existe todavía: los 5 MP4 siguen en Moodle');
} else {
    $ids = json_decode(file_get_contents($mapa), true);
    $embed = fn(string $id, string $t) => '<div class="eidti-video" style="max-width:900px;margin:12px auto;"><div style="position:relative;padding-bottom:56.25%;height:0;overflow:hidden;">' .
        "<iframe src=\"https://www.youtube-nocookie.com/embed/$id\" title=\"$t\" style=\"position:absolute;top:0;left:0;width:100%;height:100%;border:0;\" " .
        'allow="accelerometer; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe></div></div>';
    $fs = get_file_storage();
    foreach ($ids as $archivo => $id) {
        $enc = '@@PLUGINFILE@@/' . rawurlencode($archivo);
        $patron = '~<video[^>]*>\s*<source src="(?:' . preg_quote($enc, '~') . '|' . preg_quote('@@PLUGINFILE@@/' . $archivo, '~') . ')"[^>]*>\s*</video>~';
        $n = mt_replace_everywhere($course, [$patron => $embed($id, 'Video del curso')], true);
        mt_log("$archivo → YouTube $id ($n lugares)");
        // Borra el MP4 de las áreas de archivos del curso (sección 0 y etiquetas) para liberar espacio.
        $files = $DB->get_records_sql("SELECT f.* FROM {files} f JOIN {context} ctx ON ctx.id = f.contextid
            WHERE f.filename = ? AND (ctx.path LIKE ? OR ctx.id = ?)",
            [$archivo, context_course::instance($course->id)->path . '/%', context_course::instance($course->id)->id]);
        foreach ($files as $fr) {
            $fs->get_file_instance($fr)->delete();
        }
    }
}

mt_done($course);
