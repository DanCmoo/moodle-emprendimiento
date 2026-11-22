#!/usr/bin/env python3
"""Build a restorable Moodle 5.1 course backup from the supplied course backup and materials.

Uses only Python's standard library. Source student submissions stay outside Git; the
resulting .mbz is a local build artifact and is ignored by .gitignore.
"""
from __future__ import annotations

import argparse
import hashlib
import html
import mimetypes
import os
import re
import tarfile
import urllib.parse
import zipfile
from pathlib import Path
from xml.etree import ElementTree as ET

ROOT = Path(__file__).resolve().parents[1]
CONTENT = ROOT / "contenido"
SOURCE = ROOT / "Moodle-Copia.mbz"
DEFAULT_OUTPUT = ROOT / "Moodle-Emprendimiento-2026-3.mbz"

BRAND = "#8C1919"
DARK = "#192129"
GOLD = "#B49739"
BASE_STYLE = f"font-family:Montserrat,'Helvetica Neue',Arial,sans-serif;color:#272727;line-height:1.65;max-width:1100px;margin:auto;"

SECTIONS = {
    0: "Bienvenida",
    1: "Presentación del curso",
    2: "Comunidad y comunicación",
    3: "Unidad 1 · Marcos para el emprendimiento en TI",
    4: "Unidad 2 · Emprendimiento en TI",
    5: "Unidad 3 · Validación de mercado, métricas e iteración",
    6: "Unidad 4 · Industrias de TI y consolidación del plan de negocio",
    7: "Recursos de consulta",
    8: "Cierre y plan de continuidad",
}

RESOURCE_UPDATES = {
    "resource_535432": {
        "name": "Syllabus oficial · Emprendimiento e Ideas de Negocio en TI",
        "intro": "Documento oficial del curso (formato AA-FR-003). Consulta aquí los resultados de aprendizaje, contenidos, metodología y bibliografía.",
        "files": ["Grupo_06_Tarea_05/2026-01-30 Syllabus Emprendimiento e Ideas de Negocio T.I..pdf"],
    },
    "resource_535433": {
        "name": "Plan de trabajo y cronograma de 16 semanas",
        "intro": "Ruta de trabajo alineada con los cuatro módulos y el syllabus oficial.",
        "files": ["generated/plan-trabajo.html", "Grupo_04_Tarea_03/PlanDeTrabajoImagen.png"],
    },
    "resource_535403": {
        "name": "Opción de grado y calidad educativa · Paquete SCORM",
        "intro": "Paquete de aprendizaje del grupo 13 sobre opciones de grado, el caso HIRE.IT y calidad educativa. Descarga el ZIP para usarlo en un entorno Moodle con actividad SCORM habilitada.",
        "files": ["Grupo_13_Tarea_11/Tarea_11_Grupo13_SCORM_Completo (1).zip"],
    },
    "resource_543205": {
        "name": "Habilidades blandas y cultura emprendedora",
        "intro": "Material de la Unidad 2: casos para analizar, autoevaluación y guía de proyección laboral.",
        "files": [
            "Grupo_15_Tarea_12/12.1_Casos_habilidades_blandas.docx",
            "Grupo_15_Tarea_12/12.2_Autoevaluacion_habilidades_blandas.docx",
            "Grupo_15_Tarea_12/12.3_Guía de proyección laboral en empresas multinacionales.pdf",
        ],
    },
    "resource_543197": {
        "name": "Mecanismos de financiación · Caso aplicado",
        "intro": "Caso aplicado de financiación de emprendimientos tecnológicos. Revisa las etapas, alternativas y criterios antes de preparar tu propuesta.",
        "files": ["Grupo_12_Tarea_10/Caso de Financiacion.pdf"],
    },
    "resource_543209": {
        "name": "Plan de negocio dinámico para TI",
        "intro": "Herramienta editable para formular hipótesis, validar con clientes, medir resultados e iterar un emprendimiento de TI.",
        "files": [
            "generated/plan-negocio-dinamico.html",
            "Grupo_17_Tarea_14/Resumen Visual Desarrollo del Cliente.svg",
            "Grupo_17_Tarea_14/Resumenes Visuales Marketing y Creacion de Valor.svg",
            "Grupo_17_Tarea_14/Validación de Mercado y Escalamiento en TI.png",
        ],
    },
    "resource_535425": {
        "name": "Fintech, Legaltech e Insurtech",
        "intro": "Lecturas de la Unidad 4 sobre tecnología aplicada a servicios financieros, jurídicos y aseguradores.",
        "files": ["generated/fintech-legaltech-insurtech.html", "generated/plantilla-plan-sector.html"],
    },
    "resource_547415": {
        "name": "Regtech, Proptech y Wealthtech",
        "intro": "Lecturas de la Unidad 4 sobre cumplimiento, innovación inmobiliaria e inversiones digitales.",
        "files": ["generated/regtech-proptech-wealthtech.html"],
    },
    "resource_547416": {
        "name": "Femtech, Foodtech y Cleantech",
        "intro": "Lectura sobre emprendimientos tecnológicos para salud de las mujeres, alimentos y sostenibilidad.",
        "files": ["Grupo_20_Tarea_17/FEMTECH-FOODTECH-CLEANTECH.pdf"],
    },
    "resource_547417": {
        "name": "Greentech y Biotech",
        "intro": "Lectura sobre tecnologías ambientales y biotecnología.",
        "files": ["Grupo_20_Tarea_17/GreenTech-BioTech.pdf"],
    },
    "resource_547418": {
        "name": "Healthtech y Retailtech",
        "intro": "Lectura sobre innovación tecnológica en salud y comercio minorista.",
        "files": ["Grupo_20_Tarea_17/HealthTech-RetailTech.pdf"],
    },
}

