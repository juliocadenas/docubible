<?php
/**
 * Clase para manejar los shortcodes del plugin
 */
class DocuBible_Shortcodes {
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
     * Registrar shortcodes
     */
    public function register_shortcodes() {
        add_shortcode('docubible_fill_blanks', array($this, 'fill_blanks_shortcode'));
        add_shortcode('docubible_complete_verse', array($this, 'complete_verse_shortcode'));
        add_shortcode('docubible_identify_book', array($this, 'identify_book_shortcode'));
        add_shortcode('docubible_ranking', array($this, 'ranking_shortcode'));
        
        // Mantener retrocompatibilidad
        add_shortcode('docubible_complete_verse_old', array($this, 'fill_blanks_shortcode'));
    }
    
    /**
     * Shortcode para completar versículos
     */
    public function fill_blanks_shortcode($atts) {
        // Extraer atributos
        $atts = shortcode_atts(
            array(
                'bible_id' => get_option('docubible_default_bible', 'es-RVR1960'),
            ),
            $atts,
            'docubible_complete_verse'
        );
        
        // Verificar límite diario para usuarios logueados
        if (is_user_logged_in()) {
            $user_id = get_current_user_id();
            $daily_limit = get_option('docubible_daily_questions_limit', 10);
            $today_count = $this->get_today_questions_count($user_id);
            
            if ($today_count >= $daily_limit) {
                return $this->render_limit_reached_message();
            }
        }
        
        // Verificar que la versión de la Biblia es válida
        $bible_language = $this->api->get_bible_language($atts['bible_id']);
        if (!$bible_language) {
            return '<div class="docubible-error">' . __('Versión de la Biblia no válida. Por favor, verifica la configuración.', 'docubible') . '</div>';
        }
        
        // Obtener versículo aleatorio
        $verse = $this->api->get_random_verse($atts['bible_id']);
        
        if (!$verse) {
            return '<div class="docubible-error">' . __('No se pudo obtener un versículo. Por favor, verifica la configuración de la API.', 'docubible') . '</div>';
        }
        
        // Obtener versículo parcial
        $partial_verse = $this->api->get_partial_verse($verse);
        
        if (!$partial_verse) {
            return '<div class="docubible-error">' . __('Error al procesar el versículo.', 'docubible') . '</div>';
        }
        
        // Generar opciones
        $correct_option = $partial_verse['hidden'];
        $incorrect_options = $this->api->generate_incorrect_options($correct_option, $atts['bible_id']);
        
        // Combinar y mezclar opciones
        $options = array_merge(array($correct_option), $incorrect_options);
        shuffle($options);
        
        // Guardar datos en sesión para verificación
        $question_data = array(
            'type' => 'complete_verse',
            'correct_option' => $correct_option,
            'reference' => $partial_verse['reference'],
            'full_verse' => $partial_verse['full'],
            'bible_id' => $atts['bible_id']
        );
        
        $question_id = uniqid('docubible_');
        update_option($question_id, $question_data, false);
        
        // Renderizar trivia
        ob_start();
        include DOCUBIBLE_PLUGIN_DIR . 'public/partials/complete-verse-trivia.php';
        return ob_get_clean();
    }
    
    /**
     * Shortcode para completar la segunda mitad del versículo
     */
    public function complete_verse_shortcode($atts) {
        // Extraer atributos
        $atts = shortcode_atts(
            array(
                'bible_id' => get_option('docubible_default_bible', 'es-RVR1960'),
        ),
        $atts,
        'docubible_complete_verse'
        );
        
        // Verificar límite diario para usuarios logueados
        if (is_user_logged_in()) {
            $user_id = get_current_user_id();
            $daily_limit = get_option('docubible_daily_questions_limit', 10);
            $today_count = $this->get_today_questions_count($user_id);
            
            if ($today_count >= $daily_limit) {
                return $this->render_limit_reached_message();
            }
        }
        
        // Verificar que la versión de la Biblia es válida
        $bible_language = $this->api->get_bible_language($atts['bible_id']);
        if (!$bible_language) {
            return '<div class="docubible-error">' . __('Versión de la Biblia no válida. Por favor, verifica la configuración.', 'docubible') . '</div>';
        }
        
        // Obtener versículo aleatorio
        $verse = $this->api->get_random_verse($atts['bible_id']);
        
        if (!$verse) {
            return '<div class="docubible-error">' . __('No se pudo obtener un versículo. Por favor, verifica la configuración de la API.', 'docubible') . '</div>';
        }
        
        // Obtener mitades del versículo
        $verse_halves = $this->api->get_verse_halves($verse);
        
        if (!$verse_halves) {
            return '<div class="docubible-error">' . __('Error al procesar el versículo.', 'docubible') . '</div>';
        }
        
        // Generar opciones incorrectas para la segunda mitad
        $correct_option = $verse_halves['second_half'];
        $incorrect_options = $this->api->generate_verse_half_options($correct_option, $atts['bible_id']);
        
        // Combinar y mezclar opciones
        $options = array_merge(array($correct_option), $incorrect_options);
        shuffle($options);
        
        // Guardar datos en sesión para verificación
        $question_data = array(
            'type' => 'complete_verse',
            'correct_option' => $correct_option,
            'first_half' => $verse_halves['first_half'],
            'reference' => $verse_halves['reference'],
            'full_verse' => $verse_halves['full'],
            'bible_id' => $atts['bible_id']
        );
        
        $question_id = uniqid('docubible_');
        update_option($question_id, $question_data, false);
        
        // Renderizar trivia
        ob_start();
        include DOCUBIBLE_PLUGIN_DIR . 'public/partials/complete-verse-trivia.php';
        return ob_get_clean();
    }
    
