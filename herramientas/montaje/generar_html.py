# Genera los fragmentos HTML que usan los scripts de montaje, a partir de transcripciones/*.md.
# Uso, desde la raíz del repositorio:  python herramientas/montaje/generar_html.py
import html
import os
import re

RAIZ = os.path.dirname(os.path.dirname(os.path.dirname(os.path.abspath(__file__))))
TRANS = os.path.join(RAIZ, 'transcripciones')
SALIDA = os.path.join(os.path.dirname(os.path.abspath(__file__)), 'html')
os.makedirs(SALIDA, exist_ok=True)

ESTILO = "font-family:'Montserrat',Arial,sans-serif;font-size:15px;line-height:1.5;"
H4 = ('<h4 style="color:#8C1919;font-family:\'Montserrat\',Arial,sans-serif;font-weight:700;'
      'border-bottom:2px solid #950D16;padding-bottom:6px;margin-top:24px;border-left:0 !important;padding-left:0 !important;">{}</h4>')


def celda(archivo, punto):
    """Texto de la columna «Desarrollo de la entrega» del punto indicado (p. ej. '4.2'), como lista de líneas."""
    with open(os.path.join(TRANS, archivo), encoding='utf-8') as fh:
        for linea in fh:
            if linea.startswith('| ' + punto):
                partes = [p.strip() for p in re.split(r'(?<!\\)\|', linea)]
                texto = partes[2].replace('\\|', '|')
                return [html.unescape(l).strip() for l in texto.split('<br>') if l.strip()]
    raise SystemExit(f'No encontré el punto {punto} en {archivo}')


def parrafos(lineas):
    """Convierte líneas en HTML: «Etiqueta: texto» queda en viñeta con la etiqueta en negrita."""
    out, lista = [], []

    def cerrar():
        if lista:
            out.append('<ul>' + ''.join(lista) + '</ul>')
            lista.clear()

    for l in lineas:
        e = html.escape(l)
        m = re.match(r'^([^:]{3,60}):\s+(.+)$', l)
        if l.startswith('¿') and l.endswith('?'):
            lista.append(f'<li>{e}</li>')
        elif m and not l.endswith(':'):
            lista.append(f'<li><strong>{html.escape(m.group(1))}:</strong> {html.escape(m.group(2))}</li>')
        else:
            cerrar()
            out.append(f'<p><strong>{e}</strong></p>' if l.endswith(':') else f'<p>{e}</p>')
    cerrar()
    return ''.join(out)


def youtube_id(url):
    m = re.search(r'(?:v=|youtu\.be/)([\w-]{11})', url)
    return m.group(1) if m else None


def embed(vid, titulo):
    return ('<div class="eidti-video" style="max-width:720px;margin:12px auto;">'
            '<div style="position:relative;padding-bottom:56.25%;height:0;overflow:hidden;">'
            f'<iframe src="https://www.youtube-nocookie.com/embed/{vid}" title="{html.escape(titulo)}" '
            'style="position:absolute;top:0;left:0;width:100%;height:100%;border:0;" '
            'allow="accelerometer; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe></div></div>')


def g05():
    """Grupo 5: guía de los seis videos de «Perspectiva holista» (4.1 inventario + 4.2 resúmenes y preguntas)."""
    inventario = celda('Grupo_05_Tarea_04.md', '4.1')
    videos, actual = [], None
    for l in inventario:
        m = re.match(r'^Video (\d+)\.\s*(.+)$', l)
        if m:
            actual = {'n': int(m.group(1)), 'titulo': m.group(2)}
            videos.append(actual)
        elif actual is not None:
            for campo in ('Introducción', 'Fuente', 'Autor', 'Duración'):
                if l.startswith(campo + ':'):
                    actual[campo] = l.split(':', 1)[1].strip()
    resumenes = celda('Grupo_05_Tarea_04.md', '4.2')
    bloques, cur = {}, None
    for l in resumenes:
        m = re.match(r'^Video (\d+)$', l)
        if m:
            cur = int(m.group(1))
            bloques[cur] = []
        elif cur is not None:
            bloques[cur].append(l)

    partes = [f'<div style="{ESTILO}"><p>Seis videos para ver el emprendimiento tecnológico de forma integral: '
              'crear mercados nuevos, transformación digital, diseño de modelos de negocio con Canvas y ecosistemas de '
              'apoyo. Cada uno trae un resumen y preguntas para reflexionar antes de las actividades de la unidad.</p>']
    for v in videos:
        vid = youtube_id(v.get('Fuente', ''))
        partes.append(H4.format(f"Video {v['n']}. {html.escape(v['titulo'])}"))
        partes.append(f"<p><em>{html.escape(v.get('Autor', ''))} · {html.escape(v.get('Duración', ''))}</em></p>")
        partes.append(f"<p>{html.escape(v.get('Introducción', ''))}</p>")
        if vid:
            partes.append(embed(vid, v['titulo']))
            partes.append(f'<p class="small">Si el video no carga, ábrelo en '
                          f'<a class="nomediaplugin" href="https://www.youtube.com/watch?v={vid}" target="_blank" rel="noopener">YouTube</a>.</p>')
        partes.append(parrafos(bloques.get(v['n'], [])))
    partes.append('</div>')
    escribir('g05_guia_videos.html', ''.join(partes))

    # class="nomediaplugin": que el filtro multimedia de Moodle no convierta el enlace en un reproductor sin título.
    lista = ''.join(
        f"<li><a class=\"nomediaplugin\" href=\"{html.escape(v['Fuente'])}\" target=\"_blank\" rel=\"noopener\">{html.escape(v['titulo'])}</a> "
        f"<span class=\"text-muted\">({html.escape(v.get('Autor', ''))}, {html.escape(v.get('Duración', ''))})</span></li>"
        for v in videos)
    escribir('g05_lista_videos.html', lista)


