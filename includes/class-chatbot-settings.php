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
            'site_id' => 'sitio_demo',
            'welcome_message' => '¡Hola! 👋 ¿En qué puedo ayudarte?',
            'primary_color' => '#0066CC',
            'secondary_color' => '#F0F4F8',
            'text_color' => '#FFFFFF',
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
     * Esta URL NO es modificable por el usuario
     * Se configura en wp-config.php o está hardcodeada
     * 
     * @return string URL de la API
     */
    public function get_api_url() {
        return CHATBOT_QUAXAR_API_URL;
    }
    
    /**
     * Sanitizar las opciones antes de guardar
     * 
     * @param array $input Datos del formulario
     * @return array Datos sanitizados
     */
    public function sanitize_settings($input) {
        $sanitized = array();
        
        // Site ID: solo letras, números, guiones y guiones bajos
        if (isset($input['site_id'])) {
            $sanitized['site_id'] = sanitize_text_field($input['site_id']);
            $sanitized['site_id'] = preg_replace('/[^a-zA-Z0-9_-]/', '', $sanitized['site_id']);
        }
        
        // Mensaje de bienvenida: permitir texto con emojis
        if (isset($input['welcome_message'])) {
            $sanitized['welcome_message'] = wp_kses_post($input['welcome_message']);
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
            'site_id' => $this->get_option('site_id'),
            'welcome_message' => $this->get_option('welcome_message'),
            'primary_color' => $this->get_option('primary_color'),
            'secondary_color' => $this->get_option('secondary_color'),
            'text_color' => $this->get_option('text_color'),
            'button_position' => $this->get_option('button_position'),
            'button_icon_type' => $this->get_option('button_icon_type'),
            'button_icon_image' => $this->get_option('button_icon_image'),
            'button_size' => $this->get_option('button_size'),
            'button_size_px' => $this->get_button_size_px(),
            'input_border_color' => $this->get_option('input_border_color')
        );
    }
}
