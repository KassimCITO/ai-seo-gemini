<?php
/**
 * Plugin Name: AI SEO Meta by Gemini (Tematic Prompt Version)
 * Description: Genera títulos y descripciones SEO automáticamente usando Google Gemini con prompts por temática.
 * Version: 1.2
 * Author: Kassim & AI Systems
 */

add_action( 'admin_menu', function() {
    // Verifica si Rank Math está activo
    if ( !is_plugin_active('seo-by-rank-math/rank-math.php') ) {
        return;
    }
    add_submenu_page(
        'rank-math',
        'Generador SEO con IA (Gemini)', // Título de la página
        'AI SEO Generator',              // Título del menú
        'manage_options',
        'ai-seo-generator',
        'ai_seo_generator_page'
    );
    add_submenu_page(
        'rank-math',
        'Configuración Gemini API y Variables', // Título de la página
        'Gemini API Key',                       // Título del menú
        'manage_options',
        'ai-seo-gemini-api',
        'ai_seo_gemini_api_page'
    );
});
// Página de configuración para la API Key de Gemini
function ai_seo_gemini_api_page() {
    if ( !current_user_can('manage_options') ) {
        return;
    }
    $error = '';
    $success = false;
    // Variables configurables
    $default_location = get_option('ai_gemini_location', 'Apatzingán, Michoacán, México');
    $default_topic = get_option('ai_gemini_default_topic', 'noticias de Apatzingán, Michoacán');
    $default_prompt = "Eres un experto en SEO local de '{location}', escribiendo para el tema de '{topic}'. Escribe un título SEO atractivo (menos de 60 caracteres) y una descripción SEO (menos de 155 caracteres) para un artículo titulado: '{title}'. Resalta la ubicación y relevancia local. Resumen del contenido: '{excerpt}'.";
    if (
        isset($_POST['ai_gemini_api_key']) || isset($_POST['ai_gemini_prompt']) || isset($_POST['ai_gemini_location']) || isset($_POST['ai_gemini_default_topic'])
    ) {
        check_admin_referer('ai_gemini_api_key_save');
        if (isset($_POST['ai_gemini_api_key'])) {
            update_option('ai_gemini_api_key', sanitize_text_field($_POST['ai_gemini_api_key']));
        }
        if (isset($_POST['ai_gemini_location'])) {
            update_option('ai_gemini_location', sanitize_text_field($_POST['ai_gemini_location']));
            $default_location = sanitize_text_field($_POST['ai_gemini_location']);
        }
        if (isset($_POST['ai_gemini_default_topic'])) {
            update_option('ai_gemini_default_topic', sanitize_text_field($_POST['ai_gemini_default_topic']));
            $default_topic = sanitize_text_field($_POST['ai_gemini_default_topic']);
        }
        if (isset($_POST['ai_gemini_prompt'])) {
            $prompt = trim($_POST['ai_gemini_prompt']);
            $missing = [];
            foreach(['{location}','{topic}','{title}','{excerpt}'] as $var) {
                if (strpos($prompt, $var) === false) $missing[] = $var;
            }
            if ($missing) {
                $error = 'El prompt debe contener las variables: ' . implode(', ', $missing);
            } else {
                update_option('ai_gemini_prompt', wp_kses_post($prompt));
                $success = true;
            }
        }
    }
    $api_key = esc_attr(get_option('ai_gemini_api_key', ''));
    $prompt = get_option('ai_gemini_prompt', $default_prompt);
    ?>
    <div class="wrap">
        <h1>Configurar Gemini API Key y Variables Locales</h1>
        <?php if ($error): ?>
            <div class="notice notice-error is-dismissible"><p><?php echo esc_html($error); ?></p></div>
        <?php elseif ($success): ?>
            <div class="notice notice-success is-dismissible"><p>Configuración guardada correctamente.</p></div>
        <?php endif; ?>
        <form method="post">
            <?php wp_nonce_field('ai_gemini_api_key_save'); ?>
            <table class="form-table">
                <tr>
                    <th scope="row"><label for="ai_gemini_api_key">Gemini API Key</label></th>
                    <td>
                        <input name="ai_gemini_api_key" id="ai_gemini_api_key" type="text" value="<?php echo $api_key; ?>" class="regular-text" style="width:400px;" />
                        <p class="description">Consigue tu API Key en <a href="https://aistudio.google.com/app/apikey" target="_blank">aistudio.google.com/app/apikey</a></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="ai_gemini_location">Ubicación local</label></th>
                    <td>
                        <input name="ai_gemini_location" id="ai_gemini_location" type="text" value="<?php echo esc_attr($default_location); ?>" class="regular-text" style="width:400px;" />
                        <p class="description">Ejemplo: Apatzingán, Michoacán, México</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="ai_gemini_default_topic">Topic por defecto</label></th>
                    <td>
                        <input name="ai_gemini_default_topic" id="ai_gemini_default_topic" type="text" value="<?php echo esc_attr($default_topic); ?>" class="regular-text" style="width:400px;" />
                        <p class="description">Ejemplo: noticias de Apatzingán, Michoacán</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="ai_gemini_prompt">Prompt general</label></th>
                    <td>
                        <textarea name="ai_gemini_prompt" id="ai_gemini_prompt" rows="6" style="width:400px;"><?php echo esc_textarea($prompt); ?></textarea>
                        <p class="description">
                            Puedes usar las variables: <code>{location}</code>, <code>{topic}</code>, <code>{title}</code>, <code>{excerpt}</code><br>
                            <b>Recomendación:</b> Incluye siempre la ubicación local en el prompt para fortalecer el SEO local.<br>
                            Ejemplo de topic: <code><?php echo esc_html($default_topic); ?></code>
                        </p>
                        <button type="button" class="button" onclick="document.getElementById('ai_gemini_prompt').value='<?php echo esc_js($default_prompt); ?>'">Restaurar ejemplo</button>
                        <br><span style="color:#666;font-size:12px;">Ejemplo:<br><code><?php echo esc_html($default_prompt); ?></code></span>
                    </td>
                </tr>
            </table>
            <p><input type="submit" class="button button-primary" value="Guardar configuración"></p>
        </form>
    </div>
    <?php
}

