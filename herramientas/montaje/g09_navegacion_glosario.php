<?php
// Grupo 9 · Tarea 8 · Navegación y consulta (montaje: Felipe).
// Fuente: Grupo_09_Tarea_08/Verificacion_y_entrega.docx (8.1 a 8.4).
// Se monta: 8.1 índice de palabras clave con enlace a cada unidad y 8.2 glosario de entidades de apoyo.
// Pendiente del docente: 8.3 (asistente en un cuaderno de Gemini con cuenta personal) y 8.4 (propuesta de
// reorganizar el acordeón; el ejemplo es de la Unidad 2, que monta el grupo 14).
require(__DIR__ . '/lib.php');
global $DB;
$course = mt_course();
mtrace("Grupo 9 · navegación y glosario en el curso {$course->id}");
$f = "font-family:'Montserrat',Arial,sans-serif;font-size:15px;line-height:1.5;";

// 8.2 · Glosario de entidades de apoyo al emprendimiento (Recursos).
$entidades = [
    ['iNNpulsa Colombia', 'Programas de apoyo al emprendimiento, convocatorias, acompañamiento para el crecimiento empresarial y fortalecimiento de la innovación.', 'Emprendedores, startups y micro, pequeñas y medianas empresas.', 'https://www.innpulsacolombia.com/'],
    ['Fondo Emprender (SENA)', 'Capital semilla para crear empresas; los recursos pueden ser condonables si se cumplen las metas del plan de negocio.', 'Emprendedores que cumplen los perfiles y requisitos de cada convocatoria.', 'https://www.sena.edu.co/empleabilidad/fondo-emprender'],
    ['Apps.co (MinTIC)', 'Formación, mentorías y acompañamiento para validar ideas, desarrollar productos digitales y fortalecer negocios TIC.', 'Emprendedores digitales y equipos con ideas de negocio tecnológico.', 'https://apps.co/'],
    ['Cámara de Comercio de Bogotá (CCB)', 'Asesorías, mentorías, incubación y aceleración, formación empresarial y conexión con redes de inversión.', 'Emprendedores, startups y empresas que quieren crear, formalizar o crecer.', 'https://www.ccb.org.co/'],
    ['Bancóldex', 'Financiación empresarial: créditos para capital de trabajo, inversión y modernización.', 'Empresas colombianas de todos los tamaños.', 'https://www.bancoldex.com/'],
    ['Ministerio de Ciencia, Tecnología e Innovación (MinCiencias)', 'Convocatorias, programas de innovación empresarial, transferencia de conocimiento y apoyo a la propiedad intelectual.', 'Investigadores, universidades, empresas y emprendimientos de ciencia, tecnología e innovación.', 'https://minciencias.gov.co/'],
    ['Ruta N Medellín', 'Conexión con empresas e inversionistas, aceleración, innovación abierta y desarrollo de capacidades.', 'Emprendimientos de base tecnológica, científica e innovadora, en especial del ecosistema de Medellín.', 'https://rutanmedellin.org/'],
    ['Connect Bogotá', 'Programas de emprendimiento e innovación abierta y conexión entre startups, empresas y universidades.', 'Emprendedores innovadores, startups, universidades e investigadores.', 'https://www.connectbogota.org/'],
    ['HubBOG', 'Aceleración, mentorías, asesorías especializadas, networking y conexión con inversionistas.', 'Emprendedores digitales y startups tecnológicas.', 'https://www.hubbog.com/'],
    ['Endeavor Colombia', 'Acompañamiento estratégico, mentores, redes empresariales e inversión para emprendimientos de alto impacto.', 'Emprendedores con modelos de alto potencial de crecimiento.', 'https://colombia.endeavor.org/'],
    ['Dirección Nacional de Derecho de Autor (DNDA)', 'Registro de obras protegidas por derecho de autor, incluido el software.', 'Desarrolladores de software, autores y empresas tecnológicas.', 'https://www.derechodeautor.gov.co/'],
    ['Superintendencia de Industria y Comercio (SIC)', 'Propiedad industrial: registro de marcas, patentes y otros mecanismos de protección.', 'Emprendedores, empresas e inventores que quieren proteger marcas o desarrollos.', 'https://www.sic.gov.co/'],
    ['Fondo Nacional de Garantías (FNG)', 'Garantías que facilitan el acceso al crédito cuando la empresa no tiene respaldo suficiente.', 'Micro, pequeñas y medianas empresas.', 'https://www.fng.gov.co/'],
    ['ProColombia', 'Internacionalización, promoción de exportaciones y conexión con mercados internacionales.', 'Empresas y emprendedores que quieren vender en el exterior.', 'https://procolombia.co/'],
    ['Confecámaras', 'Articulación de las cámaras de comercio, fortalecimiento empresarial y apoyo a la formalización.', 'Emprendedores, empresarios y organizaciones del desarrollo empresarial.', 'https://confecamaras.org.co/'],
];
$nombre = 'Glosario de entidades de apoyo al emprendimiento';
$cm = mt_find_by_name($course, 7, 'glossary', $nombre);
if (!$cm) {
    $cm = mt_create_module($course, 7, 'glossary', [
        'name' => $nombre,
        'intro' => '<p style="' . $f . '">Entidades y organizaciones que apoyan la creación, financiación, innovación y crecimiento de emprendimientos en Colombia, con énfasis en proyectos tecnológicos. Usa la búsqueda o el índice alfabético.</p>',
        'showdescription' => 1, 'globalglossary' => 0, 'mainglossary' => 0, 'defaultapproval' => 1, 'editalways' => 0,
        'allowduplicatedentries' => 0, 'allowcomments' => 0, 'allowprintview' => 1, 'usedynalink' => 0,
        'displayformat' => 'fullwithoutauthor', 'approvaldisplayformat' => 'default', 'entbypage' => 20,
        'showalphabet' => 1, 'showall' => 1, 'showspecial' => 0, 'rsstype' => 0, 'rssarticles' => 0,
        'assessed' => 0, 'completionentries' => 0,
    ]);
}
$glossaryid = $cm->instance;
foreach ($entidades as [$concepto, $ofrece, $para, $url]) {
    $def = "<p><strong>¿Qué ofrece?</strong> $ofrece</p><p><strong>¿A quién está dirigido?</strong> $para</p>" .
        "<p><strong>Enlace oficial:</strong> <a href=\"$url\" target=\"_blank\" rel=\"noopener\">$url</a></p>";
    $prev = $DB->get_record('glossary_entries', ['glossaryid' => $glossaryid, 'concept' => $concepto]);
    $rec = ['glossaryid' => $glossaryid, 'userid' => get_admin()->id, 'concept' => $concepto, 'definition' => $def,
        'definitionformat' => FORMAT_HTML, 'definitiontrust' => 1, 'approved' => 1, 'timemodified' => time()];
    if ($prev) {
        $DB->update_record('glossary_entries', (object)(['id' => $prev->id] + $rec));
    } else {
        $DB->insert_record('glossary_entries', (object)($rec + ['timecreated' => time(), 'teacherentry' => 1,
            'sourceglossaryid' => 0, 'usedynalink' => 0, 'casesensitive' => 0, 'fullmatch' => 0]));
    }
}
mt_log(count($entidades) . ' entidades en el glosario');

