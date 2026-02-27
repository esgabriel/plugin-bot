<?php
/**
 * Clase para el panel de administración
 * 
 * Maneja la interfaz de configuración en WordPress Admin
 *
 */

class Chatbot_Quaxar_Admin {
    
    /**
     * Instancia de Settings
     */
    private $settings;
    
    /**
     * Constructor
     * 
     * @param Chatbot_Quaxar_Settings $settings Instancia de Settings
     */
    public function __construct($settings) {
        $this->settings = $settings;
        $this->init_hooks();
    }
    
    /**
     * Registrar hooks de WordPress
     */
    private function init_hooks() {
        // Agregar página de configuración en el menú
        add_action('admin_menu', array($this, 'add_admin_menu'));
        
        // Registrar configuraciones
        add_action('admin_init', array($this, 'register_settings'));
        
        // Agregar enlace de configuración en la lista de plugins
        add_filter('plugin_action_links_' . plugin_basename(CHATBOT_QUAXAR_PATH . 'chatbot-quaxar.php'), 
                   array($this, 'add_settings_link'));
    }
    
    /**
     * Agregar página de configuración al menú de WordPress
     */
    public function add_admin_menu() {
        add_options_page(
            __('Configuración Chatbot IA', 'chatbot-quaxar'),
            __('Chatbot IA', 'chatbot-quaxar'),
            'manage_options',
            'chatbot-quaxar-config',
            array($this, 'render_settings_page')
        );
    }
    