function ai_seo_generator_page() {
    if ( !is_plugin_active('seo-by-rank-math/rank-math.php') ) {
        echo '<div class="notice notice-error"><p><b>Rank Math SEO debe estar activo para usar este plugin.</b></p></div>';
        return;
    }
    if (isset($_POST['ai_generate'])) {
        $result = ai_generate_meta_by_topic();
        if ( is_wp_error($result) ) {
            echo '<div class="notice notice-error"><p>' . esc_html($result->get_error_message()) . '</p></div>';
        } elseif ($result === 'no_api_key') {
            echo '<div class="notice notice-error"><p>Debes configurar tu API Key de Gemini en Ajustes &gt; Escritura.</p></div>';
        } elseif ($result === 'no_posts') {
            echo '<div class="notice notice-warning"><p>No hay entradas pendientes de SEO meta.</p></div>';
        } else {
            echo '<div class="notice notice-success"><p>SEO meta generado para las entradas seleccionadas.</p></div>';
        }
    }
    ?>
    <div class="wrap">
        <h1>AI SEO Generator (Gemini con Temáticas)</h1>
        <form method="post">
            <p><button type="submit" name="ai_generate" class="button button-primary">Generar SEO Meta Masivo</button></p>
        </form>
    </div>
    <?php
}

function ai_generate_meta_by_topic() {
    if ( !is_plugin_active('seo-by-rank-math/rank-math.php') ) {
        return new WP_Error('no_rank_math', 'Rank Math SEO no está activo.');
    }
    $api_key = get_option('ai_gemini_api_key', '');
    if ( !$api_key ) {
        return 'no_api_key';
    }
    $prompt_template = get_option('ai_gemini_prompt', "Eres un experto en SEO escribiendo para el tema de '{topic}'. Escribe un título SEO atractivo (menos de 60 caracteres) y una descripción SEO (menos de 155 caracteres) para un artículo titulado: '{title}'. Resumen del contenido: '{excerpt}'.");
    $posts = get_posts([
        'numberposts' => 50,
        'meta_query' => [
            'relation' => 'OR',
            ['key' => 'rank_math_title', 'compare' => '=', 'value' => ''],
            ['key' => 'rank_math_description', 'compare' => '=', 'value' => '']
        ]
    ]);
    if ( empty($posts) ) {
        return 'no_posts';
    }
    foreach ($posts as $post) {
        $title = get_the_title($post);
        $excerpt = wp_trim_words(strip_tags($post->post_content), 30);
        $excerpt_generated = false;
        if (empty($excerpt)) {
            // Generar excerpt usando Gemini si no hay
            $excerpt_prompt = "Resume el siguiente contenido en menos de 30 palabras para SEO:\n" . strip_tags($post->post_content);
            $excerpt_res = ai_send_to_gemini($excerpt_prompt);
            if ($excerpt_res && isset($excerpt_res['title'])) {
                $excerpt = $excerpt_res['title'];
                $excerpt_generated = true;
            } else {
                $excerpt = 'Sin resumen disponible.';
            }
        }
        $topic = get_post_meta($post->ID, 'seo_topic', true);
        if (!$topic) {
            $topic = get_option('ai_gemini_default_topic', 'noticias de Apatzingán, Michoacán');
        }
        $location = get_option('ai_gemini_location', 'Apatzingán, Michoacán, México');
        $prompt = str_replace(
            ['{location}', '{topic}', '{title}', '{excerpt}'],
            [$location, $topic, $title, $excerpt],
            $prompt_template
        );
        $res = ai_send_to_gemini($prompt);
        if ($res && isset($res['title'], $res['description'])) {
            $seo_title = sanitize_text_field($res['title']);
            update_post_meta($post->ID, 'rank_math_title', $seo_title);
            update_post_meta($post->ID, 'rank_math_description', sanitize_text_field($res['description']));

            // === Generación avanzada del slug SEO ===
            /*
             * Lógica para crear un slug SEO optimizado, único y limpio:
             * 1. Normaliza el título generado por Gemini (minúsculas, solo letras, números y guiones).
             * 2. Si la palabra clave principal (focus keyword) no está al inicio, la antepone.
             * 3. Elimina stopwords comunes en español para mayor relevancia y limpieza.
             * 4. Limita el slug a un máximo de 8 palabras.
             * 5. Elimina palabras que sean fechas (años, meses, días, números sueltos).
             * 6. Garantiza unicidad: si el slug ya existe, añade un sufijo incremental.
             *
             * Ejemplo de resultado: "palabra-clave-principal-titulo-relevante"
             */
            $focus_keyword = get_post_meta($post->ID, 'rank_math_focus_keyword', true);
            if (!$focus_keyword && isset($res['title'])) {
                // Si aún no se generó, intenta obtener la palabra clave principal del resultado
                $focus_keyword = sanitize_title($res['title']);
            }
            // --- Fin de lógica de slug SEO ---

            // Normaliza el título para el slug
            $slug = strtolower($seo_title);
            $slug = preg_replace('/[^a-z0-9\s-]/', '', $slug); // solo letras, números, espacios y guiones
            $slug = preg_replace('/[\s_]+/', '-', $slug); // espacios y guiones bajos a guiones
            $slug = preg_replace('/-+/', '-', $slug); // evitar guiones dobles
            $slug = trim($slug, '-');

            // 1. Incluir la palabra clave principal al inicio si no está
            if ($focus_keyword) {
                $focus_keyword_clean = strtolower($focus_keyword);
                $focus_keyword_clean = preg_replace('/[^a-z0-9\s-]/', '', $focus_keyword_clean);
                $focus_keyword_clean = preg_replace('/[\s_]+/', '-', $focus_keyword_clean);
                $focus_keyword_clean = preg_replace('/-+/', '-', $focus_keyword_clean);
                $focus_keyword_clean = trim($focus_keyword_clean, '-');
                if ($focus_keyword_clean && strpos($slug, $focus_keyword_clean) !== 0) {
                    // Solo prepende si no está al inicio
                    if (strpos($slug, $focus_keyword_clean) === false) {
                        $slug = $focus_keyword_clean . '-' . $slug;
                    } else {
                        // Si está en medio, muévelo al inicio
                        $slug = $focus_keyword_clean . '-' . str_replace($focus_keyword_clean, '', $slug);
                        $slug = preg_replace('/-+/', '-', $slug);
                        $slug = trim($slug, '-');
                    }
                }
            }

            // 2. Eliminar stopwords del slug
            $stopwords = [
                'de', 'la', 'y', 'en', 'para', 'el', 'los', 'las', 'del', 'por', 'con', 'un', 'una', 'unos', 'unas', 'a', 'al', 'o', 'u', 'es', 'que', 'se', 'su', 'sus', 'le', 'lo', 'les', 'mi', 'tu', 'te', 'nos', 'vos', 'ya', 'como', 'más', 'pero', 'sin', 'sobre', 'tras', 'entre', 'si', 'ni', 'no', 'muy', 'también', 'fue', 'son', 'ha', 'han', 'ser', 'esta', 'este', 'estas', 'estos', 'esa', 'ese', 'esas', 'esos', 'cada', 'cual', 'cuales', 'donde', 'cuando', 'quien', 'quienes', 'cuyo', 'cuyos', 'cuyas', 'desde', 'hasta', 'durante', 'mediante', 'según', 'bajo', 'ante', 'contra', 'hacia', 'sobre', 'tras', 'durante', 'mediante', 'excepto', 'salvo', 'incluso', 'además', 'etc'
            ];
            $slug_words = explode('-', $slug);
            $slug_words = array_filter($slug_words, function($word) use ($stopwords) {
                return $word && !in_array($word, $stopwords);
            });

            // 3. Limitar el slug a menos de 8 palabras
            $slug_words = array_slice($slug_words, 0, 8);

            // 4. Eliminar palabras que sean fechas (año, mes, día)
            $slug_words = array_filter($slug_words, function($word) {
                $meses = ['enero','febrero','marzo','abril','mayo','junio','julio','agosto','septiembre','octubre','noviembre','diciembre'];
                $dias = ['lunes','martes','miercoles','miércoles','jueves','viernes','sabado','sábado','domingo'];
                if (in_array($word, $meses) || in_array($word, $dias)) return false;
                if (preg_match('/^(19|20)\d{2}$/', $word)) return false;
                if (preg_match('/^\d{1,2}$/', $word)) return false;
                return true;
            });

            $base_slug = implode('-', $slug_words);
            $base_slug = preg_replace('/-+/', '-', $base_slug);
            $base_slug = trim($base_slug, '-');
            if (empty($base_slug)) {
                $base_slug = 'post';
            }
            $slug = $base_slug;
            $i = 2;
            $old_slug = $post->post_name;
            // Verifica si el slug ya existe en otro post
            while (post_exists_by_slug($slug, $post->ID)) {
                $slug = $base_slug . '-' . $i;
                $i++;
            }
            // Si el slug cambia, crea redirección 301 con Rank Math
            if ($old_slug && $old_slug !== $slug && function_exists('rank_math_create_redirection')) {
                $old_url = get_permalink($post->ID);
                $old_url = str_replace($slug, $old_slug, $old_url); // reconstruye la URL antigua
                $new_url = get_permalink($post->ID);
                $new_url = str_replace($old_slug, $slug, $new_url);
                rank_math_create_redirection(
                    $old_url,
                    $new_url,
                    '301',
                    [
                        'status' => 'active',
                        'redirection_type' => 'url',
                        'source_type' => 'url',
                        'hits' => 0,
                        'created' => current_time('mysql'),
                        'updated' => current_time('mysql'),
                        'note' => 'Auto redirect by AI SEO Gemini plugin (slug change)'
                    ]
                );
            }
            wp_update_post([
                'ID' => $post->ID,
                'post_name' => $slug
            ]);
// Verifica si un slug ya existe en otro post (excepto el actual)
function post_exists_by_slug($slug, $current_id = 0) {
    global $wpdb;
    $sql = $wpdb->prepare(
        "SELECT ID FROM $wpdb->posts WHERE post_name = %s AND post_type = 'post' AND ID != %d AND post_status != 'trash' LIMIT 1",
        $slug, $current_id
    );
    $result = $wpdb->get_var($sql);
    return !empty($result);
}
            // Establecer la URL canónica usando Rank Math
            $permalink = get_permalink($post->ID);
            update_post_meta($post->ID, 'rank_math_canonical_url', esc_url_raw($permalink));
            // Generar palabra clave principal y keywords
            $kw_prompt = "A partir del siguiente contenido, tema y ubicación, sugiere la palabra o frase clave principal más relevante para SEO local (solo una, sin comillas ni hashtags):\n\nTítulo: $title\nTema: $topic\nUbicación: $location\nResumen: $excerpt\n\nPalabra o frase clave principal:";
            $kw_res = ai_send_to_gemini($kw_prompt);
            if ($kw_res && isset($kw_res['title']) && !empty($kw_res['title'])) {
                update_post_meta($post->ID, 'rank_math_focus_keyword', sanitize_text_field($kw_res['title']));
            }
            $kws_prompt = "A partir del siguiente contenido, tema y ubicación, sugiere una lista de hasta 5 palabras clave relevantes para SEO local, separadas por comas, sin hashtags ni comillas:\n\nTítulo: $title\nTema: $topic\nUbicación: $location\nResumen: $excerpt\n\nPalabras clave:";
            $kws_res = ai_send_to_gemini($kws_prompt);
            if ($kws_res && isset($kws_res['title']) && !empty($kws_res['title'])) {
                update_post_meta($post->ID, 'rank_math_keywords', sanitize_text_field($kws_res['title']));
            }
            if ($excerpt_generated) {
                // Actualiza el excerpt del post
                wp_update_post([
                    'ID' => $post->ID,
                    'post_excerpt' => $excerpt
                ]);
            }
        }
    }
    return true;
}

