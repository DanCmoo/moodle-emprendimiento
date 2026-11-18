<?php
// Grupo 4 · Tarea 3 · Presentación del curso, docente, invitados y presentaciones de módulos (montaje: Felipe).
// Fuente: Grupo_04_Tarea_03/Entrega_Tarea3_Grupo4_EmprendimientoTI.docx (3.1 a 3.5) y PlanDeTrabajoImagen.png.
// Decisiones al montar:
//  - Perfil docente: se omiten los títulos y el correo que el grupo marcó «[confirmar]». Pendiente del docente.
//  - Fichas de invitados (3.3): es una plantilla vacía; se publica cuando haya invitados confirmados.
//  - Cronograma: semanas por unidad sin fechas (el grupo las deja a criterio del docente).
// Ejecutar después de g02_identidad_visual.php (usa el banner de cada unidad).
require(__DIR__ . '/lib.php');
$course = mt_course();
mtrace("Grupo 4 · presentación del curso en el curso {$course->id}");

$f = "font-family:'Montserrat',Arial,sans-serif;font-size:15px;line-height:1.5;";

// --- Etiqueta «Presentación del curso» (acordeón) ---
$cm = mt_label_with($course, 1, ['accordionExample']);
$html = mt_get_intro($cm);

// Objetivos: la tabla original tenía 1035 px fijos y se desbordaba en celular; pasa a texto con lista.
$objetivos = '<div class="accordion-body py-2" style="' . $f . '"><p><strong>Objetivo general:</strong> desarrollar planes de negocio factibles para implementar ideas de negocio en TI siguiendo metodologías de emprendimiento tecnológico.</p>' .
    '<p><strong>Objetivos específicos:</strong></p><ul>' .
    '<li>Elaborar un marco para el emprendimiento en TI a partir de los diferentes entornos que le brindan soporte, para estructurar planes de negocio exitosos.</li>' .
    '<li>Identificar los componentes del proceso de desarrollo empresarial mediante la revisión del flujo de metodologías de emprendimiento para la creación de ideas de negocio en TI.</li>' .
    '<li>Construir un plan de negocio mediante la aplicación de modelos de emprendimiento tecnológico para materializar ideas de negocio en TI.</li></ul></div>';
$html = preg_replace('~(id="collapseOne".*?>)\s*<div class="accordion-body py-2">.*?</ul>\s*</div>~s',
    '$1' . str_replace('$', '\$', $objetivos), $html, 1, $c0);
mt_log($c0 ? 'objetivos sin tabla fija' : 'AVISO: no encontré los objetivos');
// Nombre legible en el índice del curso (Moodle lo toma del primer texto de la etiqueta).
if (!str_contains($html, 'eidti-nombre')) {
    $html = '<span class="eidti-nombre visually-hidden">Presentación del curso:</span>' . $html;
}

// 3.1 · Plan de trabajo: texto nuevo (se elimina el texto pegado desde Gemini con su botón de cita) y esquema gráfico nuevo.
$img = mt_add_intro_file($cm, MT_ENTREGAS . '/Grupo_04_Tarea_03/PlanDeTrabajoImagen.png', 'Plan_de_trabajo_esquema.png');
$plan = '<div class="accordion-body py-2"><p style="' . $f . '">Nuestro plan de trabajo se desarrollará de manera progresiva a lo largo de cuatro módulos articulados con la estructura del aula virtual y el syllabus oficial de la asignatura:</p><ul style="' . $f . '">' .
    '<li><strong>Módulo 1. Marcos y ecosistema del emprendimiento en TI:</strong> analizaremos el entorno institucional, normativo y financiero que soporta la creación de empresas de base tecnológica en Colombia, junto con modelos de negocio innovadores.</li>' .
    '<li><strong>Módulo 2. Emprendimiento en TI y metodologías:</strong> abordaremos marcos ágiles como Lean Startup, Design Thinking y Business Model Canvas para formular propuestas de valor e iniciar el descubrimiento de clientes.</li>' .
    '<li><strong>Módulo 3. Validación de mercado, métricas e iteración:</strong> aplicaremos técnicas de validación temprana mediante prototipado, producto mínimo viable (MVP), métricas de aprendizaje y estrategias de marketing 3.0 y 4.0.</li>' .
    '<li><strong>Módulo 4. Industrias de TI y consolidación del plan de negocio:</strong> exploraremos verticales estratégicas (como fintech, healthtech, edtech o legaltech) para consolidar y sustentar un plan de negocio integral y factible, según las orientaciones acordadas con el docente.</li></ul>' .
    '<p class="text-center"><img src="' . $img . '" alt="Esquema del plan de trabajo: los cuatro módulos del curso y su secuencia" class="img-fluid" style="max-width:900px;"></p>' .
    '<p style="' . $f . '">Versión para consultar o imprimir: <a href="%%ENLACE_PLAN%%">Plan de trabajo y cronograma</a>.</p></div>';