GENERATED_SOURCE_PATHS = {
    "Grupo_02_Tarea_01/Plantillas_HTML_Moodle.txt",
    "Grupo_04_Tarea_03/Entrega_Tarea3_Grupo4_EmprendimientoTI.docx",
    "Grupo_17_Tarea_14/Plan_Negocio_Dinamico_TI.docx",
    "Grupo_19_Tarea_16/Fintech_Legaltech_Insurtech_Reorganizado_Grupo19-v4.docx",
    "Grupo_19_Tarea_16/Regtech_Proptech_Wealthtech_Reorganizado_Grupo19-v4.docx",
    "Grupo_19_Tarea_16/Plantilla_Sector_Plan_Negocio_Fintech_Grupo19-v5.docx",
}


def validate_content_inventory(declared: set[str]) -> tuple[int, int]:
    actual = {p.relative_to(CONTENT).as_posix() for p in CONTENT.rglob("*") if p.is_file()}
    excluded = {
        path for path in actual
        if "Verificacion_y_entrega" in path
        or "/Fallo_" in path
        or path.endswith("19.4_Registro_revision_estudiante_moodle.docx")
    }
    missing = declared - actual
    unclassified = actual - declared - excluded
    overlap = declared & excluded
    if missing or unclassified or overlap:
        raise ValueError(
            "Inventario de contenido sin clasificar. "
            f"Faltan en SharePoint: {sorted(missing)}; "
            f"sin destino: {sorted(unclassified)}; incluidos por error: {sorted(overlap)}"
        )
    return len(declared), len(excluded)


def read_docx(path: Path) -> list[str]:
    """Extract readable paragraphs/tables without requiring python-docx."""
    with zipfile.ZipFile(path) as docx:
        root = ET.fromstring(docx.read("word/document.xml"))
    ns = {"w": "http://schemas.openxmlformats.org/wordprocessingml/2006/main"}
    body = root.find("w:body", ns)
    result: list[str] = []
    if body is None:
        return result
    for child in body:
        tag = child.tag.rsplit("}", 1)[-1]
        if tag == "p":
            text = "".join(t.text or "" for t in child.findall(".//w:t", ns)).strip()
            if text:
                result.append(text)
        elif tag == "tbl":
            for row in child.findall("w:tr", ns):
                cells = ["".join(t.text or "" for t in c.findall(".//w:t", ns)).strip() for c in row.findall("w:tc", ns)]
                if any(cells):
                    result.append(" | ".join(cells))
    return result


def docx_html(path: Path, title: str) -> str:
    lines = read_docx(path)
    body: list[str] = []
    for line in lines:
        escaped = html.escape(line)
        if len(line) < 100 and (line.isupper() or line.startswith(("PUNTO ", "ACTIVIDAD ", "UNIDAD ", "SECCIÓN "))):
            body.append(f'<h2 style="color:{BRAND};font-size:1.2rem;margin:1.2rem 0 .5rem">{escaped}</h2>')
        elif " | " in line:
            cells = "".join(f'<td style="border:1px solid #ddd;padding:.5rem">{html.escape(cell)}</td>' for cell in line.split(" | "))
            body.append(f'<table style="border-collapse:collapse;width:100%;margin:.7rem 0"><tr>{cells}</tr></table>')
        else:
            body.append(f'<p style="margin:.5rem 0">{escaped}</p>')
    return f'''<!doctype html><html lang="es"><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>{html.escape(title)}</title><body style="{BASE_STYLE}padding:1rem 1.25rem">
<header style="border-left:6px solid {BRAND};background:#f7f4f2;padding:1rem 1.2rem;margin-bottom:1.25rem"><h1 style="margin:0;color:{BRAND};font-size:1.5rem">{html.escape(title)}</h1><p style="margin:.35rem 0 0;color:#555">Emprendimiento e Ideas de Negocio en TI · Universidad Distrital</p></header>
{''.join(body)}</body></html>'''