function ai_send_to_gemini($prompt) {
    $api_key = get_option('ai_gemini_api_key', '');
    if (!$api_key) return false;

    $response = wp_remote_post('https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent', [
        'headers' => [
            'Content-Type' => 'application/json',
            'X-goog-api-key' => $api_key
        ],
        'body' => json_encode([
            'contents' => [
                [
                    'parts' => [
                        ['text' => $prompt]
                    ]
                ]
            ]
        ])
    ]);

    if (is_wp_error($response)) {
        error_log('Google Gemini API error: ' . $response->get_error_message());
        return false;
    }
    $code = wp_remote_retrieve_response_code($response);
    if ($code !== 200) {
        error_log('Google Gemini API HTTP error: ' . $code);
        return false;
    }
    $body = json_decode(wp_remote_retrieve_body($response), true);

    if (isset($body['candidates'][0]['content']['parts'][0]['text'])) {
        $lines = preg_split('/\r?\n/', trim($body['candidates'][0]['content']['parts'][0]['text']));
        return [
            'title' => $lines[0] ?? '',
            'description' => $lines[1] ?? ''
        ];
    }
    return false;
}

// Ya no es necesario el campo en Ajustes > Escritura, ahora se gestiona desde el submenú propio
// Verifica si un plugin está activo (para compatibilidad con versiones antiguas de WP)
if ( !function_exists('is_plugin_active') ) {
    require_once ABSPATH . 'wp-admin/includes/plugin.php';
}

