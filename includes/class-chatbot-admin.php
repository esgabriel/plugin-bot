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
        
        // Sección de configuración básica
        add_settings_section(
            'chatbot_quaxar_basic_section',
            __('Configuración Básica', 'chatbot-quaxar'),
            array($this, 'render_basic_section_info'),
            'chatbot-quaxar-config'
        );
        
        add_settings_field('site_id', __('ID del Sitio (Site ID)', 'chatbot-quaxar'), 
                          array($this, 'render_site_id_field'), 'chatbot-quaxar-config', 'chatbot_quaxar_basic_section');
        
        add_settings_field('welcome_message', __('Mensaje de Bienvenida', 'chatbot-quaxar'), 
                          array($this, 'render_welcome_message_field'), 'chatbot-quaxar-config', 'chatbot_quaxar_basic_section');
        
        // Sección de personalización del botón
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
        
        // Sección de personalización de colores
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
        
        add_settings_field('text_color', __('Color del Texto', 'chatbot-quaxar'),
                          array($this, 'render_text_color_field'), 'chatbot-quaxar-config', 'chatbot_quaxar_customization_section');
        
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
    
    public function render_basic_section_info() {
        echo '<p>' . __('Configura los ajustes básicos del chatbot.', 'chatbot-quaxar') . '</p>';
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
            <?php _e('Color del texto en el encabezado y botón principal.', 'chatbot-quaxar'); ?>
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