// 8.1 · Índice de palabras clave con enlace a la unidad donde se tratan.
$secid = fn(int $n) => $DB->get_field('course_sections', 'id', ['course' => $course->id, 'section' => $n]);
$link = fn(int $n, string $t) => '<a href="' . (new moodle_url('/course/section.php', ['id' => $secid($n)]))->out(false) . '">' . $t . '</a>';
$indice = [
    [3, 'Unidad 1. Marcos para el emprendimiento en TI', ['Emprendimiento digital', 'Modelo de negocio', 'Innovación tecnológica', 'Ecosistema emprendedor', 'Cadena de valor', 'Transformación digital', 'Tendencias tecnológicas', 'Estrategia competitiva', 'Gobierno corporativo de TI']],
    [4, 'Unidad 2. Emprendimiento en TI', ['Startup', 'Producto mínimo viable (MVP)', 'Metodologías ágiles', 'Design Thinking', 'Lean Startup', 'Propuesta de valor', 'Modelo Canvas', 'Escalabilidad', 'Pivoteo']],
    [5, 'Unidad 3. Validación de mercado, métricas e iteración', ['Validación de mercado', 'Métricas clave (KPI)', 'Iteración', 'Segmentación de clientes', 'Pruebas de usuario', 'Costo de adquisición de clientes (CAC)', 'Valor del tiempo de vida del cliente (LTV)', 'Retroalimentación del consumidor']],
    [6, 'Unidad 4. Industrias de TI y consolidación del plan de negocio', ['Industria de TI', 'Plan de negocio', 'Financiamiento y capital de riesgo', 'Pitch deck', 'Propiedad intelectual', 'Monetización', 'Flujo de caja']],
    [1, 'Presentación del curso', ['Lineamientos generales', 'Syllabus', 'Plan de trabajo y cronograma']],
    [2, 'Espacios de interacción', ['Foros de presentación, novedades y ayuda']],
];
$html = '<p style="' . $f . '">Busca un concepto y entra directamente a la unidad donde se trabaja. Para las entidades de apoyo, consulta el «' . $nombre . '».</p>' .
    '<table class="table table-sm" style="' . $f . '"><thead><tr><th>Palabra clave</th><th>Dónde se trabaja</th></tr></thead><tbody>';
foreach ($indice as [$num, $titulo, $palabras]) {
    foreach ($palabras as $p) {
        $html .= "<tr><td>$p</td><td>" . $link($num, $titulo) . '</td></tr>';
    }
}
$html .= '</tbody></table>';
$nombreindice = 'Índice de palabras clave';
$pg = mt_find_by_name($course, 7, 'page', $nombreindice);
if ($pg) {
    $DB->set_field('page', 'content', $html, ['id' => $pg->instance]);
    mt_log("página «{$nombreindice}» actualizada");
} else {
    mt_create_module($course, 7, 'page', [
        'name' => $nombreindice, 'intro' => '', 'showdescription' => 0,
        'page' => ['text' => $html, 'format' => FORMAT_HTML, 'itemid' => 0],
        'display' => 5, 'printintro' => 0, 'printlastmodified' => 0, 'printheading' => 1,
    ]);
}

mt_done($course);
