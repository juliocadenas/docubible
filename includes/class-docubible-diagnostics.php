<?php
/**
 * Clase para diagnósticos y resolución de problemas del plugin DocuBible
 */
class DocuBible_Diagnostics {
    
    /**
     * Ejecutar diagnósticos completos
     */
    public static function run_diagnostics() {
        $results = array();
        
        // 1. Verificar API Key
        $results['api_key'] = self::check_api_key();
        
        // 2. Verificar tablas de base de datos
        $results['database'] = self::check_database_tables();
        
        // 3. Verificar configuración
        $results['config'] = self::check_configuration();
        
        // 4. Verificar permisos
        $results['permissions'] = self::check_permissions();
        
        return $results;
    }
    
    /**
     * Verificar API Key
     */
    private static function check_api_key() {
        $api_key = get_option('docubible_api_key', '');
        
        if (empty($api_key)) {
            return array(
                'status' => 'error',
                'message' => 'API Key no configurada',
                'solution' => 'Configura tu API Key en Configuración > DocuBible'
            );
        }
        
        // Probar conexión con la API
        $api = new DocuBible_API();
        $test_response = $api->get_bible_versions();
        
        if (is_wp_error($test_response)) {
            return array(
                'status' => 'error',
                'message' => 'API Key inválida o expirada: ' . $test_response->get_error_message(),
                'solution' => 'Verifica tu API Key en https://scripture.api.bible/'
            );
        }
        
        return array(
            'status' => 'success',
            'message' => 'API Key válida y funcionando'
        );
    }
    
    /**
     * Verificar tablas de base de datos
     */
    private static function check_database_tables() {
        global $wpdb;
        
        $required_tables = array(
            DOCUBIBLE_DB_PREFIX . 'docubible_scores',
            DOCUBIBLE_DB_PREFIX . 'docubible_languages'
        );
        
        $missing_tables = array();
        
        foreach ($required_tables as $table) {
            if ($wpdb->get_var("SHOW TABLES LIKE '{$table}'") != $table) {
                $missing_tables[] = $table;
            }
        }
        
        if (!empty($missing_tables)) {
            return array(
                'status' => 'error',
                'message' => 'Tablas faltantes: ' . implode(', ', $missing_tables),
                'solution' => 'Ejecutar reparación de base de datos',
                'action' => 'repair_database'
            );
        }
        
        return array(
            'status' => 'success',
            'message' => 'Todas las tablas están presentes'
        );
    }
    
    /**
     * Verificar configuración
     */
    private static function check_configuration() {
        $issues = array();
        
        // Verificar configuración básica
        $default_bible = get_option('docubible_default_bible', '');
        if (empty($default_bible)) {
            $issues[] = 'Versión de Biblia por defecto no configurada';
        }
        
        $daily_limit = get_option('docubible_daily_questions_limit', 0);
        if ($daily_limit <= 0) {
            $issues[] = 'Límite diario de preguntas no configurado';
        }
        
        if (!empty($issues)) {
            return array(
                'status' => 'warning',
                'message' => implode(', ', $issues),
                'solution' => 'Revisar configuración en el panel de administración'
            );
        }
        
        return array(
            'status' => 'success',
            'message' => 'Configuración correcta'
        );
    }
    
    /**
     * Verificar permisos
     */
    private static function check_permissions() {
        // Verificar permisos de escritura en directorio del plugin
        if (!is_writable(DOCUBIBLE_PLUGIN_DIR)) {
            return array(
                'status' => 'warning',
                'message' => 'Directorio del plugin no tiene permisos de escritura',
                'solution' => 'Verificar permisos del directorio'
            );
        }
        
        return array(
            'status' => 'success',
            'message' => 'Permisos correctos'
        );
    }
    
    /**
     * Reparar base de datos
     */
    public static function repair_database() {
        try {
            // Forzar recreación de tablas
            DocuBible_Database::activate();
            
            return array(
                'status' => 'success',
                'message' => 'Base de datos reparada exitosamente'
            );
        } catch (Exception $e) {
            return array(
                'status' => 'error',
                'message' => 'Error al reparar base de datos: ' . $e->getMessage()
            );
        }
    }
    
    /**
     * Limpiar datos duplicados
     */
    public static function clean_duplicate_data() {
        global $wpdb;
        
        try {
            // Limpiar idiomas duplicados
            $languages_table = DOCUBIBLE_DB_PREFIX . 'docubible_languages';
            
            $wpdb->query("
                DELETE t1 FROM {$languages_table} t1
                INNER JOIN {$languages_table} t2 
                WHERE t1.id > t2.id 
                AND t1.language_code = t2.language_code
            ");
            
            return array(
                'status' => 'success',
                'message' => 'Datos duplicados eliminados'
            );
        } catch (Exception $e) {
            return array(
                'status' => 'error',
                'message' => 'Error al limpiar datos: ' . $e->getMessage()
            );
        }
    }
    
    /**
     * Resetear configuración
     */
    public static function reset_configuration() {
        // Configuración por defecto
        $default_options = array(
            'docubible_default_bible' => 'es-RVR1960',
            'docubible_daily_questions_limit' => 10,
            'docubible_competition_period' => 'monthly',
            'docubible_allowed_languages' => array('spa', 'eng')
        );
        
        foreach ($default_options as $option => $value) {
            update_option($option, $value);
        }
        
        return array(
            'status' => 'success',
            'message' => 'Configuración restablecida a valores por defecto'
        );
    }
}
