# Chatbot Quaxar IA - Plugin de WordPress

**Descripción:**  
Chatbot inteligente con IA para sitios WordPress de Quaxar. Se conecta con el backend Python para responder preguntas basadas en documentos.

---

## 🛠️ Requisitos Técnicos Mínimos

- **WordPress:** Versión 5.0 o superior (estimación basada en API estándar).
- **PHP:** Versión 7.4 o superior (compatible con la sintaxis de las clases desarrolladas).
- **Dependencias Externas:**
  - Requiere conexión activa con la API del backend de chatbot (por defecto `http://127.0.0.1:8000/api/chat` u otro valor fijado en `wp-config.php`).
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

### Paso 3: Configuración Avanzada de API (Recomendado)
Para proteger las URL y credenciales de API del acceso web, abrir el archivo `wp-config.php` y en una línea superior a `/* ¡Eso es todo, dejar de editar! */` definir las variables de entorno:
```php
define('CHATBOT_QUAXAR_API_URL', 'https://mi-api.ejemplo.com/api/chat');
define('CHATBOT_QUAXAR_API_KEY', 'token_seguro');
```

---

## ⚙️ Configuración del Plugin en wp-admin

La interfaz nativa del administrador dispone de una página de control global:
- **Ruta de Acceso:** En la barra izquierda de WordPress wp-admin, buscar **Ajustes > Chatbot IA**.
- **Opciones Base:** Aquí se puede fijar el `Site ID` para clasificar documentos, y personalizar un `Mensaje de Bienvenida` animado (soporta emojis).
- **Visual del Botón:** Configurar la posición del botón flotante (esquina inferior izquierda o derecha), se pueden definir tamaños (pequeño al grande) e incluso reemplazar el icono original vectorizado por un logo o imagen personalizada proveniente de la librería de medios.
- **Paleta de Colores Exclusiva:** Para adaptarse visualmente con el diseño del tema particular, las configuraciones tienen varios selectores de color que asignan atributos exactos al `Color Primario`, `Color Secundario`, colores de texto, y variaciones hexadecimales para los mensajes del botón y el usuario dentro de las burbujas flotantes.

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