def por_numero(lineas):
    """Agrupa líneas «N. TÍTULO: texto» y «N.k Etiqueta: texto» en {N: {'titulo', 'intro', 'subs': [(etiqueta, [líneas])]}}."""
    datos, n, sub = {}, None, None
    for l in lineas:
        m1 = re.match(r'^(\d+)\.\s+([^:]+):\s*(.*)$', l)
        m2 = re.match(r'^(\d+)\.(\d+)\s+(.*)$', l)
        if m1 and not m2:
            n = int(m1.group(1))
            datos[n] = {'titulo': m1.group(2).strip(), 'intro': [m1.group(3)] if m1.group(3) else [], 'subs': []}
            sub = None
        elif m2 and n is not None:
            resto = m2.group(3)
            if resto.endswith(':') or re.match(r'^[^:]{3,80}:\s*$', resto):
                sub = (resto.rstrip(':').strip(), [])
            elif re.match(r'^[^:]{3,80}:\s*\S', resto) and len(resto.split(':', 1)[0]) < 80:
                etiqueta, texto = resto.split(':', 1)
                sub = (etiqueta.strip(), [texto.strip()])
            else:
                sub = ('', [resto])
            datos[n]['subs'].append(sub)
        elif n is not None:
            (sub[1] if sub else datos[n]['intro']).append(l)
    return datos


def enlazar(texto):
    """Convierte las URL sueltas en enlaces."""
    return re.sub(r'(https?://[^\s<>"]+[^\s<>".,;)])',
                  r'<a href="\1" target="_blank" rel="noopener">\1</a>', html.escape(texto).replace('&amp;', '&'))


