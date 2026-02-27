<?php
/**
 * Clase para gestionar las opciones del plugin
 * 
 * Maneja el guardado, recuperación y validación de configuraciones
 *
 */

class Chatbot_Quaxar_Settings {
    
    /**
     * Prefijo para las opciones en la base de datos
     */
    private $option_prefix = 'chatbot_quaxar_';
    
    /**
     * Nombre del grupo de opciones
     */
    private $option_group = 'chatbot_quaxar_settings';
    
    /**
     * Constructor
     */
    public function __construct() {
        // Constructor vacío, se puede usar para inicialización futura
    }
    
    /**
     * Establecer valores por defecto al activar el plugin
     */
    public function set_defaults() {
        $defaults = $this->get_defaults();
        
        foreach ($defaults as $key => $value) {
            if (get_option($this->option_prefix . $key) === false) {
                update_option($this->option_prefix . $key, $value);
            }
        }
    }
    
    /**
     * Obtener valores por defecto
     * 
     * @return array Valores por defecto
     */
    public function get_defaults() {
        return array(
            'api_url' => defined('CHATBOT_QUAXAR_API_URL') ? CHATBOT_QUAXAR_API_URL : 'http://127.0.0.1:8000/api/chat',
            'api_key' => defined('CHATBOT_QUAXAR_API_KEY') ? CHATBOT_QUAXAR_API_KEY : '',
            'site_id' => 'sitio_demo',
            'welcome_message' => '¡Hola! 👋 ¿En qué puedo ayudarte?',
            'chatbot_name'    => 'Asistente Virtual',
            'status_text'     => 'En línea',
            'widget_enabled'  => '1',
            'input_placeholder' => 'Type your message...',
            'error_message'     => 'Sorry, something went wrong. Please try again.',
            'visibility_mode'   => 'all',
            'visibility_pages'  => '',
            'primary_color' => '#0066CC',
            'secondary_color' => '#F0F4F8',
            'text_color' => '#FFFFFF',
            'bot_text_color' => '#1f2937',
            'user_text_color' => '#FFFFFF',
            'button_position' => 'bottom-right',
            'button_icon_type' => 'default',
            'button_icon_image' => '',              // URL de la imagen
            'button_size' => 'medium',
            'input_border_color' => '#0066CC'
        );
    }
    
    /**
     * Obtener una opción
     * 
     * @param string $key Clave de la opción
     * @param mixed $default Valor por defecto
     * @return mixed Valor de la opción
     */
    public function get_option($key, $default = null) {
        $value = get_option($this->option_prefix . $key, $default);
        
        // Si no hay valor y tenemos un default en los defaults del plugin
        if ($value === $default && $default === null) {
            $defaults = $this->get_defaults();
            if (isset($defaults[$key])) {
                return $defaults[$key];
            }
        }
        
        return $value;
    }
    
    /**
     * Actualizar una opción
     * 
     * @param string $key Clave de la opción
     * @param mixed $value Valor a guardar
     * @return bool True si se actualizó correctamente
     */
    public function update_option($key, $value) {
        return update_option($this->option_prefix . $key, $value);
    }
    
    /**
     * Obtener la URL de la API
     * 
     * Prioridad: valor guardado en BD > constante en wp-config.php
     * 
     * @return string URL de la API
     */
    public function get_api_url() {
        $saved = get_option($this->option_prefix . 'api_url', '');
        if (!empty($saved)) {
            return $saved;
        }
        return defined('CHATBOT_QUAXAR_API_URL') ? CHATBOT_QUAXAR_API_URL : '';
    }
    
    /**
     * Obtener la API Key
     * 
     * Prioridad: valor guardado en BD > constante en wp-config.php
     * 
     * @return string API Key
     */
    public function get_api_key() {
        $saved = get_option($this->option_prefix . 'api_key', '');
        if (!empty($saved)) {
            return $saved;
        }
        return defined('CHATBOT_QUAXAR_API_KEY') ? CHATBOT_QUAXAR_API_KEY : '';
    }
    