    /**
     * Shortcode para identificar el libro de un versículo
     */
    public function identify_book_shortcode($atts) {
        // Extraer atributos
        $atts = shortcode_atts(
            array(
                'bible_id' => get_option('docubible_default_bible', 'es-RVR1960'),
            ),
            $atts,
            'docubible_identify_book'
        );
        
        // Verificar límite diario para usuarios logueados
        if (is_user_logged_in()) {
            $user_id = get_current_user_id();
            $daily_limit = get_option('docubible_daily_questions_limit', 10);
            $today_count = $this->get_today_questions_count($user_id);
            
            if ($today_count >= $daily_limit) {
                return $this->render_limit_reached_message();
            }
        }
        
        // Verificar que la versión de la Biblia es válida
        $bible_language = $this->api->get_bible_language($atts['bible_id']);
        if (!$bible_language) {
            return '<div class="docubible-error">' . __('Versión de la Biblia no válida. Por favor, verifica la configuración.', 'docubible') . '</div>';
        }
        
        // Obtener versículo aleatorio
        $verse = $this->api->get_random_verse($atts['bible_id']);
        
        if (!$verse) {
            return '<div class="docubible-error">' . __('No se pudo obtener un versículo. Por favor, verifica la configuración de la API.', 'docubible') . '</div>';
        }
        
        // Obtener libros para opciones
        $books = $this->api->get_bible_books($atts['bible_id']);
        
        if (empty($books)) {
            return '<div class="docubible-error">' . __('No se pudieron obtener los libros de la Biblia.', 'docubible') . '</div>';
        }
        
        // Obtener libro correcto
        $correct_book = null;
        foreach ($books as $book) {
            if ($book->id === $verse->reference->bookId) {
                $correct_book = $book;
                break;
            }
        }
        
        if (!$correct_book) {
            return '<div class="docubible-error">' . __('Error al identificar el libro del versículo.', 'docubible') . '</div>';
        }
        
        // Generar opciones incorrectas
        $incorrect_books = array();
        $books_copy = $books;
        shuffle($books_copy);
        
        foreach ($books_copy as $book) {
            if (count($incorrect_books) >= 3) {
                break;
            }
            
            if ($book->id !== $correct_book->id) {
                $incorrect_books[] = $book;
            }
        }
        
        // Combinar y mezclar opciones
        $options = array_merge(array($correct_book), $incorrect_books);
        shuffle($options);
        
        // Guardar datos en sesión para verificación
        $question_data = array(
            'type' => 'identify_book',
            'correct_book_id' => $correct_book->id,
            'correct_book_name' => $correct_book->name,
            'verse_content' => $verse->content,
            'reference' => $verse->reference,
            'bible_id' => $atts['bible_id']
        );
        
        $question_id = uniqid('docubible_');
        update_option($question_id, $question_data, false);
        
        // Renderizar trivia
        ob_start();
        include DOCUBIBLE_PLUGIN_DIR . 'public/partials/identify-book-trivia.php';
        return ob_get_clean();
    }
    
    /**
     * Shortcode para mostrar el ranking
     */
    public function ranking_shortcode($atts) {
        // Extraer atributos
        $atts = shortcode_atts(
            array(
                'limit' => 10,
                'period' => get_option('docubible_competition_period', 'monthly')
            ),
            $atts,
            'docubible_ranking'
        );
        
        global $wpdb;
        $table_name = DOCUBIBLE_DB_PREFIX . 'docubible_scores';
        
        // Determinar período
        $period_clause = '';
        switch ($atts['period']) {
            case 'daily':
                $period_clause = "AND DATE(created_at) = CURDATE()";
                break;
            case 'weekly':
                $period_clause = "AND YEARWEEK(created_at, 1) = YEARWEEK(CURDATE(), 1)";
                break;
            case 'monthly':
                $period_clause = "AND YEAR(created_at) = YEAR(CURDATE()) AND MONTH(created_at) = MONTH(CURDATE())";
                break;
            case 'yearly':
                $period_clause = "AND YEAR(created_at) = YEAR(CURDATE())";
                break;
            case 'all':
                $period_clause = "";
                break;
        }
        
        // Obtener ranking
        $ranking = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT u.display_name, SUM(s.score) as total_score, COUNT(*) as questions_answered
                FROM {$table_name} s
                JOIN {$wpdb->users} u ON s.user_id = u.ID
                WHERE 1=1 {$period_clause}
                GROUP BY s.user_id
                ORDER BY total_score DESC, questions_answered ASC
                LIMIT %d",
                intval($atts['limit'])
            )
        );
        
        // Renderizar ranking
        ob_start();
        include DOCUBIBLE_PLUGIN_DIR . 'public/partials/ranking.php';
        return ob_get_clean();
    }
    
    /**
     * Obtener cantidad de preguntas respondidas hoy por un usuario
     */
    private function get_today_questions_count($user_id) {
        global $wpdb;
        $table_name = DOCUBIBLE_DB_PREFIX . 'docubible_scores';
        
        return $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM {$table_name} WHERE user_id = %d AND DATE(created_at) = CURDATE()",
                $user_id
            )
        );
    }
    
    /**
     * Renderizar mensaje de límite alcanzado
     */
    private function render_limit_reached_message() {
        $daily_limit = get_option('docubible_daily_questions_limit', 10);
        
        return '<div class="docubible-limit-reached">' . 
            sprintf(__('Has alcanzado el límite diario de %d preguntas. Vuelve mañana para más trivias bíblicas.', 'docubible'), $daily_limit) . 
            '</div>';
    }
}