    /**
     * Registrar configuraciones en WordPress
     */
    public function register_settings() {
        register_setting(
            'chatbot_quaxar_settings',
            'chatbot_quaxar_options',
            array($this, 'sanitize_callback')
        );
        
        // Sección 1 — Estado del Widget
        add_settings_section(
            'chatbot_quaxar_status_section',
            __('Estado del Widget', 'chatbot-quaxar'),
            array($this, 'render_status_section_info'),
            'chatbot-quaxar-config'
        );
        
        add_settings_field('widget_enabled', __('Activar Widget', 'chatbot-quaxar'),
            array($this, 'render_widget_enabled_field'), 'chatbot-quaxar-config', 'chatbot_quaxar_status_section');
        
        add_settings_field('visibility_mode', __('Visibilidad del Widget', 'chatbot-quaxar'),
            array($this, 'render_visibility_mode_field'), 'chatbot-quaxar-config', 'chatbot_quaxar_status_section');
        
        add_settings_field('visibility_pages', __('Páginas', 'chatbot-quaxar'),
            array($this, 'render_visibility_pages_field'), 'chatbot-quaxar-config', 'chatbot_quaxar_status_section');
        
        // Sección 2 — Conexión con el Servidor
        add_settings_section(
            'chatbot_quaxar_connection_section',
            __('Conexión con el Servidor', 'chatbot-quaxar'),
            array($this, 'render_connection_section_info'),
            'chatbot-quaxar-config'
        );
        
        add_settings_field('api_url', __('URL del Servidor (API)', 'chatbot-quaxar'),
            array($this, 'render_api_url_field'), 'chatbot-quaxar-config', 'chatbot_quaxar_connection_section');
        
        add_settings_field('api_key', __('Clave de Autenticación (API Key)', 'chatbot-quaxar'),
            array($this, 'render_api_key_field'), 'chatbot-quaxar-config', 'chatbot_quaxar_connection_section');
        
        // Sección 3 — Configuración Básica
        add_settings_section(
            'chatbot_quaxar_basic_section',
            __('Configuración Básica', 'chatbot-quaxar'),
            array($this, 'render_basic_section_info'),
            'chatbot-quaxar-config'
        );
        
        add_settings_field('chatbot_name', __('Nombre del Chatbot', 'chatbot-quaxar'),
            array($this, 'render_chatbot_name_field'), 'chatbot-quaxar-config', 'chatbot_quaxar_basic_section');
        
        add_settings_field('status_text', __('Texto de Estado', 'chatbot-quaxar'),
            array($this, 'render_status_text_field'), 'chatbot-quaxar-config', 'chatbot_quaxar_basic_section');
        
        add_settings_field('site_id', __('ID del Sitio (Site ID)', 'chatbot-quaxar'),
            array($this, 'render_site_id_field'), 'chatbot-quaxar-config', 'chatbot_quaxar_basic_section');
        
        add_settings_field('welcome_message', __('Mensaje de Bienvenida', 'chatbot-quaxar'),
            array($this, 'render_welcome_message_field'), 'chatbot-quaxar-config', 'chatbot_quaxar_basic_section');
        
        add_settings_field('input_placeholder', __('Placeholder del Input', 'chatbot-quaxar'),
            array($this, 'render_input_placeholder_field'), 'chatbot-quaxar-config', 'chatbot_quaxar_basic_section');
        
        add_settings_field('error_message', __('Mensaje de Error', 'chatbot-quaxar'),
            array($this, 'render_error_message_field'), 'chatbot-quaxar-config', 'chatbot_quaxar_basic_section');
        
        // Sección 4 — Personalización del Botón
        add_settings_section(
            'chatbot_quaxar_button_section',
            __('Personalización del Botón', 'chatbot-quaxar'),
            array($this, 'render_button_section_info'),
            'chatbot-quaxar-config'
        );
        
        add_settings_field('button_icon_type', __('Tipo de Ícono', 'chatbot-quaxar'),
            array($this, 'render_button_icon_type_field'), 'chatbot-quaxar-config', 'chatbot_quaxar_button_section');
        
        add_settings_field('button_icon_image', __('Imagen Personalizada', 'chatbot-quaxar'),
            array($this, 'render_button_icon_image_field'), 'chatbot-quaxar-config', 'chatbot_quaxar_button_section');
        
        add_settings_field('button_size', __('Tamaño del Botón', 'chatbot-quaxar'),
            array($this, 'render_button_size_field'), 'chatbot-quaxar-config', 'chatbot_quaxar_button_section');
        
        add_settings_field('button_position', __('Posición del Botón', 'chatbot-quaxar'),
            array($this, 'render_button_position_field'), 'chatbot-quaxar-config', 'chatbot_quaxar_button_section');
        
        // Sección 5 — Personalización de Colores
        add_settings_section(
            'chatbot_quaxar_customization_section',
            __('Personalización de Colores', 'chatbot-quaxar'),
            array($this, 'render_customization_section_info'),
            'chatbot-quaxar-config'
        );
        
        add_settings_field('primary_color', __('Color Primario', 'chatbot-quaxar'),
            array($this, 'render_primary_color_field'), 'chatbot-quaxar-config', 'chatbot_quaxar_customization_section');
        
        add_settings_field('secondary_color', __('Color Secundario', 'chatbot-quaxar'),
            array($this, 'render_secondary_color_field'), 'chatbot-quaxar-config', 'chatbot_quaxar_customization_section');
        
        add_settings_field('text_color', __('Color del Texto Principal', 'chatbot-quaxar'),
            array($this, 'render_text_color_field'), 'chatbot-quaxar-config', 'chatbot_quaxar_customization_section');
        
        add_settings_field('bot_text_color', __('Color del Texto (Bot)', 'chatbot-quaxar'),
            array($this, 'render_bot_text_color_field'), 'chatbot-quaxar-config', 'chatbot_quaxar_customization_section');
        
        add_settings_field('user_text_color', __('Color del Texto (Usuario)', 'chatbot-quaxar'),
            array($this, 'render_user_text_color_field'), 'chatbot-quaxar-config', 'chatbot_quaxar_customization_section');
        
        add_settings_field('input_border_color', __('Color del Borde del Input', 'chatbot-quaxar'),
            array($this, 'render_input_border_color_field'), 'chatbot-quaxar-config', 'chatbot_quaxar_customization_section');
    }
    
    /**
     * Renderizar la página de configuración
     */
    public function render_settings_page() {
        if (!current_user_can('manage_options')) {
            wp_die(__('No tienes permisos suficientes para acceder a esta página.', 'chatbot-quaxar'));
        }
        
        include CHATBOT_QUAXAR_PATH . 'admin/views/settings-page.php';
    }
    
