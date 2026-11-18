# Guía del equipo Moodle (grupos 10 y 14)

Curso **Emprendimiento e Ideas de Negocio en TI**. Esta guía explica qué versión de Moodle usar, cómo dejar el Moodle de prueba igual en todos los computadores y qué le toca a cada quien. Todo se monta y se prueba primero en el **Moodle de prueba (local)**; el aula real no se toca hasta que el curso de prueba esté aprobado.

---

## 1. Versión de Moodle que funciona

| | Aula real (aulasvirtuales.udistrital.edu.co, curso 14158) | Moodle de prueba (Docker) |
|---|---|---|
| Moodle | **5.1.1+** (Build 20251219) | **5.1.9+**, rama `MOODLE_501_STABLE` |
| Formato del curso | Tiles (mosaicos) | Tiles **5.1.0.2** |
| Tema | Moove | Moove **5.1.2** |
| PHP / base de datos | — | PHP 8.3 · MariaDB 11.4 |

**Usen la rama 5.1. No usen 5.3 ni otra versión.** Una copia de seguridad hecha en una rama más nueva puede fallar al restaurarse en el aula real. Entre 5.1.9 (prueba) y 5.1.1 (real) Moodle solo muestra un aviso y deja restaurar sin problema.

---

## 2. Montar el Moodle de prueba (una sola vez)

Requisitos: Git, Docker Desktop **encendido** y Git Bash.

> **Si Docker no arranca** (se queda en «starting»): en Windows hay que activar la *Plataforma de máquina virtual*. En PowerShell como administrador ejecuten `wsl.exe --install --no-distribution` y reinicien el computador.

1. **Clonar y cambiar a la rama del equipo**
   ```bash
   git clone https://github.com/DanCmoo/moodle-emprendimiento.git
   cd moodle-emprendimiento
   git switch felipe/entregas-grupos
   ```
2. **Descargar Moodle 5.1 e iniciar Docker:** `bash docker/setup.sh`. Si una imagen se corta al descargar, vuelvan a ejecutarlo.
3. **Agregar el formato Tiles 5.1.0.2** del directorio oficial de moodle.org (plugin `format_tiles`): descomprímanlo en `.moodle-runtime/moodle/public/course/format/tiles`. Tiene que estar **antes** del paso 4.
4. **Poner el código de Moodle en un volumen de Docker** y luego instalar Moodle con el comando del [README](README.md). En Windows, leer el código desde la carpeta hace que la instalación tarde más de una hora; con el volumen tarda unos 2 minutos. Los pasos exactos están en [`herramientas/montaje/README.md`](herramientas/montaje/README.md), sección «Código de Moodle en un volumen de Docker».
5. **Aplicar el diseño UD** (tema Moove, vino, Montserrat y Lora): `bash herramientas/montaje/tema_ud.sh`
6. **Restaurar el curso real y montar todo:**
   - Pidan a Felipe la copia `copia_de_seguridad-…-nu.mbz` (sin usuarios, 75 MB). No está en Git por su tamaño.
   - Pónganla en la carpeta `Copia moodle/`.
   - Ejecuten:
     ```bash
     REINICIAR=1 bash herramientas/montaje/montar.sh
     ```
   Eso borra el curso local, restaura la copia limpia y aplica todo lo montado hasta ahora. El curso queda en http://localhost:8000; el usuario es `admin` y la contraseña es la de prueba del README.

---

## 3. Lo que ya está montado (grupo 10)

Ya está en `herramientas/montaje/`, y se aplica solo con `montar.sh`:

| Persona | Grupos | Qué quedó |
|---|---|---|
| Felipe | 2, 4, 6, 7, 9 | Identidad visual (banners, íconos, nombres de unidades, recursos y cuestionarios), presentación del curso y de las 4 unidades, ruta formativa, cronograma, syllabus correcto, bibliografía, material complementario de las 4 unidades, foros, glosario e índice de palabras clave |
| Daniel Sanabria | 5, 8, 11, 12 | Los 5 videos del curso en YouTube, guía de videos de Perspectiva holista, textos y enlaces corregidos (secciones 1 a 3), casos de éxito, financiación y entorno legal |

