<div class="wrap">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
    
    <div class="card">
        <h2><?php _e('Uso de shortcodes', 'docubible'); ?></h2>
        <p><?php _e('Para mostrar las trivias bíblicas en tus páginas o entradas, utiliza los siguientes shortcodes:', 'docubible'); ?></p>
        
        <h3><?php _e('Trivia de rellenar espacios en blanco', 'docubible'); ?></h3>
        <code>[docubible_fill_blanks]</code>

        <h3><?php _e('Trivia de completar versículo', 'docubible'); ?></h3>
        <code>[docubible_complete_verse]</code>
        
        <h3><?php _e('Trivia de identificar el libro', 'docubible'); ?></h3>
        <code>[docubible_identify_book]</code>
        
        <h3><?php _e('Mostrar ranking', 'docubible'); ?></h3>
        <code>[docubible_ranking limit="10" period="monthly"]</code>
        
        <p><?php _e('Parámetros disponibles para el ranking:', 'docubible'); ?></p>
        <ul>
            <li><code>limit</code>: <?php _e('Número de usuarios a mostrar (por defecto: 10)', 'docubible'); ?></li>
            <li><code>period</code>: <?php _e('Período del ranking (daily, weekly, monthly, yearly, all)', 'docubible'); ?></li>
        </ul>

        <table>
            <thead>
                <tr>
                    <th><?php _e('Shortcode', 'docubible'); ?></th>
                    <th><?php _e('Descripción', 'docubible'); ?></th>
                    <th><?php _e('Atributos', 'docubible'); ?></th>
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
            </tbody>
        </table>

        <p><?php _e('Ejemplos de uso:', 'docubible'); ?></p>
        <ul>
            <li><code>[docubible_fill_blanks bible_id="es-RVR1960"]</code> - <?php _e('Trivia de rellenar espacios en blanco con la Biblia Reina Valera 1960', 'docubible'); ?></li>
            <li><code>[docubible_complete_verse bible_id="es-RVR1960"]</code> - <?php _e('Trivia de completar versículo con la Biblia Reina Valera 1960', 'docubible'); ?></li>
        </ul>
    </div>
    
    <div class="card">
        <h2><?php _e('Configuración de la API', 'docubible'); ?></h2>
        <p><?php _e('Para utilizar este plugin, necesitas una API Key de scripture.api.bible.', 'docubible'); ?></p>
        <p><?php _e('Puedes obtener una API Key gratuita en:', 'docubible'); ?> <a href="https://scripture.api.bible/signup" target="_blank">scripture.api.bible</a></p>
        <p><?php _e('Una vez obtenida, introduce la API Key en la página de configuración del plugin.', 'docubible'); ?></p>
    </div>
    
    <div class="card">
        <h2><?php _e('Soporte', 'docubible'); ?></h2>
        <p><?php _e('Si necesitas ayuda con el plugin, puedes contactarnos a través de:', 'docubible'); ?></p>
        <ul>
            <li><?php _e('Email:', 'docubible'); ?> <a href="mailto:soporte@tudominio.com">soporte@tudominio.com</a></li>
            <li><?php _e('Sitio web:', 'docubible'); ?> <a href="https://tudominio.com/soporte" target="_blank">tudominio.com/soporte</a></li>
        </ul>
    </div>
</div>
