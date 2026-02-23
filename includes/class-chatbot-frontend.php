<?php
/**
 * Clase para el frontend público
 * 
 * Maneja la visualización del widget de chat en las páginas públicas
 *
 */

class Chatbot_Quaxar_Frontend {
    
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
    }
    
    /**
     * Encolar scripts y estilos en el frontend
     */
    public function enqueue_public_assets() {
        // Agregar DOMPurify para sanitización XSS del HTML generado por el bot
        wp_enqueue_script(
            'dompurify',
            'https://cdnjs.cloudflare.com/ajax/libs/dompurify/3.1.6/purify.min.js',
            array(),
            '3.1.6',
            true
        );

        wp_enqueue_style(
            'chatbot-quaxar-widget',
            CHATBOT_QUAXAR_URL . 'public/css/chatbot-widget.css',
            array(),
            CHATBOT_QUAXAR_VERSION
        );

        wp_enqueue_script(
            'chatbot-quaxar-widget',
            CHATBOT_QUAXAR_URL . 'public/js/chatbot-widget.js',
            array('dompurify'),
            CHATBOT_QUAXAR_VERSION,
            true
        );

        $this->localize_script();
    }
    
    /**
     * Pasar datos de PHP a JavaScript
     */
    private function localize_script() {
        $config = array(
            'apiUrl' => $this->settings->get_api_url(),
            'siteId' => $this->settings->get_option('site_id'),
            'apiKey' => defined('CHATBOT_QUAXAR_API_KEY') ? CHATBOT_QUAXAR_API_KEY : '',
            'welcomeMessage' => $this->settings->get_option('welcome_message') ?: '¡Hola! 👋 ¿En qué puedo ayudarte?',
            'primaryColor' => $this->settings->get_option('primary_color') ?: '#0066CC',
            'secondaryColor' => $this->settings->get_option('secondary_color') ?: '#F0F4F8',
            'textColor' => $this->settings->get_option('text_color') ?: '#FFFFFF',
            'botTextColor' => $this->settings->get_option('bot_text_color') ?: '#1f2937',
            'userTextColor' => $this->settings->get_option('user_text_color') ?: '#FFFFFF',
            'buttonPosition' => $this->settings->get_option('button_position') ?: 'bottom-right',
            'buttonIconType' => $this->settings->get_option('button_icon_type'),
            'buttonIconImage' => $this->settings->get_option('button_icon_image'),
            'buttonSize' => $this->settings->get_button_size_px(),
            'pluginUrl' => CHATBOT_QUAXAR_URL,
            'nonce' => wp_create_nonce('chatbot_quaxar_nonce')
        );
        
        wp_localize_script('chatbot-quaxar-widget', 'chatbotQuaxarConfig', $config);
    }
    
    /**
     * Renderizar el HTML del widget de chat
     */
    public function render_chat_widget() {
        $button_position = $this->settings->get_option('button_position') ?: 'bottom-right';
        $primary_color = $this->settings->get_option('primary_color') ?: '#0066CC';
        $button_size = $this->settings->get_button_size_px();
        $icon_type = $this->settings->get_option('button_icon_type');
        $custom_image = $this->settings->get_option('button_icon_image');
        ?>
        <!-- Chatbot Quaxar IA -->
        <div id="chatbot-quaxar-container" class="chatbot-quaxar-position-<?php echo esc_attr($button_position); ?>">
            
            <!-- Botón flotante -->
            <button id="chatbot-quaxar-toggle" 
                    class="chatbot-quaxar-toggle" 
                    aria-label="<?php esc_attr_e('Abrir chat', 'chatbot-quaxar'); ?>"
                    style="background-color: <?php echo esc_attr($primary_color); ?>; width: <?php echo $button_size; ?>px; height: <?php echo $button_size; ?>px;">
                
                <?php if ($icon_type === 'custom' && !empty($custom_image)): ?>
                    <!-- Imagen personalizada -->
                    <img src="<?php echo esc_url($custom_image); ?>" 
                         alt="Chat" 
                         class="chatbot-icon-custom chatbot-icon-open"
                         style="width: 60%; height: 60%; object-fit: contain;">
                    <svg class="chatbot-icon-close" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="18" y1="6" x2="6" y2="18"></line>
                        <line x1="6" y1="6" x2="18" y2="18"></line>
                    </svg>
                <?php else: ?>
                    <!-- Ícono por defecto (Mensaje moderno) -->
                    <svg class="chatbot-icon-open" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
                    </svg>
                    <svg class="chatbot-icon-close" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="18" y1="6" x2="6" y2="18"></line>
                        <line x1="6" y1="6" x2="18" y2="18"></line>
                    </svg>
                <?php endif; ?>
            </button>
            
            <!-- Ventana del chat -->
            <div id="chatbot-quaxar-window" class="chatbot-quaxar-window">
                
                <!-- Header -->
                <div class="chatbot-quaxar-header" style="background-color: <?php echo esc_attr($primary_color); ?>;">
                    <div class="chatbot-quaxar-header-content">
                        <div class="chatbot-quaxar-avatar">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="3" y="11" width="18" height="10" rx="2"></rect>
                                <circle cx="12" cy="7" r="4"></circle>
                                <line x1="12" y1="11" x2="12" y2="21"></line>
                            </svg>
                        </div>
                        <div class="chatbot-quaxar-title">
                            <h3><?php esc_html_e('Asistente Virtual', 'chatbot-quaxar'); ?></h3>
                            <span class="chatbot-quaxar-status"><?php esc_html_e('En línea', 'chatbot-quaxar'); ?></span>
                        </div>
                    </div>
                    <button id="chatbot-quaxar-close" 
                            class="chatbot-quaxar-close-btn"
                            aria-label="<?php esc_attr_e('Cerrar chat', 'chatbot-quaxar'); ?>">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="18" y1="6" x2="6" y2="18"></line>
                            <line x1="6" y1="6" x2="18" y2="18"></line>
                        </svg>
                    </button>
                </div>
                
                <!-- Mensajes -->
                <div id="chatbot-quaxar-messages" class="chatbot-quaxar-messages">
                    <!-- Los mensajes se agregan dinámicamente aquí -->
                </div>
                
                <!-- Input -->
                <div class="chatbot-quaxar-input-container">
                    <form id="chatbot-quaxar-form">
                        <input type="text" 
                               id="chatbot-quaxar-input" 
                               class="chatbot-quaxar-input"
                               placeholder="<?php esc_attr_e('Escribe tu pregunta...', 'chatbot-quaxar'); ?>"
                               autocomplete="off">
                        <button type="submit" 
                                class="chatbot-quaxar-send-btn"
                                aria-label="<?php esc_attr_e('Enviar mensaje', 'chatbot-quaxar'); ?>"
                                style="background-color: <?php echo esc_attr($primary_color); ?>;">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <line x1="22" y1="2" x2="11" y2="13"></line>
                                <polygon points="22 2 15 22 11 13 2 9 22 2"></polygon>
                            </svg>
                        </button>
                    </form>
                </div>
                
            </div>
            
        </div>
        <!-- /Chatbot Quaxar IA -->
        <?php
    }
}