**Esto ya está hecho en sus secciones, no lo repitan:**
- Nombres corregidos de recursos y cuestionarios de las unidades 2 a 4. El cuestionario de la Sección 4 ya dice «Unidad 4».
- Banner, presentación, video y bloques «Inicio de la unidad», «Temas» y «Evaluación y cierre» de las unidades 2 a 4.
- Material complementario de las unidades 2 a 4.
- El plan de trabajo de Cálculo Integral que había en Recursos ya se reemplazó.

---

## 4. Lo que les corresponde (grupo 14)

Se reparte **por sección del aula** para no pisarse. El reparto entre ustedes dos es sugerido; pueden cambiarlo.

### Daniel Camacho: mapas y secciones 2 y 3

| Grupo | Tarea | Qué montar | Dónde | Ojo con |
|---|---|---|---|---|
| **3** | Mapas mentales | Mapa general nuevo (reemplaza las 2 apariciones del mapa de Cálculo Integral), los 4 mapas de unidad legibles y los PDF navegables | «Mapa de la unidad» de cada unidad (hoy son imágenes `Animales - Marco N.jpg` que no se leen) | Comprimir los JPG (2 a 4 MB cada uno). El punto **2.4 está vacío**: pedirlo al grupo 3, que depende del grupo 11. Los mapas quedan ampliables solos (ver §5) |
| **13** | Opción de grado (Sección 2) | Paquete SCORM `Tarea_11_Grupo13_SCORM_Completo (1).zip` con la ruta, el ejemplo HIRE.IT y calidad educativa | Unidad 2, bloque «Temas» | Usar `Verificacion_y_entrega_Grupo13.docx` (el otro está vacío). Probar el SCORM como estudiante en celular y computador |
| **15** | Habilidades blandas (Sección 2) | 4 casos, autoevaluación de 10 afirmaciones y guía de proyección laboral | Unidad 2 | **El PDF pesa 11,8 MB: comprimirlo antes de subirlo.** El contenido está en los adjuntos, no en el Word |
| **16** | Metodologías (Sección 2) | Comparativa de las 5 metodologías, ejemplos y 3 situaciones con respuesta modelo | Unidad 2 | Contenido completo; solo les faltó marcar «Entregado» |
| **17** | Mercadeo y plan (Sección 3) | 2 documentos de contenido, caso TiendaÁgil, plan de negocio dinámico, infografía y 2 resúmenes visuales | Unidad 3 | «Desarrollo del cliente» hoy abre `Psicologia_del_Consumidor_Grupo15.pdf`: confirmar el documento correcto. Los SVG pesan 1,2 y 1,5 MB (convertirlos a PNG o WebP). La imagen pegada en su Word está rota; usar el PNG de la carpeta |

### Camilo: Sección 4, actividades, evaluación y cierre

| Grupo | Tarea | Qué montar | Dónde | Ojo con |
|---|---|---|---|---|
| **18** | Actividades prácticas | Ficha común, consigna grupal de la Unidad 3 con rúbrica, una actividad por unidad (infografía, lienzo, validación, video-pitch) | Bloque «Evaluación y cierre» de cada unidad | Usar `Verificacion_y_entrega_Tarea15_Grupo18.docx`. No tenían las entregas de los grupos 12, 16, 17 y 20: cruzarlas al montar |
| **19** | Sección 4 (A) | Plantilla «del sector al plan de negocio» y contenidos de Fintech, Legaltech, Insurtech, Regtech, Proptech y Wealthtech | Unidad 4 | Usar las versiones `-v4`/`-v5`. Los archivos ya están en la carpeta, no hace falta OneDrive |
| **20** | Sección 4 (B) | 3 PDF: Femtech-Foodtech-Cleantech, Greentech-Biotech, Healthtech-Retailtech | Unidad 4: reemplazar el archivo de los recursos existentes | Hoy el estudiante **solo ve BioTech y HealthTech**: GreenTech y RetailTech están como archivo secundario. Reemplazar por los PDF combinados del grupo 20 |
| **21** | Evaluación | Revisión de los 4 cuestionarios, 12 preguntas (3 por unidad), tabla de pesos y configuración del libro de calificaciones | Cuestionarios de cada unidad y calificaciones | **Incompleto:** 18.1 y 18.2 son capturas sin texto y la pregunta 12 no tiene retroalimentación; devolverlo al grupo. Sus pesos no coinciden con los del syllabus (25/20/20/20/10/5): **los decide el docente** |
| **22** | Cierre y prueba final | Texto de cierre, instrucciones y rúbrica del plan final, autoevaluación, directorio institucional, mapa de ruta y registro de 5 fallos | Sección «[Cierre] - opcional» (hoy oculta) | La encuesta se llama «[nombre de encuesta]» y tiene 0 preguntas. Su registro de fallos (F-01 a F-05) sirve como lista final de revisión |

