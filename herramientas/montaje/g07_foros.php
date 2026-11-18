<?php
// Grupo 7 · Tarea 6 · Foros y novedades (montaje: Felipe).
// Fuente: Grupo_07_Tarea_06/Verificacion_y_entrega.docx (6.1 a 6.3).
// Ajustes al montar: se llenaron los espacios «[Nombre de la materia/carrera]» y «[Unidad/Módulo X]»,
// la actividad inicial se entrega en su propio foro (antes decía «Foro de Presentación») y la fecha
// fija del 16 de noviembre se cambió por «la fecha que indique el docente», porque ya pasó.
require(__DIR__ . '/lib.php');
$course = mt_course();
mtrace("Grupo 7 · foros en el curso {$course->id}");

$p = fn(string $t) => '<p style="font-family:\'Montserrat\',Arial,sans-serif;font-size:15px;line-height:1.5;">' . $t . '</p>';
$h = fn(string $t) => '<h4 style="color:#8C1919;font-family:\'Montserrat\',Arial,sans-serif;font-weight:700;' . MT_NOBAR . '">' . $t . '</h4>';

$presentacion = $h('Foro de presentación: «Nos conocemos»') .
    $p('Este espacio es para que empecemos a construir comunidad. Preséntate ante tus compañeros y docentes respondiendo brevemente: ¿cuál es tu nombre?, ¿de dónde nos escribes? y ¿qué expectativas tienes para esta asignatura? Si lo deseas, comparte un pasatiempo o interés personal.') .
    $p('<strong>Pregunta detonadora:</strong> ¿Qué te motiva a estudiar emprendimiento en TI y qué esperas aprender en este curso?') .
    $p('<strong>Participación esperada:</strong> una intervención inicial individual. Lee las presentaciones de al menos dos compañeros y responde a una de ellas de forma respetuosa.');

$ayuda = $h('Foro de ayuda: «Dudas e inquietudes»') .
    $p('Usa este foro exclusivamente para plantear dudas sobre el contenido, las actividades o la plataforma. Antes de publicar, revisa si tu pregunta ya fue respondida por un compañero o por el docente, para evitar duplicados.') .
    $p('<strong>Pregunta detonadora:</strong> ¿Qué concepto de la unidad que estás estudiando te resulta más complejo o te genera dudas hasta el momento?') .
    $p('<strong>Participación esperada:</strong> voluntaria. Se espera que, además de preguntar, aportes respuestas o recursos a las dudas de tus compañeros.');

$novedades = $h('Foro de novedades') .
    $p('<strong>Propósito:</strong> mantenerte informado sobre cambios en el cronograma, fechas de entrega, apertura de recursos, orientaciones académicas y cualquier eventualidad relevante de la asignatura, de forma oportuna y en un solo lugar.') .
    $p('<strong>Quién publica:</strong> el docente titular, Abel Antonio Navarrete López. Solo el docente puede publicar en este foro.') .
    $p('<strong>Cómo consultarlo:</strong> los mensajes llegan automáticamente a tu correo institucional. Revisa el aula virtual y tu correo al menos tres veces por semana. Los estudiantes no pueden publicar aquí; solo leen y reciben las notificaciones.');

$inicial = $h('Actividad inicial: «Mi primera idea de negocio en TI»') .
    $p('<strong>Propósito:</strong> que reconozcas la estructura del aula virtual, te familiarices con las herramientas de comunicación e identifiques tus saberes previos y tu motivación frente al emprendimiento y la generación de ideas de negocio en tecnologías de la información.') .
    '<p style="font-family:\'Montserrat\',Arial,sans-serif;font-size:15px;"><strong>Pasos:</strong></p><ol style="font-family:\'Montserrat\',Arial,sans-serif;font-size:15px;line-height:1.5;">' .
    '<li><strong>Exploración:</strong> navega por la página principal del curso y localiza el Foro de presentación y el Foro de novedades.</li>' .
    '<li><strong>Lectura:</strong> revisa el syllabus de la asignatura en «Presentación del curso».</li>' .
    '<li><strong>Reflexión:</strong> piensa en un problema, necesidad u oportunidad de tu entorno (personal, laboral o comunitario) que podría resolverse con una idea de negocio apoyada en tecnología.</li>' .
    '<li><strong>Publicación:</strong> en este foro, comparte tu nombre, tu expectativa frente al curso y una breve descripción de tu idea de negocio en TI (máximo 150 palabras).</li></ol>' .
    $p('<strong>Entrega:</strong> publicación en este foro en la fecha que indique el docente en el Foro de novedades. No se requiere archivo adjunto.') .
    '<p style="font-family:\'Montserrat\',Arial,sans-serif;font-size:15px;"><strong>Criterios de evaluación:</strong></p><ul style="font-family:\'Montserrat\',Arial,sans-serif;font-size:15px;line-height:1.5;">' .
    '<li><strong>Claridad (40 %):</strong> la presentación es comprensible, ordenada y responde a lo solicitado.</li>' .
    '<li><strong>Pertinencia (40 %):</strong> la idea de negocio se relaciona con el emprendimiento y las TI, y responde a una necesidad real.</li>' .
    '<li><strong>Interacción (20 %):</strong> respondes de forma respetuosa a la publicación de al menos un compañero.</li></ul>';

mt_set_intro(mt_cm($course, 2, 'forum', 'presentacion'), $presentacion);
mt_set_intro(mt_cm($course, 2, 'forum', 'ayuda'), $ayuda);
mt_set_intro(mt_cm($course, 2, 'forum', 'novedades'), $novedades);
mt_set_intro(mt_cm($course, 7, 'forum', 'Actividad inicial'), $inicial);

mt_done($course);
