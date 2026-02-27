/**
 * JavaScript del widget de chat
 * 
 * Maneja toda la lógica del chatbot en el frontend
 * 
 */

(function () {
    'use strict';

    // Variables globales
    let chatWindow = null;
    let chatMessages = null;
    let chatInput = null;
    let chatForm = null;
    let sendBtn = null;
    let toggleButton = null;
    let isOpen = false;
    let isLoading = false;

    // Clave para sessionStorage — el historial se borra al cerrar la pestaña
    const SESSION_KEY = 'chatbot_quaxar_history';

    // Configuración (viene de PHP via wp_localize_script)
    const config = window.chatbotQuaxarConfig || {};

    /**
     * Inicializar el chatbot cuando el DOM esté listo
     */
    document.addEventListener('DOMContentLoaded', function () {
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
        sendBtn = document.querySelector('.chatbot-quaxar-send-btn');
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

        // Cargar historial de la sesión actual
        const restored = loadChatHistory();

        // Mostrar mensaje de bienvenida solo si no hay historial previo
        if (!restored) {
            displayWelcomeMessage();
        }
    }

    /**
     * Aplicar colores personalizados desde la configuración
     */
    function applyCustomColors() {
        const root = document.documentElement;
        const primary = config.primaryColor || '#0066CC';
        root.style.setProperty('--chatbot-primary-color', primary);
        root.style.setProperty('--chatbot-primary-dark', darkenHex(primary, 15));
        root.style.setProperty('--chatbot-secondary-color', config.secondaryColor || '#F0F4F8');
        root.style.setProperty('--chatbot-text-color', config.textColor || '#FFFFFF');
        root.style.setProperty('--chatbot-bot-text-color', config.botTextColor || '#1f2937');
        root.style.setProperty('--chatbot-user-text-color', config.userTextColor || '#FFFFFF');
    }

    /**
     * Oscurecer un color hexadecimal en la cantidad indicada
     */
    function darkenHex(hex, amount) {
        hex = hex.replace('#', '');
        if (hex.length === 3) hex = hex.split('').map(c => c + c).join('');
        const r = Math.max(0, parseInt(hex.slice(0, 2), 16) - amount);
        const g = Math.max(0, parseInt(hex.slice(2, 4), 16) - amount);
        const b = Math.max(0, parseInt(hex.slice(4, 6), 16) - amount);
        return '#' + [r, g, b].map(v => v.toString(16).padStart(2, '0')).join('');
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
        setTimeout(function () {
            chatInput.focus();
        }, 300);

        // Llevar la vista al mensaje más reciente
        scrollToBottom();

        // Guardar estado
        saveOpenState(true);
    }

    /**
     * Cerrar chat
     */
    function closeChat() {
        chatWindow.classList.remove('chatbot-quaxar-window-open');
        chatWindow.classList.add('chatbot-quaxar-window-closing');

        setTimeout(function () {
            chatWindow.classList.remove('chatbot-quaxar-window-closing');
            toggleButton.classList.remove('chatbot-quaxar-toggle-active');
        }, 220); // Debe coincidir con la duración de chatbot-slide-out en el CSS

        isOpen = false;
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

        // Deshabilitar input y botón mientras se espera la respuesta
        chatInput.disabled = true;
        if (sendBtn) sendBtn.disabled = true;
        chatInput.placeholder = config.inputPlaceholder || 'Type your message...';

        // Mostrar indicador de "escribiendo..."
        displayTypingIndicator();

        try {
            const response = await fetch(config.apiUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-API-Key': config.apiKey
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
            // Rehabilitar input y botón
            isLoading = false;
            chatInput.disabled = false;
            if (sendBtn) sendBtn.disabled = false;
            chatInput.placeholder = config.inputPlaceholder || 'Type your message...';
            chatInput.focus();
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

        // Sanitizar HTML para prevenir XSS antes de insertar en el DOM
        const parsed = parseMarkdown(text);
        bubbleDiv.innerHTML = sanitize(parsed);

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
        const message = (config.errorMessage && config.errorMessage.trim() !== '')
            ? config.errorMessage
            : 'Sorry, something went wrong. Please try again.';
        displayMessage(message, 'bot');
    }

    /**
     * Scroll al final del chat
     */
    function scrollToBottom() {
        requestAnimationFrame(function () {
            chatMessages.scrollTo({
                top: chatMessages.scrollHeight,
                behavior: 'smooth'
            });
        });
    }

    /**
     * Guardar historial del chat en sessionStorage
     * El historial se borra automáticamente al cerrar la pestaña o el navegador
     */
    function saveChatHistory() {
        try {
            const messages = [];
            const messageElements = chatMessages.querySelectorAll('.chatbot-message:not(.chatbot-typing-indicator)');

            messageElements.forEach(function (el) {
                const sender = el.classList.contains('chatbot-message-user') ? 'user' : 'bot';
                const bubble = el.querySelector('.chatbot-message-bubble');
                // Guardar el HTML para preservar los links ya renderizados
                if (bubble) messages.push({ sender, html: bubble.innerHTML });
            });

            sessionStorage.setItem(SESSION_KEY, JSON.stringify(messages));
        } catch (error) {
            console.error('Error al guardar historial:', error);
        }
    }

    /**
     * Cargar historial del chat desde sessionStorage
     * 
     * @returns {boolean} true si se restauró al menos un mensaje
     */
    function loadChatHistory() {
        try {
            const history = sessionStorage.getItem(SESSION_KEY);
            if (!history) return false;

            const messages = JSON.parse(history);
            if (!messages.length) return false;

            messages.forEach(function (msg) {
                const messageDiv = document.createElement('div');
                messageDiv.className = 'chatbot-message chatbot-message-' + msg.sender;

                const bubbleDiv = document.createElement('div');
                bubbleDiv.className = 'chatbot-message-bubble';
                // Sanitizar al restaurar para evitar XSS almacenado
                bubbleDiv.innerHTML = sanitize(msg.html || '');

                messageDiv.appendChild(bubbleDiv);
                chatMessages.appendChild(messageDiv);
            });

            scrollToBottom();
            return true;
        } catch (error) {
            console.error('Error al cargar historial:', error);
            return false;
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

    function parseMarkdown(text) {
        return text
            // Links en formato Markdown [texto](url)
            .replace(/\[([^\]]+)\]\((https?:\/\/[^\)]+)\)/g,
                '<a href="$2" target="_blank" rel="noopener noreferrer">$1</a>')
            // URLs desnudas que no estén ya dentro de un atributo href
            .replace(/(?<![("'])((https?:\/\/)[^\s<)"']+)/g,
                '<a href="$1" target="_blank" rel="noopener noreferrer">$1</a>')
            // Negritas **texto**
            .replace(/\*\*([^*]+)\*\*/g, '<strong>$1</strong>')
            // Saltos de línea
            .replace(/\n/g, '<br>');
    }

    /**
     * Sanitizar HTML para prevenir XSS
     * Usar DOMPurify si está disponible, o un fallback básico
     */
    function sanitize(html) {
        if (window.DOMPurify) {
            return DOMPurify.sanitize(html, {
                ALLOWED_TAGS: ['a', 'br', 'strong', 'em'],
                ALLOWED_ATTR: ['href', 'target', 'rel']
            });
        }
        // Fallback básico si DOMPurify no cargó
        return html
            .replace(/<script[^>]*>.*?<\/script>/gi, '')
            .replace(/on\w+="[^"]*"/gi, '');
    }

    /**
     * Limpiar historial del chat
     */
    window.clearChatbotHistory = function () {
        try {
            sessionStorage.removeItem(SESSION_KEY);
            chatMessages.innerHTML = '';
            displayWelcomeMessage();
        } catch (error) {
            console.error('Error al limpiar historial:', error);
        }
    };

})();