def card(title: str, body: str) -> str:
    return f'<article style="border:1px solid #e3dddd;border-top:4px solid {BRAND};border-radius:8px;padding:1rem;margin:.8rem 0;background:white"><h3 style="margin:0 0 .45rem;color:{BRAND};font-size:1.12rem">{title}</h3><div>{body}</div></article>'


def link_html(filename: str, label: str) -> str:
    return f'<a href="@@PLUGINFILE@@/{urllib.parse.quote(filename)}" style="color:{BRAND};font-weight:600">{html.escape(label)}</a>'


def pluginfile(filename: str) -> str:
    return "@@PLUGINFILE@@/" + urllib.parse.quote(filename)


def make_generated() -> dict[str, bytes]:
    generated: dict[str, bytes] = {}
    plan4 = CONTENT / "Grupo_04_Tarea_03" / "Entrega_Tarea3_Grupo4_EmprendimientoTI.docx"
    plan17 = CONTENT / "Grupo_17_Tarea_14" / "Plan_Negocio_Dinamico_TI.docx"
    fintech = CONTENT / "Grupo_19_Tarea_16" / "Fintech_Legaltech_Insurtech_Reorganizado_Grupo19-v4.docx"
    regtech = CONTENT / "Grupo_19_Tarea_16" / "Regtech_Proptech_Wealthtech_Reorganizado_Grupo19-v4.docx"
    template = CONTENT / "Grupo_19_Tarea_16" / "Plantilla_Sector_Plan_Negocio_Fintech_Grupo19-v5.docx"
    def add(out: str, src: Path, title: str):
        if not src.is_file():
            raise FileNotFoundError(f"Falta el material requerido: {src.relative_to(ROOT)}")
        generated[out] = docx_html(src, title).encode("utf-8")
    add("generated/plan-trabajo.html", plan4, "Plan de trabajo y cronograma")
    add("generated/plan-negocio-dinamico.html", plan17, "Plan de negocio dinámico para TI")
    add("generated/fintech-legaltech-insurtech.html", fintech, "Fintech, Legaltech e Insurtech")
    add("generated/regtech-proptech-wealthtech.html", regtech, "Regtech, Proptech y Wealthtech")
    add("generated/plantilla-plan-sector.html", template, "Del sector tecnológico al plan de negocio")
    html_plan = generated["generated/plan-trabajo.html"].decode("utf-8")
    html_plan = html_plan.replace("</body>", f'<figure style="margin:1.5rem 0;text-align:center"><img src="{pluginfile("PlanDeTrabajoImagen.png")}" alt="Infografía del plan de trabajo" style="max-width:100%;height:auto"><figcaption>Infografía del plan de trabajo</figcaption></figure></body>')
    generated["generated/plan-trabajo.html"] = html_plan.encode("utf-8")
    dynamic_plan = generated["generated/plan-negocio-dinamico.html"].decode("utf-8")
    visual_assets = [
        "Resumen Visual Desarrollo del Cliente.svg",
        "Resumenes Visuales Marketing y Creacion de Valor.svg",
        "Validación de Mercado y Escalamiento en TI.png",
    ]
    visual_cards = "".join(
        card(html.escape(Path(name).stem), f'<a href="{pluginfile(name)}" style="color:{BRAND}">Abrir recurso visual</a>')
        for name in visual_assets
    )
    dynamic_plan = dynamic_plan.replace("</body>", f'<section><h2 style="color:{BRAND}">Recursos visuales de la unidad</h2>{visual_cards}</section></body>')
    generated["generated/plan-negocio-dinamico.html"] = dynamic_plan.encode("utf-8")
    template_text = (CONTENT / "Grupo_02_Tarea_01" / "Plantillas_HTML_Moodle.txt").read_text(encoding="utf-8-sig")
    template_text = re.sub(r"(?m)^Grupo 2:.*(?:\r?\n|$)", "", template_text)
    generated["generated/plantillas-html-moodle.txt"] = template_text.encode("utf-8")
    return generated


def find_member(names: set[str], suffix: str) -> str:
    hits = [n for n in names if n.endswith(suffix)]
    if len(hits) != 1:
        raise ValueError(f"Esperaba un único miembro *{suffix}, encontré {hits}")
    return hits[0]


def get_text(root: ET.Element, path: str, default: str = "") -> str:
    node = root.find(path)
    return (node.text or default) if node is not None else default


def xml_bytes(root: ET.Element) -> bytes:
    return ET.tostring(root, encoding="utf-8", xml_declaration=True)


