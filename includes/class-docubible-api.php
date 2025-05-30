<?php
/**
 * Clase para manejar la comunicación con la API de scripture.api.bible
 */
class DocuBible_API {
    /**
     * API Key
     */
    private $api_key;
    
    /**
     * API URL base
     */
    private $api_url = 'https://api.scripture.api.bible/v1';
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->api_key = get_option('docubible_api_key', '');
    }
    
    /**
     * Obtener el idioma de una versión específica de la Biblia
     */
    public function get_bible_language($bible_id) {
        $bible_info = $this->get_bible_info($bible_id);
        
        if (!$bible_info || !isset($bible_info->language) || !isset($bible_info->language->id)) {
            return false;
        }
        
        return $bible_info->language;
    }
    
    /**
     * Obtener versiones de la Biblia disponibles
     */
    public function get_bible_versions() {
        $response = $this->make_request('bibles');
        
        if (is_wp_error($response)) {
            return array();
        }
        
        // Filtrar solo versiones en español u otros idiomas configurados
        $allowed_languages = $this->get_allowed_languages();
        $filtered_versions = array();
        
        foreach ($response->data as $bible) {
            if (isset($bible->language) && in_array($bible->language->id, $allowed_languages)) {
                $filtered_versions[] = $bible;
            }
        }
        
        return $filtered_versions;
    }
    
    /**
     * Obtener idiomas permitidos
     */
    private function get_allowed_languages() {
        $configured_languages = get_option('docubible_allowed_languages', array('spa'));
        
        // Si no hay idiomas configurados, usar español por defecto
        if (empty($configured_languages)) {
            return array('spa');
        }
        
        return $configured_languages;
    }
    
    /**
     * Obtener libros de una versión específica de la Biblia
     */
    public function get_bible_books($bible_id) {
        $response = $this->make_request("bibles/{$bible_id}/books");
        
        if (is_wp_error($response)) {
            return array();
        }
        
        return $response->data;
    }
    
    /**
     * Obtener capítulos de un libro específico
     */
    public function get_book_chapters($bible_id, $book_id) {
        $response = $this->make_request("bibles/{$bible_id}/books/{$book_id}/chapters");
        
        if (is_wp_error($response)) {
            return array();
        }
        
        return $response->data;
    }
    
    /**
     * Obtener versículos de un capítulo específico
     */
    public function get_chapter_verses($bible_id, $chapter_id) {
        $response = $this->make_request("bibles/{$bible_id}/chapters/{$chapter_id}/verses");
        
        if (is_wp_error($response)) {
            return array();
        }
        
        return $response->data;
    }
    
    /**
     * Obtener un versículo específico
     */
    public function get_verse($bible_id, $verse_id) {
        $response = $this->make_request("bibles/{$bible_id}/verses/{$verse_id}");
        
        if (is_wp_error($response)) {
            return null;
        }
        
        return $response->data;
    }
    
    /**
     * Buscar versículos por texto
     */
    public function search_verses($bible_id, $query) {
        $response = $this->make_request("bibles/{$bible_id}/search?query=" . urlencode($query));
        
        if (is_wp_error($response)) {
            return array();
        }
        
        return $response->data->passages;
    }
    
    /**
     * Realizar solicitud a la API
     */
    private function make_request($endpoint) {
        if (empty($this->api_key)) {
            return new WP_Error('no_api_key', __('No se ha configurado la API Key', 'docubible'));
        }
        
        $args = array(
            'headers' => array(
                'api-key' => $this->api_key,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
                'Accept-Language' => 'es',
                'Accept-Charset' => 'UTF-8'
            ),
            'timeout' => 15
        );
        
        $response = wp_remote_get("{$this->api_url}/{$endpoint}", $args);
        
        if (is_wp_error($response)) {
            error_log('DocuBible API Error: ' . $response->get_error_message());
            return $response;
        }
        
        $status_code = wp_remote_retrieve_response_code($response);
        if ($status_code !== 200) {
            error_log('DocuBible API Error: Status code ' . $status_code);
            return new WP_Error('api_error', sprintf(__('Error en la API (código %d)', 'docubible'), $status_code));
        }
        
        $body = wp_remote_retrieve_body($response);
        
        // Asegurar que estamos trabajando con UTF-8
        if (!$this->is_utf8($body)) {
            $body = utf8_encode($body);
        }
        
        $data = json_decode($body);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            error_log('DocuBible JSON Error: ' . json_last_error_msg());
            return new WP_Error('invalid_response', __('Respuesta inválida de la API', 'docubible'));
        }
        
        return $data;
    }
    
    /**
     * Verificar si una cadena está en UTF-8
     */
    private function is_utf8($string) {
        return mb_detect_encoding($string, 'UTF-8', true);
    }
    
    /**
     * Obtener información de una versión específica de la Biblia
     */
    public function get_bible_info($bible_id) {
        $response = $this->make_request("bibles/{$bible_id}");
        
        if (is_wp_error($response)) {
            return null;
        }
        
        return $response->data;
    }
    
    /**
     * Verificar si una versión de la Biblia está en un idioma permitido
     */
    public function is_allowed_bible($bible_id) {
        $bible_info = $this->get_bible_info($bible_id);
        
        if (!$bible_info || !isset($bible_info->language) || !isset($bible_info->language->id)) {
            return false;
        }
        
        $allowed_languages = $this->get_allowed_languages();
        return in_array($bible_info->language->id, $allowed_languages);
    }
    
    /**
     * Obtener versículo aleatorio
     */
    public function get_random_verse($bible_id) {
        // Verificar que la versión de la Biblia está en un idioma permitido
        if (!$this->is_allowed_bible($bible_id)) {
            error_log("DocuBible: Versión de Biblia no permitida - {$bible_id}");
            
            // Intentar usar una versión en español por defecto
            $bible_id = 'es-RVR1960';
            
            // Si tampoco está permitida, mostrar error
            if (!$this->is_allowed_bible($bible_id)) {
                return null;
            }
        }
        
        // Obtener libros
        $books = $this->get_bible_books($bible_id);
        
        if (empty($books)) {
            error_log("DocuBible: No se encontraron libros para la versión {$bible_id}");
            return null;
        }
        
        // Seleccionar libro aleatorio
        $random_book = $books[array_rand($books)];
        
        // Obtener capítulos
        $chapters = $this->get_book_chapters($bible_id, $random_book->id);
        
        if (empty($chapters)) {
            error_log("DocuBible: No se encontraron capítulos para el libro {$random_book->id}");
            return null;
        }
        
        // Seleccionar capítulo aleatorio (excluyendo intro)
        $filtered_chapters = array_filter($chapters, function($chapter) {
            return $chapter->number !== 'intro';
        });
        
        if (empty($filtered_chapters)) {
            error_log("DocuBible: No se encontraron capítulos válidos para el libro {$random_book->id}");
            return null;
        }
        
        $random_chapter = $filtered_chapters[array_rand($filtered_chapters)];
        
        // Obtener versículos
        $verses = $this->get_chapter_verses($bible_id, $random_chapter->id);
        
        if (empty($verses)) {
            error_log("DocuBible: No se encontraron versículos para el capítulo {$random_chapter->id}");
            return null;
        }
        
        // Seleccionar versículo aleatorio
        $random_verse = $verses[array_rand($verses)];
        
        // Obtener contenido del versículo
        $verse = $this->get_verse($bible_id, $random_verse->id);
        
        if (!$verse) {
            error_log("DocuBible: No se pudo obtener el versículo {$random_verse->id}");
            return null;
        }
        
        // Asegurar que el contenido está en UTF-8
        if (isset($verse->content) && !$this->is_utf8($verse->content)) {
            $verse->content = utf8_encode($verse->content);
        }
        
        return $verse;
    }
    
    /**
     * Obtener versículo parcial (para completar)
     */
    public function get_partial_verse($verse) {
        if (!$verse || !isset($verse->content)) {
            return null;
        }
        
        $content = $verse->content;
        
        // Eliminar etiquetas HTML que puedan estar en el contenido
        $content = strip_tags($content);
        
        // Dividir por palabras respetando espacios
        preg_match_all('/\S+|\s+/u', $content, $matches);
        $tokens = $matches[0];
        
        // Filtrar solo palabras (no espacios)
        $words = array_values(array_filter($tokens, function($token) {
            return trim($token) !== '';
        }));
        
        // Determinar cuántas palabras ocultar (25-40% del total)
        $total_words = count($words);
        $words_to_hide = max(1, round($total_words * (mt_rand(25, 40) / 100)));
        
        // Determinar posición de inicio para ocultar palabras
        $start_pos = mt_rand(0, $total_words - $words_to_hide);
        
        // Crear versículo parcial
        $partial_content = '';
        $hidden_content = '';
        $current_word = 0;
        
        foreach ($tokens as $token) {
            if (trim($token) === '') {
                // Es un espacio, mantenerlo
                $partial_content .= $token;
            } else {
                // Es una palabra
                if ($current_word >= $start_pos && $current_word < ($start_pos + $words_to_hide)) {
                    $partial_content .= ' _____ ';
                    $hidden_content .= $token . ' ';
                } else {
                    $partial_content .= $token;
                }
                $current_word++;
            }
        }
        
        return array(
            'partial' => trim($partial_content),
            'hidden' => trim($hidden_content),
            'full' => $content,
            'reference' => $verse->reference,
            'book' => $verse->reference->bookId,
            'chapter' => $verse->reference->chapterId,
            'verse' => $verse->reference->verseId
        );
    }

    /**
     * Obtener mitades del versículo (para completar la segunda mitad)
     */
    public function get_verse_halves($verse) {
        if (!$verse || !isset($verse->content)) {
            return null;
        }
        
        $content = $verse->content;
        
        // Eliminar etiquetas HTML que puedan estar en el contenido
        $content = strip_tags($content);
        
        // Dividir por palabras
        $words = preg_split('/\s+/u', trim($content));
        $total_words = count($words);
        
        // Verificar que el versículo tenga suficientes palabras (mínimo 6)
        if ($total_words < 6) {
            return null;
        }
        
        // Dividir exactamente por la mitad
        $half_point = floor($total_words / 2);
        
        $first_half_words = array_slice($words, 0, $half_point);
        $second_half_words = array_slice($words, $half_point);
        
        $first_half = implode(' ', $first_half_words);
        $second_half = implode(' ', $second_half_words);
        
        return array(
            'first_half' => $first_half,
            'second_half' => $second_half,
            'full' => $content,
            'reference' => $verse->reference,
            'book' => $verse->reference->bookId,
            'chapter' => $verse->reference->chapterId,
            'verse' => $verse->reference->verseId,
            'split_point' => $half_point
        );
    }

    /**
     * Generar opciones incorrectas para la segunda mitad del versículo
     */
    public function generate_verse_half_options($correct_half, $bible_id, $count = 3) {
        $incorrect_options = array();
        
        // Obtener la longitud aproximada de la mitad correcta
        $correct_words = explode(' ', $correct_half);
        $target_length = count($correct_words);
        
        // Intentar obtener opciones incorrectas mediante búsqueda
        $search_terms = array('Dios', 'Jesús', 'amor', 'fe', 'esperanza', 'vida', 'camino', 'verdad', 'corazón', 'alma');
        shuffle($search_terms);
        
        foreach ($search_terms as $term) {
            if (count($incorrect_options) >= $count) {
                break;
            }
            
            $search_results = $this->search_verses($bible_id, $term);
            
            if (!empty($search_results)) {
                foreach ($search_results as $result) {
                    if (count($incorrect_options) >= $count) {
                        break;
                    }
                    
                    $result_text = strip_tags($result->text);
                    $result_words = explode(' ', $result_text);
                    
                    // Buscar una porción que tenga longitud similar
                    if (count($result_words) >= $target_length) {
                        $max_start = count($result_words) - $target_length;
                        $start = mt_rand(0, $max_start);
                        $option = implode(' ', array_slice($result_words, $start, $target_length));
                        
                        // Verificar que no sea igual a la correcta y no esté ya en las opciones
                        if ($option !== $correct_half && !in_array($option, $incorrect_options)) {
                            $incorrect_options[] = $option;
                        }
                    }
                }
            }
        }
        
        // Si no se obtuvieron suficientes opciones, generar algunas genéricas
        $generic_endings = array(
            'y su misericordia es para siempre',
            'porque él es bueno y fiel',
            'y no hay otro como él',
            'que permanece para siempre',
            'y su amor nunca falla',
            'porque él es nuestro refugio',
            'y en él encontramos paz',
            'que nos da esperanza eterna'
        );
        
        shuffle($generic_endings);
        
        while (count($incorrect_options) < $count && !empty($generic_endings)) {
            $option = array_shift($generic_endings);
            
            if ($option !== $correct_half && !in_array($option, $incorrect_options)) {
                $incorrect_options[] = $option;
            }
        }
        
        return array_slice($incorrect_options, 0, $count);
    }

    /**
     * Generar opciones incorrectas para un versículo
     */
    public function generate_incorrect_options($correct_text, $bible_id, $count = 3) {
        $incorrect_options = array();
        
        // Intentar obtener opciones incorrectas mediante búsqueda
        $search_terms = array('Dios', 'Jesús', 'amor', 'fe', 'esperanza', 'vida', 'camino', 'verdad');
        $random_term = $search_terms[array_rand($search_terms)];
        
        $search_results = $this->search_verses($bible_id, $random_term);
        
        if (!empty($search_results)) {
            shuffle($search_results);
            
            foreach ($search_results as $result) {
                if (count($incorrect_options) >= $count) {
                    break;
                }
                
                // Asegurarse de que la opción incorrecta sea diferente a la correcta
                if ($result->text !== $correct_text) {
                    // Extraer una parte similar en longitud a la correcta
                    $words = explode(' ', strip_tags($result->text));
                    $correct_words = explode(' ', strip_tags($correct_text));
                    
                    if (count($words) >= count($correct_words)) {
                        $start = mt_rand(0, count($words) - count($correct_words));
                        $option = implode(' ', array_slice($words, $start, count($correct_words)));
                        
                        if (!in_array($option, $incorrect_options) && $option !== $correct_text) {
                            $incorrect_options[] = $option;
                        }
                    }
                }
            }
        }
        
        // Si no se obtuvieron suficientes opciones, generar algunas genéricas
        $generic_options = array(
            'el amor de Dios es eterno',
            'la fe mueve montañas',
            'la verdad os hará libres',
            'el camino, la verdad y la vida',
            'la paz de Dios, que sobrepasa todo entendimiento',
            'por gracia sois salvos por medio de la fe',
            'el que cree en mí, tiene vida eterna',
            'bienaventurados los de limpio corazón'
        );
        
        shuffle($generic_options);
        
        while (count($incorrect_options) < $count) {
            $option = array_shift($generic_options);
            
            if (!in_array($option, $incorrect_options) && $option !== $correct_text) {
                $incorrect_options[] = $option;
            }
            
            if (empty($generic_options)) {
                break;
            }
        }
        
        return $incorrect_options;
    }
}