    /**
     * Sanitizar las opciones antes de guardar
     * 
     * @param array $input Datos del formulario
     * @return array Datos sanitizados
     */
    public function sanitize_settings($input) {
        $sanitized = array();
        
        // URL de la API
        if (isset($input['api_url'])) {
            $url = esc_url_raw(trim($input['api_url']));
            if (!empty($url) && substr($url, -9) !== '/api/chat') {
                $url = rtrim($url, '/') . '/api/chat';
            }
            $sanitized['api_url'] = $url;
        }
        
        // API Key — si viene vacía, no sobreescribir la existente
        if (isset($input['api_key'])) {
            $key = sanitize_text_field(trim($input['api_key']));
            if (!empty($key)) {
                $sanitized['api_key'] = $key;
            }
        }
        
        // Site ID: solo letras, números, guiones y guiones bajos
        if (isset($input['site_id'])) {
            $sanitized['site_id'] = sanitize_text_field($input['site_id']);
            $sanitized['site_id'] = preg_replace('/[^a-zA-Z0-9_-]/', '', $sanitized['site_id']);
        }
        
        // Mensaje de bienvenida: permitir texto con emojis
        if (isset($input['welcome_message'])) {
            $sanitized['welcome_message'] = wp_kses_post($input['welcome_message']);
        }
        
        // Nombre del chatbot
        if (isset($input['chatbot_name'])) {
            $sanitized['chatbot_name'] = sanitize_text_field($input['chatbot_name']);
        }
        
        // Texto de estado
        if (isset($input['status_text'])) {
            $sanitized['status_text'] = sanitize_text_field($input['status_text']);
        }
        
        // Widget habilitado — checkbox: si no viene en el input significa que está desmarcado
        $sanitized['widget_enabled'] = isset($input['widget_enabled']) && $input['widget_enabled'] === '1' ? '1' : '0';
        
        // Placeholder del input
        if (isset($input['input_placeholder'])) {
            $sanitized['input_placeholder'] = sanitize_text_field($input['input_placeholder']);
        }
        
        // Mensaje de error
        if (isset($input['error_message'])) {
            $sanitized['error_message'] = sanitize_text_field($input['error_message']);
        }
        
        // Modo de visibilidad
        if (isset($input['visibility_mode'])) {
            $allowed_modes = array('all', 'include', 'exclude');
            $sanitized['visibility_mode'] = in_array($input['visibility_mode'], $allowed_modes)
                ? $input['visibility_mode']
                : 'all';
        }
        
        // Páginas de visibilidad
        if (isset($input['visibility_pages'])) {
            $sanitized['visibility_pages'] = sanitize_textarea_field($input['visibility_pages']);
        }
        
        // Colores: validar formato hexadecimal
        if (isset($input['primary_color'])) {
            $sanitized['primary_color'] = $this->sanitize_hex_color($input['primary_color']);
        }
        
        if (isset($input['secondary_color'])) {
            $sanitized['secondary_color'] = $this->sanitize_hex_color($input['secondary_color']);
        }
        
        if (isset($input['text_color'])) {
            $sanitized['text_color'] = $this->sanitize_hex_color($input['text_color']);
        }
        
        if (isset($input['bot_text_color'])) {
            $sanitized['bot_text_color'] = $this->sanitize_hex_color($input['bot_text_color']);
        }
        
        if (isset($input['user_text_color'])) {
            $sanitized['user_text_color'] = $this->sanitize_hex_color($input['user_text_color']);
        }
        
        if (isset($input['input_border_color'])) {
            $sanitized['input_border_color'] = $this->sanitize_hex_color($input['input_border_color']);
        }
        
        // Posición del botón: validar opciones permitidas
        if (isset($input['button_position'])) {
            $allowed_positions = array('bottom-right', 'bottom-left');
            $sanitized['button_position'] = in_array($input['button_position'], $allowed_positions) 
                ? $input['button_position'] 
                : 'bottom-right';
        }
        
        // NUEVO: Tipo de ícono del botón
        if (isset($input['button_icon_type'])) {
            $allowed_types = array('default', 'custom');
            $sanitized['button_icon_type'] = in_array($input['button_icon_type'], $allowed_types)
                ? $input['button_icon_type']
                : 'default';
        }
        
        // NUEVO: Imagen personalizada del botón
        if (isset($input['button_icon_image'])) {
            $sanitized['button_icon_image'] = esc_url_raw($input['button_icon_image']);
        }
        
        // NUEVO: Tamaño del botón
        if (isset($input['button_size'])) {
            $allowed_sizes = array('small', 'medium', 'large');
            $sanitized['button_size'] = in_array($input['button_size'], $allowed_sizes)
                ? $input['button_size']
                : 'medium';
        }
        
        return $sanitized;
    }
    
