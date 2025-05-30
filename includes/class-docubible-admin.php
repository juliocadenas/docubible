<?php
/**
 * Clase para manejar la administración del plugin
 */
class DocuBible_Admin {
    /**
     * Instancia de la API
     */
    private $api;
    
    /**
     * Constructor
     */
    public function __construct($api) {
        $this->api = $api;
    }
    
    /**
     * Registrar estilos para el admin
     */
    public function enqueue_styles() {
        wp_enqueue_style(
            'docubible-admin',
            DOCUBIBLE_PLUGIN_URL . 'admin/css/docubible-admin.css',
            array(),
            DOCUBIBLE_VERSION,
            'all'
        );
    }
    
    /**
     * Registrar scripts para el admin
     */
    public function enqueue_scripts() {
        wp_enqueue_script(
            'docubible-admin',
            DOCUBIBLE_PLUGIN_URL . 'admin/js/docubible-admin.js',
            array('jquery'),
            DOCUBIBLE_VERSION,
            true
        );
        
        wp_localize_script(
            'docubible-admin',
            'docubible_admin_params',
            array(
                'nonce' => wp_create_nonce('docubible_admin_nonce'),
                'ajax_url' => admin_url('admin-ajax.php')
            )
        );
    }
    
    /**
     * Agregar menú de administración
     */
    public function add_admin_menu() {
        add_menu_page(
            __('DocuBible', 'docubible'),
            __('DocuBible', 'docubible'),
            'manage_options',
            'docubible',
            array($this, 'display_settings_page'),
            'dashicons-book-alt',
            30
        );
        
        add_submenu_page(
            'docubible',
            __('Configuración', 'docubible'),
            __('Configuración', 'docubible'),
            'manage_options',
            'docubible',
            array($this, 'display_settings_page')
        );
        
        add_submenu_page(
            'docubible',
            __('Competiciones', 'docubible'),
            __('Competiciones', 'docubible'),
            'manage_options',
            'docubible-competitions',
            array($this, 'display_competitions_page')
        );
        
        add_submenu_page(
            'docubible',
            __('Estadísticas', 'docubible'),
            __('Estadísticas', 'docubible'),
            'manage_options',
            'docubible-stats',
            array($this, 'display_stats_page')
        );
        
        add_submenu_page(
            'docubible',
            __('Ayuda', 'docubible'),
            __('Ayuda', 'docubible'),
            'manage_options',
            'docubible-help',
            array($this, 'display_help_page')
        );
        
        add_submenu_page(
            'docubible',
            __('Diagnóstico', 'docubible'),
            __('Diagnóstico', 'docubible'),
            'manage_options',
            'docubible-diagnostics',
            array($this, 'display_diagnostics_page')
        );
    }
    
