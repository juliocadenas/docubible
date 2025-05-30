<?php
// Asegurar que estamos trabajando con UTF-8
header('Content-Type: text/html; charset=utf-8');
?>
<div class="docubible-trivia docubible-complete-verse" data-question-id="<?php echo esc_attr($question_id); ?>">
    <div class="docubible-trivia-header">
        <h3><?php _e('Completa el versículo', 'docubible'); ?></h3>
        <?php if (get_option('docubible_response_time_limit', 30) > 0): ?>
        <div class="docubible-timer">
            <span class="docubible-timer-label"><?php _e('Tiempo restante:', 'docubible'); ?></span>
            <span class="docubible-timer-value" data-time="<?php echo esc_attr(get_option('docubible_response_time_limit', 30)); ?>">
                <?php echo esc_html(get_option('docubible_response_time_limit', 30)); ?>
            </span>
        </div>
        <?php endif; ?>
    </div>
    
    <div class="docubible-trivia-content">
        <div class="docubible-verse-first-half">
            <span class="docubible-first-half-text"><?php echo wp_kses_post($verse_halves['first_half']); ?></span>
            <span class="docubible-continuation">...</span>
        </div>
        
        <div class="docubible-instruction">
            <p><?php _e('Selecciona la continuación correcta del versículo:', 'docubible'); ?></p>
        </div>
        
        <div class="docubible-options">
            <?php foreach ($options as $index => $option): ?>
                <div class="docubible-option" data-option="<?php echo esc_attr($option); ?>">
                    <label>
                        <input type="radio" name="docubible_answer" value="<?php echo esc_attr($option); ?>">
                        <span><?php echo wp_kses_post($option); ?></span>
                    </label>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    
    <div class="docubible-trivia-footer">
        <div class="docubible-buttons">
            <button class="docubible-button docubible-check-answer"><?php _e('Evaluar respuesta', 'docubible'); ?></button>
            <button class="docubible-button docubible-show-answer"><?php _e('Ver respuesta', 'docubible'); ?></button>
            <button class="docubible-button docubible-next-question"><?php _e('Siguiente', 'docubible'); ?></button>
        </div>
        
        <div class="docubible-result"></div>
        
        <div class="docubible-verse-reference" style="display: none;">
            <?php echo esc_html($verse_halves['reference']->bookId . ' ' . $verse_halves['reference']->chapterId . ':' . $verse_halves['reference']->verseId); ?>
        </div>
        
        <div class="docubible-verse-full" style="display: none;">
            <strong><?php _e('Versículo completo:', 'docubible'); ?></strong><br>
            <?php echo wp_kses_post($verse_halves['full']); ?>
        </div>
    </div>
</div>
