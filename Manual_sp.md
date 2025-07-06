# Manual de Usuario – AI SEO Meta by Gemini (Versión Temática)

## Descripción
Este plugin para WordPress genera automáticamente títulos, descripciones y palabras clave SEO optimizadas para Rank Math SEO, usando inteligencia artificial Gemini y prompts personalizables por temática y ubicación local.

---

## Instalación
1. **Requisitos previos:**
   - WordPress 5.0 o superior.
   - Plugin Rank Math SEO instalado y activo.
   - API Key de Gemini (https://openrouter.ai/).
2. **Sube la carpeta del plugin** a `/wp-content/plugins/` o instálalo como ZIP desde el panel de WordPress.
3. **Activa el plugin** desde el menú de Plugins en WordPress.

![Pantalla de plugins de WordPress](IMAGEN_PLUGIN_ACTIVADO.png)

---

## Configuración inicial
1. Ve a **Rank Math > Gemini API Key** en el menú de administración.
2. Ingresa tu API Key de Gemini.
3. Personaliza la ubicación local, el topic por defecto y el prompt general si lo deseas.
4. Guarda los cambios.

![Pantalla de configuración del plugin](IMAGEN_CONFIGURACION.png)

---

## Uso y explotación
### Generación masiva de SEO
1. Ve a **Rank Math > AI SEO Generator**.
2. Haz clic en “Generar SEO Meta Masivo”.
3. El plugin generará y actualizará automáticamente los campos SEO de las entradas pendientes.

![Pantalla de generación masiva](IMAGEN_GENERACION_MASIVA.png)

### Edición manual en entradas
- Al editar una entrada, verás en la barra lateral los campos:
  - Temática SEO (Gemini)
  - Palabra clave principal
  - Palabras clave sugeridas
- Puedes editar estos campos manualmente y guardar los cambios.

![Metabox en editor de entradas](IMAGEN_EDITOR_ENTRADA.png)

---

## Desinstalación
1. Desactiva el plugin desde el menú de Plugins.
2. (Opcional) Elimina el plugin para borrar los archivos.
3. Los metadatos generados permanecerán en las entradas a menos que los borres manualmente.

---

## Soporte y contacto
- Desarrollado por Kassim & AI Systems
- Correo: KassimCITO@gmail.com
- Celular/WhatsApp: +52 (443) 505.1882

---

> **Nota:** Las imágenes de ejemplo deben ser capturas reales de tu instalación para mayor claridad.
---

## ¿Cómo se genera el slug SEO?

El plugin crea automáticamente slugs (URL amigables) optimizados para SEO local siguiendo estas reglas:

1. El slug se basa en el título generado por Gemini, normalizado a minúsculas y solo con letras, números y guiones.
2. Si la palabra clave principal (focus keyword) no está al inicio, se antepone al slug.
3. Se eliminan stopwords comunes en español (ej: de, la, y, en, para, etc.) para mayor limpieza y relevancia.
4. El slug se limita a un máximo de 8 palabras.
5. Se eliminan palabras que sean fechas (años, meses, días, números sueltos).
6. Si el slug ya existe en otro post, se añade un sufijo incremental para garantizar unicidad.

**Ejemplo visual:**

Supón que el título generado es:
    "Noticias de la Feria de Apatzingán en julio 2024"
y la palabra clave principal es:
    "feria apatzingan"

El slug resultante será:
    feria-apatzingan-noticias

Si ya existe, se generará:
    feria-apatzingan-noticias-2

Así se obtiene una URL limpia, relevante y optimizada para SEO local.
---

## Redirecciones automáticas tras cambio de slug

Cuando el plugin modifica el slug de una entrada, si el módulo de Redirecciones de Rank Math está activo, se crea automáticamente una redirección 301 de la URL anterior a la nueva. Así, los visitantes y los motores de búsqueda no encontrarán errores y el SEO no se ve afectado.

No necesitas hacer nada manualmente: la redirección se gestiona de forma transparente.
