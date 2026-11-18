<?php
// Grupo 8 · Tarea 7 · Textos, ortografía y enlaces de las secciones 1 a 3 (montaje: Daniel Sanabria).
// Fuente: Grupo_08_Tarea_07/Verificacion_y_entrega.docx, anexos A (frases por lectura), B (36 errores),
// C (textos corregidos) y D (36 enlaces).
// Ya resueltos por otros scripts: nombres de secciones, recursos y cuestionarios (g02), presentaciones de unidad
// con las introducciones del grupo 4 (g04), material complementario y referencias (g06).
// Ejecutar después de g02, g04 y g06.
require(__DIR__ . '/lib.php');
global $DB;
$course = mt_course();
mtrace("Grupo 8 · textos y enlaces en el curso {$course->id}");
$f = "font-family:'Montserrat',Arial,sans-serif;font-size:15px;line-height:1.5;";

// Anexo A · Una frase por lectura, como descripción visible bajo cada recurso.
$frases = [
    3 => ['Entorno institucional' => 'Identifica las entidades que respaldan al emprendedor de TI y el papel de cada una.',
          'Entorno de modelos de negocio' => 'Explica cómo la innovación y los modelos de negocio convierten una idea tecnológica en una propuesta con valor.',
          'Entorno del ecosistema' => 'Describe los actores y redes del ecosistema con los que puedes articular tu proyecto.',
          'Mecanismos de financiacion' => 'Reúne las fuentes de recursos disponibles para crear y hacer crecer tu empresa.',
          'Entorno legal' => 'Resume la normatividad vigente que regula y beneficia a los emprendimientos, para que la uses a tu favor.',
          'Casos de exito' => 'Presenta ejemplos de emprendedores del área que sirven de referencia para tu propio proyecto.'],
    4 => ['opcion de grado' => 'Aclara cómo un plan de negocio puede convertirse en opción de grado y qué se espera de él.',
          'calidad educativa' => 'Explica el marco de calidad educativa en el que se desarrolla el emprendimiento universitario.',
          'Habilidades blandas' => 'Destaca las habilidades personales y la cultura que sostienen el trabajo de un emprendedor.',
          'Metodologias' => 'Explica Lean Startup, Design Thinking y Business Model Canvas para transformar una idea en un modelo validado.'],
    5 => ['Desarrollo del cliente' => 'Detalla las fases de descubrimiento y validación del cliente para lograr el encaje producto-mercado.',
          'Marketing' => 'Aplica los conceptos de marketing 3.0 y 4.0 a la creación de valor en TI.',
          'Plan de negocio' => 'Estructura la propuesta técnica y comercial y las métricas que permiten validarla.',
          'Estrategias de escalamiento' => 'Muestra cómo proyectar el crecimiento de una solución tecnológica ya validada.'],
];
foreach ($frases as $num => $lista) {
    foreach ($lista as $match => $frase) {
        $cm = mt_cm($course, $num, 'resource', $match);
        $DB->update_record('resource', (object)['id' => $cm->instance, 'intro' => "<p>$frase</p>", 'introformat' => FORMAT_HTML]);
        $DB->set_field('course_modules', 'showdescription', 1, ['id' => $cm->id]);
    }
}
mt_log('frases por lectura agregadas a 14 recursos');

// Anexo C · Textos corregidos del bloque «Contenido temático» de las unidades 1 a 3.
$recomendacion = '<p style="' . $f . '">Los siguientes recursos orientarán el desarrollo del contenido temático principal y te permitirán comprender, aplicar, analizar y evaluar las temáticas requeridas. ' .
    'Te recomendamos una atenta lectura y, en lo posible, apoyarte en diferentes estrategias (toma de notas, mapas conceptuales, mapas mentales, entre otras) para apropiar cada temática.</p>';
$unidad2 = '<p style="' . $f . '">Exploraremos las herramientas y procesos que definen a las <em>startups</em> exitosas hoy en día:</p><ul style="' . $f . '">' .
    '<li><strong>Metodologías ágiles de emprendimiento:</strong> estudio y aplicación de <em>Lean Startup</em>, <em>Design Thinking</em> y <em>Business Model Canvas</em>.</li>' .
    '<li><strong>Modelo de desarrollo de clientes:</strong> fases de descubrimiento y validación del cliente para asegurar el encaje producto-mercado.</li>' .
    '<li><strong>Marketing y valor:</strong> conceptos de marketing 3.0 y 4.0 aplicados a la creación de valor en el sector de las tecnologías de la información.</li>' .
    '<li><strong>Plan de negocio en TI:</strong> estructuración de la propuesta técnica y comercial.</li>' .
    '<li><strong>Métricas de validación y herramientas de iteración:</strong> diseño de productos mínimos viables (MVP, por sus siglas en inglés), técnicas de prototipado y ejecución de pruebas piloto.</li>' .
    '<li><strong>Estrategias de escalamiento:</strong> cómo proyectar el crecimiento de una solución tecnológica una vez validada.</li></ul>' . $recomendacion;
// El texto queda sin título propio: va justo debajo del subtítulo «Temas» del grupo 2 (antes se repetía el título).
// La Unidad 4 recibe el mismo párrafo corregido para que las cuatro unidades se vean igual.
foreach ([3 => $recomendacion, 4 => $unidad2, 5 => $recomendacion, 6 => $recomendacion] as $num => $cuerpo) {
    $cm = mt_label_with($course, $num, ['eidti-contenido', 'Temas a Desarrollar', 'Contenido temático']);
    mt_set_intro($cm, '<div class="eidti-contenido">' . $cuerpo . '</div>');
}

// Anexo B · Correcciones puntuales que quedan en etiquetas, resúmenes y descripciones.
$n = mt_replace_everywhere($course, [
    'Resultado de Aprendizaje Esperado' => 'Resultado de aprendizaje esperado',
    'según el contexto de negocio que estés abordando</span>' => 'según el contexto de negocio que estés abordando.</span>',
    'Presentación del módulo' => 'Presentación de la unidad',
    'Vídeo de presentación del curso' => 'Video de presentación del curso',
    '<em>¡Haz clic para visualizar!</em>' => '<em>Mira el video de presentación del curso</em>',
    'fortalecer su proceso de aprendizaje' => 'fortalecer tu proceso de aprendizaje',
]);
mt_log("$n etiquetas o resúmenes corregidos");

// Error 31 · Descripción del cuestionario de la Unidad 3: punto final y comillas angulares.
$quiz = mt_cm($course, 5, 'quiz', 'Cuestionario');
$intro = mt_get_intro($quiz);
$nuevo = str_replace(['"aprendizaje validado"', '&quot;aprendizaje validado&quot;'], '«aprendizaje validado»', $intro);
$nuevo = preg_replace('~necesidad latente del cliente(\s*</)~u', 'necesidad latente del cliente.$1', $nuevo);
if ($nuevo !== $intro) {
    mt_set_intro($quiz, $nuevo);
}

// Anexo D · Enlaces: se quitan las redirecciones de Google («google.com/url?...&q=») que quedaran en el curso.
$n = mt_replace_everywhere($course, [
    '~https?://www\.google\.com/url\?[^"]*?q=([^"&]+)[^"]*~' => fn($m) => urldecode($m[1]),
], true);
mt_log("redirecciones de Google limpiadas en $n lugares");

mt_done($course);