    public function render_status_section_info() {
        echo '<p>' . __('Controla si el widget de chat es visible en el sitio. Desactívalo durante mantenimientos sin necesidad de desinstalar el plugin.', 'chatbot-quaxar') . '</p>';
    }
    
    public function render_basic_section_info() {
        echo '<p>' . __('Configura los ajustes básicos del chatbot.', 'chatbot-quaxar') . '</p>';
    }
    
    public function render_chatbot_name_field() {
        $value = $this->settings->get_option('chatbot_name');
        ?>
        <input type="text"
               name="chatbot_quaxar_options[chatbot_name]"
               id="chatbot_quaxar_chatbot_name"
               value="<?php echo esc_attr($value); ?>"
               class="regular-text"
               placeholder="Asistente Virtual">
        <p class="description">
            <?php _e('Nombre que aparece en el encabezado de la ventana del chat.', 'chatbot-quaxar'); ?>
        </p>
        <?php
    }
    
    public function render_status_text_field() {
        $value = $this->settings->get_option('status_text');
        ?>
        <input type="text"
               name="chatbot_quaxar_options[status_text]"
               id="chatbot_quaxar_status_text"
               value="<?php echo esc_attr($value); ?>"
               class="regular-text"
               placeholder="En línea">
        <p class="description">
            <?php _e('Texto que aparece debajo del nombre del chatbot. Ejemplos: "En línea", "Online", "Disponible 24/7".', 'chatbot-quaxar'); ?>
        </p>
        <?php
    }
    
    public function render_widget_enabled_field() {
        $value = $this->settings->get_option('widget_enabled');
        ?>
        <label>
            <input type="checkbox"
                   name="chatbot_quaxar_options[widget_enabled]"
                   id="chatbot_quaxar_widget_enabled"
                   value="1"
                   <?php checked($value, '1'); ?>>
            <?php _e('Mostrar el widget de chat en el sitio', 'chatbot-quaxar'); ?>
        </label>
        <p class="description">
            <?php _e('Desactiva esta opción para ocultar el chatbot temporalmente sin desinstalar el plugin. Útil durante mantenimientos.', 'chatbot-quaxar'); ?>
        </p>
        <?php
    }
    
    public function render_input_placeholder_field() {
        $value = $this->settings->get_option('input_placeholder');
        ?>
        <input type="text"
               name="chatbot_quaxar_options[input_placeholder]"
               id="chatbot_quaxar_input_placeholder"
               value="<?php echo esc_attr($value); ?>"
               class="regular-text"
               placeholder="Type your message...">
        <p class="description">
            <?php _e('Texto guía que aparece dentro del campo de texto cuando está vacío.', 'chatbot-quaxar'); ?>
        </p>
        <?php
    }
    
    public function render_error_message_field() {
        $value = $this->settings->get_option('error_message');
        ?>
        <textarea name="chatbot_quaxar_options[error_message]"
                  id="chatbot_quaxar_error_message"
                  rows="2"
                  class="large-text"><?php echo esc_textarea($value); ?></textarea>
        <p class="description">
            <?php _e('Mensaje que ve el usuario cuando el servidor no responde o hay un error de conexión.', 'chatbot-quaxar'); ?>
        </p>
        <?php
    }
    
    public function render_visibility_mode_field() {
        $value = $this->settings->get_option('visibility_mode') ?: 'all';
        ?>
        <select name="chatbot_quaxar_options[visibility_mode]" id="chatbot_quaxar_visibility_mode">
            <option value="all" <?php selected($value, 'all'); ?>>
                <?php _e('Todas las páginas', 'chatbot-quaxar'); ?>
            </option>
            <option value="include" <?php selected($value, 'include'); ?>>
                <?php _e('Solo en estas páginas', 'chatbot-quaxar'); ?>
            </option>
            <option value="exclude" <?php selected($value, 'exclude'); ?>>
                <?php _e('En todas excepto estas', 'chatbot-quaxar'); ?>
            </option>
        </select>
        <p class="description">
            <?php _e('Controla en qué páginas aparece el widget del chatbot.', 'chatbot-quaxar'); ?>
        </p>
        <?php
    }
    
