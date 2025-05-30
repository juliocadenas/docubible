<div class="wrap">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
    
    <div class="nav-tab-wrapper">
        <a href="?page=docubible-competitions&tab=active" class="nav-tab <?php echo (!isset($_GET['tab']) || $_GET['tab'] === 'active') ? 'nav-tab-active' : ''; ?>"><?php _e('Competiciones activas', 'docubible'); ?></a>
        <a href="?page=docubible-competitions&tab=upcoming" class="nav-tab <?php echo (isset($_GET['tab']) && $_GET['tab'] === 'upcoming') ? 'nav-tab-active' : ''; ?>"><?php _e('Próximas competiciones', 'docubible'); ?></a>
        <a href="?page=docubible-competitions&tab=past" class="nav-tab <?php echo (isset($_GET['tab']) && $_GET['tab'] === 'past') ? 'nav-tab-active' : ''; ?>"><?php _e('Competiciones pasadas', 'docubible'); ?></a>
        <a href="?page=docubible-competitions&tab=add" class="nav-tab <?php echo (isset($_GET['tab']) && $_GET['tab'] === 'add') ? 'nav-tab-active' : ''; ?>"><?php _e('Añadir nueva', 'docubible'); ?></a>
    </div>
    
    <div class="tab-content">
        <?php
        $tab = isset($_GET['tab']) ? $_GET['tab'] : 'active';
        
        switch ($tab) {
            case 'active':
                include DOCUBIBLE_PLUGIN_DIR . 'admin/partials/competitions-active.php';
                break;
            case 'upcoming':
                include DOCUBIBLE_PLUGIN_DIR . 'admin/partials/competitions-upcoming.php';
                break;
            case 'past':
                include DOCUBIBLE_PLUGIN_DIR . 'admin/partials/competitions-past.php';
                break;
            case 'add':
                include DOCUBIBLE_PLUGIN_DIR . 'admin/partials/competitions-add.php';
                break;
        }
        ?>
    </div>
</div>
