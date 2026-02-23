<?php
/**
 * Vista del panel de configuración
 * 
 * HTML de la página de configuración en WordPress Admin
 *
 */

// Si este archivo es llamado directamente, abortar.
if (!defined('WPINC')) {
    die;
}
?>

<div class="wrap chatbot-quaxar-admin-wrap">
    
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
    
    <div class="chatbot-quaxar-admin-container">
        
        <!-- Columna principal -->
        <div class="chatbot-quaxar-admin-main">
            
            <?php settings_errors('chatbot_quaxar_messages'); ?>
            
            <form method="post" action="options.php">
                <?php
                // Campos de seguridad
                settings_fields('chatbot_quaxar_settings');
                
                // Secciones y campos
                do_settings_sections('chatbot-quaxar-config');
                
                // Botón de guardar
                submit_button(__('Guardar Configuración', 'chatbot-quaxar'));
                ?>
            </form>
            
        </div>
        
        <!-- Sidebar -->
        <div class="chatbot-quaxar-admin-sidebar">
            
            <?php
            // Estado de la API
            $current_api_url = defined('CHATBOT_QUAXAR_API_URL') ? CHATBOT_QUAXAR_API_URL : '';
            $health_url = str_replace(array('/api/chat', '/chat'), '/health', $current_api_url);
            
            $response = wp_remote_get($health_url, array('timeout' => 2));
            
            if (is_wp_error($response) || wp_remote_retrieve_response_code($response) != 200) {
                $status_badge = '🔴 ' . __('Desconectado o Error en API', 'chatbot-quaxar');
            } else {
                $status_badge = '🟢 ' . __('Conectado y Operativo', 'chatbot-quaxar');
            }
            ?>
            <!-- API Health Check Card -->
            <div class="chatbot-quaxar-card">
                <h3><?php _e('Información', 'chatbot-quaxar'); ?></h3>
                <p>
                    <strong><?php _e('Versión:', 'chatbot-quaxar'); ?></strong> 
                    <?php echo CHATBOT_QUAXAR_VERSION; ?>
                </p>
                <p>
                    <strong><?php _e('Estado de la API:', 'chatbot-quaxar'); ?></strong><br>
                    <?php echo $status_badge; ?>
                </p>
            </div>
            
            <!-- Ayuda -->
            <div class="chatbot-quaxar-card">
                <h3><?php _e('Ayuda', 'chatbot-quaxar'); ?></h3>
                <ul class="chatbot-quaxar-help-list">
                    <li>
                        <strong><?php _e('Site ID:', 'chatbot-quaxar'); ?></strong>
                        <?php _e('Identificador único que filtra los documentos específicos de este sitio.', 'chatbot-quaxar'); ?>
                    </li>
                    <li>
                        <strong><?php _e('Mensaje de Bienvenida:', 'chatbot-quaxar'); ?></strong>
                        <?php _e('Primer mensaje que verá el usuario al abrir el chat.', 'chatbot-quaxar'); ?>
                    </li>
                    <li>
                        <strong><?php _e('Colores:', 'chatbot-quaxar'); ?></strong>
                        <?php _e('Personaliza la apariencia para que coincida con tu marca.', 'chatbot-quaxar'); ?>
                    </li>
                </ul>
            </div>
            
            <!-- Vista previa de colores -->
            <div class="chatbot-quaxar-card">
                <h3><?php _e('Vista Previa', 'chatbot-quaxar'); ?></h3>
                <div id="chatbot-quaxar-preview">
                    <div class="preview-button" id="preview-button">
                        <span><?php _e('Botón del Chat', 'chatbot-quaxar'); ?></span>
                    </div>
                    <div class="preview-bubble-bot" id="preview-bubble-bot">
                        <p><?php _e('Mensaje del bot', 'chatbot-quaxar'); ?></p>
                    </div>
                </div>
            </div>
            
        </div>
        
    </div>
    
</div>