    /**
     * Sanitizar color hexadecimal
     * 
     * @param string $color Color en formato hex
     * @return string Color sanitizado
     */
    private function sanitize_hex_color($color) {
        // Eliminar espacios
        $color = trim($color);
        
        // Agregar # si no lo tiene
        if (strpos($color, '#') !== 0) {
            $color = '#' . $color;
        }
        
        // Validar formato hexadecimal
        if (preg_match('/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/', $color)) {
            return $color;
        }
        
        // Si no es válido, retornar color por defecto
        return '#0066CC';
    }
    
    /**
     * Validar que el site_id sea válido
     * 
     * @param string $site_id Site ID a validar
     * @return bool True si es válido
     */
    public function validate_site_id($site_id) {
        // Debe tener al menos 3 caracteres
        if (strlen($site_id) < 3) {
            return false;
        }
        
        // Solo letras, números, guiones y guiones bajos
        if (!preg_match('/^[a-zA-Z0-9_-]+$/', $site_id)) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Determinar si el widget debe mostrarse en la página actual
     * 
     * @return bool True si debe mostrarse
     */
    public function should_show_widget() {
        $mode = $this->get_option('visibility_mode') ?: 'all';
        
        // Si el modo es "all", siempre mostrar
        if ($mode === 'all') {
            return true;
        }
        
        // Obtener las páginas configuradas
        $pages_raw = $this->get_option('visibility_pages');
        if (empty($pages_raw)) {
            // Si no hay páginas configuradas:
            // - include sin páginas = no mostrar en ninguna
            // - exclude sin páginas = mostrar en todas
            return $mode === 'exclude';
        }
        
        // Parsear las páginas (una por línea)
        $pages = array_filter(array_map(function($line) {
            $line = trim($line);
            // Asegurar que empiece con /
            if (!empty($line) && $line[0] !== '/') {
                $line = '/' . $line;
            }
            return rtrim($line, '/');
        }, explode("\n", $pages_raw)));
        
        if (empty($pages)) {
            return $mode === 'exclude';
        }
        
        // Obtener la ruta actual
        $current_path = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '/';
        $current_path = parse_url($current_path, PHP_URL_PATH);
        $current_path = rtrim($current_path, '/');
        
        // La home es un caso especial
        if (empty($current_path)) {
            $current_path = '/';
        }
        
        // Verificar si la ruta actual está en la lista
        $is_in_list = false;
        foreach ($pages as $page) {
            $page_clean = empty($page) ? '/' : $page;
            if ($current_path === $page_clean) {
                $is_in_list = true;
                break;
            }
        }
        
        // include: mostrar solo si está en la lista
        // exclude: mostrar solo si NO está en la lista
        return $mode === 'include' ? $is_in_list : !$is_in_list;
    }
    
    /**
     * Obtener el tamaño del botón en píxeles
     * 
     * @return int Tamaño en píxeles
     */
    public function get_button_size_px() {
        $size = $this->get_option('button_size');
        
        $sizes = array(
            'small' => 50,
            'medium' => 60,
            'large' => 70
        );
        
        return isset($sizes[$size]) ? $sizes[$size] : 60;
    }
    
    /**
     * Obtener todas las opciones del plugin
     * 
     * @return array Todas las opciones
     */
    public function get_all_settings() {
        return array(
            'api_url' => $this->get_api_url(),
            'api_key' => $this->get_api_key(),
            'site_id' => $this->get_option('site_id'),
            'welcome_message' => $this->get_option('welcome_message'),
            'chatbot_name'      => $this->get_option('chatbot_name'),
            'status_text'       => $this->get_option('status_text'),
            'widget_enabled'    => $this->get_option('widget_enabled'),
            'input_placeholder' => $this->get_option('input_placeholder'),
            'error_message'     => $this->get_option('error_message'),
            'visibility_mode'   => $this->get_option('visibility_mode'),
            'visibility_pages'  => $this->get_option('visibility_pages'),
            'primary_color' => $this->get_option('primary_color'),
            'secondary_color' => $this->get_option('secondary_color'),
            'text_color' => $this->get_option('text_color'),
            'bot_text_color' => $this->get_option('bot_text_color'),
            'user_text_color' => $this->get_option('user_text_color'),
            'button_position' => $this->get_option('button_position'),
            'button_icon_type' => $this->get_option('button_icon_type'),
            'button_icon_image' => $this->get_option('button_icon_image'),
            'button_size' => $this->get_option('button_size'),
            'button_size_px' => $this->get_button_size_px(),
            'input_border_color' => $this->get_option('input_border_color')
        );
    }
}
