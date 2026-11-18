<?php
// Grupo 6 · Tarea 5 · Syllabus, bibliografía y material complementario (montaje: Felipe).
// Fuente: Grupo_06_Tarea_05/Verificacion_y_entrega.docx (5.1 a 5.4) y el PDF del syllabus.
// Decisión al montar: NO se enlazan copias completas de libros comerciales alojadas en sitios de terceros
// (theoffice.pe, camarabaq.org.co, archive.org, drive personales, elhacker.info), porque pueden infringir
// derechos de autor. Esas obras quedan con su referencia corregida y «consulta en el Sistema de Bibliotecas UD».
require(__DIR__ . '/lib.php');
$course = mt_course();
mtrace("Grupo 6 · syllabus y bibliografía en el curso {$course->id}");

const BIBLIOTECA = 'https://bibliotecas.udistrital.edu.co/';
$f = "font-family:'Montserrat',Arial,sans-serif;font-size:15px;line-height:1.5;";
$h4 = fn(string $t) => '<h4 style="color:#8C1919;font-family:\'Montserrat\',Arial,sans-serif;font-weight:700;border-bottom:2px solid #950D16;padding-bottom:6px;' . MT_NOBAR . '">' . $t . '</h4>';
$bib = '<a href="' . BIBLIOTECA . '" target="_blank" rel="noopener">Sistema de Bibliotecas UD</a>';

// 5.1 · El recurso «Syllabus» de Recursos tenía el PDF de Cálculo Integral.
$syllabus = MT_ENTREGAS . '/Grupo_06_Tarea_05/2026-01-30 Syllabus Emprendimiento e Ideas de Negocio T.I..pdf';
mt_replace_resource_file(mt_cm($course, 7, 'resource', 'Syllabus'), $syllabus,
    'Syllabus Emprendimiento e Ideas de Negocio TI (AA-FR-003).pdf');

// 5.2 y 5.4 · Bibliografía corregida contra la sección XI del syllabus.
$basica = [
    'Blank, S. y Dorf, B. (2013). <em>El manual del emprendedor: la guía paso a paso para crear una gran empresa</em>. Gestión 2000.',
    'Blank, S. y Dorf, B. (2020). <em>The startup owner\'s manual: the step-by-step guide for building a great company</em>. Wiley.',
    'Osterwalder, A. y Pigneur, Y. (2013). <em>Generación de modelos de negocio</em>. Grupo Planeta.',
    'Osterwalder, A., Pigneur, Y., Bernarda, G., Smith, A. y Papadakos, T. (2015). <em>Diseñando la propuesta de valor: cómo crear los productos y servicios que tus clientes están esperando</em>. Grupo Planeta.',
    'Sánchez, L. (2023). <em>Planeación estratégica para mipymes</em>. Ediciones de la U.',
];
$complementaria = [
    'Alcaraz Rodríguez, R. E. (2020). <em>El emprendedor de éxito</em>. McGraw-Hill Interamericana.',
    'Belausteguigoitia Rius, I. (2017). <em>Empresas familiares: dinámica, equilibrio y consolidación</em>. McGraw-Hill Interamericana.',
    'Caldas Blanco, M. E., Carrión Herráez, R. y Heras Fernández, A. J. (2022). <em>Empresa e iniciativa emprendedora</em>. Editex.',
    'Gámez Gutiérrez, J. (2013). <em>Emprendimiento y creación de empresas: teoría, modelos y casos</em>. Banco de la República.',
    'Murcia Cabra, H. H. (2023). <em>Creación y fortalecimiento de modelos de negocios con resiliencia e innovación</em>. Hipertexto.',
    'Souto Pérez, J. E. (2013). <em>Innovación, emprendimiento y empresas de base tecnológica en España: factores críticos e impacto sobre la competitividad de la economía</em>. Netbiblo. ' .
        '<a href="https://www.madrimasd.org/sites/default/files/informacionidi/biblioteca/publicacion/doc/Innovacion_emprendimiento.pdf" target="_blank" rel="noopener">Texto completo de acceso abierto (madri+d)</a>.',
];
$li = fn(array $items) => '<ul style="' . $f . '">' . implode('', array_map(fn($i) => "<li style=\"margin-bottom:6px;\">$i</li>", $items)) . '</ul>';
$pagina = '<p style="' . $f . '">Referencias de la sección XI del syllabus oficial (código AA-FR-003), revisadas por el grupo 6. ' .
    'Las obras sin enlace se consultan en el ' . $bib . ' (catálogo y bases de datos con tu cuenta institucional).</p>' .
    $h4('Bibliografía básica') . $li($basica) . $h4('Bibliografía complementaria') . $li($complementaria);

$nombre = 'Bibliografía del curso';
$cm = mt_find_by_name($course, 7, 'page', $nombre);
if ($cm) {
    global $DB;
    $DB->set_field('page', 'content', $pagina, ['id' => $cm->instance]);
    mt_log('página «' . $nombre . '» actualizada');
} else {
    mt_create_module($course, 7, 'page', [
        'name' => $nombre, 'intro' => '', 'showdescription' => 0,
        'page' => ['text' => $pagina, 'format' => FORMAT_HTML, 'itemid' => 0],
        'display' => 5, 'printintro' => 0, 'printlastmodified' => 1, 'printheading' => 1,
    ], mt_cm($course, 7, 'resource', 'Plan de trabajo', false));
}