// Agrega campos personalizados al editor de entradas para SEO Gemini
add_action('add_meta_boxes', function() {
    add_meta_box('seo_topic_box', 'Temática SEO (Gemini)', 'render_seo_topic_box', 'post', 'side', 'default');
    add_meta_box('seo_keywords_box', 'Palabras clave SEO (Gemini)', 'render_seo_keywords_box', 'post', 'side', 'default');
});

function render_seo_topic_box($post) {
    $value = get_post_meta($post->ID, 'seo_topic', true);
    echo '<label for="seo_topic">Tema principal:</label>';
    echo '<input type="text" id="seo_topic" name="seo_topic" value="' . esc_attr($value) . '" style="width:100%;" />';
    echo '<p class="description">Ej: noticias, tecnología, salud, política, ISP...</p>';
}

function render_seo_keywords_box($post) {
    $focus = get_post_meta($post->ID, 'rank_math_focus_keyword', true);
    $keywords = get_post_meta($post->ID, 'rank_math_keywords', true);
    echo '<label for="rank_math_focus_keyword">Palabra clave principal:</label>';
    echo '<input type="text" id="rank_math_focus_keyword" name="rank_math_focus_keyword" value="' . esc_attr($focus) . '" style="width:100%;margin-bottom:6px;" />';
    echo '<label for="rank_math_keywords">Palabras clave sugeridas (separadas por coma):</label>';
    echo '<input type="text" id="rank_math_keywords" name="rank_math_keywords" value="' . esc_attr($keywords) . '" style="width:100%;" />';
    echo '<p class="description">Puedes editar lo sugerido por Gemini o dejarlo en blanco para que se regenere en el próximo uso masivo.</p>';
}

add_action('save_post', function($post_id) {
    if (array_key_exists('seo_topic', $_POST)) {
        update_post_meta($post_id, 'seo_topic', sanitize_text_field($_POST['seo_topic']));
    }
    if (array_key_exists('rank_math_focus_keyword', $_POST)) {
        update_post_meta($post_id, 'rank_math_focus_keyword', sanitize_text_field($_POST['rank_math_focus_keyword']));
    }
    if (array_key_exists('rank_math_keywords', $_POST)) {
        update_post_meta($post_id, 'rank_math_keywords', sanitize_text_field($_POST['rank_math_keywords']));
    }
});