def build(source: Path, output: Path) -> None:
    if not CONTENT.is_dir():
        raise FileNotFoundError(f"No se encontró {CONTENT}")
    generated = make_generated()
    with tarfile.open(source, "r:gz") as src:
        names = set(src.getnames())
        manifest_path = "moodle_backup.xml"
        manifest = ET.fromstring(src.extractfile(manifest_path).read())
        info = manifest.find("./information")
        release = get_text(info, "moodle_release") if info is not None else ""
        if not release.startswith("5.1"):
            raise ValueError(f"El respaldo es Moodle {release}; se esperaba la línea 5.1 confirmada.")

        # Prepare course/section copy edits.
        changes: dict[str, bytes] = {}
        course_path = "course/course.xml"
        course = ET.fromstring(src.extractfile(course_path).read())
        for tag, value in {
            "shortname": "EIDTI 2026-3",
            "fullname": "Emprendimiento e Ideas de Negocio en TI",
            "summary": "Curso orientado a formular, validar y escalar ideas de negocio basadas en tecnologías de la información, con enfoque en el ecosistema colombiano.",
        }.items():
            node = course.find(tag)
            if node is not None:
                node.text = value
        for option in course.findall("./courseformatoptions/courseformatoption"):
            if get_text(option, "name") == "basecolour":
                option.find("value").text = BRAND
        changes[course_path] = xml_bytes(course)

        section_files: dict[int, tuple[str, ET.Element]] = {}
        for name in names:
            if name.startswith("sections/section_") and name.endswith("/section.xml"):
                sec = ET.fromstring(src.extractfile(name).read())
                number = int(get_text(sec, "number", "-1"))
                section_files[number] = (name, sec)
        for number, title in SECTIONS.items():
            if number in section_files:
                path, sec = section_files[number]
                node = sec.find("name")
                if node is not None:
                    node.text = title
                changes[path] = xml_bytes(sec)

        # Human-readable course entry and content summaries; preserve embedded backup files in map/video labels.
        intro_by_label: dict[str, str] = {}
        intro_by_label["label_535382"] = f'''<div style="{BASE_STYLE}">
<header style="background:{BRAND};color:#fff;padding:1.5rem;border-bottom:6px solid {GOLD};border-radius:10px;margin:0 0 1rem"><p style="margin:0;letter-spacing:.12em;font-weight:700">UNIVERSIDAD DISTRITAL · INGENIERÍA DE SISTEMAS</p><h1 style="color:#fff;margin:.4rem 0;font-size:1.8rem">Emprendimiento e Ideas de Negocio en TI</h1><p style="color:#fff;margin:0;font-style:italic">De una oportunidad tecnológica a una propuesta de negocio validada.</p></header>
{card('Propósito del curso','Desarrollar planes de negocio factibles para ideas de negocio en TI, comprendiendo el entorno institucional, aplicando metodologías de emprendimiento, validando con clientes y consolidando un modelo sostenible.')}
<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:.7rem">{card('1 · Marcos para emprender','Ecosistema institucional, legal y financiero colombiano.')}{card('2 · Emprendimiento en TI','Opciones de grado, habilidades blandas y metodologías.')}{card('3 · Validación e iteración','Desarrollo del cliente, marketing, métricas y escalamiento.')}{card('4 · Industrias y plan de negocio','Industrias digitales y formulación del plan final.')}</div>
{card('Cómo avanzar','Sigue las unidades en orden, estudia los recursos, participa en los espacios de interacción y conserva las evidencias de tus actividades. Revisa el syllabus y el cronograma antes de iniciar cada módulo.')}
</div>'''
        for label, title, objective in [
            ("label_535388", "Unidad 1 · Marcos para el emprendimiento en TI", "Reconoce el ecosistema institucional, los modelos de negocio, las fuentes de financiación y el marco legal que acompañan un emprendimiento tecnológico."),
            ("label_535399", "Unidad 2 · Emprendimiento en TI", "Explora las opciones de grado, la calidad educativa, las habilidades emprendedoras y metodologías para convertir una oportunidad en una propuesta de valor."),
            ("label_535410", "Unidad 3 · Validación de mercado, métricas e iteración", "Formula hipótesis, conversa con clientes, construye experimentos, interpreta métricas y decide cómo iterar o escalar."),
            ("label_535421", "Unidad 4 · Industrias de TI y consolidación del plan", "Analiza sectores digitales y conecta una oportunidad de mercado con un modelo y un plan de negocio verificables."),
        ]:
            intro_by_label[label] = f'''<div style="{BASE_STYLE}"><header style="background:{BRAND};color:white;padding:1rem 1.25rem;border-radius:8px;border-bottom:5px solid {GOLD}"><p style="margin:0;font-size:.8rem;letter-spacing:.12em;font-weight:700">RUTA FORMATIVA</p><h2 style="color:white;margin:.25rem 0;font-size:1.45rem">{title}</h2><p style="color:white;margin:0">{objective}</p></header><p style="text-align:center;margin:1rem 0">Revisa los temas, consulta el mapa y realiza las actividades antes de avanzar.</p></div>'''

        topic_cards = {
            "label_535390": "<h3>Temas de la unidad</h3><ul><li>Ecosistema institucional y entidades de apoyo a la innovación en Colombia.</li><li>Modelos de negocio e innovación.</li><li>Financiación por etapas y alternativas para emprendimientos.</li><li>Marco legal, formalización y casos de emprendimiento en ingeniería de sistemas.</li></ul>",
            "label_535401": "<h3>Temas de la unidad</h3><ul><li>Opción de grado y calidad educativa.</li><li>Habilidades blandas, cultura emprendedora y trabajo en equipo.</li><li>Design Thinking, Lean Startup, Scrum, Canvas e innovación abierta.</li></ul>",
            "label_535412": "<h3>Temas de la unidad</h3><ul><li>Desarrollo del cliente y validación de problema-solución.</li><li>Marketing y creación de valor.</li><li>Plan de negocio, métricas, aprendizaje e iteración.</li><li>Estrategias de crecimiento y escalamiento responsable.</li></ul>",
            "label_535423": "<h3>Temas de la unidad</h3><ul><li>Fintech, Legaltech, Insurtech, Regtech, Proptech y Wealthtech.</li><li>Femtech, Foodtech, Cleantech, Greentech, Biotech, Healthtech y Retailtech.</li><li>Del análisis sectorial a una oportunidad y un plan de negocio sostenible.</li></ul>",
        }
        activity_cards = {
            "label_535393": "<h3>Actividad de aprendizaje · Unidad 1</h3><ol><li>Selecciona una oportunidad de emprendimiento en TI.</li><li>Identifica las entidades, aliados, alternativas de financiación y aspectos legales relacionados.</li><li>Resume cómo estos factores condicionan un plan de negocio viable.</li></ol>",
            "label_535404": "<h3>Resultado de aprendizaje · Unidad 2</h3><p>Relaciona las opciones para emprender en TI con las habilidades y metodologías que permiten diseñar, probar y mejorar una propuesta de negocio.</p>",
            "label_535415": "<h3>Actividad de aprendizaje · Unidad 3</h3><ol><li>Formula una hipótesis de cliente y problema.</li><li>Diseña una entrevista o experimento de validación.</li><li>Define una métrica y explica cómo los resultados modificarían tu propuesta.</li></ol>",
            "label_535426": "<h3>Actividad de aprendizaje · Unidad 4</h3><ol><li>Elige una industria digital y describe una necesidad concreta.</li><li>Relaciona la oportunidad con clientes, propuesta de valor, ingresos y costos.</li><li>Prepara una versión inicial del plan de negocio y susténtala con evidencia.</li></ol>",
        }
        extras_by_label = {**topic_cards, **activity_cards}

        # Attach learner-facing maps and their editable/source versions.
        map_specs = {
            "label_535389": [
                "Grupo_03_Tarea_02/Mapas interactivos/Mapa_General_navegable.pdf",
                "Grupo_03_Tarea_02/Mapa General/Mapa General.jpg",
                "Grupo_03_Tarea_02/Mapa General/Mapa_General.drawio",
                "Grupo_03_Tarea_02/Mapas interactivos/Mapa_Unidad1_navegable.pdf",
                "Grupo_03_Tarea_02/Mapas Unidades/Mapa_Unidad1.jpg",
                "Grupo_03_Tarea_02/Mapas Unidades/DrawIO/Mapa_Unidad1.drawio",
                "Grupo_03_Tarea_02/Mapas Unidades/Mapa_CasosExito_Grupo11.jpg",
                "Grupo_03_Tarea_02/Mapas Unidades/DrawIO/Mapa_CasosExito_G11.drawio",
            ],
            "label_535400": ["Grupo_03_Tarea_02/Mapas interactivos/Mapa_Unidad2_navegable.pdf", "Grupo_03_Tarea_02/Mapas Unidades/Mapa_Unidad2.jpg", "Grupo_03_Tarea_02/Mapas Unidades/DrawIO/Maoa_Unidad2.drawio"],
            "label_535411": ["Grupo_03_Tarea_02/Mapas interactivos/Mapa_Unidad3_navegable.pdf", "Grupo_03_Tarea_02/Mapas Unidades/Mapa_Unidad3.jpg", "Grupo_03_Tarea_02/Mapas Unidades/DrawIO/Mapa_Unidad3.drawio"],
            "label_535422": ["Grupo_03_Tarea_02/Mapas interactivos/Mapa_Unidad4_navegable.pdf", "Grupo_03_Tarea_02/Mapas Unidades/Mapa_Unidad4.jpg", "Grupo_03_Tarea_02/Mapas Unidades/DrawIO/Mapa_Unidad4.drawio"],
        }

        # Existing resources are selected by their activity-directory names, not by PHP code.
        for activity_dir, spec in RESOURCE_UPDATES.items():
            nodepath = f"activities/{activity_dir}/resource.xml"
            if nodepath not in names:
                raise ValueError(f"Falta actividad Moodle {nodepath}")
            activity = ET.fromstring(src.extractfile(nodepath).read())
            res = activity.find("resource")
            if res is None:
                raise ValueError(f"XML resource inválido: {nodepath}")
            res.find("name").text = spec["name"]
            intro = res.find("intro")
            if intro is not None:
                intro.text = f'<div style="{BASE_STYLE}"><h2 style="color:{BRAND}">{html.escape(spec["name"])}</h2><p>{html.escape(spec["intro"])}</p></div>'
            changes[nodepath] = xml_bytes(activity)

        # Prepare new material files and HTML labels after resolving the backup's module IDs/contexts.
        manifest_activities = manifest.findall("./information/contents/activities/activity")
        by_dir = {Path(a.findtext("directory", "")).name: a for a in manifest_activities}
        global_files = ET.fromstring(src.extractfile("files.xml").read())
        next_file_id = max((int(f.get("id", "0")) for f in global_files.findall("file")), default=0) + 1
        file_payloads: dict[str, bytes] = {}
        def activity_context(activity_dir: str) -> tuple[int, int, str]:
            module_path = f"activities/{activity_dir}/module.xml"
            m = ET.fromstring(src.extractfile(module_path).read())
            mod = m
            activity_id = int(mod.get("id"))
            modname = get_text(mod, "modulename")
            activity_path = f"activities/{activity_dir}/{modname}.xml"
            activity_data = ET.fromstring(src.extractfile(activity_path).read())
            context_id = int(activity_data.get("contextid"))
            return activity_id, context_id, modname

        def remove_area_files(context_id: int, component: str, area: str, activity_dir: str) -> None:
            doomed = [f for f in global_files.findall("file") if int(get_text(f, "contextid", "0")) == context_id and get_text(f, "component") == component and get_text(f, "filearea") == area]
            ids = {int(f.get("id", "0")) for f in doomed}
            for f in doomed:
                global_files.remove(f)
            ref_path = f"activities/{activity_dir}/inforef.xml"
            ref = ET.fromstring(src.extractfile(ref_path).read()) if ref_path not in changes else ET.fromstring(changes[ref_path])
            for fileref in ref.findall("./fileref/file"):
                if int(get_text(fileref, "id", "0")) in ids:
                    ref.find("fileref").remove(fileref)
            changes[ref_path] = xml_bytes(ref)

        def add_file(activity_dir: str, relpath: str, output_name: str | None = None, replace: bool = False) -> str:
            nonlocal next_file_id
            activity = by_dir[activity_dir]
            activity_id, context_id, modname = activity_context(activity_dir)
            component = "mod_resource" if modname == "resource" else "mod_label"
            area = "content" if modname == "resource" else "intro"
            if replace:
                remove_area_files(context_id, component, area, activity_dir)
            data = generated[relpath] if relpath in generated else (CONTENT / relpath).read_bytes()
            filename = output_name or Path(relpath).name
            digest = hashlib.sha1(data).hexdigest()
            file_id = next_file_id
            next_file_id += 1
            f = ET.SubElement(global_files, "file", {"id": str(file_id)})
            values = {
                    "contenthash": digest,
                    "contextid": str(context_id),
                    "component": component,
                    "filearea": area,
                    "itemid": "0",
                    "filepath": "/",
                    "filename": filename,
                    "userid": "5",
                    "filesize": str(len(data)),
                    "mimetype": mimetypes.guess_type(filename)[0] or "application/octet-stream",
                    "status": "0",
                    "timecreated": "0",
                    "timemodified": "0",
                    "source": filename,
                    "author": "Equipo del curso",
                    "license": "allrightsreserved",
                    "sortorder": "0",
                    "repositorytype": "$@NULL@$",
                    "repositoryid": "$@NULL@$",
                    "reference": "$@NULL@$",
            }
            for tag, value in values.items():
                ET.SubElement(f, tag).text = value
            payload_path = f"files/{digest[:2]}/{digest}"
            if payload_path not in names:
                file_payloads[payload_path] = data
            ref_path = f"activities/{activity_dir}/inforef.xml"
            ref = ET.fromstring(changes[ref_path]) if ref_path in changes else ET.fromstring(src.extractfile(ref_path).read())
            fileref = ref.find("fileref")
            if fileref is None:
                fileref = ET.SubElement(ref, "fileref")
            ET.SubElement(ET.SubElement(fileref, "file"), "id").text = str(file_id)
            changes[ref_path] = xml_bytes(ref)
            return filename

        # Replace course resources with current materials, retaining the Moodle module itself.
        for activity_dir, spec in RESOURCE_UPDATES.items():
            activity = ET.fromstring(src.extractfile(f"activities/{activity_dir}/resource.xml").read())
            res = activity.find("resource")
            _, context_id, _ = activity_context(activity_dir)
            remove_area_files(context_id, "mod_resource", "content", activity_dir)
            names_added = []
            for relpath in spec["files"]:
                if relpath.startswith("generated/") and relpath not in generated:
                    raise ValueError(f"Material generado no encontrado: {relpath}")
                names_added.append(add_file(activity_dir, relpath))
            # Keep resources useful even if learners don't use the intro; supplementary items are named clearly.
            intro = res.find("intro")
            if intro is not None:
                intro.text = f'<div style="{BASE_STYLE}"><h2 style="color:{BRAND}">{html.escape(spec["name"])}</h2><p>{html.escape(spec["intro"])}</p><ul>' + ''.join(f'<li>{html.escape(n)}</li>' for n in names_added) + '</ul></div>'
            changes[f"activities/{activity_dir}/resource.xml"] = xml_bytes(activity)

        # Add navigable PDF maps to the existing map labels.
        for label_dir, files in map_specs.items():
            act_path = f"activities/{label_dir}/label.xml"
            activity = ET.fromstring(src.extractfile(act_path).read())
            label = activity.find("label")
            intro = label.findtext("intro", "")
            # Keep the original map image if it exists, with responsive sizing.
            image = re.search(r'<img\b[^>]*>', intro)
            image_html = image.group(0) if image else ""
            if image_html:
                image_html = re.sub(r'\swidth="[^"]*"', '', image_html)
                image_html = re.sub(r'\sheight="[^"]*"', '', image_html)
                image_html = image_html.replace('<img ', '<img style="max-width:100%;height:auto;border:1px solid #ddd;border-radius:8px" ')
            labels = []
            for relpath in files:
                name = add_file(label_dir, relpath)
                labels.append(link_html(name, Path(name).stem.replace("_", " ")))
            label.find("intro").text = f'<section style="{BASE_STYLE}"><h3 style="color:{BRAND}">Mapa conceptual y navegación</h3><p>Consulta el mapa visual y abre la versión navegable en PDF para explorar conceptos y relaciones.</p><div style="text-align:center">{image_html}</div><ul>' + ''.join(f'<li>{x}</li>' for x in labels) + '</ul></section>'
            changes[act_path] = xml_bytes(activity)

        # Replace old course-introduction and unit opening/map/topic/activity label content.
        # Keep attached resources already referenced by these labels when the module is not explicitly rebuilt.
        for label_dir, intro_html in intro_by_label.items():
            path = f"activities/{label_dir}/label.xml"
            act = ET.fromstring(src.extractfile(path).read())
            act.find("label/intro").text = intro_html
            changes[path] = xml_bytes(act)

        for label_dir, extra in extras_by_label.items():
            path = f"activities/{label_dir}/label.xml"
            act = ET.fromstring(src.extractfile(path).read())
            label = act.find("label")
            old_html = label.findtext("intro", "")
            label.find("intro").text = f'<div style="{BASE_STYLE}">{old_html}{card("Guía de trabajo", extra)}</div>'
            changes[path] = xml_bytes(act)

        # Replace resources and close-out overview to match the supplied final project materials.
        closure = {
            "Grupo_22_Tarea_19/19.1_Texto_cierre_sintesis_conceptos.docx": "Síntesis, conclusiones y conceptos clave",
            "Grupo_22_Tarea_19/19.2_Plan_negocio_instrucciones_rubrica.docx": "Plan de negocio final · instrucciones y rúbrica",
            "Grupo_22_Tarea_19/19.3_Autoevaluacion_directorio_institucional.docx": "Autoevaluación y directorio institucional",
        }
        closure_links = []
        closure_dir = "label_543192"
        for relpath, title in closure.items():
            data = docx_html(CONTENT / relpath, title).encode("utf-8")
            generated[f"generated/{Path(relpath).stem}.html"] = data
            # Generated closure files are attached to the existing end-of-course label.
            rel = f"generated/{Path(relpath).stem}.html"
            filename = add_file(closure_dir, rel)
            closure_links.append(f'<li>{link_html(filename, title)}</li>')
        closure_path = f"activities/{closure_dir}/label.xml"
        closure_activity = ET.fromstring(src.extractfile(closure_path).read())
        closure_activity.find("label/name").text = "Cierre · Síntesis y plan de negocio final"
        closure_activity.find("label/intro").text = f'''<div style="{BASE_STYLE}"><header style="background:{BRAND};color:white;padding:1rem;border-radius:8px"><h2 style="color:white;margin:0">Cierra el curso con una propuesta verificable</h2><p style="color:white;margin:.35rem 0 0">Integra lo aprendido en un plan de negocio TI, identifica próximos pasos y revisa la consistencia de tu propuesta.</p></header><h3 style="color:{BRAND}">Material de cierre</h3><ul>{''.join(closure_links)}</ul></div>'''
        changes[closure_path] = xml_bytes(closure_activity)

        # Supplementary source visuals, activity worksheet, and editable map files.
        extra_label_files = {
            "label_535382": [
                ("Grupo_02_Tarea_01/Identidad_visual_editables.zip", "Identidad visual editable del aula"),
                ("Grupo_02_Tarea_01/Portadas_editables.zip", "Portadas editables del curso y unidades"),
                ("Grupo_02_Tarea_01/Maqueta_menu_curso.png", "Maqueta del menú del curso"),
                ("Grupo_02_Tarea_01/Maqueta_unidad_3.png", "Maqueta de la Unidad 3"),
                ("Grupo_02_Tarea_01/Maqueta_unidad_4.png", "Maqueta de la Unidad 4"),
                ("Grupo_02_Tarea_01/Portada_unidad_3.png", "Portada de la Unidad 3"),
                ("Grupo_02_Tarea_01/Portada_unidad_4.png", "Portada de la Unidad 4"),
                ("generated/plantillas-html-moodle.txt", "Plantillas HTML para editar el aula"),
            ],
            "label_535415": [
                ("Grupo_18_Tarea_15/Ficha_comun_actividad_Grupo18.docx", "Ficha común de actividad · Grupo 18"),
            ],
            "label_543192": [
                ("Grupo_22_Tarea_19/Mapa_Ruta_Plan_Negocio_Final.png", "Mapa de ruta del plan de negocio final"),
                ("Grupo_22_Tarea_19/Mapa_Ruta_Plan_Negocio_Final.drawio", "Archivo editable del mapa de ruta"),
            ],
        }
        for label_dir, assets in extra_label_files.items():
            path = f"activities/{label_dir}/label.xml"
            activity = ET.fromstring(changes[path]) if path in changes else ET.fromstring(src.extractfile(path).read())
            label = activity.find("label")
            links = []
            for relpath, title in assets:
                filename = add_file(label_dir, relpath)
                links.append(f'<li>{link_html(filename, title)}</li>')
            heading = "Materiales visuales y editables" if label_dir == "label_535382" else "Material complementario"
            extra_html = f'<section style="{BASE_STYLE}border-top:3px solid {GOLD};margin-top:1rem;padding-top:.75rem"><h3 style="color:{BRAND}">{heading}</h3><ul>{"".join(links)}</ul></section>'
            label.find("intro").text = (label.findtext("intro", "") or "") + extra_html
            changes[path] = xml_bytes(activity)

        declared_content = set(GENERATED_SOURCE_PATHS) | set(closure)
        declared_content.update(
            path
            for spec in RESOURCE_UPDATES.values()
            for path in spec["files"]
            if not path.startswith("generated/")
        )
        declared_content.update(path for files in map_specs.values() for path in files)
        declared_content.update(
            path
            for assets in extra_label_files.values()
            for path, _ in assets
            if not path.startswith("generated/")
        )
        included_count, excluded_count = validate_content_inventory(declared_content)

        # The backup metadata retains its Moodle 5.1 source but gets a clean user-facing course identity.
        if info is not None:
            for tag, value in {"original_course_fullname": "Emprendimiento e Ideas de Negocio en TI", "original_course_shortname": "EIDTI 2026-3"}.items():
                node = info.find(tag)
                if node is not None:
                    node.text = value
        changes[manifest_path] = xml_bytes(manifest)
        changes["files.xml"] = xml_bytes(global_files)

        output.parent.mkdir(parents=True, exist_ok=True)
        tmp = output.with_suffix(output.suffix + ".tmp")
        with tarfile.open(tmp, "w:gz", format=tarfile.PAX_FORMAT) as dst:
            for member in src.getmembers():
                if member.name in changes:
                    data = changes.pop(member.name)
                    ti = tarfile.TarInfo(member.name)
                    ti.mode, ti.mtime = member.mode, member.mtime
                    ti.size = len(data)
                    dst.addfile(ti, __import__("io").BytesIO(data))
                else:
                    f = src.extractfile(member)
                    dst.addfile(member, f)
            for name, data in file_payloads.items():
                ti = tarfile.TarInfo(name)
                ti.mode, ti.mtime, ti.size = 0o644, 0, len(data)
                dst.addfile(ti, __import__("io").BytesIO(data))
        if changes:
            tmp.unlink(missing_ok=True)
            raise ValueError(f"No se escribieron los miembros esperados: {sorted(changes)}")
        os.replace(tmp, output)
    print(f"Inventario revisado: {included_count} recursos incluidos/transformados; {excluded_count} archivos internos excluidos.")
    print(f"Curso Moodle 5.1 creado: {output} ({output.stat().st_size:,} bytes)")


def main() -> None:
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument("--source", type=Path, default=SOURCE, help="Respaldo Moodle 5.1 base")
    parser.add_argument("--output", type=Path, default=DEFAULT_OUTPUT, help="Archivo .mbz resultante")
    args = parser.parse_args()
    build(args.source, args.output)


if __name__ == "__main__":
    main()
