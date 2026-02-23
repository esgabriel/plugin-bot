/**
 * JavaScript del widget de chat
 * 
 * Maneja toda la lógica del chatbot en el frontend
 * 
 */

(function() {
    'use strict';
    
    // Variables globales
    let chatWindow = null;
    let chatMessages = null;
    let chatInput = null;
    let chatForm = null;
    let toggleButton = null;
    let isOpen = false;
    let isLoading = false;
    
    // Configuración (viene de PHP via wp_localize_script)
    const config = window.chatbotQuaxarConfig || {};
    
    /**
     * Inicializar el chatbot cuando el DOM esté listo
     */
    document.addEventListener('DOMContentLoaded', function() {
        initChatbot();
    });
    
    /**
     * Inicializar elementos y event listeners
     */
    function initChatbot() {
        // Obtener referencias a elementos
        chatWindow = document.getElementById('chatbot-quaxar-window');
        chatMessages = document.getElementById('chatbot-quaxar-messages');
        chatInput = document.getElementById('chatbot-quaxar-input');
        chatForm = document.getElementById('chatbot-quaxar-form');
        toggleButton = document.getElementById('chatbot-quaxar-toggle');
        const closeButton = document.getElementById('chatbot-quaxar-close');
        
        if (!chatWindow || !chatMessages || !chatInput || !chatForm || !toggleButton) {
            console.error('Chatbot Quaxar: Elementos no encontrados');
            return;
        }
        
        // Aplicar colores personalizados
        applyCustomColors();
        
        // Event listeners
        toggleButton.addEventListener('click', toggleChat);
        closeButton.addEventListener('click', closeChat);
        chatForm.addEventListener('submit', handleSubmit);
        
        // Cargar historial del chat
        loadChatHistory();
        
        // Mostrar mensaje de bienvenida si el chat está vacío
        if (chatMessages.children.length === 0) {
            displayWelcomeMessage();
        }
    }
    
    /**
     * Aplicar colores personalizados desde la configuración
     */
    function applyCustomColors() {
        const root = document.documentElement;
        root.style.setProperty('--chatbot-primary-color', config.primaryColor || '#0066CC');
        root.style.setProperty('--chatbot-secondary-color', config.secondaryColor || '#F0F4F8');
        root.style.setProperty('--chatbot-text-color', config.textColor || '#FFFFFF');
        root.style.setProperty('--chatbot-input-border-color', config.inputBorderColor || '#0066CC');
        
        // Convertir color hex a RGB para el box-shadow
        const inputBorderRgb = hexToRgb(config.inputBorderColor || '#0066CC');
        root.style.setProperty('--chatbot-input-border-rgb', inputBorderRgb);
    }
    
    /**
     * Convertir color hexadecimal a RGB
     */
    function hexToRgb(hex) {
        // Remover el # si existe
        hex = hex.replace('#', '');
        
        // Convertir formato corto (ej: #FFF) a formato largo (ej: #FFFFFF)
        if (hex.length === 3) {
            hex = hex[0] + hex[0] + hex[1] + hex[1] + hex[2] + hex[2];
        }
        
        const r = parseInt(hex.substring(0, 2), 16);
        const g = parseInt(hex.substring(2, 4), 16);
        const b = parseInt(hex.substring(4, 6), 16);
        
        return r + ', ' + g + ', ' + b;
    }
    
    /**
     * Alternar visibilidad del chat
     */
    function toggleChat() {
        if (isOpen) {
            closeChat();
        } else {
            openChat();
        }
    }
    
    /**
     * Abrir chat
     */
    function openChat() {
        chatWindow.classList.add('chatbot-quaxar-window-open');
        toggleButton.classList.add('chatbot-quaxar-toggle-active');
        isOpen = true;
        
        // Focus en el input
        setTimeout(function() {
            chatInput.focus();
        }, 300);
        
        // Guardar estado
        saveOpenState(true);
    }
    
    /**
     * Cerrar chat
     */
    function closeChat() {
        chatWindow.classList.remove('chatbot-quaxar-window-open');
        toggleButton.classList.remove('chatbot-quaxar-toggle-active');
        isOpen = false;
        
        // Guardar estado
        saveOpenState(false);
    }
    
    /**
     * Manejar envío del formulario
     */
    function handleSubmit(e) {
        e.preventDefault();
        
        if (isLoading) {
            return;
        }
        
        const message = chatInput.value.trim();
        
        if (!message) {
            return;
        }
        
        // Limpiar input
        chatInput.value = '';
        
        // Mostrar mensaje del usuario
        displayMessage(message, 'user');
        
        // Enviar a la API
        sendToAPI(message);
    }
    
    /**
     * Enviar mensaje a la API
     */
    async function sendToAPI(message) {
        isLoading = true;
        
        // Mostrar indicador de "escribiendo..."
        displayTypingIndicator();
        
        try {
            const response = await fetch(config.apiUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    texto: message,
                    site_id: config.siteId
                })
            });
            
            if (!response.ok) {
                throw new Error('Error en la respuesta de la API');
            }
            
            const data = await response.json();
            
            // Remover indicador de "escribiendo..."
            removeTypingIndicator();
            
            // Mostrar respuesta
            if (data.respuesta) {
                displayMessage(data.respuesta, 'bot');
            } else {
                throw new Error('Respuesta vacía de la API');
            }
            
        } catch (error) {
            console.error('Error al comunicarse con la API:', error);
            
            // Remover indicador de "escribiendo..."
            removeTypingIndicator();
            
            // Mostrar mensaje de error
            displayErrorMessage();
        } finally {
            isLoading = false;
        }
    }
    
    /**
     * Mostrar mensaje en el chat
     */
    function displayMessage(text, sender) {
        const messageDiv = document.createElement('div');
        messageDiv.className = 'chatbot-message chatbot-message-' + sender;
        
        const bubbleDiv = document.createElement('div');
        bubbleDiv.className = 'chatbot-message-bubble';
        bubbleDiv.textContent = text;
        
        messageDiv.appendChild(bubbleDiv);
        chatMessages.appendChild(messageDiv);
        
        // Scroll al final
        scrollToBottom();
        
        // Guardar en historial
        saveChatHistory();
    }
    
    /**
     * Mostrar mensaje de bienvenida
     */
    function displayWelcomeMessage() {
        const welcomeMessage = config.welcomeMessage || '¡Hola! 👋 ¿En qué puedo ayudarte?';
        displayMessage(welcomeMessage, 'bot');
    }
    
    /**
     * Mostrar indicador de "escribiendo..."
     */
    function displayTypingIndicator() {
        const typingDiv = document.createElement('div');
        typingDiv.className = 'chatbot-message chatbot-message-bot chatbot-typing-indicator';
        typingDiv.id = 'chatbot-typing-indicator';
        
        const bubbleDiv = document.createElement('div');
        bubbleDiv.className = 'chatbot-message-bubble';
        
        const dot1 = document.createElement('span');
        const dot2 = document.createElement('span');
        const dot3 = document.createElement('span');
        
        bubbleDiv.appendChild(dot1);
        bubbleDiv.appendChild(dot2);
        bubbleDiv.appendChild(dot3);
        
        typingDiv.appendChild(bubbleDiv);
        chatMessages.appendChild(typingDiv);
        
        scrollToBottom();
    }
    
    /**
     * Remover indicador de "escribiendo..."
     */
    function removeTypingIndicator() {
        const indicator = document.getElementById('chatbot-typing-indicator');
        if (indicator) {
            indicator.remove();
        }
    }
    
    /**
     * Mostrar mensaje de error
     */
    function displayErrorMessage() {
        displayMessage(
            'Lo siento, hubo un error al procesar tu mensaje. Por favor, intenta de nuevo.',
            'bot'
        );
    }
    
    /**
     * Scroll al final del chat
     */
    function scrollToBottom() {
        setTimeout(function() {
            chatMessages.scrollTop = chatMessages.scrollHeight;
        }, 100);
    }
    
    /**
     * Guardar historial del chat en localStorage
     */
    function saveChatHistory() {
        try {
            const messages = [];
            const messageElements = chatMessages.querySelectorAll('.chatbot-message:not(.chatbot-typing-indicator)');
            
            messageElements.forEach(function(el) {
                const sender = el.classList.contains('chatbot-message-user') ? 'user' : 'bot';
                const text = el.querySelector('.chatbot-message-bubble').textContent;
                messages.push({ sender, text });
            });
            
            localStorage.setItem('chatbot_quaxar_history', JSON.stringify(messages));
        } catch (error) {
            console.error('Error al guardar historial:', error);
        }
    }
    
    /**
     * Cargar historial del chat desde localStorage
     */
    function loadChatHistory() {
        try {
            const history = localStorage.getItem('chatbot_quaxar_history');
            
            if (history) {
                const messages = JSON.parse(history);
                
                messages.forEach(function(msg) {
                    displayMessage(msg.text, msg.sender);
                });
            }
        } catch (error) {
            console.error('Error al cargar historial:', error);
        }
    }
    
    /**
     * Guardar estado de apertura del chat
     */
    function saveOpenState(open) {
        try {
            localStorage.setItem('chatbot_quaxar_open', open ? '1' : '0');
        } catch (error) {
            console.error('Error al guardar estado:', error);
        }
    }
    
    /**
     * Limpiar historial del chat
     */
    window.clearChatbotHistory = function() {
        try {
            localStorage.removeItem('chatbot_quaxar_history');
            chatMessages.innerHTML = '';
            displayWelcomeMessage();
        } catch (error) {
            console.error('Error al limpiar historial:', error);
        }
    };
    
})();
