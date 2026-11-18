<?php
// Borra del Moodle LOCAL los cursos restaurados de Emprendimiento para volver a restaurar la copia limpia.
// Solo lo usa montar.sh con REINICIAR=1. No toca el Moodle real.
require(__DIR__ . '/lib.php');
global $DB;
$cursos = $DB->get_records_select('course', 'fullname LIKE ?', ['%EMPRENDIMIENTO E IDEAS DE NEGOCIO%']);
foreach ($cursos as $c) {
    mtrace("Borrando curso local {$c->id}: {$c->fullname}");
    delete_course($c, false);
}
fix_course_sortorder();
mtrace(count($cursos) . ' curso(s) borrados.');