    /**
     * Registrar configuraciones
     */
    public function register_settings() {
        register_setting('docubible_settings', 'docubible_api_key');
        register_setting('docubible_settings', 'docubible_default_bible');
        register_setting('docubible_settings', 'docubible_daily_questions_limit', array(
            'type' => 'integer',
            'sanitize_callback' => 'intval',
            'default' => 10
        ));
        register_setting('docubible_settings', 'docubible_competition_period', array(
            'type' => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default' => 'monthly'
        ));
        register_setting('docubible_settings', 'docubible_prize_positions', array(
            'type' => 'integer',
            'sanitize_callback' => 'intval',
            'default' => 3
        ));
        
        register_setting('docubible_settings', 'docubible_charset', array(
            'type' => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default' => 'UTF-8'
        ));

        register_setting('docubible_settings', 'docubible_response_time_limit', array(
            'type' => 'integer',
            'sanitize_callback' => 'intval',
            'default' => 30
        ));
        
        register_setting('docubible_settings', 'docubible_allowed_languages', array(
            'type' => 'array',
            'default' => array('spa')
        ));
        
        add_settings_section(
            'docubible_api_settings',
            __('Configuración de la API', 'docubible'),
            array($this, 'api_settings_section_callback'),
            'docubible'
        );
        
        add_settings_field(
            'docubible_api_key',
            __('API Key', 'docubible'),
            array($this, 'api_key_field_callback'),
            'docubible',
            'docubible_api_settings'
        );
        
        add_settings_field(
            'docubible_default_bible',
            __('Versión de la Biblia por defecto', 'docubible'),
            array($this, 'default_bible_field_callback'),
            'docubible',
            'docubible_api_settings'
        );
        
        add_settings_section(
            'docubible_trivia_settings',
            __('Configuración de Trivias', 'docubible'),
            array($this, 'trivia_settings_section_callback'),
            'docubible'
        );
        
        add_settings_field(
            'docubible_daily_questions_limit',
            __('Límite diario de preguntas por usuario', 'docubible'),
            array($this, 'daily_limit_field_callback'),
            'docubible',
            'docubible_trivia_settings'
        );
        
        add_settings_section(
            'docubible_competition_settings',
            __('Configuración de Competiciones', 'docubible'),
            array($this, 'competition_settings_section_callback'),
            'docubible'
        );
        
        add_settings_field(
            'docubible_competition_period',
            __('Período de competición por defecto', 'docubible'),
            array($this, 'competition_period_field_callback'),
            'docubible',
            'docubible_competition_settings'
        );
        
        add_settings_field(
            'docubible_prize_positions',
            __('Número de posiciones a premiar', 'docubible'),
            array($this, 'prize_positions_field_callback'),
            'docubible',
            'docubible_competition_settings'
        );
        
        add_settings_section(
            'docubible_display_settings',
            __('Configuración de visualización', 'docubible'),
            array($this, 'display_settings_section_callback'),
            'docubible'
        );

        add_settings_field(
            'docubible_charset',
            __('Codificación de caracteres', 'docubible'),
            array($this, 'charset_field_callback'),
            'docubible',
            'docubible_display_settings'
        );

        add_settings_field(
            'docubible_response_time_limit',
            __('Tiempo límite para responder (segundos)', 'docubible'),
            array($this, 'response_time_limit_field_callback'),
            'docubible',
            'docubible_display_settings'
        );
        
        add_settings_field(
            'docubible_allowed_languages',
            __('Idiomas permitidos', 'docubible'),
            array($this, 'allowed_languages_field_callback'),
            'docubible',
            'docubible_display_settings'
        );
    }
    
    /**
     * Callback para la sección de configuración de API
     */
    public function api_settings_section_callback() {
        echo '<p>' . __('Configura tu API Key de scripture.api.bible para comenzar a usar el plugin.', 'docubible') . '</p>';
        echo '<p>' . sprintf(__('Si no tienes una API Key, puedes obtenerla en %s.', 'docubible'), '<a href="https://scripture.api.bible/signup" target="_blank">scripture.api.bible</a>') . '</p>';
    }
    
    /**
     * Callback para el campo de API Key
     */
    public function api_key_field_callback() {
        $api_key = get_option('docubible_api_key', '');
        echo '<input type="text" name="docubible_api_key" value="' . esc_attr($api_key) . '" class="regular-text" />';
    }
    
    /**
     * Callback para el campo de versión de la Biblia por defecto
     */
    public function default_bible_field_callback() {
        $default_bible = get_option('docubible_default_bible', 'es-RVR1960');
        $bible_versions = $this->api->get_bible_versions();
        
        echo '<select name="docubible_default_bible">';
        
        if (empty($bible_versions)) {
            echo '<option value="es-RVR1960">' . __('Reina Valera 1960 (Español)', 'docubible') . '</option>';
            echo '<option value="en-KJV">' . __('King James Version (English)', 'docubible') . '</option>';
        } else {
            foreach ($bible_versions as $bible) {
                $selected = ($bible->id === $default_bible) ? 'selected' : '';
                echo '<option value="' . esc_attr($bible->id) . '" ' . $selected . '>' . esc_html($bible->name) . ' (' . esc_html($bible->language->name) . ')</option>';
            }
        }
        
        echo '</select>';
    }
    
    /**
     * Callback para la sección de configuración de trivias
     */
    public function trivia_settings_section_callback() {
        echo '<p>' . __('Configura el comportamiento de las trivias bíblicas.', 'docubible') . '</p>';
    }
    
    /**
     * Callback para el campo de límite diario
     */
    public function daily_limit_field_callback() {
        $daily_limit = get_option('docubible_daily_questions_limit', 10);
        echo '<input type="number" name="docubible_daily_questions_limit" value="' . esc_attr($daily_limit) . '" min="1" max="100" />';
    }
    
    /**
     * Callback para la sección de configuración de competiciones
     */
    public function competition_settings_section_callback() {
        echo '<p>' . __('Configura las competiciones y premios.', 'docubible') . '</p>';
    }
    