    public function render_visibility_pages_field() {
        $value = $this->settings->get_option('visibility_pages');
        ?>
        <textarea name="chatbot_quaxar_options[visibility_pages]"
                  id="chatbot_quaxar_visibility_pages"
                  rows="5"
                  class="large-text"
                  placeholder="/contacto&#10;/terminos&#10;/blog"><?php echo esc_textarea($value); ?></textarea>
        <p class="description">
            <?php _e('Escribe una ruta por línea (slug de la página). Ejemplos: <code>/contacto</code>, <code>/blog</code>, <code>/terminos-y-condiciones</code>. Usa <code>/</code> para la página de inicio.', 'chatbot-quaxar'); ?>
        </p>
        <?php
    }
    
    public function render_connection_section_info() {
        echo '<p>' . __('Configura la URL del servidor backend y la clave de autenticación. Estos datos te los proporciona el equipo técnico al instalar el servidor del chatbot.', 'chatbot-quaxar') . '</p>';
    }
    
    public function render_api_url_field() {
        $value = $this->settings->get_api_url();
        ?>
        <input type="url"
               name="chatbot_quaxar_options[api_url]"
               id="chatbot_quaxar_api_url"
               value="<?php echo esc_attr($value); ?>"
               class="regular-text"
               placeholder="http://34.218.238.17:8000/api/chat">
        <p class="description">
            <?php _e('URL completa del servidor del chatbot. Debe terminar en <code>/api/chat</code>. Ejemplo: <code>http://34.218.238.17:8000/api/chat</code>', 'chatbot-quaxar'); ?>
        </p>
        <?php
    }
    
    public function render_api_key_field() {
        $has_key = !empty($this->settings->get_api_key());
        ?>
        <input type="password"
               name="chatbot_quaxar_options[api_key]"
               id="chatbot_quaxar_api_key"
               value=""
               class="regular-text"
               autocomplete="new-password"
               placeholder="<?php echo $has_key ? __('(clave guardada — escribe para cambiarla)', 'chatbot-quaxar') : __('Pega aquí la clave de autenticación', 'chatbot-quaxar'); ?>">
        <button type="button"
                onclick="var f=document.getElementById('chatbot_quaxar_api_key'); f.type=f.type==='password'?'text':'password';"
                class="button">
            <?php _e('Mostrar / Ocultar', 'chatbot-quaxar'); ?>
        </button>
        <?php if ($has_key): ?>
            <span style="color:#00a32a; margin-left:8px;">✔ <?php _e('Clave guardada correctamente', 'chatbot-quaxar'); ?></span>
        <?php endif; ?>
        <p class="description">
            <?php _e('Clave secreta que te proporciona el equipo técnico. Si dejas el campo vacío al guardar, la clave actual no cambia.', 'chatbot-quaxar'); ?>
        </p>
        <?php
    }
    
    public function render_button_section_info() {
        echo '<p>' . __('Personaliza la apariencia del botón flotante del chat.', 'chatbot-quaxar') . '</p>';
    }
    
    public function render_customization_section_info() {
        echo '<p>' . __('Personaliza los colores del chatbot para que coincida con tu sitio.', 'chatbot-quaxar') . '</p>';
    }
    
    public function render_site_id_field() {
        $value = $this->settings->get_option('site_id');
        ?>
        <input type="text" 
               name="chatbot_quaxar_options[site_id]" 
               id="chatbot_quaxar_site_id"
               value="<?php echo esc_attr($value); ?>" 
               class="regular-text"
               required>
        <p class="description">
            <?php _e('Identificador único para filtrar los documentos de este sitio. Solo letras, números, guiones y guiones bajos.', 'chatbot-quaxar'); ?>
        </p>
        <?php
    }
    
    public function render_welcome_message_field() {
        $value = $this->settings->get_option('welcome_message');
        ?>
        <textarea name="chatbot_quaxar_options[welcome_message]" 
                  id="chatbot_quaxar_welcome_message"
                  rows="3" 
                  class="large-text"><?php echo esc_textarea($value); ?></textarea>
        <p class="description">
            <?php _e('Mensaje inicial que verá el usuario al abrir el chat. Puedes usar emojis.', 'chatbot-quaxar'); ?>
        </p>
        <?php
    }
    
