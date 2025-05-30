<?php
// Ejecutar diagnósticos si se solicita
$diagnostics_results = null;
$repair_result = null;

if (isset($_POST['run_diagnostics'])) {
    $diagnostics_results = DocuBible_Diagnostics::run_diagnostics();
}

if (isset($_POST['repair_database'])) {
    $repair_result = DocuBible_Diagnostics::repair_database();
}

if (isset($_POST['clean_duplicates'])) {
    $repair_result = DocuBible_Diagnostics::clean_duplicate_data();
}

if (isset($_POST['reset_config'])) {
    $repair_result = DocuBible_Diagnostics::reset_configuration();
}
?>

<div class="wrap">
    <h1><?php _e('Diagnósticos DocuBible', 'docubible'); ?></h1>
    
    <div class="card">
        <h2><?php _e('Herramientas de Diagnóstico', 'docubible'); ?></h2>
        
        <form method="post">
            <?php wp_nonce_field('docubible_diagnostics'); ?>
            <p>
                <input type="submit" name="run_diagnostics" class="button button-primary" 
                       value="<?php _e('Ejecutar Diagnósticos', 'docubible'); ?>">
            </p>
        </form>
        
        <?php if ($diagnostics_results): ?>
            <div class="diagnostics-results">
                <h3><?php _e('Resultados del Diagnóstico', 'docubible'); ?></h3>
                
                <?php foreach ($diagnostics_results as $test => $result): ?>
                    <div class="diagnostic-item">
                        <h4><?php echo ucfirst($test); ?></h4>
                        <div class="status-<?php echo $result['status']; ?>">
                            <strong><?php echo $result['message']; ?></strong>
                            <?php if (isset($result['solution'])): ?>
                                <p><em><?php _e('Solución:', 'docubible'); ?> <?php echo $result['solution']; ?></em></p>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
    
    <div class="card">
        <h2><?php _e('Herramientas de Reparación', 'docubible'); ?></h2>
        
        <form method="post">
            <?php wp_nonce_field('docubible_repair'); ?>
            
            <p>
                <input type="submit" name="repair_database" class="button" 
                       value="<?php _e('Reparar Base de Datos', 'docubible'); ?>"
                       onclick="return confirm('¿Estás seguro de que quieres reparar la base de datos?');">
                <span class="description"><?php _e('Recrea las tablas faltantes del plugin.', 'docubible'); ?></span>
            </p>
            
            <p>
                <input type="submit" name="clean_duplicates" class="button" 
                       value="<?php _e('Limpiar Datos Duplicados', 'docubible'); ?>"
                       onclick="return confirm('¿Estás seguro de que quieres limpiar los datos duplicados?');">
                <span class="description"><?php _e('Elimina entradas duplicadas en la base de datos.', 'docubible'); ?></span>
            </p>
            
            <p>
                <input type="submit" name="reset_config" class="button" 
                       value="<?php _e('Restablecer Configuración', 'docubible'); ?>"
                       onclick="return confirm('¿Estás seguro de que quieres restablecer la configuración?');">
                <span class="description"><?php _e('Restablece la configuración a valores por defecto.', 'docubible'); ?></span>
            </p>
        </form>
        
        <?php if ($repair_result): ?>
            <div class="repair-result status-<?php echo $repair_result['status']; ?>">
                <p><strong><?php echo $repair_result['message']; ?></strong></p>
            </div>
        <?php endif; ?>
    </div>
    
    <div class="card">
        <h2><?php _e('Información del Sistema', 'docubible'); ?></h2>
        
        <table class="widefat">
            <tbody>
                <tr>
                    <td><strong><?php _e('Versión del Plugin', 'docubible'); ?></strong></td>
                    <td><?php echo DOCUBIBLE_VERSION; ?></td>
                </tr>
                <tr>
                    <td><strong><?php _e('Versión de WordPress', 'docubible'); ?></strong></td>
                    <td><?php echo get_bloginfo('version'); ?></td>
                </tr>
                <tr>
                    <td><strong><?php _e('Versión de PHP', 'docubible'); ?></strong></td>
                    <td><?php echo phpversion(); ?></td>
                </tr>
                <tr>
                    <td><strong><?php _e('API Key Configurada', 'docubible'); ?></strong></td>
                    <td><?php echo !empty(get_option('docubible_api_key')) ? __('Sí', 'docubible') : __('No', 'docubible'); ?></td>
                </tr>
                <tr>
                    <td><strong><?php _e('Biblia por Defecto', 'docubible'); ?></strong></td>
                    <td><?php echo get_option('docubible_default_bible', __('No configurada', 'docubible')); ?></td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<style>
.diagnostic-item {
    margin: 10px 0;
    padding: 10px;
    border-left: 4px solid #ccc;
}

.status-success {
    border-left-color: #46b450;
    background-color: #f7fcf0;
}

.status-warning {
    border-left-color: #ffb900;
    background-color: #fff8e5;
}

.status-error {
    border-left-color: #dc3232;
    background-color: #fbeaea;
}

.repair-result {
    margin: 15px 0;
    padding: 10px;
    border-radius: 4px;
}
</style>