    /**
     * Callback para el campo de período de competición
     */
    public function competition_period_field_callback() {
        $period = get_option('docubible_competition_period', 'monthly');
        $periods = array(
            'daily' => __('Diario', 'docubible'),
            'weekly' => __('Semanal', 'docubible'),
            'monthly' => __('Mensual', 'docubible'),
            'yearly' => __('Anual', 'docubible')
        );
        
        echo '<select name="docubible_competition_period">';
        foreach ($periods as $value => $label) {
            $selected = ($value === $period) ? 'selected' : '';
            echo '<option value="' . esc_attr($value) . '" ' . $selected . '>' . esc_html($label) . '</option>';
        }
        echo '</select>';
    }
    
    /**
     * Callback para el campo de posiciones a premiar
     */
    public function prize_positions_field_callback() {
        $positions = get_option('docubible_prize_positions', 3);
        echo '<input type="number" name="docubible_prize_positions" value="' . esc_attr($positions) . '" min="1" max="10" />';
    }
    
    /**
     * Callback para la sección de configuración de visualización
     */
    public function display_settings_section_callback() {
        echo '<p>' . __('Configura cómo se visualizan las trivias y el comportamiento de respuesta.', 'docubible') . '</p>';
    }

    /**
     * Callback para el campo de codificación de caracteres
     */
    public function charset_field_callback() {
        $charset = get_option('docubible_charset', 'UTF-8');
        $charsets = array(
            'UTF-8' => 'UTF-8 (Recomendado)',
            'ISO-8859-1' => 'ISO-8859-1 (Latín 1)',
            'ISO-8859-15' => 'ISO-8859-15 (Latín 9)',
            'Windows-1252' => 'Windows-1252 (Europa Occidental)'
        );
        
        echo '<select name="docubible_charset">';
        foreach ($charsets as $value => $label) {
            $selected = ($value === $charset) ? 'selected' : '';
            echo '<option value="' . esc_attr($value) . '" ' . $selected . '>' . esc_html($label) . '</option>';
        }
        echo '</select>';
        echo '<p class="description">' . __('Selecciona la codificación de caracteres para mostrar correctamente los textos bíblicos.', 'docubible') . '</p>';
    }

    /**
     * Callback para el campo de tiempo límite de respuesta
     */
    public function response_time_limit_field_callback() {
        $time_limit = get_option('docubible_response_time_limit', 30);
        echo '<input type="number" name="docubible_response_time_limit" value="' . esc_attr($time_limit) . '" min="5" max="120" step="5" />';
        echo '<p class="description">' . __('Tiempo en segundos que tienen los usuarios para responder cada pregunta. Establece 0 para desactivar el límite de tiempo.', 'docubible') . '</p>';
    }
    
    /**
     * Callback para el campo de idiomas permitidos
     */
    public function allowed_languages_field_callback() {
        $allowed_languages = get_option('docubible_allowed_languages', array('spa'));
        
        $available_languages = array(
            'spa' => __('Español', 'docubible'),
            'eng' => __('Inglés', 'docubible'),
            'por' => __('Portugués', 'docubible'),
            'fra' => __('Francés', 'docubible'),
            'deu' => __('Alemán', 'docubible'),
            'ita' => __('Italiano', 'docubible')
        );
        
        echo '<div class="docubible-checkbox-group">';
        
        foreach ($available_languages as $code => $name) {
            $checked = in_array($code, $allowed_languages) ? 'checked' : '';
            
            echo '<div class="docubible-checkbox-item">';
            echo '<input type="checkbox" name="docubible_allowed_languages[]" value="' . esc_attr($code) . '" id="docubible_lang_' . esc_attr($code) . '" ' . $checked . '>';
            echo '<label for="docubible_lang_' . esc_attr($code) . '">' . esc_html($name) . ' (' . esc_html($code) . ')</label>';
            echo '</div>';
        }
        
        echo '</div>';
        echo '<p class="description">' . __('Selecciona los idiomas permitidos para las trivias. Si no seleccionas ninguno, se usará español por defecto.', 'docubible') . '</p>';
    }
    
    // ... Resto de métodos de la clase ...
    
    /**
     * Mostrar página de configuración
     */
    public function display_settings_page() {
        if (!current_user_can('manage_options')) {
            return;
        }
        
        include DOCUBIBLE_PLUGIN_DIR . 'admin/partials/settings-page.php';
    }
    
    /**
     * Mostrar página de competiciones
     */
    public function display_competitions_page() {
        if (!current_user_can('manage_options')) {
            return;
        }
        
        include DOCUBIBLE_PLUGIN_DIR . 'admin/partials/competitions-page.php';
    }
    