Los detalles de cada entrega están en [`ESTADO_Y_REPARTO.md`](ESTADO_Y_REPARTO.md) y el texto completo de cada Word en [`transcripciones/`](transcripciones/).

---

## 5. Cómo montar su parte

1. Creen un script por grupo en `herramientas/montaje/`, siguiendo `g11_casos_exito.php` (página nueva) o `g06_syllabus_bibliografia.php` (reemplazar archivos y etiquetas). Agréguenlo a la lista `orden` de `montar.sh`. Si su script usa adjuntos de un grupo, agreguen esa carpeta a la lista que copia `montar.sh`.
2. Funciones listas en `lib.php`:
   - `mt_cm` y `mt_label_with`: buscar una actividad o etiqueta.
   - `mt_set_intro`: cambiar el texto.
   - `mt_rename`: cambiar el nombre.
   - `mt_replace_resource_file`: reemplazar el archivo de un recurso.
   - `mt_create_module`: crear páginas, recursos, etiquetas o glosarios.
   - `mt_titulo`: título de bloque.
   - Colores y letras: `MT_VINO`, `MT_ROJO`, `MT_OSCURO`, `MT_FUENTE` y `MT_SERIF`.
3. Prueben con `REINICIAR=1 bash herramientas/montaje/montar.sh` y revisen el curso en http://localhost:8000. Cuando funcione, commit en su rama y Pull Request.

**Errores que ya nos pasaron y cómo evitarlos:**
- **Íconos SVG:** no los usen; el filtro de emoticonos de Moodle los rompe. Usen íconos Font Awesome (`<i class="fa fa-solid fa-…">`).
- **Barra azul de Tiles:** a cada título h2–h5 pónganle el estilo `MT_NOBAR`, o les sale la barra azul.
- **Enlaces de YouTube:** que no deban volverse reproductor llevan `class="nomediaplugin"`.
- **Páginas nuevas:** créenlas con `mt_create_module`, que ya guarda el contenido. Si no, quedan vacías.
- **Imágenes:** quedan ampliables solas, en una ventana con ✕ y botón «Cerrar». No tienen que hacer nada.
- **Videos:** van a YouTube como «no listado», nunca como MP4 dentro de Moodle.
- **Enlaces a libros:** no enlacen PDF de libros comerciales en sitios de terceros; pongan la referencia y «consulta en el Sistema de Bibliotecas UD».

---

## 6. Pendientes del equipo

- **Daniel Camacho:** agregar a `felipefbr24` como colaborador del repositorio en GitHub (*Settings → Collaborators*). Sin eso la rama `felipe/entregas-grupos` no se puede subir.
- **Docente:** confirmar fechas, pesos de calificación, datos del perfil docente, el asistente de Gemini (grupo 9) y la reorganización del acordeón.
- **Paso al aula real:** cuando el curso de prueba esté aprobado:
  1. Hacer una copia de seguridad del aula real.
  2. Restaurar en el aula real la copia del curso de prueba.
  3. Aplicar los ajustes del tema descritos en [`herramientas/montaje/README.md`](herramientas/montaje/README.md), sección «Aula real».
