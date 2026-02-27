# Chatbot Quaxar IA - Plugin de WordPress

**Descripción:**  
Chatbot inteligente con IA para sitios WordPress de Quaxar. Se conecta con el backend Python para responder preguntas basadas en documentos.

---

## 🛠️ Requisitos Técnicos Mínimos

- **WordPress:** Versión 5.0 o superior (estimación basada en API estándar).
- **PHP:** Versión 7.4 o superior (compatible con la sintaxis de las clases desarrolladas).
- **Dependencias Externas:**
  - Requiere conexión activa con la API del backend de chatbot. La URL y la API Key se configuran desde **Ajustes → Chatbot IA** en el panel de WordPress.
  - Llama y encola al instante una dependencia CDN de **DOMPurify** (versión 3.1.6, `cdnjs.cloudflare.com`) obligatoria por motivos de seguridad; el script purifica el HTML devuelto por la IA para prevenir ataques XSS.

---

## 🚀 Instalación y Activación

### Paso 1: Subida de la carpeta
1. Descargar los archivos del plugin y extraerlos si están en formato `.zip`.
2. Subir la carpeta completa **`quaxar-chatbot-wordpress-plugin`** a la ruta `/wp-content/plugins/` del servidor.
   - *Alternativa:* En wp-admin, ir a **Plugins > Añadir nuevo > Subir plugin**, subir el archivo `.zip` y seguir los pasos de la interfaz.

### Paso 2: Activación
1. Ir al panel de control de WordPress.
2. Navegar al menú izquierdo en **Plugins > Plugins instalados**.
3. Localizar el plugin **Chatbot Quaxar IA**.
4. Hacer clic en el botón de **Activar**.

### Paso 3: Configuración de la API
La URL del servidor y la API Key se configuran desde el panel de administración de WordPress en **Ajustes → Chatbot IA**, sección **Conexión con el Servidor**.

---

## ⚙️ Configuración del Plugin en wp-admin

La interfaz de administración se encuentra en **Ajustes → Chatbot IA** y está dividida en **5 secciones**, un **sidebar informativo** y una **vista previa en tiempo real**.

### Sección 1 — Estado del Widget
Controla la visibilidad del chatbot en el sitio:
- **Activar Widget:** Checkbox para activar o desactivar el widget sin necesidad de desinstalar el plugin. Útil durante mantenimientos.
- **Visibilidad del Widget:** Selector con tres opciones:
  - *Todas las páginas* — el widget aparece en todo el sitio.
  - *Solo en estas páginas* — se muestra únicamente en las rutas especificadas.
  - *En todas excepto estas* — se oculta en las rutas especificadas.
- **Páginas:** Textarea donde se escriben las rutas (una por línea, ej: `/contacto`, `/blog`, `/`).

### Sección 2 — Conexión con el Servidor
Configura los datos de conexión con el backend:
- **URL del Servidor (API):** URL completa del servidor del chatbot. Debe terminar en `/api/chat` (se agrega automáticamente si falta).
- **Clave de Autenticación (API Key):** Campo tipo contraseña con botón **Mostrar / Ocultar**. Si hay una clave guardada, se muestra un indicador ✔ verde. Si se deja vacío al guardar, la clave existente **no se modifica**.

### Sección 3 — Configuración Básica
Ajustes generales del chatbot:
- **Nombre del Chatbot:** Nombre que aparece en el encabezado de la ventana del chat (ej: `Asistente Virtual`).
- **Texto de Estado:** Texto debajo del nombre (ej: `En línea`, `Disponible 24/7`).
- **ID del Sitio (Site ID):** Identificador único para filtrar documentos. Solo letras, números, guiones y guiones bajos.
- **Mensaje de Bienvenida:** Mensaje inicial al abrir el chat. Soporta emojis.
- **Placeholder del Input:** Texto guía dentro del campo de texto cuando está vacío.
- **Mensaje de Error:** Mensaje que ve el usuario cuando el servidor no responde o hay un error de conexión.

### Sección 4 — Personalización del Botón
Configura la apariencia del botón flotante:
- **Tipo de Ícono:** Ícono por defecto (burbuja de chat SVG) o imagen/logo personalizado desde la librería de medios de WordPress.
- **Imagen Personalizada:** Seleccionar imagen desde la librería de medios. Tamaño recomendado: 60×60px (PNG con fondo transparente).
- **Tamaño del Botón:** Pequeño (50px), Mediano (60px) o Grande (70px).
- **Posición del Botón:** Abajo a la derecha o abajo a la izquierda.