    public function render_button_icon_type_field() {
        $value = $this->settings->get_option('button_icon_type');
        ?>
        <fieldset>
            <label>
                <input type="radio" 
                       name="chatbot_quaxar_options[button_icon_type]" 
                       value="default" 
                       <?php checked($value, 'default'); ?>>
                <?php _e('Usar ícono por defecto (chat)', 'chatbot-quaxar'); ?>
            </label><br>
            <label>
                <input type="radio" 
                       name="chatbot_quaxar_options[button_icon_type]" 
                       value="custom" 
                       <?php checked($value, 'custom'); ?>>
                <?php _e('Usar imagen/logo personalizado', 'chatbot-quaxar'); ?>
            </label>
        </fieldset>
        <?php
    }
    
    public function render_button_icon_image_field() {
        $value = $this->settings->get_option('button_icon_image');
        ?>
        <div class="chatbot-image-upload-container">
            <input type="hidden" 
                   name="chatbot_quaxar_options[button_icon_image]" 
                   id="chatbot_quaxar_button_icon_image"
                   value="<?php echo esc_attr($value); ?>">
            
            <button type="button" 
                    class="button chatbot-upload-image-button" 
                    id="chatbot_upload_button_icon">
                <?php _e('Seleccionar Imagen', 'chatbot-quaxar'); ?>
            </button>
            
            <button type="button" 
                    class="button chatbot-remove-image-button" 
                    id="chatbot_remove_button_icon"
                    style="<?php echo empty($value) ? 'display:none;' : ''; ?>">
                <?php _e('Quitar Imagen', 'chatbot-quaxar'); ?>
            </button>
            
            <div class="chatbot-image-preview" id="chatbot_button_icon_preview">
                <?php if (!empty($value)): ?>
                    <img src="<?php echo esc_url($value); ?>" style="max-width: 60px; max-height: 60px; margin-top: 10px;">
                <?php endif; ?>
            </div>
            
            <p class="description">
                <?php _e('Tamaño recomendado: 60x60 píxeles (PNG con fondo transparente). Solo se usa si seleccionas "imagen personalizada" arriba.', 'chatbot-quaxar'); ?>
            </p>
        </div>
        <?php
    }
    
    public function render_button_size_field() {
        $value = $this->settings->get_option('button_size');
        ?>
        <select name="chatbot_quaxar_options[button_size]" id="chatbot_quaxar_button_size">
            <option value="small" <?php selected($value, 'small'); ?>>
                <?php _e('Pequeño (50px)', 'chatbot-quaxar'); ?>
            </option>
            <option value="medium" <?php selected($value, 'medium'); ?>>
                <?php _e('Mediano (60px)', 'chatbot-quaxar'); ?>
            </option>
            <option value="large" <?php selected($value, 'large'); ?>>
                <?php _e('Grande (70px)', 'chatbot-quaxar'); ?>
            </option>
        </select>
        <p class="description">
            <?php _e('Tamaño del botón flotante del chat.', 'chatbot-quaxar'); ?>
        </p>
        <?php
    }
    
    public function render_button_position_field() {
        $value = $this->settings->get_option('button_position');
        ?>
        <select name="chatbot_quaxar_options[button_position]" id="chatbot_quaxar_button_position">
            <option value="bottom-right" <?php selected($value, 'bottom-right'); ?>>
                <?php _e('Abajo a la derecha', 'chatbot-quaxar'); ?>
            </option>
            <option value="bottom-left" <?php selected($value, 'bottom-left'); ?>>
                <?php _e('Abajo a la izquierda', 'chatbot-quaxar'); ?>
            </option>
        </select>
        <p class="description">
            <?php _e('Posición del botón flotante del chat.', 'chatbot-quaxar'); ?>
        </p>
        <?php
    }
    
    public function render_primary_color_field() {
        $value = $this->settings->get_option('primary_color');
        ?>
        <input type="text" 
               name="chatbot_quaxar_options[primary_color]" 
               id="chatbot_quaxar_primary_color"
               value="<?php echo esc_attr($value); ?>" 
               class="chatbot-color-picker">
        <p class="description">
            <?php _e('Color para el botón, encabezado y elementos principales.', 'chatbot-quaxar'); ?>
        </p>
        <?php
    }
    
