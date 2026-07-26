<?php
/**
 * Infrastructure for data-driven editorial category hubs.
 *
 * A category keeps the standard category.php output until its hub_enabled
 * term meta is explicitly set to "1".
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Return true when a category has opted in to the hub template.
 *
 * @param WP_Term|null $term Category term. Defaults to the queried object.
 * @return bool
 */
function loraleya_is_category_hub($term = null) {
    if (null === $term) {
        $term = get_queried_object();
    }

    return $term instanceof WP_Term
        && 'category' === $term->taxonomy
        && '1' === (string) get_term_meta($term->term_id, 'hub_enabled', true);
}

/**
 * Decode a saved hub JSON field into a numerically indexed array.
 *
 * @param int    $term_id Term ID.
 * @param string $key     Term meta key.
 * @return array
 */
function loraleya_get_category_hub_json($term_id, $key) {
    $raw = get_term_meta((int) $term_id, $key, true);
    if (!is_string($raw) || '' === trim($raw)) {
        return [];
    }

    $data = json_decode($raw, true);
    return is_array($data) && loraleya_category_hub_is_list($data) ? $data : [];
}

/**
 * PHP 7.4-compatible equivalent of array_is_list().
 *
 * @param array $items Array to inspect.
 * @return bool
 */
function loraleya_category_hub_is_list($items) {
    if ([] === $items) {
        return true;
    }

    return array_keys($items) === range(0, count($items) - 1);
}

/**
 * Validate and sanitize one of the structured JSON fields.
 *
 * @param string $raw  Submitted JSON.
 * @param string $type steps, care, links or faq.
 * @return string|WP_Error Normalized JSON or validation error.
 */
