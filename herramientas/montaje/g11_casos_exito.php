<?php
// Grupo 11 · Tarea 9 · Casos de éxito y contenidos de la Sección 1 (montaje: Daniel Sanabria).
// Fuente: Grupo_11_Tarea_09/Verificacion_y_entrega.docx (9.1 a 9.4). HTML generado con generar_html.py.
// Se crean dos páginas en la Unidad 1, dentro del bloque «Temas»: los seis casos de éxito y los conceptos corregidos.
// Pendiente: los «resúmenes visuales» del punto 9.4 son enlaces de Canva; se dejan como enlace (no hay imágenes en la carpeta).
require(__DIR__ . '/lib.php');
global $DB;
$course = mt_course();
mtrace("Grupo 11 · casos de éxito en el curso {$course->id}");

$paginas = [
    ['Casos de éxito de emprendimiento en TI', 'g11_casos_exito.html'],
    ['Conceptos clave de la Unidad 1', 'g11_conceptos_unidad1.html'],
];
foreach ($paginas as [$nombre, $archivo]) {
    $contenido = mt_html($archivo);
    if ($pg = mt_find_by_name($course, 3, 'page', $nombre)) {
        $DB->set_field('page', 'content', $contenido, ['id' => $pg->instance]);
        mt_log("página «{$nombre}» actualizada");
        continue;
    }
    // Se ubica al final de los temas, antes del bloque «Evaluación y cierre» (o de «Actividades de aprendizaje»).
    $antes = mt_label_with($course, 3, ['Evaluación y cierre</h4>', 'Actividades de aprendizaje'], []);
    mt_create_module($course, 3, 'page', [
        'name' => $nombre, 'intro' => '', 'showdescription' => 0,
        'page' => ['text' => $contenido, 'format' => FORMAT_HTML, 'itemid' => 0],
        'display' => 5, 'printintro' => 0, 'printlastmodified' => 0, 'printheading' => 1,
    ], $antes);
}

mt_done($course);
