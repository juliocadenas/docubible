<div class="docubible-ranking">
    <div class="docubible-ranking-header">
        <h3>
            <?php 
            $period_text = '';
            switch ($atts['period']) {
                case 'daily':
                    $period_text = __('Ranking diario', 'docubible');
                    break;
                case 'weekly':
                    $period_text = __('Ranking semanal', 'docubible');
                    break;
                case 'monthly':
                    $period_text = __('Ranking mensual', 'docubible');
                    break;
                case 'yearly':
                    $period_text = __('Ranking anual', 'docubible');
                    break;
                case 'all':
                    $period_text = __('Ranking general', 'docubible');
                    break;
            }
            echo esc_html($period_text);
            ?>
        </h3>
    </div>
    
    <div class="docubible-ranking-content">
        <?php if (empty($ranking)): ?>
            <div class="docubible-no-results">
                <?php _e('No hay resultados disponibles para este período.', 'docubible'); ?>
            </div>
        <?php else: ?>
            <table class="docubible-ranking-table">
                <thead>
                    <tr>
                        <th><?php _e('Posición', 'docubible'); ?></th>
                        <th><?php _e('Usuario', 'docubible'); ?></th>
                        <th><?php _e('Puntuación', 'docubible'); ?></th>
                        <th><?php _e('Preguntas', 'docubible'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($ranking as $index => $user): ?>
                        <tr class="<?php echo ($index < 3) ? 'docubible-top-' . ($index + 1) : ''; ?>">
                            <td><?php echo esc_html($index + 1); ?></td>
                            <td><?php echo esc_html($user->display_name); ?></td>
                            <td><?php echo esc_html($user->total_score); ?></td>
                            <td><?php echo esc_html($user->questions_answered); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>