$html = preg_replace('~(id="collapseThree".*?>)\s*<div class="accordion-body py-2">.*?</div>\s*(</div>\s*</div>\s*<div class="accordion-item">)~s',
    '$1' . str_replace('$', '\$', $plan) . '$2', $html, 1, $c1);
mt_log($c1 ? 'plan de trabajo reemplazado' : 'AVISO: no encontré el bloque del plan de trabajo');

// 3.1 · Cronograma de 16 semanas, sin fechas fijas.
$filas = [
    ['Inicio', 'Semana 1', 'Encuentro inicial, presentación del curso, revisión del syllabus oficial y acuerdos de trabajo.', 'Socialización del programa y conformación de grupos.'],
    ['Unidad 1', 'Semanas 2 a 4', '<strong>Marcos y ecosistema del emprendimiento en TI:</strong> entorno institucional, normativo (Leyes 590, 905, 1014 y 2069), financiación y modelos de negocio innovadores.', 'Abordaje conceptual y ejercicios diagnósticos.'],
    ['Unidad 2', 'Semanas 5 a 8', '<strong>Emprendimiento en TI y metodologías:</strong> Lean Startup, Design Thinking, Business Model Canvas, propuesta de valor y descubrimiento de clientes.', 'Estructuración metodológica de la idea de negocio en TI.'],
    ['Unidad 3', 'Semanas 9 a 12', '<strong>Validación de mercado, métricas e iteración:</strong> validación de clientes, prototipado, MVP, métricas y marketing 3.0 y 4.0.', 'Ejercicios de validación y seguimiento del avance del proyecto.'],
    ['Unidad 4', 'Semanas 13 a 16', '<strong>Industrias de TI y consolidación del plan de negocio:</strong> verticales tecnológicas (fintech, healthtech, edtech, entre otras), viabilidad integral y escalamiento.', 'Consolidación y presentación final del plan de negocio en TI.'],
];
$tabla = '<div class="accordion-body py-2"><p style="' . $f . '">Estructura general por unidades. Las fechas de cada entrega las define el docente y se publican en el Foro de novedades. ' .
    'Evaluación según el syllabus: actividades entregables 25 %, talleres 20 %, informes del proyecto final 20 %, proyecto final 20 %, parciales 10 % y exposiciones 5 %.</p>' .
    '<div class="table-responsive"><table class="table table-sm table-hover mb-0" style="' . $f . '"><thead class="table-light"><tr><th>Unidad</th><th>Semanas</th><th>Temáticas</th><th>Desarrollo y entregas</th></tr></thead><tbody>';
foreach ($filas as $r) {
    $tabla .= "<tr><th>{$r[0]}</th><td>{$r[1]}</td><td>{$r[2]}</td><td>{$r[3]}</td></tr>";
}
$tabla .= '</tbody></table></div></div>';
$html = preg_replace('~(id="collapseFour".*?>)\s*<div class="accordion-body py-2">.*?</table>\s*</div>~s',
    '$1' . str_replace('$', '\$', $tabla), $html, 1, $c2);
mt_log($c2 ? 'cronograma reemplazado' : 'AVISO: no encontré el cronograma');

// El recurso «Plan de trabajo» de Recursos tenía el PDF «CALCULO INTEGRAL – Plan de trabajo» (otra materia, 3,8 MB).
// Se reemplaza por una página con el plan y el cronograma del grupo 4, y el PDF sale del curso.
global $DB;
$nombreplan = 'Plan de trabajo y cronograma';
$contenidoplan = preg_replace(['~^<div class="accordion-body py-2">~', '~<p class="text-center"><img.*?</p>.*$~s'], '', $plan) .
    '<h4 style="color:#8C1919;font-family:\'Montserrat\',Arial,sans-serif;font-weight:700;' . MT_NOBAR . '">Cronograma general (16 semanas)</h4>' .
    preg_replace(['~^<div class="accordion-body py-2">~', '~</div>$~'], '', $tabla);