def g11():
    """Grupo 11: casos de éxito (9.1 fichas, 9.2 líneas de tiempo, 9.3 consejos) y conceptos corregidos (9.4)."""
    fichas = por_numero(celda('Grupo_11_Tarea_09.md', '9.1'))
    lineas_tiempo = por_numero(celda('Grupo_11_Tarea_09.md', '9.2'))
    consejos = por_numero(celda('Grupo_11_Tarea_09.md', '9.3'))
    partes = [f'<div style="{ESTILO}"><p>Seis emprendimientos tecnológicos colombianos analizados por etapas: el problema que '
              'resolvieron, su solución, los resultados, los hitos de su historia y lo que puedes aprender de cada uno '
              'para tu propio proyecto.</p>']
    for n in sorted(fichas):
        c = fichas[n]
        nombre = c['titulo'].title() if c['titulo'].isupper() else c['titulo']
        partes.append(H4.format(f'{n}. {html.escape(nombre)}'))
        for l in c['intro']:
            partes.append(f'<p>{enlazar(l)}</p>')
        for etiqueta, textos in c['subs']:
            if etiqueta.lower().startswith('fuentes'):
                continue
            partes.append(f'<p><strong>{html.escape(etiqueta)}:</strong> ' + ' '.join(enlazar(t) for t in textos) + '</p>')
        hitos = lineas_tiempo.get(n, {}).get('intro', [])
        if hitos:
            partes.append('<p><strong>Línea de tiempo:</strong></p><ul>')
            for h in hitos:
                h = h.lstrip('- ').strip()
                m = re.match(r'^([\d\s\-–()a-záéíóúñ]+?)\s*:\s*(.+)$', h, re.I)
                if m:
                    partes.append(f'<li><strong>{html.escape(m.group(1).strip())}:</strong> {enlazar(m.group(2))}</li>')
                else:
                    partes.append(f'<li>{enlazar(h)}</li>')
            partes.append('</ul>')
        lecciones = consejos.get(n, {}).get('subs', [])
        if lecciones:
            partes.append('<p><strong>Lecciones para tu emprendimiento:</strong></p><ul>')
            for etiqueta, textos in lecciones:
                partes.append('<li>' + enlazar(' '.join(([etiqueta + ':'] if etiqueta else []) + textos)) + '</li>')
            partes.append('</ul>')
        fuentes = [t for e, ts in c['subs'] if e.lower().startswith('fuentes') for t in ts]
        if fuentes:
            partes.append('<details><summary>Fuentes</summary><ul>' +
                          ''.join(f'<li>{enlazar(t)}</li>' for t in fuentes) + '</ul></details>')
    partes.append('</div>')
    escribir('g11_casos_exito.html', ''.join(partes))

    conceptos = por_numero(celda('Grupo_11_Tarea_09.md', '9.4'))
    partes = [f'<div style="{ESTILO}"><p>Conceptos de los temas de la Unidad 1 revisados por el grupo 11, con ejemplos '
              'aplicados al emprendimiento en TI.</p>']
    for n in sorted(conceptos):
        c = conceptos[n]
        partes.append(H4.format(html.escape(c['titulo'].capitalize())))
        for l in c['intro']:
            partes.append(f'<p>{enlazar(l)}</p>')
        for etiqueta, textos in c['subs']:
            partes.append(f'<p><strong>{html.escape(etiqueta)}.</strong> ' + ' '.join(enlazar(t) for t in textos) + '</p>')
    partes.append('</div>')
    escribir('g11_conceptos_unidad1.html', ''.join(partes))


def g12():
    """Grupo 12: financiación por etapas y entorno legal (10.1 y 10.2), leído directamente del Word por las tablas anidadas."""
    import docx  # python-docx
    from docx.oxml.ns import qn
    d = docx.Document(os.path.join(RAIZ, 'Grupo_12_Tarea_10', 'Verificacion_y_entrega.docx'))
    filas = d.tables[0].rows

    def bloques(celda):
        """Párrafos y tablas de una celda, en orden, como HTML."""
        out = []
        for el in celda._tc.iterchildren():
            if el.tag == qn('w:p'):
                t = ''.join(x.text or '' for x in el.iter(qn('w:t'))).strip()
                if not t or t.startswith(('Completen aquí', 'Escriban', 'Peguen', 'Adjunto o enlace')):
                    continue
                m = re.match(r'^([^:]{3,90}):\s*(.*)$', t)
                if m and m.group(2):
                    out.append(f'<p><strong>{html.escape(m.group(1))}:</strong> {enlazar(m.group(2))}</p>')
                elif t.endswith(':') or len(t) < 90 and not t.endswith('.'):
                    out.append(f'<h5 style="color:#8C1919;font-weight:700;margin-top:18px;border-left:0 !important;padding-left:0 !important;">{html.escape(t.rstrip(":"))}</h5>')
                else:
                    out.append(f'<p>{enlazar(t)}</p>')
            elif el.tag == qn('w:tbl'):
                tabla = docx.table.Table(el, celda)
                out.append('<div class="table-responsive"><table class="table table-sm table-bordered" style="font-size:14px;">')
                for i, r in enumerate(tabla.rows):
                    tag = 'th' if i == 0 else 'td'
                    out.append('<tr>' + ''.join(
                        f'<{tag} style="vertical-align:top;">' +
                        '<br>'.join(enlazar(p.text) for p in c.paragraphs if p.text.strip()) + f'</{tag}>'
                        for c in r.cells) + '</tr>')
                out.append('</table></div>')
        return ''.join(out)

    cuerpo = [f'<div style="{ESTILO}"><p>Cómo se financia un emprendimiento de TI en cada etapa y qué normas lo benefician u '
              'obligan, con las correcciones legales al documento base y textos que conectan financiación y entorno legal.</p>']
    for r in filas[1:3]:
        cuerpo.append(bloques(r.cells[1]))
    cuerpo.append('</div>')
    escribir('g12_financiacion_legal.html', ''.join(cuerpo))


def escribir(nombre, contenido):
    with open(os.path.join(SALIDA, nombre), 'w', encoding='utf-8') as fh:
        fh.write(contenido)
    print(f'html/{nombre}  ({len(contenido)} caracteres)')


if __name__ == '__main__':
    g05()
    g11()
    g12()
