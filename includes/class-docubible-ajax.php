<?php
/**
 * Clase para manejar las solicitudes AJAX
 */
class DocuBible_AJAX {
    /**
     * Instancia de la API
     */
    private $api;
    
    /**
     * Instancia de los shortcodes
     */
    private $shortcodes;
    
    /**
     * Constructor
     */
    public function __construct($api, $shortcodes) {
        $this->api = $api;
        $this->shortcodes = $shortcodes;
        
        // Registrar acciones AJAX
        add_action('wp_ajax_docubible_check_answer', array($this, 'check_answer'));
        add_action('wp_ajax_nopriv_docubible_check_answer', array($this, 'check_answer'));
        
        add_action('wp_ajax_docubible_show_answer', array($this, 'show_answer'));
        add_action('wp_ajax_nopriv_docubible_show_answer', array($this, 'show_answer'));
        
        add_action('wp_ajax_docubible_next_question', array($this, 'next_question'));
        add_action('wp_ajax_nopriv_docubible_next_question', array($this, 'next_question'));
    }
    
    /**
     * Verificar respuesta
     */
    public function check_answer() {
        // Verificar nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'docubible_nonce')) {
            wp_send_json_error(array('message' => __('Error de seguridad. Por favor, recarga la página.', 'docubible')));
        }
        
        // Verificar datos
        if (!isset($_POST['question_id']) || !isset($_POST['answer'])) {
            wp_send_json_error(array('message' => __('Datos incompletos.', 'docubible')));
        }
        
        $question_id = sanitize_text_field($_POST['question_id']);
        $answer = sanitize_text_field($_POST['answer']);
        
        // Obtener datos de la pregunta
        $question_data = get_option($question_id);
        
        if (!$question_data) {
            wp_send_json_error(array('message' => __('Pregunta no encontrada.', 'docubible')));
        }
        
        // Verificar respuesta según el tipo
        $is_correct = false;
        
        switch ($question_data['type']) {
            case 'fill_blanks':
            case 'complete_verse':
                $is_correct = ($answer === $question_data['correct_option']);
                break;
            case 'identify_book':
                $is_correct = ($answer === $question_data['correct_book_id']);
                break;
        }
        
        // Guardar puntuación si el usuario está logueado
        if (is_user_logged_in()) {
            $user_id = get_current_user_id();
            $score = $is_correct ? 10 : 0;
            
            global $wpdb;
            $table_name = DOCUBIBLE_DB_PREFIX . 'docubible_scores';
            
            $wpdb->insert(
                $table_name,
                array(
                    'user_id' => $user_id,
                    'score' => $score,
                    'question_type' => $question_data['type'],
                    'question_data' => json_encode($question_data)
                )
            );
        }
        
        wp_send_json_success(array('is_correct' => $is_correct));
    }
    
    /**
     * Mostrar respuesta
     */
    public function show_answer() {
        // Verificar nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'docubible_nonce')) {
            wp_send_json_error(array('message' => __('Error de seguridad. Por favor, recarga la página.', 'docubible')));
        }
        
        // Verificar datos
        if (!isset($_POST['question_id'])) {
            wp_send_json_error(array('message' => __('Datos incompletos.', 'docubible')));
        }
        
        $question_id = sanitize_text_field($_POST['question_id']);
        
        // Obtener datos de la pregunta
        $question_data = get_option($question_id);
        
        if (!$question_data) {
            wp_send_json_error(array('message' => __('Pregunta no encontrada.', 'docubible')));
        }
        
        // Preparar respuesta
        $response = array();
        
        if ($question_data['type'] === 'complete_verse') {
            $response['correct_answer'] = sprintf(
                __('La respuesta correcta es: %s', 'docubible'),
                $question_data['correct_option']
            );
            $response['correct_option'] = $question_data['correct_option'];
        } elseif ($question_data['type'] === 'identify_book') {
            $response['correct_answer'] = sprintf(
                __('El libro correcto es: %s', 'docubible'),
                $question_data['correct_book_name']
            );
            $response['correct_option'] = $question_data['correct_book_id'];
        }
        
        wp_send_json_success($response);
    }
    
    /**
     * Obtener siguiente pregunta
     */
    public function next_question() {
        // Verificar nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'docubible_nonce')) {
            wp_send_json_error(array('message' => __('Error de seguridad. Por favor, recarga la página.', 'docubible')));
        }
        
        // Verificar datos
        if (!isset($_POST['trivia_type'])) {
            wp_send_json_error(array('message' => __('Datos incompletos.', 'docubible')));
        }
        
        $trivia_type = sanitize_text_field($_POST['trivia_type']);
        
        // Obtener la versión de la Biblia de la configuración
        $bible_id = get_option('docubible_default_bible', 'es-RVR1960');
        
        // Generar nueva pregunta
        $html = '';
        
        switch ($trivia_type) {
            case 'fill_blanks':
                $html = $this->shortcodes->fill_blanks_shortcode(array('bible_id' => $bible_id));
                break;
            case 'complete_verse':
                $html = $this->shortcodes->complete_verse_shortcode(array('bible_id' => $bible_id));
                break;
            case 'identify_book':
                $html = $this->shortcodes->identify_book_shortcode(array('bible_id' => $bible_id));
                break;
            default:
                // Fallback para compatibilidad
                $html = $this->shortcodes->complete_verse_shortcode(array('bible_id' => $bible_id));
                break;
        }
        
        if (empty($html)) {
            wp_send_json_error(array('message' => __('Error al generar la siguiente pregunta.', 'docubible')));
        }
        
        wp_send_json_success(array('html' => $html));
    }
}
