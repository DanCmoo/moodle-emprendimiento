<?php
// Grupo 2 · Tarea 1 · Identidad visual del aula (montaje: Felipe).
// Fuente: Grupo_02_Tarea_01/Verificacion_y_entrega.docx (1.1 a 1.4) y Plantillas_HTML_Moodle.txt.
require(__DIR__ . '/lib.php');
$course = mt_course();
mtrace("Grupo 2 · identidad visual en el curso {$course->id}");

// Paleta y letras tomadas de udistrital.edu.co (noviembre 2026): vino #8C1919, rojo de acento #950D16,
// franja oscura #192129; títulos y texto en Montserrat, acento serif en Lora. Reemplaza el dorado y
// Raleway/Open Sans de la guía original del grupo 2, por indicación del equipo Moodle.
const VINO = MT_VINO;
const ACENTO = MT_ROJO;
const OSCURO = MT_OSCURO;

function g02_banner(string $etiqueta, string $icono, string $titulo, string $subtitulo, string $tag = 'h3'): string {
    return '<div class="eidti-banner" style="background:' . VINO . ';color:#FFFFFF;border-radius:10px;border-bottom:6px solid ' . OSCURO .
        ';padding:24px 28px;margin-bottom:16px;font-family:' . MT_FUENTE . ';">' .
        '<span style="display:inline-block;background:#FFFFFF;color:' . VINO . ';font-weight:700;font-size:12px;' .
        'letter-spacing:.12em;border-radius:4px;padding:2px 9px;">' . $etiqueta . '</span>' .
        "<$tag style=\"color:#FFFFFF;font-family:" . MT_FUENTE . ";font-weight:700;font-size:28px;line-height:1.2;margin:8px 0 4px;" . MT_NOBAR . "\">" .
        '<i class="fa fa-solid ' . $icono . '" aria-hidden="true"></i>&nbsp; ' . $titulo . "</$tag>" .
        '<p style="color:#FFFFFF;font-family:' . MT_SERIF . ';font-style:italic;font-size:16px;margin:0;">' . $subtitulo . '</p></div>';
}

function g02_subtitulo(string $icono, string $texto): string {
    return '<h4 class="eidti-subtitulo" style="color:' . VINO . ';font-family:' . MT_FUENTE . ';font-weight:700;font-size:20px;' .
        'border-bottom:2px solid ' . ACENTO . ';padding-bottom:6px;margin-top:12px;' . MT_NOBAR . '"><i class="fa fa-solid ' . $icono .
        '" aria-hidden="true"></i>&nbsp; ' . $texto . '</h4>';
}

// 1. Nombres de las secciones (punto 1.2) e iconos de mosaico (punto 1.1; Tiles solo trae Font Awesome 4).
$unidades = [
    3 => ['Unidad 1. Marcos para el emprendimiento en TI', 'university', 'fa-landmark', 'UNIDAD 1',
          'Marcos para el emprendimiento en TI', 'Entornos institucional, legal y financiero que enmarcan un emprendimiento tecnológico.'],
    4 => ['Unidad 2. Emprendimiento en TI', 'lightbulb-o', 'fa-lightbulb', 'UNIDAD 2',
          'Emprendimiento en TI', 'El emprendimiento como opción de grado, habilidades y metodologías para emprender.'],
    5 => ['Unidad 3. Validación de mercado, métricas e iteración', 'line-chart', 'fa-chart-line', 'UNIDAD 3',
          'Validación de mercado, métricas e iteración', 'Validar la idea con clientes reales, medir resultados, iterar y escalar el modelo.'],
    6 => ['Unidad 4. Industrias de TI y consolidación del plan de negocio', 'briefcase', 'fa-briefcase', 'UNIDAD 4',
          'Industrias de TI y consolidación del plan de negocio', 'Sectores de TI como fintech, healthtech y greentech, y cierre del plan de negocio.'],
];
mt_set_section($course, 2, ['name' => 'Espacios de interacción']);
foreach ($unidades as $num => $u) {
    mt_set_section($course, $num, ['name' => $u[0]]);
    mt_tile_icon($course, $num, $u[1]);
}
mt_tile_icon($course, 1, 'flag-o');
mt_tile_icon($course, 2, 'comments-o');
mt_tile_icon($course, 7, 'book');
mt_tile_icon($course, 8, 'flag-checkered');

// 2. Banner de bienvenida: reemplaza la barra gris de la sección inicial.
global $DB;
$s0 = $DB->get_record('course_sections', ['course' => $course->id, 'section' => 0]);
$banner = g02_banner('BIENVENIDOS', 'fa-lightbulb', 'Emprendimiento e Ideas de Negocio en TI',
    'Un espacio para formar profesionales de TI con visión emprendedora y global.', 'h2');
$nuevo = preg_replace('~<div style="border-radius: 10px 10px 150px 150px;.*?</div>~s', $banner, $s0->summary, 1, $count);
if ($count) {
    mt_set_section($course, 0, ['summary' => $nuevo]);
} else {
    mt_log('sección 0: la barra gris ya no estaba (sin cambios)');
}

// 3. Cada «Presentación del módulo» abre con el banner de su unidad (reemplaza la caja gris del título).
foreach ($unidades as $num => $u) {
    $cm = mt_label_with($course, $num, ['padding: 25px 50px', 'eidti-cuerpo', 'eidti-banner']);
    $html = mt_get_intro($cm);
    if (!str_contains($html, 'eidti-banner')) {
        $html = preg_replace('~^\s*<div style="border-radius.*?</h4>\s*</div>~s', g02_banner($u[3], $u[2], $u[4], $u[5]), $html, 1);
        mt_set_intro($cm, $html);
    }
}