    /**
     * Mostrar página de estadísticas
     */
    public function display_stats_page() {
        if (!current_user_can('manage_options')) {
            return;
        }
        
        include DOCUBIBLE_PLUGIN_DIR . 'admin/partials/stats-page.php';
    }
    
    /**
     * Mostrar página de ayuda
     */
    public function display_help_page() {
        if (!current_user_can('manage_options')) {
            return;
        }
        
        include DOCUBIBLE_PLUGIN_DIR . 'admin/partials/help-page.php';
    }
    
    /**
     * Mostrar página de diagnóstico
     */
    public function display_diagnostics_page() {
        if (!current_user_can('manage_options')) {
            return;
        }
        
        // Obtener información de depuración
        $debug_info = apply_filters('docubible_debug_info', array());
        
        // Realizar prueba de API si se solicita
        $api_test_result = null;
        
        if (isset($_POST['docubible_test_api']) && check_admin_referer('docubible_diagnostics')) {
            $api_test_result = $this->test_api_connection();
        }
        
        // Realizar prueba de idioma si se solicita
        $language_test_result = null;
        
        if (isset($_POST['docubible_test_language']) && check_admin_referer('docubible_diagnostics')) {
            $language_test_result = $this->test_language_support();
        }
        
        // Mostrar página
        include DOCUBIBLE_PLUGIN_DIR . 'admin/partials/diagnostics-page.php';
    }
    
    /**
     * Probar conexión con la API
     */
    private function test_api_connection() {
        $api_key = get_option('docubible_api_key', '');
        
        if (empty($api_key)) {
            return array(
                'success' => false,
                'message' => __('No se ha configurado la API Key', 'docubible')
            );
        }
        
        $response = wp_remote_get('https://api.scripture.api.bible/v1/bibles', array(
            'headers' => array(
                'api-key' => $api_key,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json'
            ),
            'timeout' => 15
        ));
        
        if (is_wp_error($response)) {
            return array(
                'success' => false,
                'message' => $response->get_error_message()
            );
        }
        
        $status_code = wp_remote_retrieve_response_code($response);
        
        if ($status_code !== 200) {
            return array(
                'success' => false,
                'message' => sprintf(__('Error en la API (código %d)', 'docubible'), $status_code)
            );
        }
        
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            return array(
                'success' => false,
                'message' => __('Respuesta inválida de la API', 'docubible')
            );
        }
        
        return array(
            'success' => true,
            'message' => sprintf(__('Conexión exitosa. Se encontraron %d versiones de la Biblia.', 'docubible'), count($data->data))
        );
    }
    
    /**
     * Probar soporte de idioma
     */
    private function test_language_support() {
        $bible_id = get_option('docubible_default_bible', 'es-RVR1960');
        
        $response = wp_remote_get('https://api.scripture.api.bible/v1/bibles/' . $bible_id, array(
            'headers' => array(
                'api-key' => get_option('docubible_api_key', ''),
                'Content-Type' => 'application/json',
                'Accept' => 'application/json'
            ),
            'timeout' => 15
        ));
        
        if (is_wp_error($response)) {
            return array(
                'success' => false,
                'message' => $response->get_error_message()
            );
        }
        
        $status_code = wp_remote_retrieve_response_code($response);
        
        if ($status_code !== 200) {
            return array(
                'success' => false,
                'message' => sprintf(__('Error en la API (código %d)', 'docubible'), $status_code)
            );
        }
        
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            return array(
                'success' => false,
                'message' => __('Respuesta inválida de la API', 'docubible')
            );
        }
        
        $language = isset($data->data->language) ? $data->data->language : null;
        
        if (!$language) {
            return array(
                'success' => false,
                'message' => __('No se pudo determinar el idioma de la versión de la Biblia', 'docubible')
            );
        }
        
        $allowed_languages = get_option('docubible_allowed_languages', array('spa'));
        
        if (!in_array($language->id, $allowed_languages)) {
            return array(
                'success' => false,
                'message' => sprintf(__('El idioma de la versión de la Biblia (%s) no está en la lista de idiomas permitidos', 'docubible'), $language->name)
            );
        }
        
        return array(
            'success' => true,
            'message' => sprintf(__('La versión de la Biblia está en %s (%s), que es un idioma permitido', 'docubible'), $language->name, $language->id)
        );
    }
}
