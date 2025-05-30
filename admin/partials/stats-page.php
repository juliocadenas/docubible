<div class="wrap">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
    
    <div class="nav-tab-wrapper">
        <a href="?page=docubible-stats&tab=users" class="nav-tab <?php echo (!isset($_GET['tab']) || $_GET['tab'] === 'users') ? 'nav-tab-active' : ''; ?>"><?php _e('Estadísticas de usuarios', 'docubible'); ?></a>
        <a href="?page=docubible-stats&tab=questions" class="nav-tab <?php echo (isset($_GET['tab']) && $_GET['tab'] === 'questions') ? 'nav-tab-active' : ''; ?>"><?php _e('Estadísticas de preguntas', 'docubible'); ?></a>
        <a href="?page=docubible-stats&tab=export" class="nav-tab <?php echo (isset($_GET['tab']) && $_GET['tab'] === 'export') ? 'nav-tab-active' : ''; ?>"><?php _e('Exportar datos', 'docubible'); ?></a>
    </div>
    
    <div class="tab-content">
        <?php
        $tab = isset($_GET['tab']) ? $_GET['tab'] : 'users';
        
        switch ($tab) {
            case 'users':
                include DOCUBIBLE_PLUGIN_DIR . 'admin/partials/stats-users.php';
                break;
            case 'questions':
                include DOCUBIBLE_PLUGIN_DIR . 'admin/partials/stats-questions.php';
                break;
            case 'export':
                include DOCUBIBLE_PLUGIN_DIR . 'admin/partials/stats-export.php';
                break;
        }
        ?>
    </div>
</div>
