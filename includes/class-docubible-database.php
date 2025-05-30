<?php
/**
 * Clase para manejar la base de datos del plugin DocuBible
 */
class DocuBible_Database {
    
    /**
     * Activar plugin y crear tablas
     */
    public static function activate() {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();
        
        // Tabla de puntuaciones
        $table_name = DOCUBIBLE_DB_PREFIX . 'docubible_scores';
        $sql = "CREATE TABLE IF NOT EXISTS {$table_name} (
            id int(11) NOT NULL AUTO_INCREMENT,
            user_id bigint(20) NOT NULL,
            question_type varchar(50) NOT NULL,
            score int(11) NOT NULL DEFAULT 0,
            response_time int(11) NOT NULL DEFAULT 0,
            bible_id varchar(50) NOT NULL,
            created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY user_id (user_id),
            KEY question_type (question_type),
            KEY created_at (created_at)
        ) {$charset_collate};";
        
        // Tabla de competiciones
        $table_name_competitions = DOCUBIBLE_DB_PREFIX . 'docubible_competitions';
        $sql .= "CREATE TABLE IF NOT EXISTS {$table_name_competitions} (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            name varchar(255) NOT NULL,
            description text,
            start_date datetime NOT NULL,
            end_date datetime NOT NULL,
            status varchar(20) NOT NULL DEFAULT 'active',
            created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY status (status),
            KEY date_range (start_date, end_date)
        ) {$charset_collate};";
        
        // Tabla de premios
        $table_name_prizes = DOCUBIBLE_DB_PREFIX . 'docubible_prizes';
        $sql .= "CREATE TABLE IF NOT EXISTS {$table_name_prizes} (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            competition_id bigint(20) NOT NULL,
            position int(11) NOT NULL,
            prize_name varchar(255) NOT NULL,
            prize_description text,
            created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY competition_id (competition_id)
        ) {$charset_collate};";
        
        // Tabla de configuración de idiomas
        $table_name_languages = DOCUBIBLE_DB_PREFIX . 'docubible_languages';
        $sql .= "CREATE TABLE IF NOT EXISTS {$table_name_languages} (
            id int(11) NOT NULL AUTO_INCREMENT,
            language_code varchar(10) NOT NULL,
            language_name varchar(100) NOT NULL,
            bible_id varchar(50) NOT NULL,
            is_active tinyint(1) NOT NULL DEFAULT 1,
            created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY language_code (language_code)
        ) {$charset_collate};";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
        
        // Agregar opciones por defecto
        add_option('docubible_api_key', '');
        add_option('docubible_default_bible', 'es-RVR1960');
        add_option('docubible_daily_questions_limit', 10);
        add_option('docubible_competition_period', 'monthly');
        add_option('docubible_prize_positions', 3);
        add_option('docubible_allowed_languages', array('spa'));
        add_option('docubible_response_time_limit', 30);
        add_option('docubible_charset', 'UTF-8');
        
        // Insertar idiomas por defecto
        $default_languages = array(
            array('es', 'Español', 'es-RVR1960'),
            array('en', 'English', 'en-KJV'),
            array('pt', 'Português', 'pt-ARC'),
            array('fr', 'Français', 'fr-LSG')
        );
        
        foreach ($default_languages as $lang) {
            $wpdb->insert(
                $table_name_languages,
                array(
                    'language_code' => $lang[0],
                    'language_name' => $lang[1],
                    'bible_id' => $lang[2],
                    'is_active' => 1
                )
            );
        }
    }
    
    /**
     * Desactivar plugin
     */
    public static function deactivate() {
        // Limpiar cache y tareas programadas
        wp_clear_scheduled_hook('docubible_cleanup');
    }
    
    /**
     * Desinstalar plugin
     */
    public static function uninstall() {
        global $wpdb;
        
        // Eliminar tablas
        $wpdb->query("DROP TABLE IF EXISTS " . DOCUBIBLE_DB_PREFIX . "docubible_scores");
        $wpdb->query("DROP TABLE IF EXISTS " . DOCUBIBLE_DB_PREFIX . "docubible_competitions");
        $wpdb->query("DROP TABLE IF EXISTS " . DOCUBIBLE_DB_PREFIX . "docubible_prizes");
        $wpdb->query("DROP TABLE IF EXISTS " . DOCUBIBLE_DB_PREFIX . "docubible_languages");
        
        // Eliminar opciones
        delete_option('docubible_api_key');
        delete_option('docubible_default_bible');
        delete_option('docubible_daily_questions_limit');
        delete_option('docubible_competition_period');
        delete_option('docubible_prize_positions');
        delete_option('docubible_allowed_languages');
        delete_option('docubible_response_time_limit');
        delete_option('docubible_charset');
    }
}