if ($pg = mt_find_by_name($course, 7, 'page', $nombreplan)) {
    $DB->set_field('page', 'content', $contenidoplan, ['id' => $pg->instance]);
} else {
    $viejo = mt_cm($course, 7, 'resource', 'Plan de trabajo', false);
    $pg = mt_create_module($course, 7, 'page', [
        'name' => $nombreplan, 'intro' => '', 'showdescription' => 0,
        'page' => ['text' => $contenidoplan, 'format' => FORMAT_HTML, 'itemid' => 0],
        'display' => 5, 'printintro' => 0, 'printlastmodified' => 1, 'printheading' => 1,
    ], $viejo);
    if ($viejo) {
        course_delete_module($viejo->id);
        mt_log('borrado el recurso «Plan de trabajo» con el PDF de Cálculo Integral');
    }
}
$html = str_replace('%%ENLACE_PLAN%%', (new moodle_url('/mod/page/view.php', ['id' => $pg->id]))->out(false), $html);

// 3.2 · Perfil docente (sin los datos que el grupo marcó por confirmar).
$perfil = '<p class="card-text mb-0">Docente Investigador en Universidad Distrital FJC</p>' .
    '<p class="card-text mt-2" style="' . $f . '">Docente investigador de la Facultad de Ingeniería de la Universidad Distrital Francisco José de Caldas, adscrito al Proyecto Curricular de Ingeniería de Sistemas. ' .
    'Su trayectoria académica e investigativa se enfoca en el emprendimiento de base tecnológica, la formulación y evaluación de planes de negocio en TI, la innovación digital y la articulación con el ecosistema emprendedor colombiano, ' .
    'y orienta procesos de investigación aplicada y opciones de grado en emprendimiento.</p>' .
    '<p class="card-text" style="' . $f . '"><strong>Contacto:</strong> mensajería interna del aula virtual.</p>';
if (!str_contains($html, 'Su trayectoria académica')) {
    $html = str_replace('<p class="card-text mb-0">Docente Investigador en Universidad Distrital FJC</p>', $perfil, $html, $c3);
    mt_log($c3 ? 'perfil docente ampliado' : 'AVISO: no encontré el perfil docente');
}
// Sin la barra azul que el formato Tiles pone a los títulos del acordeón y al nombre del docente.
$html = str_replace(['class="accordion-header"', '<h3 aria-label="Profile Name">'],
    ['class="accordion-header" style="' . MT_NOBAR . '"', '<h3 aria-label="Profile Name" style="' . MT_NOBAR . '">'], $html);
// Texto alternativo de las imágenes (regla de accesibilidad del grupo 2, punto 1.4).
$html = str_replace('alt="Foto Profesor"', 'alt="Fotografía del docente Abel Antonio Navarrete López"', $html);
mt_set_intro($cm, $html);

