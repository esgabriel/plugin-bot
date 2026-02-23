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
            
            <!-- Información del plugin -->
            <div class="chatbot-quaxar-card">
                <h3><?php _e('Información', 'chatbot-quaxar'); ?></h3>
                <p>
                    <strong><?php _e('Versión:', 'chatbot-quaxar'); ?></strong> 
                    <?php echo CHATBOT_QUAXAR_VERSION; ?>
                </p>
                <p>
                    <strong><?php _e('URL de la API:', 'chatbot-quaxar'); ?></strong><br>
                    <code><?php echo esc_html(CHATBOT_QUAXAR_API_URL); ?></code>
                </p>
                <p class="description">
                    <?php _e('La URL de la API está configurada a nivel de servidor y no puede modificarse desde aquí.', 'chatbot-quaxar'); ?>
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
