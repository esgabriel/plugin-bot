<?php
/**
 * Clase principal del plugin
 * 
 * Inicializa y coordina todos los componentes del plugin
 *
 */

class Chatbot_Quaxar_Core {
    
    /**
     * Instancia de la clase Settings
     */
    private $settings;
    
    /**
     * Instancia de la clase Admin
     */
    private $admin;
    
    /**
     * Instancia de la clase Frontend
     */
    private $frontend;
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->load_dependencies();
        $this->init_hooks();
    }
    
    /**
     * Cargar dependencias y crear instancias
     */
    private function load_dependencies() {
        // Instanciar clases principales
        $this->settings = new Chatbot_Quaxar_Settings();
        $this->admin = new Chatbot_Quaxar_Admin($this->settings);
        $this->frontend = new Chatbot_Quaxar_Frontend($this->settings);
    }
    
    /**
     * Registrar hooks de WordPress
     */
    private function init_hooks() {
        // Hook para cargar traducciones
        add_action('plugins_loaded', array($this, 'load_textdomain'));
        
        // Hook para scripts y estilos en admin
        add_action('admin_enqueue_scripts', array($this->admin, 'enqueue_admin_assets'));
        
        // Hook para scripts y estilos en frontend
        add_action('wp_enqueue_scripts', array($this->frontend, 'enqueue_public_assets'));
        
        // Hook para renderizar el widget en el footer
        add_action('wp_footer', array($this->frontend, 'render_chat_widget'));
    }
    
    /**
     * Cargar traducciones del plugin
     */
    public function load_textdomain() {
        load_plugin_textdomain(
            'chatbot-quaxar',
            false,
            dirname(plugin_basename(__FILE__)) . '/languages/'
        );
    }
    
    /**
     * Ejecutar el plugin
     */
    public function run() {
        // El plugin ya está corriendo a través de los hooks
        // Esta función existe por si necesitas lógica adicional de inicialización
    }
}
