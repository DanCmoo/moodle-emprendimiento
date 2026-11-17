---
name: publicar-respaldo-moodle
description: Actualiza en el repositorio GitHub del proyecto la copia de seguridad exportada de un curso Moodle (.mbz), conservando una ruta estable y evitando publicar datos sensibles.
metadata:
  short-description: Publica respaldo Moodle en GitHub
---

# Publicar respaldo Moodle

Usa esta skill cuando el equipo quiera publicar o reemplazar el `.mbz` del curso en este repositorio.

1. Localiza el `.mbz` que el usuario exportó desde Moodle. La skill no exporta el curso desde una instancia Moodle por sí sola.
2. Confirma que es la copia destinada al equipo y revisa nombre y tamaño. No inspecciones ni publiques información personal de estudiantes. Si el archivo pudiera incluir datos reales, solicita una versión saneada y detente antes de subir.
3. Conserva una ruta estable: `backups/curso-latest.mbz`. Crea `backups/` si hace falta y reemplaza solo ese archivo. No agregues múltiples copias binarias con nombres de fecha sin que el usuario lo pida.
4. Comprueba que `backups/curso-latest.mbz` esté excluido del patrón `*.mbz` del `.gitignore` y que el resto de backups/secretos no vaya a quedar incluido.
5. Revisa `git status` y el diff de los archivos de texto. No incluyas `config.php`, bases de datos, `moodledata`, credenciales ni artefactos del entorno Docker local.
6. Si el respaldo tiene más de 100 MB, no lo fuerces a Git normal; propone Git LFS o un GitHub Release y sigue solo con la alternativa que el usuario haya aprobado.
7. Antes de un `git push` o una carga vía GitHub, informa qué ruta y tamaño se publicarán y pide confirmación si esta conversación no autoriza explícitamente la publicación concreta. No cambies la visibilidad del repositorio.
8. Publica en la rama del proyecto según sus instrucciones (preferiblemente rama de trabajo + Pull Request si el equipo usa revisión). Nunca sobrescribas historial remoto ni fuerces el push.
9. Confirma el commit/enlace resultante y recuerda que descargar el `.mbz` no sincroniza cambios hechos en las Moodle locales: cada actualización requiere nueva exportación y publicación.

El respaldo canónico del repo es `backups/curso-latest.mbz`. Para bajar la versión actualizada, los integrantes deben hacer `git pull` o descargar el archivo desde GitHub y restaurarlo en su Moodle local.