// --- 3.4 y 3.5 · Presentaciones de los cuatro módulos ---
$modulos = [
    3 => ['Introduce las bases estructurales y normativas que regulan y fomentan el emprendimiento de base tecnológica en Colombia y en la Universidad Distrital, para articular una idea TI con instrumentos reales de financiación y formalización.',
          'Comprender los entornos institucional, normativo, financiero y académico que soportan la creación de empresas de base tecnológica en Colombia, identificando oportunidades de mercado y mecanismos de fomento.',
          'Entorno institucional (MinCiencias, MinTIC, MinCIT, iNNpulsa, Fondo Emprender); modelos de negocio innovadores (Canvas, océanos azules, plataformas multilaterales); ecosistema emprendedor y financiación (capital semilla, ángeles inversionistas, capital de riesgo, crowdfunding); marco legal (Leyes 590 de 2000, 905 de 2004, 1014 de 2006, 1789 de 2016, 1838 de 2017 y 2069 de 2020); opción de grado en planes de negocio y casos de éxito.',
          'Análisis del ecosistema emprendedor TI y reconocimiento del marco normativo y de fomento aplicable a una iniciativa tecnológica.',
          ['¿Qué instituciones y leyes en Colombia facilitan la creación y financiación de una startup TI?', '¿Cómo se articula un plan de negocio como opción de grado en Ingeniería de Sistemas?', '¿Qué diferencia un modelo tradicional de una plataforma multilateral o un océano azul?']],
    4 => ['Aborda los marcos metodológicos que reducen la incertidumbre en etapas tempranas, centrando el diseño en comprender las necesidades del usuario antes de desarrollar soluciones tecnológicas complejas.',
          'Aplicar metodologías ágiles de emprendimiento tecnológico para estructurar modelos de negocio, diseñar propuestas de valor pertinentes e iniciar el descubrimiento de clientes.',
          'Lean Startup, Design Thinking y Business Model Canvas; diseño de la propuesta de valor; descubrimiento de clientes e identificación de nichos de mercado en TI.',
          'Formulación del modelo de negocio (Canvas) y definición de hipótesis iniciales de valor y segmento de clientes.',
          ['¿Cómo identificar un problema real en el mercado antes de desarrollar software o infraestructura?', '¿Cómo se conectan los nueve bloques del Business Model Canvas con la propuesta de valor?', '¿Qué hipótesis críticas deben formularse en el descubrimiento de clientes?']],
    5 => ['Guía la transición del diseño conceptual a la experimentación en el mercado mediante prototipos funcionales de bajo costo, medición de la respuesta de los usuarios y decisiones basadas en evidencia.',
          'Contrastar las hipótesis del modelo de negocio mediante validación con clientes, prototipado rápido y medición de indicadores clave para iterar o escalar la solución.',
          'Validación de clientes; producto mínimo viable (MVP); prototipado y pruebas piloto; métricas de validación y aprendizaje; marketing 3.0 y 4.0; pivoteo y escalamiento.',
          'Diseño de un prototipo o MVP y recolección de retroalimentación temprana para ajustar la propuesta de negocio.',
          ['¿Qué características debe tener un MVP para validar una hipótesis sin desperdiciar recursos técnicos?', '¿Qué métricas distinguen entre vanidad y tracción real en un producto TI?', '¿Cómo aprovechar el marketing 4.0 para posicionar una solución digital?']],
    6 => ['Integra los aprendizajes del curso en un plan de negocio integral y factible, analizando sectores especializados de alta demanda tecnológica para consolidar la viabilidad técnica, jurídica y financiera del proyecto.',
          'Integrar los componentes estratégicos, técnicos, legales y financieros en un plan de negocio factible orientado a verticales específicas de la industria TI.',
          'Verticales tecnológicas (fintech, legaltech, insurtech, regtech, proptech, wealthtech, femtech, foodtech, cleantech, greentech, biotech, healthtech y retailtech); formulación integral del plan de negocio en TI y sustentación ejecutiva.',
          'Consolidación del documento del plan de negocio en TI y presentación ejecutiva ante el docente y el grupo.',
          ['¿Qué oportunidades y barreras regulatorias presentan industrias como fintech, healthtech o legaltech en Colombia?', '¿Qué componentes financieros garantizan la sostenibilidad de una empresa TI?', '¿Cómo estructurar una presentación efectiva ante inversionistas o jurados académicos?']],
];
foreach ($modulos as $num => [$intro, $objetivo, $temas, $actividad, $preguntas]) {
    $cm = mt_label_with($course, $num, ['padding: 25px 50px', 'eidti-cuerpo', 'eidti-banner']);
    $html = mt_get_intro($cm);
    $corte = strpos($html, '<div style="padding: 25px 50px;">');
    if ($corte === false) {
        $corte = strpos($html, '<div class="eidti-cuerpo"');
    }
    if ($corte === false) {
        cli_error("No encontré el cuerpo de la presentación del módulo en la sección $num");
    }
    preg_match('~@@PLUGINFILE@@/[^"]+\.mp4~', $html, $video);
    $cuerpo = '<div class="eidti-cuerpo" style="padding:16px 8px;' . $f . '">' .
        "<p>$intro</p>" .
        "<p><strong>Objetivo:</strong> $objetivo</p>" .
        "<p><strong>Temas:</strong> $temas</p>" .
        "<p><strong>Actividad esperada:</strong> $actividad</p>" .
        '<p><strong>Preguntas orientadoras:</strong></p><ol>' . implode('', array_map(fn($q) => "<li>$q</li>", $preguntas)) . '</ol>' .
        ($video ? '<div class="eidti-video text-center"><p><em>Video de presentación de la unidad</em></p>' .
            '<video controls style="width:100%;max-width:900px;height:auto;"><source src="' . $video[0] . '" type="video/mp4"></video></div>' : '') .
        '</div>';
    mt_set_intro($cm, substr($html, 0, $corte) . $cuerpo);
}

mt_done($course);