### Sección 5 — Personalización de Colores
Selectores de color para adaptar el chatbot al diseño del sitio:
- **Color Primario:** Botón flotante, encabezado y elementos principales.
- **Color Secundario:** Fondo de las burbujas de mensajes del bot.
- **Color del Texto Principal:** Texto en el encabezado y el botón flotante.
- **Color del Texto (Bot):** Texto dentro de las burbujas de mensajes del bot.
- **Color del Texto (Usuario):** Texto dentro de las burbujas de mensajes del usuario.
- **Color del Borde del Input:** Borde del campo de texto cuando está activo (focus).

### Sidebar del Panel
El panel de configuración incluye un sidebar con dos cards:
- **Información:** Muestra la versión actual del plugin y el estado de la API. El Health Check realiza una petición al endpoint `/health` del servidor usando la URL guardada en los ajustes.
  - 🟢 **Conectado y Operativo** — el servidor responde correctamente.
  - 🔴 **Desconectado o Error en API** — no hay respuesta o el servidor devuelve error.
- **Vista Previa:** Muestra en tiempo real cómo se verán los colores configurados en el botón del chat y la burbuja del bot.

---

## ⚠️ Advertencias Importantes

### Requisito de HTTPS (Mixed Content)
Si el sitio WordPress se sirve por **HTTPS**, la URL de la API también **debe** usar HTTPS. Una URL `http://` desde una página `https://` será bloqueada automáticamente por el navegador como **Mixed Content**. El Health Check mostrará 🔴 y el chat no funcionará.

**Solución:** Asegurar que la URL configurada en el campo *URL del Servidor (API)* use el protocolo `https://`.

### Seguridad de la API Key
La API Key se almacena en la tabla `wp_options` de WordPress. Por seguridad:
- El campo **nunca** muestra la clave en texto plano; siempre se presenta como campo de tipo contraseña.
- Si se deja **vacío** al guardar, la clave existente no se sobreescribe.
- Se recomienda usar conexiones HTTPS entre WordPress y el servidor de la API para proteger el token en tránsito.

---

## 📖 Documentación Técnica: Hooks, Shortcodes y Post Types

A nivel lógico, la integración del plugin en el ecosistema del CMS es la siguiente:

### 1. Shortcodes
- **Ninguno disponible.** Este plugin procesa el frontend inyectando la estructura sin requerir shortcodes, renderizando dinámicamente un contendor sobre todo el frontend y las entradas públicas.

### 2. Custom Post Types Registrados
- **Ninguno creado ni registrado.** El flujo de persistencia depende de su base externa (el CMS no guarda información localmente); toda lógica recae en las opciones nativas de WordPress (`wp_options`).

### 3. Hooks Principales

Las implementaciones controlan qué recursos cargar mediante diversos Hooks predefinidos en `chatbot-quaxar.php`, `class-chatbot-core.php`, etc.

**Acciones (`add_action`)**
- `plugins_loaded`: Carga la traducción local con `load_plugin_textdomain` instanciada en `class-chatbot-core.php`.
- `admin_menu`: Añade la propia opción bajo las subrutas de Ajustes, ejecutando un `add_options_page()` hacia `chatbot-quaxar-config`.
- `admin_init`: Registra dinámicamente (`register_setting()`, `add_settings_section()`, `add_settings_field()`) todos los campos y colores mostrados al administrador global.
- `admin_enqueue_scripts`: Condiciona el entorno y garantiza que el CSS `admin-style.css` y las bibliotecas JS `wp-color-picker` y `media-upload` solo actúen al estar en las áreas de configuración exactas.
- `wp_enqueue_scripts`: Añade al script global los recursos `chatbot-widget.css` y su script homólogo e integra `wp_localize_script()` comunicando todas las selecciones del wp-admin listas para consumir vía JSON por el compilado local.
- `wp_footer`: Utilizado por el método `render_chat_widget()` en el Frontend. Permite inyectar silenciosamente el árbol DOM (botones y ventana del chat) final al cuerpo de la página.

**Filtros (`add_filter`)**
- `plugin_action_links_{plugin_basename}`: Vincula el enlace visible de "Configuración" desde el menú clásico de vistas rápidas en `plugins.php`, agilizando los accesos desde la administración.
