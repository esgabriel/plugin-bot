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
        wp_enqueue_style(
            'chatbot-quaxar-widget',
            CHATBOT_QUAXAR_URL . 'public/css/chatbot-widget.css',
            array(),
            CHATBOT_QUAXAR_VERSION
        );
        
        wp_enqueue_script(
            'chatbot-quaxar-widget',
            CHATBOT_QUAXAR_URL . 'public/js/chatbot-widget.js',
            array('jquery'),
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
            'welcomeMessage' => $this->settings->get_option('welcome_message'),
            'primaryColor' => $this->settings->get_option('primary_color'),
            'secondaryColor' => $this->settings->get_option('secondary_color'),
            'textColor' => $this->settings->get_option('text_color'),
            'buttonPosition' => $this->settings->get_option('button_position'),
            'buttonIconType' => $this->settings->get_option('button_icon_type'),
            'buttonIconImage' => $this->settings->get_option('button_icon_image'),
            'buttonSize' => $this->settings->get_button_size_px(),
            'inputBorderColor' => $this->settings->get_option('input_border_color'),
            'pluginUrl' => CHATBOT_QUAXAR_URL,
            'nonce' => wp_create_nonce('chatbot_quaxar_nonce')
        );
        
        wp_localize_script('chatbot-quaxar-widget', 'chatbotQuaxarConfig', $config);
    }
    
    /**
     * Renderizar el HTML del widget de chat
     */
    public function render_chat_widget() {
        $button_position = $this->settings->get_option('button_position');
        $primary_color = $this->settings->get_option('primary_color');
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
                    <svg class="chatbot-icon-close" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z" fill="currentColor"/>
                    </svg>
                <?php else: ?>
                    <!-- Ícono por defecto -->
                    <svg class="chatbot-icon-open" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M20 2H4c-1.1 0-2 .9-2 2v18l4-4h14c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2z" fill="currentColor"/>
                    </svg>
                    <svg class="chatbot-icon-close" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z" fill="currentColor"/>
                    </svg>
                <?php endif; ?>
            </button>
            
            <!-- Ventana del chat -->
            <div id="chatbot-quaxar-window" class="chatbot-quaxar-window">
                
                <!-- Header -->
                <div class="chatbot-quaxar-header" style="background-color: <?php echo esc_attr($primary_color); ?>;">
                    <div class="chatbot-quaxar-header-content">
                        <div class="chatbot-quaxar-avatar">
                            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 3c1.66 0 3 1.34 3 3s-1.34 3-3 3-3-1.34-3-3 1.34-3 3-3zm0 14.2c-2.5 0-4.71-1.28-6-3.22.03-1.99 4-3.08 6-3.08 1.99 0 5.97 1.09 6 3.08-1.29 1.94-3.5 3.22-6 3.22z" fill="currentColor"/>
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
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z" fill="currentColor"/>
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
                               autocomplete="off"
                               required>
                        <button type="submit" 
                                class="chatbot-quaxar-send-btn"
                                aria-label="<?php esc_attr_e('Enviar mensaje', 'chatbot-quaxar'); ?>"
                                style="background-color: <?php echo esc_attr($primary_color); ?>;">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z" fill="currentColor"/>
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
