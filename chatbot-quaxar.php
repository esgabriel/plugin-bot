<?php
/**
 * Plugin Name: Chatbot Quaxar IA
 * Description: Chatbot inteligente con IA para sitios WordPress de Quaxar. Se conecta con el backend Python para responder preguntas basadas en documentos.
 * Version: 1.0.0
 */

if (!defined('WPINC')) {
    die;
}

/**
 * Versión actual del plugin
 */
define('CHATBOT_QUAXAR_VERSION', '1.0.0');

/**
 * Ruta del plugin
 */
define('CHATBOT_QUAXAR_PATH', plugin_dir_path(__FILE__));

/**
 * URL del plugin
 */
define('CHATBOT_QUAXAR_URL', plugin_dir_url(__FILE__));

/**
 * URL de la API (configurable en wp-config.php)
 * 
 * Para configurar en wp-config.php, agregar:
 * definir('CHATBOT_QUAXAR_API_URL', 'https://api.quaxar.com/api/chat');
 */
if (!defined('CHATBOT_QUAXAR_API_URL')) {
    // URL por defecto (se puede cambiar aquí o en wp-config.php)
    define('CHATBOT_QUAXAR_API_URL', 'http://127.0.0.1:8000/api/chat');
}

if (!defined('CHATBOT_QUAXAR_API_KEY')) {
    define('CHATBOT_QUAXAR_API_KEY', '');  // Se configura en wp-config.php
}

/**
 * Cargar las clases principales del plugin
 */
require_once CHATBOT_QUAXAR_PATH . 'includes/class-chatbot-core.php';
require_once CHATBOT_QUAXAR_PATH . 'includes/class-chatbot-settings.php';
require_once CHATBOT_QUAXAR_PATH . 'includes/class-chatbot-admin.php';
require_once CHATBOT_QUAXAR_PATH . 'includes/class-chatbot-frontend.php';

/**
 * Función de activación del plugin
 * Se ejecuta cuando el plugin es activado
 */
function activate_chatbot_quaxar() {
    // Establecer valores por defecto
    $settings = new Chatbot_Quaxar_Settings();
    $settings->set_defaults();
    
    // Limpiar rewrite rules
    flush_rewrite_rules();
}

/**
 * Función de desactivación del plugin
 * Se ejecuta cuando el plugin es desactivado
 */
function deactivate_chatbot_quaxar() {
    // Limpiar rewrite rules
    flush_rewrite_rules();
}

// Registrar hooks de activación y desactivación
register_activation_hook(__FILE__, 'activate_chatbot_quaxar');
register_deactivation_hook(__FILE__, 'deactivate_chatbot_quaxar');

/**
 * Iniciar el plugin
 */
function run_chatbot_quaxar() {
    $plugin = new Chatbot_Quaxar_Core();
    $plugin->run();
}

// Ejecutar el plugin
run_chatbot_quaxar();
