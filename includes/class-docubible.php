<?php
/**
 * Clase principal del plugin DocuBible
 */
class DocuBible {
    /**
     * Instancia de la API
     */
    private $api;
    
    /**
     * Instancia de los shortcodes
     */
    private $shortcodes;
    
    /**
     * Instancia del admin
     */
    private $admin;
    
    /**
     * Constructor
     */
    public function __construct($api = null, $shortcodes = null, $admin = null) {
        $this->api = $api ?: new DocuBible_API();
        $this->shortcodes = $shortcodes ?: new DocuBible_Shortcodes($this->api);
        $this->admin = $admin ?: new DocuBible_Admin($this->api);
        
        $this->define_admin_hooks();
        $this->define_public_hooks();
    }
    
    /**
     * Definir hooks de administración
     */
    private function define_admin_hooks() {
        add_action('admin_enqueue_scripts', array($this->admin, 'enqueue_styles'));
        add_action('admin_enqueue_scripts', array($this->admin, 'enqueue_scripts'));
        add_action('admin_menu', array($this->admin, 'add_admin_menu'));
        add_action('admin_init', array($this->admin, 'register_settings'));
    }
    
    /**
     * Definir hooks públicos
     */
    private function define_public_hooks() {
        // Los scripts y estilos se registran en el archivo principal
    }
    
    /**
     * Ejecutar el plugin
     */
    public function run() {
        $this->shortcodes->register_shortcodes();
        
        // Agregar filtro para depuración
        add_filter('docubible_debug_info', array($this, 'get_debug_info'));
    }
    
    /**
     * Obtener información de depuración
     */
    public function get_debug_info() {
        $debug_info = array(
            'plugin_version' => DOCUBIBLE_VERSION,
            'wp_version' => get_bloginfo('version'),
            'php_version' => phpversion(),
            'api_key_set' => !empty(get_option('docubible_api_key', '')),
            'default_bible' => get_option('docubible_default_bible', 'es-RVR1960'),
            'charset' => get_option('docubible_charset', 'UTF-8'),
            'response_time_limit' => get_option('docubible_response_time_limit', 30)
        );
        
        return $debug_info;
    }
}
