<?php
/**
 * Plugin Name: DocuBible
 * Plugin URI: https://tudominio.com/docubible
 * Description: Plugin de trivias bíblicas con sistema de ranking utilizando la API de scripture.api.bible
 * Version: 1.1.0
 * Author: Tu Nombre
 * Author URI: https://tudominio.com
 * Text Domain: docubible
 * Domain Path: /languages
 * License: GPL v2 or later
 */

// Evitar acceso directo
if (!defined('ABSPATH')) {
    exit;
}

// Definir constantes
define('DOCUBIBLE_VERSION', '1.1.0');
define('DOCUBIBLE_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('DOCUBIBLE_PLUGIN_URL', plugin_dir_url(__FILE__));
define('DOCUBIBLE_PLUGIN_BASENAME', plugin_basename(__FILE__));
define('DOCUBIBLE_DB_PREFIX', $GLOBALS['wpdb']->prefix); // Usar el prefijo de WordPress en lugar de uno personalizado

// Incluir archivos necesarios
require_once DOCUBIBLE_PLUGIN_DIR . 'includes/class-docubible-api.php';
require_once DOCUBIBLE_PLUGIN_DIR . 'includes/class-docubible-shortcodes.php';
require_once DOCUBIBLE_PLUGIN_DIR . 'includes/class-docubible-admin.php';
require_once DOCUBIBLE_PLUGIN_DIR . 'includes/class-docubible-database.php';
require_once DOCUBIBLE_PLUGIN_DIR . 'includes/class-docubible-ajax.php';
require_once DOCUBIBLE_PLUGIN_DIR . 'includes/class-docubible.php';

// Activación, desactivación y desinstalación
register_activation_hook(__FILE__, array('DocuBible_Database', 'activate'));
register_deactivation_hook(__FILE__, array('DocuBible_Database', 'deactivate'));
register_uninstall_hook(__FILE__, array('DocuBible_Database', 'uninstall'));

// Iniciar el plugin
function docubible_init() {
    // Cargar traducciones
    load_plugin_textdomain('docubible', false, dirname(plugin_basename(__FILE__)) . '/languages');
    
    // Inicializar clases principales
    $api = new DocuBible_API();
    $shortcodes = new DocuBible_Shortcodes($api);
    $admin = new DocuBible_Admin($api);
    $ajax = new DocuBible_AJAX($api, $shortcodes);
    
    // Inicializar plugin
    $docubible = new DocuBible($api, $shortcodes, $admin);
    $docubible->run();
}
add_action('plugins_loaded', 'docubible_init');

// Función para registrar scripts y estilos
function docubible_enqueue_scripts() {
    wp_enqueue_style(
        'docubible-public',
        DOCUBIBLE_PLUGIN_URL . 'public/css/docubible-public.css',
        array(),
        DOCUBIBLE_VERSION,
        'all'
    );
    
    wp_enqueue_script(
        'docubible-public',
        DOCUBIBLE_PLUGIN_URL . 'public/js/docubible-public.js',
        array('jquery'),
        DOCUBIBLE_VERSION,
        true
    );
    
    wp_localize_script(
        'docubible-public',
        'docubible_params',
        array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('docubible_nonce'),
            'i18n' => array(
                'correct' => __('¡Correcto!', 'docubible'),
                'incorrect' => __('Incorrecto. Inténtalo de nuevo.', 'docubible'),
                'loading' => __('Cargando...', 'docubible'),
                'error' => __('Ha ocurrido un error. Por favor, inténtalo de nuevo.', 'docubible'),
                'select_option' => __('Por favor, selecciona una opción.', 'docubible'),
                'time_up' => __('¡Tiempo agotado!', 'docubible')
            )
        )
    );
}
add_action('wp_enqueue_scripts', 'docubible_enqueue_scripts');

// Agregar enlace a la configuración en la página de plugins
function docubible_plugin_action_links($links) {
    $settings_link = '<a href="' . admin_url('admin.php?page=docubible') . '">' . __('Configuración', 'docubible') . '</a>';
    array_unshift($links, $settings_link);
    return $links;
}
add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'docubible_plugin_action_links');

// Agregar filtro para depuración
function docubible_debug_info() {
    return array(
        'plugin_version' => DOCUBIBLE_VERSION,
        'wp_version' => get_bloginfo('version'),
        'php_version' => phpversion(),
        'api_key_set' => !empty(get_option('docubible_api_key', '')),
        'default_bible' => get_option('docubible_default_bible', 'es-RVR1960'),
        'charset' => get_option('docubible_charset', 'UTF-8'),
        'response_time_limit' => get_option('docubible_response_time_limit', 30),
        'allowed_languages' => get_option('docubible_allowed_languages', array('spa'))
    );
}
add_filter('docubible_debug_info', 'docubible_debug_info');

// Forzar la creación de tablas si no existen
function docubible_check_tables() {
    global $wpdb;
    $table_name = DOCUBIBLE_DB_PREFIX . 'docubible_scores';
    
    if ($wpdb->get_var("SHOW TABLES LIKE '{$table_name}'") != $table_name) {
        DocuBible_Database::activate();
    }
}
add_action('init', 'docubible_check_tables');