// 5.3 · Material complementario de cada unidad, con para qué sirve cada recurso.
$intro = '<p style="' . $f . '">Estos recursos amplían los contenidos de la unidad. Las obras sin enlace se consultan en el ' . $bib . '.</p>';
$item = fn(string $titulo, string $para) => "<li style=\"margin-bottom:8px;\"><strong>$titulo.</strong> $para</li>";
$material = [
    3 => [
        $item('Souto Pérez (2013), <a href="https://www.madrimasd.org/sites/default/files/informacionidi/biblioteca/publicacion/doc/Innovacion_emprendimiento.pdf" target="_blank" rel="noopener"><em>Innovación, emprendimiento y empresas de base tecnológica</em></a>',
            'Sirve para entender el contexto y el ecosistema: cómo funcionan las empresas de base tecnológica, qué factores determinan su éxito o supervivencia y cómo se relacionan con el entorno institucional y económico.'),
        $item('Blank y Dorf (2013), <em>El manual del emprendedor</em>',
            'Marco inicial para estructurar una startup: diferencia una startup tecnológica de una empresa tradicional y sienta las bases del descubrimiento de los primeros clientes.'),
        $item('Entidades de apoyo', 'Para la normativa y los programas de fomento: <a href="https://www.mipymes.gov.co/" target="_blank" rel="noopener">mipymes.gov.co</a> (Leyes 590 de 2000 y 2069 de 2020), ' .
            '<a href="https://www.innpulsacolombia.com/" target="_blank" rel="noopener">iNNpulsa Colombia</a>, ' .
            '<a href="https://www.sena.edu.co/es-co/trabajo/Paginas/fondo-emprender.aspx" target="_blank" rel="noopener">Fondo Emprender del SENA</a> y ' .
            '<a href="https://www.mintic.gov.co/" target="_blank" rel="noopener">MinTIC</a>.'),
    ],
    4 => [
        $item('Blank y Dorf (2013), <em>El manual del emprendedor</em>',
            'Guía paso a paso del desarrollo de clientes: probar hipótesis en el mercado, construir y medir un producto mínimo viable e iterar sin desperdiciar recursos.'),
        $item('Osterwalder y Pigneur (2013), <em>Generación de modelos de negocio</em>',
            'Estructura la lógica del negocio en los nueve bloques del Business Model Canvas y conecta la solución técnica con canales, ingresos, costos y recursos clave.'),
        $item('Osterwalder et al. (2015), <em>Diseñando la propuesta de valor</em>',
            'Profundiza en el encaje entre el perfil del cliente y el producto tecnológico, para asegurar que el software resuelva un problema real antes de prototipar.'),
    ],
    5 => [
        $item('Blank y Dorf (2013), <em>El manual del emprendedor</em>',
            'Dirige el ciclo de prueba e iteración: experimentos con MVP y pruebas piloto, métricas de validación con clientes y decisión de pivotar o escalar.'),
        $item('Osterwalder et al. (2015), <em>Diseñando la propuesta de valor</em>',
            'Permite verificar con datos reales si el prototipo alivia las frustraciones y crea las alegrías del segmento elegido.'),
        $item('Sánchez (2023), <em>Planeación estratégica para mipymes</em>',
            'Traduce los resultados validados en un plan de negocio formal, con objetivos estratégicos, indicadores de gestión y viabilidad organizacional en Colombia.'),
    ],
    6 => [
        $item('Recursos sobre industrias y nichos de TI',
            'Portales sectoriales y gremiales (MinTIC, Colombia TIC, ACIS) para caracterizar el sector, entender la normativa de cada industria y sustentar con cifras la pertinencia de la solución.'),
        $item('Kim y Mauborgne, <em>La estrategia del océano azul</em>',
            'Ayuda a diferenciar la propuesta de valor en industrias saturadas, compitiendo por innovación en valor y no solo por precio.'),
        $item('Manual de elaboración de planes de negocio',
            'Guía operativa para integrar los componentes técnicos, financieros, organizacionales y legales del plan de negocio final.'),
    ],
];
foreach ($material as $num => $items) {
    $cm = mt_label_with($course, $num, ['Material complementario']);
    [$cabecera] = mt_split_header(mt_get_intro($cm));
    if ($cabecera === '') {
        cli_error("No encontré el encabezado de Material complementario en la sección $num");
    }
    $nuevo = $cabecera . $intro . '<ul style="' . $f . '">' . implode('', $items) . '</ul>';
    if ($num === 6) {
        // Se conservan los enlaces sectoriales que ya tenía la unidad 4, sin la redirección de Google.
        $nuevo .= '<p style="' . $f . '">Enlaces sectoriales: <a href="https://www.bbva.com/es/innovacion/" target="_blank" rel="noopener">BBVA Innovación</a>, ' .
            '<a href="https://colombiatic.mintic.gov.co/" target="_blank" rel="noopener">Colombia TIC (MinTIC)</a> y ' .
            '<a href="https://acis.org.co/" target="_blank" rel="noopener">Asociación Colombiana de Ingenieros de Sistemas (ACIS)</a>.</p>';
    }
    mt_set_intro($cm, $nuevo);
}

mt_done($course);
