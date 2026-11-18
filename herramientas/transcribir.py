# Regenera transcripciones/*.md a partir de los Word de las carpetas Grupo_*.
# Uso, desde la raiz del repositorio:  python herramientas/transcribir.py
# Requiere:  pip install python-docx
import glob, os, sys, docx
from docx.oxml.ns import qn
sys.stdout.reconfigure(encoding='utf-8')
OUT = 'transcripciones'
os.makedirs(OUT, exist_ok=True)
def ptext(p):
    t = ''.join(x.text or '' for x in p.iter(qn('w:t')))
    n = len(p.xpath('.//a:blip'))
    if n: t += ' [IMAGEN]' * n
    return t
def celltext(tc):
    parts=[]
    for el in tc.iterchildren():
        if el.tag == qn('w:p'):
            t=ptext(el).strip()
            if t: parts.append(t)
        elif el.tag == qn('w:tbl'):
            parts.append('(tabla anidada: ' + ' ; '.join(' / '.join(celltext(c) for c in r.findall(qn('w:tc'))) for r in el.findall(qn('w:tr'))) + ')')
    return '<br>'.join(p.replace('|', r'\|') for p in parts)
def body_md(el_parent):
    lines=[]
    for el in el_parent.iterchildren():
        if el.tag == qn('w:p'):
            t=ptext(el).strip()
            if t: lines.append(t); lines.append('')
        elif el.tag == qn('w:tbl'):
            rows=[[celltext(c) for c in r.findall(qn('w:tc'))] for r in el.findall(qn('w:tr'))]
            if not rows: continue
            w=max(len(r) for r in rows)
            rows=[r+['']*(w-len(r)) for r in rows]
            lines.append('| '+' | '.join(rows[0])+' |')
            lines.append('|'+'---|'*w)
            for r in rows[1:]: lines.append('| '+' | '.join(r)+' |')
            lines.append('')
    return lines
groups={}
for p in sorted(glob.glob('Grupo_*/**/*.docx', recursive=True)):
    g=p.split(os.sep)[0]
    groups.setdefault(g,[]).append(p)
for g, files in groups.items():
    others=[f for f in glob.glob(g+'/**/*', recursive=True) if os.path.isfile(f) and not f.endswith('.docx')]
    md=[f'# {g.replace("_"," ")} — transcripción', '', '> Texto extraído automáticamente de los Word de la carpeta. Las imágenes aparecen como [IMAGEN]; revisar el original para verlas.', '']
    if others:
        md += ['**Otros adjuntos en la carpeta:**', ''] + [f'- `{os.path.relpath(f,g)}` ({os.path.getsize(f)/1e6:.1f} MB)' for f in sorted(others)] + ['']
    for f in files:
        md += ['---', '', f'## Archivo: `{os.path.relpath(f,g)}`', '']
        md += body_md(docx.Document(f).element.body)
    with open(os.path.join(OUT, g+'.md'), 'w', encoding='utf-8') as fh:
        fh.write('\n'.join(md))
    print(g, len(files))
