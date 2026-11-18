<?php
// Grupo 12 · Tarea 10 · Financiación y marco legal de la Sección 1 (montaje: Daniel Sanabria).
// Fuente: Grupo_12_Tarea_10/Verificacion_y_entrega.docx (10.1 a 10.3) y «Caso de Financiacion.pdf».
// Nota: el grupo indica insertar los párrafos de conexión dentro de los PDF «Entorno legal» y «Mecanismos de financiación».
// Esos PDF no tienen editable en la carpeta, así que los textos se publican en una página propia junto a esos temas.
require(__DIR__ . '/lib.php');
global $DB;
$course = mt_course();
mtrace("Grupo 12 · financiación y entorno legal en el curso {$course->id}");

// 10.1 y 10.2 · Página con la tabla por etapas, correcciones legales, párrafos de conexión y pregunta integradora.
$nombre = 'Financiación y entorno legal por etapas';
$contenido = mt_html('g12_financiacion_legal.html');
if ($pg = mt_find_by_name($course, 3, 'page', $nombre)) {
    $DB->set_field('page', 'content', $contenido, ['id' => $pg->instance]);
    mt_log("página «{$nombre}» actualizada");
} else {
    mt_create_module($course, 3, 'page', [
        'name' => $nombre, 'intro' => '<p>Complementa las lecturas «Mecanismos de financiación» y «Entorno legal».</p>', 'showdescription' => 1,
        'page' => ['text' => $contenido, 'format' => FORMAT_HTML, 'itemid' => 0],
        'display' => 5, 'printintro' => 0, 'printlastmodified' => 0, 'printheading' => 1,
    ], mt_cm($course, 3, 'resource', 'Casos de exito'));
}

// 10.3 · Caso de financiación con rúbrica y preguntas, en el bloque de evaluación.
$nombre = 'Caso de financiación: análisis, preguntas y rúbrica';
$pdf = MT_ENTREGAS . '/Grupo_12_Tarea_10/Caso de Financiacion.pdf';
if ($cm = mt_find_by_name($course, 3, 'resource', $nombre)) {
    mt_replace_resource_file($cm, $pdf, 'Caso_de_financiacion.pdf');
} else {
    $cm = mt_create_module($course, 3, 'resource', [
        'name' => $nombre, 'intro' => '<p>Lee el caso, responde las preguntas y revisa la rúbrica de participación.</p>', 'showdescription' => 1,
        'files' => 0, 'display' => 0, 'printintro' => 1, 'filterfiles' => 0, 'tobemigrated' => 0, 'legacyfiles' => 0,
    ], mt_cm($course, 3, 'quiz', 'Cuestionario'));
    mt_replace_resource_file($cm, $pdf, 'Caso_de_financiacion.pdf');
}

mt_done($course);