function loraleya_sanitize_category_hub_json($raw, $type) {
    $raw = trim((string) wp_unslash($raw));
    if ('' === $raw) {
        return '';
    }

    $items = json_decode($raw, true);
    if (JSON_ERROR_NONE !== json_last_error() || !is_array($items) || !loraleya_category_hub_is_list($items)) {
        return new WP_Error('invalid_hub_json', 'Ожидается JSON-массив.');
    }

    $clean = [];
    foreach ($items as $item) {
        if (!is_array($item)) {
            return new WP_Error('invalid_hub_json_item', 'Каждый элемент JSON должен быть объектом.');
        }

        if ('steps' === $type) {
            $label  = isset($item['label']) ? sanitize_text_field($item['label']) : '';
            $target = isset($item['target']) ? sanitize_title(ltrim((string) $item['target'], '#')) : '';
            if ('' === $label || '' === $target) {
                return new WP_Error('invalid_hub_step', 'Каждому шагу нужны label и target.');
            }
            $clean[] = ['label' => $label, 'target' => $target];
            continue;
        }

        if ('care' === $type) {
            $value = isset($item['value']) ? sanitize_text_field($item['value']) : '';
            $label = isset($item['label']) ? sanitize_text_field($item['label']) : '';
            $note  = isset($item['note']) ? sanitize_text_field($item['note']) : '';
            if ('' === $value || '' === $label) {
                return new WP_Error('invalid_hub_care', 'Каждому параметру ухода нужны value и label.');
            }
            $clean[] = ['value' => $value, 'label' => $label, 'note' => $note];
            continue;
        }

        if ('links' === $type) {
            $label = isset($item['label']) ? sanitize_text_field($item['label']) : '';
            $url   = isset($item['url']) ? trim((string) $item['url']) : '';
            if ('' === $label || '' === $url) {
                return new WP_Error('invalid_hub_link', 'Каждой ссылке нужны label и url.');
            }
            if (0 === strpos($url, '/')) {
                $url = '/' . ltrim(sanitize_text_field($url), '/');
            } else {
                $url = esc_url_raw($url);
            }
            if ('' === $url) {
                return new WP_Error('invalid_hub_link_url', 'Указан некорректный URL.');
            }
            $clean[] = ['label' => $label, 'url' => $url];
            continue;
        }

        if ('faq' === $type) {
            $question = isset($item['question']) ? sanitize_text_field($item['question']) : '';
            $answer   = isset($item['answer']) ? wp_kses_post($item['answer']) : '';
            if ('' === $question || '' === trim(wp_strip_all_tags($answer))) {
                return new WP_Error('invalid_hub_faq', 'Каждому вопросу нужны question и answer.');
            }
            $clean[] = ['question' => $question, 'answer' => $answer];
        }
    }

    return wp_json_encode($clean, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
}

/**
 * Shared field definitions for category add/edit forms.
 *
 * @return array
 */
function loraleya_category_hub_fields() {
    return [
        'hub_h1' => [
            'label' => 'Hub: H1',
            'type'  => 'text',
            'help'  => 'Если пусто, используется название рубрики.',
        ],
        'hub_lead' => [
            'label' => 'Hub: лид',
            'type'  => 'textarea',
            'help'  => 'Короткий вводный абзац в hero.',
        ],
        'hub_image_id' => [
            'label' => 'Hub: ID главного изображения',
            'type'  => 'number',
            'help'  => 'Attachment ID из медиатеки WordPress.',
        ],
        'hub_image_caption' => [
            'label' => 'Hub: подпись изображения',
            'type'  => 'text',
            'help'  => 'Необязательная подпись под hero.',
        ],
        'hub_featured_post_id' => [
            'label' => 'Hub: ID главной статьи',
            'type'  => 'number',
            'help'  => 'Опубликованная статья из этой рубрики.',
        ],
        'hub_position_text' => [
            'label' => 'Hub: позиция LoraLeya (HTML)',
            'type'  => 'textarea',
            'help'  => 'Допустим безопасный HTML WordPress.',
        ],
        'hub_steps_json' => [
            'label' => 'Hub: шаги (JSON)',
            'type'  => 'json',
            'help'  => '[{"label":"Выбрать материал","target":"material"}]',
        ],
        'hub_care_json' => [
            'label' => 'Hub: параметры ухода (JSON)',
            'type'  => 'json',
            'help'  => '[{"value":"30 °C","label":"Стирка","note":"Без отбеливателя"}]',
        ],
        'hub_links_json' => [
            'label' => 'Hub: товарные переходы (JSON)',
            'type'  => 'json',
            'help'  => '[{"label":"Скатерти","url":"/product-category/skaterti/"}]',
        ],
        'seo_faq' => [
            'label' => 'SEO FAQ рубрики (JSON)',
            'type'  => 'json',
            'help'  => '[{"question":"Что такое жаккард?","answer":"..."}]',
        ],
    ];
}

/**
 * Render the additional fields on the Add category screen.
 */
function loraleya_category_hub_add_fields() {
    wp_nonce_field('loraleya_category_hub_save', 'loraleya_category_hub_nonce');
    ?>
    <div class="form-field">
        <label>
            <input type="checkbox" name="hub_enabled" value="1">
            Включить шаблон редакционного хаба
        </label>
        <p>По умолчанию выключено. Без отметки продолжает работать обычный архив рубрики.</p>
    </div>
    <?php foreach (loraleya_category_hub_fields() as $key => $field) : ?>
        <div class="form-field">
            <label for="<?php echo esc_attr($key); ?>"><?php echo esc_html($field['label']); ?></label>
            <?php if ('text' === $field['type'] || 'number' === $field['type']) : ?>
                <input type="<?php echo esc_attr($field['type']); ?>" name="<?php echo esc_attr($key); ?>" id="<?php echo esc_attr($key); ?>" value="">
            <?php else : ?>
                <textarea name="<?php echo esc_attr($key); ?>" id="<?php echo esc_attr($key); ?>" rows="<?php echo 'json' === $field['type'] ? 7 : 5; ?>"></textarea>
            <?php endif; ?>
            <p><?php echo esc_html($field['help']); ?></p>
        </div>
    <?php endforeach;
}
add_action('category_add_form_fields', 'loraleya_category_hub_add_fields', 20);

/**
 * Render the additional fields on the Edit category screen.
 *
 * @param WP_Term $term Category term.
 */
function loraleya_category_hub_edit_fields($term) {
    wp_nonce_field('loraleya_category_hub_save', 'loraleya_category_hub_nonce');
    $enabled = '1' === (string) get_term_meta($term->term_id, 'hub_enabled', true);
    ?>
    <tr class="form-field">
        <th scope="row">Редакционный хаб</th>
        <td>
            <label>
                <input type="checkbox" name="hub_enabled" value="1" <?php checked($enabled); ?>>
                Включить шаблон редакционного хаба
            </label>
            <p class="description">Без отметки продолжает работать обычный архив рубрики.</p>
        </td>
    </tr>
    <?php foreach (loraleya_category_hub_fields() as $key => $field) :
        $value = get_term_meta($term->term_id, $key, true);
        ?>
        <tr class="form-field">
            <th scope="row"><label for="<?php echo esc_attr($key); ?>"><?php echo esc_html($field['label']); ?></label></th>
            <td>
                <?php if ('text' === $field['type'] || 'number' === $field['type']) : ?>
                    <input class="regular-text" type="<?php echo esc_attr($field['type']); ?>" name="<?php echo esc_attr($key); ?>" id="<?php echo esc_attr($key); ?>" value="<?php echo esc_attr($value); ?>">
                <?php else : ?>
                    <textarea class="large-text code" name="<?php echo esc_attr($key); ?>" id="<?php echo esc_attr($key); ?>" rows="<?php echo 'json' === $field['type'] ? 8 : 6; ?>"><?php echo esc_textarea($value); ?></textarea>
                <?php endif; ?>
                <p class="description"><?php echo esc_html($field['help']); ?></p>
            </td>
        </tr>
    <?php endforeach;
}
add_action('category_edit_form_fields', 'loraleya_category_hub_edit_fields', 20);

/**
 * Save and validate category hub fields.
 *
 * Invalid JSON and invalid attachment/post IDs do not overwrite a previously
 * saved valid value.
 *
 * @param int $term_id Category term ID.
 */
function loraleya_save_category_hub_fields($term_id) {
    if (!current_user_can('manage_categories')) {
        return;
    }
    if (!isset($_POST['loraleya_category_hub_nonce']) ||
        !wp_verify_nonce(
            sanitize_text_field(wp_unslash($_POST['loraleya_category_hub_nonce'])),
            'loraleya_category_hub_save'
        )) {
        return;
    }

    update_term_meta($term_id, 'hub_enabled', isset($_POST['hub_enabled']) ? '1' : '0');

    $text_fields = ['hub_h1', 'hub_lead', 'hub_image_caption'];
    foreach ($text_fields as $key) {
        if (isset($_POST[$key])) {
            update_term_meta($term_id, $key, sanitize_text_field(wp_unslash($_POST[$key])));
        }
    }

    if (isset($_POST['hub_position_text'])) {
        update_term_meta($term_id, 'hub_position_text', wp_kses_post(wp_unslash($_POST['hub_position_text'])));
    }

    if (isset($_POST['hub_image_id'])) {
        $image_id = absint($_POST['hub_image_id']);
        if (0 === $image_id || 'attachment' === get_post_type($image_id)) {
            update_term_meta($term_id, 'hub_image_id', $image_id);
        }
    }

    if (isset($_POST['hub_featured_post_id'])) {
        $post_id = absint($_POST['hub_featured_post_id']);
        $valid   = 0 === $post_id;
        if ($post_id) {
            $post       = get_post($post_id);
            $categories = wp_get_post_categories($post_id);
            $valid      = $post instanceof WP_Post
                && 'post' === $post->post_type
                && 'publish' === $post->post_status
                && in_array((int) $term_id, array_map('intval', $categories), true);
        }
        if ($valid) {
            update_term_meta($term_id, 'hub_featured_post_id', $post_id);
        }
    }

    $json_fields = [
        'hub_steps_json' => 'steps',
        'hub_care_json'  => 'care',
        'hub_links_json' => 'links',
        'seo_faq'        => 'faq',
    ];
    foreach ($json_fields as $key => $type) {
        if (!isset($_POST[$key])) {
            continue;
        }
        $clean = loraleya_sanitize_category_hub_json($_POST[$key], $type);
        if (!is_wp_error($clean)) {
            update_term_meta($term_id, $key, $clean);
        }
    }
}
add_action('created_category', 'loraleya_save_category_hub_fields', 20);
add_action('edited_category', 'loraleya_save_category_hub_fields', 20);

/**
 * Keep featured material out of the remaining archive loop and pagination.
 *
 * @param WP_Query $query Main category query.
 */
function loraleya_category_hub_exclude_featured($query) {
    if (is_admin() || !$query->is_main_query() || !$query->is_category()) {
        return;
    }

    $term = get_queried_object();
    if (!loraleya_is_category_hub($term)) {
        return;
    }

    $featured_id = absint(get_term_meta($term->term_id, 'hub_featured_post_id', true));
    if (!$featured_id) {
        return;
    }

    $excluded   = array_map('absint', (array) $query->get('post__not_in'));
    $excluded[] = $featured_id;
    $query->set('post__not_in', array_values(array_unique($excluded)));
}
add_action('pre_get_posts', 'loraleya_category_hub_exclude_featured');

/**
 * Resolve stored relative paths against the active WordPress installation.
 *
 * @param string $url Stored link.
 * @return string
 */
function loraleya_category_hub_url($url) {
    $url = trim((string) $url);
    return 0 === strpos($url, '/') ? home_url($url) : $url;
}

/**
 * Category FAQ schema uses the same seo_faq data as the visible FAQ.
 */
function loraleya_category_hub_faq_schema() {
    if (!is_category()) {
        return;
    }

    $term = get_queried_object();
    if (!loraleya_is_category_hub($term)) {
        return;
    }

    $faq = loraleya_get_category_hub_json($term->term_id, 'seo_faq');
    if (!$faq) {
        return;
    }

    $entities = [];
    foreach ($faq as $item) {
        if (empty($item['question']) || empty($item['answer'])) {
            continue;
        }
        $entities[] = [
            '@type'          => 'Question',
            'name'           => wp_strip_all_tags($item['question']),
            'acceptedAnswer' => [
                '@type' => 'Answer',
                'text'  => wp_strip_all_tags($item['answer']),
            ],
        ];
    }
    if (!$entities) {
        return;
    }

    $schema = [
        '@context'   => 'https://schema.org',
        '@type'      => 'FAQPage',
        'mainEntity' => $entities,
    ];
    echo "\n" . '<script type="application/ld+json">' .
        wp_json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) .
        '</script>' . "\n";
}
add_action('wp_head', 'loraleya_category_hub_faq_schema', 30);