    public function render_secondary_color_field() {
        $value = $this->settings->get_option('secondary_color');
        ?>
        <input type="text" 
               name="chatbot_quaxar_options[secondary_color]" 
               id="chatbot_quaxar_secondary_color"
               value="<?php echo esc_attr($value); ?>" 
               class="chatbot-color-picker">
        <p class="description">
            <?php _e('Color de fondo para las burbujas de mensajes del bot.', 'chatbot-quaxar'); ?>
        </p>
        <?php
    }
    
    public function render_text_color_field() {
        $value = $this->settings->get_option('text_color');
        ?>
        <input type="text" 
               name="chatbot_quaxar_options[text_color]" 
               id="chatbot_quaxar_text_color"
               value="<?php echo esc_attr($value); ?>" 
               class="chatbot-color-picker">
        <p class="description">
            <?php _e('Color principal del texto (usado en el encabezado de la ventana y el botón flotante).', 'chatbot-quaxar'); ?>
        </p>
        <?php
    }
    
    public function render_bot_text_color_field() {
        $value = $this->settings->get_option('bot_text_color');
        ?>
        <input type="text" 
               name="chatbot_quaxar_options[bot_text_color]" 
               id="chatbot_quaxar_bot_text_color"
               value="<?php echo esc_attr($value); ?>" 
               class="chatbot-color-picker">
        <p class="description">
            <?php _e('Color del texto para las burbujas de mensajes que envía el bot.', 'chatbot-quaxar'); ?>
        </p>
        <?php
    }
    
    public function render_user_text_color_field() {
        $value = $this->settings->get_option('user_text_color');
        ?>
        <input type="text" 
               name="chatbot_quaxar_options[user_text_color]" 
               id="chatbot_quaxar_user_text_color"
               value="<?php echo esc_attr($value); ?>" 
               class="chatbot-color-picker">
        <p class="description">
            <?php _e('Color del texto para las burbujas de mensajes que escriben los usuarios.', 'chatbot-quaxar'); ?>
        </p>
        <?php
    }
    
    public function render_input_border_color_field() {
        $value = $this->settings->get_option('input_border_color');
        ?>
        <input type="text" 
               name="chatbot_quaxar_options[input_border_color]" 
               id="chatbot_quaxar_input_border_color"
               value="<?php echo esc_attr($value); ?>" 
               class="chatbot-color-picker">
        <p class="description">
            <?php _e('Color del borde del campo de texto cuando está activo (focus).', 'chatbot-quaxar'); ?>
        </p>
        <?php
    }
    
    public function sanitize_callback($input) {
        $sanitized = $this->settings->sanitize_settings($input);
        
        foreach ($sanitized as $key => $value) {
            $this->settings->update_option($key, $value);
        }
        
        add_settings_error(
            'chatbot_quaxar_messages',
            'chatbot_quaxar_message',
            __('Configuración guardada correctamente.', 'chatbot-quaxar'),
            'success'
        );
        
        return $input;
    }
    
    public function enqueue_admin_assets($hook) {
        if ($hook !== 'settings_page_chatbot-quaxar-config') {
            return;
        }
        
        wp_enqueue_media();
        wp_enqueue_style('wp-color-picker');
        
        wp_enqueue_style(
            'chatbot-quaxar-admin',
            CHATBOT_QUAXAR_URL . 'admin/css/admin-style.css',
            array(),
            CHATBOT_QUAXAR_VERSION
        );
        
        wp_enqueue_script(
            'chatbot-quaxar-admin',
            CHATBOT_QUAXAR_URL . 'admin/js/admin-script.js',
            array('jquery', 'wp-color-picker', 'media-upload'),
            CHATBOT_QUAXAR_VERSION,
            true
        );
    }
    
    public function add_settings_link($links) {
        $settings_link = '<a href="' . admin_url('options-general.php?page=chatbot-quaxar-config') . '">' 
                       . __('Configuración', 'chatbot-quaxar') 
                       . '</a>';
        array_unshift($links, $settings_link);
        return $links;
    }
}
