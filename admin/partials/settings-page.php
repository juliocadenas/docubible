<div class="wrap">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
    
    <form method="post" action="options.php">
        <?php
        settings_fields('docubible_settings');
        do_settings_sections('docubible');
        submit_button(__('Guardar cambios', 'docubible'));
        ?>
    </form>
    
    <div class="card docubible-help-card" style="margin-top: 20px;">
        <h2><?php _e('Shortcodes disponibles', 'docubible'); ?></h2>
        <p><?php _e('Utiliza estos shortcodes para mostrar las trivias bíblicas en tus páginas o entradas:', 'docubible'); ?></p>
        
        <table class="widefat" style="margin-top: 10px;">
            <thead>
                <tr>
                    <th><?php _e('Shortcode', 'docubible'); ?></th>
                    <th><?php _e('Descripción', 'docubible'); ?></th>
                    <th><?php _e('Parámetros', 'docubible'); ?></th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><code>[docubible_fill_blanks]</code></td>
                    <td><?php _e('Muestra una trivia para rellenar palabras faltantes en un versículo bíblico.', 'docubible'); ?></td>
                    <td><code>bible_id</code> - <?php _e('ID de la versión de la Biblia (opcional)', 'docubible'); ?></td>
                </tr>
                <tr>
                    <td><code>[docubible_complete_verse]</code></td>
                    <td><?php _e('Muestra una trivia para completar la segunda mitad de un versículo bíblico.', 'docubible'); ?></td>
                    <td><code>bible_id</code> - <?php _e('ID de la versión de la Biblia (opcional)', 'docubible'); ?></td>
                </tr>
                <tr>
                    <td><code>[docubible_identify_book]</code></td>
                    <td><?php _e('Muestra una trivia para identificar el libro de un versículo.', 'docubible'); ?></td>
                    <td><code>bible_id</code> - <?php _e('ID de la versión de la Biblia (opcional)', 'docubible'); ?></td>
                </tr>
                <tr>
                    <td><code>[docubible_ranking]</code></td>
                    <td><?php _e('Muestra el ranking de usuarios.', 'docubible'); ?></td>
                    <td>
                        <code>limit</code> - <?php _e('Número de usuarios a mostrar (por defecto: 10)', 'docubible'); ?><br>
                        <code>period</code> - <?php _e('Período: daily, weekly, monthly, yearly, all (por defecto: monthly)', 'docubible'); ?>
                    </td>
                </tr>
            </tbody>
        </table>
        
        <h3><?php _e('Ejemplos de uso:', 'docubible'); ?></h3>
        <ul>
            <li><code>[docubible_fill_blanks bible_id="es-RVR1960"]</code> - <?php _e('Trivia de rellenar espacios en blanco con la Biblia Reina Valera 1960', 'docubible'); ?></li>
            <li><code>[docubible_complete_verse bible_id="es-RVR1960"]</code> - <?php _e('Trivia de completar la segunda mitad del versículo con la Biblia Reina Valera 1960', 'docubible'); ?></li>
            <li><code>[docubible_identify_book bible_id="en-KJV"]</code> - <?php _e('Trivia de identificar libro con la King James Version', 'docubible'); ?></li>
            <li><code>[docubible_ranking limit="5" period="weekly"]</code> - <?php _e('Ranking semanal mostrando los 5 mejores usuarios', 'docubible'); ?></li>
        </ul>
        
        <div class="docubible-notice docubible-notice-info" style="margin-top: 15px; padding: 10px; background-color: #d9edf7; border: 1px solid #bce8f1; border-radius: 4px;">
            <h4 style="margin-top: 0;"><?php _e('Nota sobre compatibilidad:', 'docubible'); ?></h4>
            <p style="margin-bottom: 0;"><?php _e('Si ya estás usando el shortcode [docubible_complete_verse] en versiones anteriores, seguirá funcionando como "rellenar espacios en blanco". Para la nueva funcionalidad de "completar versículo", usa el shortcode actualizado.', 'docubible'); ?></p>
        </div>
        
        <p><?php _e('Para más información, consulta la página de ayuda completa.', 'docubible'); ?></p>
    </div>
</div>
