/**
 * Panel de administración
 * 
 */

(function ($) {
    'use strict';

    $(document).ready(function () {

        // Inicializar color pickers
        initColorPickers();

        // Actualizar vista previa al cambiar colores
        updatePreviewOnChange();

        // Validar Site ID
        validateSiteId();

        // Inicializar Media Uploader para imagen del botón
        initMediaUploader();

        // Mostrar/ocultar campo de imagen según el tipo seleccionado
        toggleCustomImageField();

        // Mostrar/ocultar campo de slugs según la visibilidad seleccionada
        toggleVisibilityField();

    });

    /**
     * Mostrar/ocultar campo de páginas según el modo de visibilidad
     */
    function toggleVisibilityField() {
        var $select = $('#chatbot_quaxar_visibility_mode');
        var $pagesRow = $('#chatbot_quaxar_visibility_pages').closest('tr');

        function toggle() {
            if ($select.val() === 'all') {
                $pagesRow.hide();
            } else {
                $pagesRow.show();
            }
        }

        // Evaluar estado inicial
        toggle();

        // Actualizar al cambiar
        $select.on('change', function () {
            toggle();
        });
    }

    /**
     * Inicializar WordPress Color Pickers
     */
    function initColorPickers() {
        $('.chatbot-color-picker').wpColorPicker({
            change: function (event, ui) {
                updatePreview();
            },
            clear: function () {
                updatePreview();
            }
        });
    }

    /**
     * Actualizar vista previa cuando cambian los colores o configuración
     */
    function updatePreviewOnChange() {
        // Actualizar vista previa inicial
        updatePreview();

        // Actualizar al cambiar tamaño del botón
        $('#chatbot_quaxar_button_size').on('change', function () {
            updatePreview();
        });

        // Actualizar al cambiar tipo de ícono
        $('input[name="chatbot_quaxar_options[button_icon_type]"]').on('change', function () {
            updatePreview();
        });
    }

    /**
     * Actualizar la vista previa de colores y botón
     */
    function updatePreview() {
        const primaryColor = $('#chatbot_quaxar_primary_color').val() || '#0066CC';
        const secondaryColor = $('#chatbot_quaxar_secondary_color').val() || '#F0F4F8';
        const textColor = $('#chatbot_quaxar_text_color').val() || '#FFFFFF';
        const botTextColor = $('#chatbot_quaxar_bot_text_color').val() || '#1f2937';
        const userTextColor = $('#chatbot_quaxar_user_text_color').val() || '#FFFFFF';
        const buttonSize = $('#chatbot_quaxar_button_size').val() || 'medium';
        const iconType = $('input[name="chatbot_quaxar_options[button_icon_type]"]:checked').val() || 'default';
        const customImage = $('#chatbot_quaxar_button_icon_image').val();

        // Actualizar botón de vista previa
        const sizesMap = { small: '50px', medium: '60px', large: '70px' };
        const buttonSizePx = sizesMap[buttonSize];

        $('#preview-button').css({
            'background-color': primaryColor,
            'color': textColor,
            'width': buttonSizePx,
            'height': buttonSizePx
        });

        // Actualizar contenido del botón según el tipo
        if (iconType === 'custom' && customImage) {
            $('#preview-button').html('<img src="' + customImage + '" style="width: 60%; height: 60%; object-fit: contain;">');
        } else {
            $('#preview-button').html('<span>💬</span>');
        }

        // Actualizar burbuja de vista previa
        $('#preview-bubble-bot').css({
            'background-color': secondaryColor,
            'color': botTextColor
        });
    }

    /**
     * Validar Site ID
     */
    function validateSiteId() {
        $('#chatbot_quaxar_site_id').on('input', function () {
            let value = $(this).val();

            // Remover caracteres no permitidos
            value = value.replace(/[^a-zA-Z0-9_-]/g, '');

            // Actualizar valor
            $(this).val(value);

            // Validar longitud mínima
            if (value.length > 0 && value.length < 3) {
                $(this).css('border-color', '#dc3545');
            } else if (value.length >= 3) {
                $(this).css('border-color', '#28a745');
            } else {
                $(this).css('border-color', '');
            }
        });
    }

    /**
     * Inicializar Media Uploader para la imagen del botón
     */
    function initMediaUploader() {
        let mediaUploader;

        // Botón para subir imagen
        $('#chatbot_upload_button_icon').on('click', function (e) {
            e.preventDefault();

            // Si el uploader ya existe, abrirlo
            if (mediaUploader) {
                mediaUploader.open();
                return;
            }

            // Crear el Media Uploader
            mediaUploader = wp.media({
                title: 'Seleccionar Imagen del Botón',
                button: {
                    text: 'Usar esta imagen'
                },
                multiple: false,
                library: {
                    type: 'image'
                }
            });

            // Cuando se selecciona una imagen
            mediaUploader.on('select', function () {
                const attachment = mediaUploader.state().get('selection').first().toJSON();

                // Actualizar campo hidden con la URL
                $('#chatbot_quaxar_button_icon_image').val(attachment.url);

                // Mostrar preview
                $('#chatbot_button_icon_preview').html(
                    '<img src="' + attachment.url + '" style="max-width: 60px; max-height: 60px; margin-top: 10px; border-radius: 8px; border: 2px solid #ddd;">'
                );

                // Mostrar botón de quitar
                $('#chatbot_remove_button_icon').show();

                // Actualizar vista previa general
                updatePreview();
            });

            // Abrir el uploader
            mediaUploader.open();
        });

        // Botón para quitar imagen
        $('#chatbot_remove_button_icon').on('click', function (e) {
            e.preventDefault();

            // Limpiar campo hidden
            $('#chatbot_quaxar_button_icon_image').val('');

            // Limpiar preview
            $('#chatbot_button_icon_preview').html('');

            // Ocultar botón de quitar
            $(this).hide();

            // Actualizar vista previa general
            updatePreview();
        });
    }

    /**
     * Mostrar/ocultar campo de imagen personalizada según el tipo seleccionado
     */
    function toggleCustomImageField() {
        const imageField = $('.chatbot-image-upload-container').closest('tr');

        function checkIconType() {
            const iconType = $('input[name="chatbot_quaxar_options[button_icon_type]"]:checked').val();

            if (iconType === 'custom') {
                imageField.show();
            } else {
                imageField.hide();
            }
        }

        // Verificar al cargar
        checkIconType();

        // Verificar al cambiar
        $('input[name="chatbot_quaxar_options[button_icon_type]"]').on('change', function () {
            checkIconType();
        });
    }

})(jQuery);