// 4. Las demás cajas grises con ícono SVG pasan a ser títulos de bloque en vino con ícono Font Awesome.
//    Los SVG se quitan porque el filtro de emoticonos de Moodle convierte secuencias como «8-)» de su
//    dibujo en caritas y rompe el HTML (aparecía texto como «35-2.107-.692…» en la página).
$n = mt_replace_everywhere($course, [
    '~<div style="[^"]*background-color:\s*(?:#5b5b5b|#8C1919)[^"]*"[^>]*>.*?<h4[^>]*>(.*?)</h4>\s*</div>~is' =>
        fn($m) => mt_titulo(trim(preg_replace('/\s+/u', ' ', html_entity_decode(strip_tags(preg_replace('~<svg.*?</svg>~s', '', $m[1])),
            ENT_QUOTES | ENT_HTML5, 'UTF-8')))),
], true);
mt_log("cajas de título convertidas en $n etiquetas");

// 5. Sin sangría: el curso original traía etiquetas sangradas que desbordan el banner y anidan el índice.
$DB->set_field('course_modules', 'indent', 0, ['course' => $course->id]);

// 6. Subtítulos de bloque de cada unidad: Inicio de la unidad, Temas y Evaluación y cierre.
foreach ($unidades as $num => $u) {
    $bloques = [
        [['eidti-banner'], 'fa-flag', 'Inicio de la unidad'],
        [['eidti-contenido', 'Contenido temático', 'Temas a Desarrollar'], 'fa-book-open', 'Temas'],
        [['Actividades de aprendizaje', 'Resultado de Aprendizaje', 'Resultado de aprendizaje'], 'fa-clipboard-check', 'Evaluación y cierre'],
    ];
    foreach ($bloques as $b) {
        $texto = $b[2];
        $existe = false;
        foreach (get_fast_modinfo($course)->get_section_info($num)->get_sequence_cm_infos() as $c) {
            if ($c->modname === 'label' && str_contains(mt_get_intro($c), 'eidti-subtitulo') &&
                    str_contains(mt_get_intro($c), $texto . '</h4>')) {
                $existe = true;
            }
        }
        if ($existe) {
            continue;
        }
        $antes = isset($b[3]) ? mt_cm($course, $num, $b[3], '') : mt_label_with($course, $num, $b[0]);
        mt_create_module($course, $num, 'label', ['intro' => g02_subtitulo($b[1], $texto), 'name' => $texto], $antes);
    }
}

// 7. Nombres de recursos, etiquetas y cuestionarios (tabla «Nombres corregidos» del punto 1.2).
$renombres = [
    3 => ['resource' => [
            'ENTORNO INSTITUTCIONAL' => 'Entorno institucional',
            'ENTORNO DE MODELOS DE NEGOCIOS' => 'Entorno de modelos de negocio e innovación',
            'ENTORNO DEL ECOSISTEMA' => 'Entorno del ecosistema emprendedor',
            'MECANISMOS DE FINANCIACION' => 'Mecanismos de financiación',
            'ENTORNO LEGAL' => 'Entorno legal',
            'CASOS DE EXITOS' => 'Casos de éxito de emprendimiento',
          ], 'quiz' => ['Cuestionario' => 'Cuestionario Unidad 1. Marcos para el emprendimiento en TI']],
    4 => ['resource' => [
            'ENTORNO DE OPCION DE GRADO' => 'Entorno de opción de grado y planes de negocio',
            'ENTORNO DE CALIDAD EDUCATIVA' => 'Entorno de calidad educativa',
            'HABILIDADES BLANDAS' => 'Habilidades blandas y cultura emprendedora',
            'METODOLOGIAS DE EMPRENDIMIENTO' => 'Metodologías de emprendimiento tecnológico',
          ], 'quiz' => ['Cuestionario' => 'Cuestionario Unidad 2. Emprendimiento en TI']],
    5 => ['resource' => [
            'DESARROLLO DEL CLIENTE' => 'Desarrollo del cliente',
            'MARKETING Y CREACION' => 'Marketing y creación de valor',
            'PLAN DE NEGOCIO Y METRICAS' => 'Plan de negocio y métricas',
            'ESTRATEGIAS DE ESCALAMIENTO' => 'Estrategias de escalamiento',
          ], 'quiz' => ['Cuestionario' => 'Cuestionario Unidad 3. Validación de mercado, métricas e iteración']],
    6 => ['resource' => [
            'FINTECH' => 'Fintech, Legaltech e Insurtech',
            'REGTECH' => 'Regtech, Proptech y Wealthtech',
            'FEMTECH' => 'Femtech, Foodtech y Cleantech',
            'GREETECH' => 'Greentech y Biotech',
            'HEALTHTECH' => 'Healthtech y Retailtech',
          ], 'quiz' => ['Cuestionario' => 'Cuestionario Unidad 4. Industrias de TI y consolidación del plan de negocio']],
];
foreach ($renombres as $num => $tipos) {
    foreach ($tipos as $modname => $pares) {
        foreach ($pares as $match => $nuevo) {
            $cm = mt_cm($course, $num, $modname, $match, false);
            if ($cm && $cm->name !== $nuevo) {
                mt_rename($cm, $nuevo);
            }
        }
    }
}
$n = mt_replace_everywhere($course, [
    'Mapa de la Unidad' => 'Mapa de la unidad',
    'Espacios de Iteración y Comunicación' => 'Espacios de interacción y comunicación',
]);
mt_log("títulos de etiqueta corregidos en $n etiquetas");

mt_done($course);
