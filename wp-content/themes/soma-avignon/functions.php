<?php
/**
 * SOMA Avignon — Child Theme d'Astra
 * Functions and definitions
 */

if (!defined('ABSPATH')) exit;

define('SOMA_VERSION', '2.0.2');
define('SOMA_DIR', get_stylesheet_directory());
define('SOMA_URI', get_stylesheet_directory_uri());

/* ============================================
   Enqueue Parent + Child Styles & Scripts
   ============================================ */
function soma_enqueue_assets() {
    // Parent theme (Astra)
    wp_enqueue_style('astra-parent', get_template_directory_uri() . '/style.css');

    // Google Fonts
    wp_enqueue_style(
        'soma-google-fonts',
        'https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,500;0,600;1,400&family=Montserrat:wght@300;400;500;600;700&display=swap',
        array(),
        null
    );

    // Child theme stylesheet
    wp_enqueue_style('soma-child', get_stylesheet_uri(), array('astra-parent'), SOMA_VERSION);

    // Main JS
    wp_enqueue_script('soma-main', SOMA_URI . '/js/main.js', array('jquery'), SOMA_VERSION, true);

    // Cal.com Popup JS (vanilla, pas besoin de jQuery)
    wp_enqueue_script('soma-calcom', SOMA_URI . '/js/calcom-popup.js', array(), SOMA_VERSION, true);

    // Pass PHP settings to JS
    wp_localize_script('soma-calcom', 'somaSettings', array(
        'calcomUrl'  => esc_url(get_theme_mod('soma_calcom_url', 'https://calcom.tempo-hub.fr/VOTRE-USERNAME')),
    ));
}
add_action('wp_enqueue_scripts', 'soma_enqueue_assets');

/* ============================================
   Customizer — Paramètres SOMA
   ============================================ */
function soma_customizer_register($wp_customize) {

    /* --- Paramètres Généraux --- */
    $wp_customize->add_section('soma_general', array(
        'title'    => __('SOMA — Informations', 'soma-avignon'),
        'priority' => 30,
    ));

    $fields_general = array(
        'soma_phone' => array(
            'label'   => 'Numéro de téléphone',
            'default' => '06 00 00 00 00',
            'type'    => 'text',
        ),
        'soma_email' => array(
            'label'   => 'Adresse email',
            'default' => 'contact@soma-avignon.fr',
            'type'    => 'email',
        ),
        'soma_address' => array(
            'label'   => 'Adresse postale',
            'default' => 'Avignon, France',
            'type'    => 'text',
        ),
        'soma_hours' => array(
            'label'   => 'Horaires d\'ouverture',
            'default' => 'Lun - Ven : 9h00 - 18h00',
            'type'    => 'text',
        ),
    );

    foreach ($fields_general as $id => $field) {
        $sanitize = ($field['type'] === 'email') ? 'sanitize_email' : 'sanitize_text_field';
        $wp_customize->add_setting($id, array('default' => $field['default'], 'sanitize_callback' => $sanitize));
        $wp_customize->add_control($id, array(
            'label'   => __($field['label'], 'soma-avignon'),
            'section' => 'soma_general',
            'type'    => $field['type'],
        ));
    }

    /* --- Réseaux Sociaux --- */
    $wp_customize->add_section('soma_social', array(
        'title'    => __('SOMA — Réseaux sociaux', 'soma-avignon'),
        'priority' => 31,
    ));

    $social_fields = array(
        'soma_instagram' => array('label' => 'URL Instagram', 'default' => 'https://www.instagram.com/soma.avignon/'),
        'soma_facebook'  => array('label' => 'URL Facebook',  'default' => ''),
    );

    foreach ($social_fields as $id => $field) {
        $wp_customize->add_setting($id, array('default' => $field['default'], 'sanitize_callback' => 'esc_url_raw'));
        $wp_customize->add_control($id, array(
            'label'   => __($field['label'], 'soma-avignon'),
            'section' => 'soma_social',
            'type'    => 'url',
        ));
    }

    /* --- Réservation & Paiement --- */
    $wp_customize->add_section('soma_booking', array(
        'title'    => __('SOMA — Réservation & Paiement', 'soma-avignon'),
        'priority' => 32,
    ));

    $wp_customize->add_setting('soma_calcom_url', array(
        'default'           => 'https://calcom.tempo-hub.fr/VOTRE-USERNAME',
        'sanitize_callback' => 'esc_url_raw',
    ));
    $wp_customize->add_control('soma_calcom_url', array(
        'label'       => __('URL Cal.com (sans paramètres)', 'soma-avignon'),
        'description' => 'Ex: https://calcom.tempo-hub.fr/votre-nom',
        'section'     => 'soma_booking',
        'type'        => 'url',
    ));

    $wp_customize->add_setting('soma_simplepay_form_id', array(
        'default'           => '',
        'sanitize_callback' => 'sanitize_text_field',
    ));
    $wp_customize->add_control('soma_simplepay_form_id', array(
        'label'       => __('ID du formulaire WP Simple Pay', 'soma-avignon'),
        'description' => 'L\'ID du formulaire créé dans WP Simple Pay (ex: 123). Laissez vide pour afficher un lien vers la page /paiement/',
        'section'     => 'soma_booking',
        'type'        => 'text',
    ));

    /* --- Footer --- */
    $wp_customize->add_section('soma_footer', array(
        'title'    => __('SOMA — Pied de page', 'soma-avignon'),
        'priority' => 33,
    ));

    $wp_customize->add_setting('soma_footer_text', array(
        'default'           => 'Un espace dédié au bien-être, au massage énergétique crânien et à la coiffure à Avignon.',
        'sanitize_callback' => 'sanitize_textarea_field',
    ));
    $wp_customize->add_control('soma_footer_text', array(
        'label'   => __('Texte descriptif du footer', 'soma-avignon'),
        'section' => 'soma_footer',
        'type'    => 'textarea',
    ));

    $wp_customize->add_setting('soma_google_maps_embed', array(
        'default'           => '<iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d91926.61358798794!2d4.750390497522362!3d43.94474389588553!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x84f64115da1336dd%3A0x5e79d95daee93b60!2sSoma%20Avignon!5e0!3m2!1sen!2sfr!4v1775919094711!5m2!1sen!2sfr" width="100%" height="450" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>',
        'sanitize_callback' => 'wp_kses_post',
    ));
    $wp_customize->add_control('soma_google_maps_embed', array(
        'label'       => __('Code iframe Google Maps', 'soma-avignon'),
        'description' => 'Collez le code embed iframe de Google Maps',
        'section'     => 'soma_footer',
        'type'        => 'textarea',
    ));
}
add_action('customize_register', 'soma_customizer_register');

/* ============================================
   Custom Post Type: Prestations
   ============================================ */
function soma_register_post_types() {
    register_post_type('prestation', array(
        'labels' => array(
            'name'               => 'Prestations',
            'singular_name'      => 'Prestation',
            'add_new'            => 'Ajouter une prestation',
            'add_new_item'       => 'Ajouter une nouvelle prestation',
            'edit_item'          => 'Modifier la prestation',
            'view_item'          => 'Voir la prestation',
            'all_items'          => 'Toutes les prestations',
            'search_items'       => 'Chercher une prestation',
            'not_found'          => 'Aucune prestation trouvée',
        ),
        'public'       => true,
        'has_archive'  => true,
        'rewrite'      => array('slug' => 'nos-prestations'),
        'supports'     => array('title', 'editor', 'thumbnail', 'excerpt', 'page-attributes'),
        'menu_icon'    => 'dashicons-heart',
        'show_in_rest' => true,
    ));

    register_post_type('temoignage', array(
        'labels' => array(
            'name'               => 'Témoignages',
            'singular_name'      => 'Témoignage',
            'add_new'            => 'Ajouter un témoignage',
            'add_new_item'       => 'Ajouter un nouveau témoignage',
            'edit_item'          => 'Modifier le témoignage',
            'all_items'          => 'Tous les témoignages',
        ),
        'public'       => true,
        'has_archive'  => false,
        'supports'     => array('title', 'editor'),
        'menu_icon'    => 'dashicons-format-quote',
        'show_in_rest' => true,
    ));
}
add_action('init', 'soma_register_post_types');

/* ============================================
   Meta Boxes — Prestations (prix, durée)
   ============================================ */
function soma_prestation_meta_boxes() {
    add_meta_box('soma_prestation_details', 'Détails de la prestation', 'soma_prestation_meta_html', 'prestation', 'side', 'high');
}
add_action('add_meta_boxes', 'soma_prestation_meta_boxes');

function soma_prestation_meta_html($post) {
    wp_nonce_field('soma_prestation_meta', 'soma_prestation_nonce');
    $price    = get_post_meta($post->ID, '_soma_price', true);
    $duration = get_post_meta($post->ID, '_soma_duration', true);
    ?>
    <p>
        <label for="soma_price"><strong>Prix (ex: 65€)</strong></label><br>
        <input type="text" id="soma_price" name="soma_price" value="<?php echo esc_attr($price); ?>" style="width:100%;" placeholder="65€">
    </p>
    <p>
        <label for="soma_duration"><strong>Durée (ex: 60 min)</strong></label><br>
        <input type="text" id="soma_duration" name="soma_duration" value="<?php echo esc_attr($duration); ?>" style="width:100%;" placeholder="60 min">
    </p>
    <?php
}

function soma_save_prestation_meta($post_id) {
    if (!isset($_POST['soma_prestation_nonce']) || !wp_verify_nonce($_POST['soma_prestation_nonce'], 'soma_prestation_meta')) return;
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (!current_user_can('edit_post', $post_id)) return;

    if (isset($_POST['soma_price'])) {
        update_post_meta($post_id, '_soma_price', sanitize_text_field($_POST['soma_price']));
    }
    if (isset($_POST['soma_duration'])) {
        update_post_meta($post_id, '_soma_duration', sanitize_text_field($_POST['soma_duration']));
    }
}
add_action('save_post_prestation', 'soma_save_prestation_meta');

/* ============================================
   Meta Boxes — Témoignages (étoiles)
   ============================================ */
function soma_temoignage_meta_boxes() {
    add_meta_box('soma_temoignage_details', 'Détails du témoignage', 'soma_temoignage_meta_html', 'temoignage', 'side', 'high');
}
add_action('add_meta_boxes', 'soma_temoignage_meta_boxes');

function soma_temoignage_meta_html($post) {
    wp_nonce_field('soma_temoignage_meta', 'soma_temoignage_nonce');
    $stars = get_post_meta($post->ID, '_soma_stars', true) ?: 5;
    ?>
    <p>
        <label for="soma_stars"><strong>Note (étoiles)</strong></label><br>
        <select id="soma_stars" name="soma_stars" style="width:100%;">
            <?php for ($i = 1; $i <= 5; $i++) : ?>
                <option value="<?php echo $i; ?>" <?php selected($stars, $i); ?>><?php echo str_repeat('★', $i); ?></option>
            <?php endfor; ?>
        </select>
    </p>
    <?php
}

function soma_save_temoignage_meta($post_id) {
    if (!isset($_POST['soma_temoignage_nonce']) || !wp_verify_nonce($_POST['soma_temoignage_nonce'], 'soma_temoignage_meta')) return;
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (!current_user_can('edit_post', $post_id)) return;

    if (isset($_POST['soma_stars'])) {
        update_post_meta($post_id, '_soma_stars', intval($_POST['soma_stars']));
    }
}
add_action('save_post_temoignage', 'soma_save_temoignage_meta');

/* ============================================
   Shortcodes
   ============================================ */

// [soma_prestations] — Affiche les cartes de prestations
function soma_prestations_shortcode($atts) {
    $atts = shortcode_atts(array('limit' => 6), $atts);

    $query = new WP_Query(array(
        'post_type'      => 'prestation',
        'posts_per_page' => intval($atts['limit']),
        'orderby'        => 'menu_order',
        'order'          => 'ASC',
    ));

    if (!$query->have_posts()) return '<p style="text-align:center;">Aucune prestation disponible pour le moment.</p>';

    $output = '<div class="soma-services-grid soma-stagger">';

    while ($query->have_posts()) {
        $query->the_post();
        $price    = get_post_meta(get_the_ID(), '_soma_price', true);
        $duration = get_post_meta(get_the_ID(), '_soma_duration', true);
        $thumb    = get_the_post_thumbnail_url(get_the_ID(), 'service-card');

        $output .= '<div class="soma-service-card">';
        if ($thumb) {
            $output .= '<div class="soma-card-img"><img src="' . esc_url($thumb) . '" alt="' . esc_attr(get_the_title()) . '" loading="lazy"></div>';
        } else {
            $output .= '<div class="soma-card-placeholder"><svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path></svg></div>';
        }
        $output .= '<div class="soma-service-card-content">';
        $output .= '<h3>' . esc_html(get_the_title()) . '</h3>';
        if ($duration) {
            $output .= '<span class="soma-service-duration">' . esc_html($duration) . '</span>';
        }
        $output .= '<p>' . esc_html(get_the_excerpt()) . '</p>';
        if ($price) {
            $output .= '<p class="soma-service-price">' . esc_html($price) . '</p>';
        }
        $output .= '<a href="#" class="wp-block-button__link soma-rdv-btn">Prendre rendez-vous</a>';
        $output .= '</div></div>';
    }

    $output .= '</div>';
    wp_reset_postdata();
    return $output;
}
add_shortcode('soma_prestations', 'soma_prestations_shortcode');

// [soma_temoignages] — Affiche les témoignages
function soma_temoignages_shortcode($atts) {
    $atts = shortcode_atts(array('limit' => 3), $atts);

    $query = new WP_Query(array(
        'post_type'      => 'temoignage',
        'posts_per_page' => intval($atts['limit']),
        'orderby'        => 'date',
        'order'          => 'DESC',
    ));

    if (!$query->have_posts()) return '';

    $output = '<div class="soma-testimonials-grid soma-stagger">';

    while ($query->have_posts()) {
        $query->the_post();
        $stars = get_post_meta(get_the_ID(), '_soma_stars', true) ?: 5;

        $output .= '<div class="soma-testimonial">';
        $output .= '<div class="soma-stars">' . str_repeat('★ ', intval($stars)) . '</div>';
        $output .= '<p class="soma-testimonial-text">' . esc_html(get_the_content()) . '</p>';
        $output .= '<p class="soma-testimonial-author">— ' . esc_html(get_the_title()) . '</p>';
        $output .= '</div>';
    }

    $output .= '</div>';
    wp_reset_postdata();
    return $output;
}
add_shortcode('soma_temoignages', 'soma_temoignages_shortcode');

// [soma_stats] — Compteurs animés
function soma_stats_shortcode($atts) {
    $atts = shortcode_atts(array(
        'items' => '500+|Clientes accompagnées,5|Années d\'expérience,15+|Soins proposés,100%|Bienveillance',
    ), $atts);

    $items = explode(',', $atts['items']);
    $output = '<div class="soma-stats soma-fade-in">';

    foreach ($items as $item) {
        $parts = explode('|', trim($item));
        if (count($parts) !== 2) continue;

        $value = trim($parts[0]);
        $label = trim($parts[1]);

        // Extract number, prefix, suffix
        preg_match('/^([^0-9]*)(\d+)(.*)$/', $value, $matches);
        $prefix = isset($matches[1]) ? $matches[1] : '';
        $number = isset($matches[2]) ? $matches[2] : $value;
        $suffix = isset($matches[3]) ? $matches[3] : '';

        $output .= '<div class="soma-stat-item">';
        $output .= '<div class="soma-stat-number" data-count="' . esc_attr($number) . '" data-prefix="' . esc_attr($prefix) . '" data-suffix="' . esc_attr($suffix) . '">0</div>';
        $output .= '<div class="soma-stat-label">' . esc_html($label) . '</div>';
        $output .= '</div>';
    }

    $output .= '</div>';
    return $output;
}
add_shortcode('soma_stats', 'soma_stats_shortcode');

// [soma_contact_info] — Affiche les infos de contact avec icônes
function soma_contact_info_shortcode() {
    $phone   = get_theme_mod('soma_phone', '06 00 00 00 00');
    $email   = get_theme_mod('soma_email', 'contact@soma-avignon.fr');
    $address = get_theme_mod('soma_address', 'Avignon, France');
    $hours   = get_theme_mod('soma_hours', 'Lun - Ven : 9h00 - 18h00');

    $icon_map = '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>';
    $icon_phone = '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path></svg>';
    $icon_email = '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path><polyline points="22,6 12,13 2,6"></polyline></svg>';
    $icon_clock = '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>';

    $output = '<div class="soma-contact-grid soma-stagger">';

    $output .= '<div class="soma-contact-item">';
    $output .= '<div class="soma-contact-icon">' . $icon_map . '</div>';
    $output .= '<h3>Adresse</h3><p>' . esc_html($address) . '</p></div>';

    $output .= '<div class="soma-contact-item">';
    $output .= '<div class="soma-contact-icon">' . $icon_phone . '</div>';
    $output .= '<h3>Téléphone</h3><p><a href="tel:' . esc_attr(str_replace(' ', '', $phone)) . '">' . esc_html($phone) . '</a></p></div>';

    $output .= '<div class="soma-contact-item">';
    $output .= '<div class="soma-contact-icon">' . $icon_email . '</div>';
    $output .= '<h3>Email</h3><p><a href="mailto:' . esc_attr($email) . '">' . esc_html($email) . '</a></p></div>';

    $output .= '<div class="soma-contact-item">';
    $output .= '<div class="soma-contact-icon">' . $icon_clock . '</div>';
    $output .= '<h3>Horaires</h3><p>' . esc_html($hours) . '</p></div>';

    $output .= '</div>';

    // Google Maps embed
    $maps_embed = get_theme_mod('soma_google_maps_embed', '');
    if ($maps_embed) {
        $output .= '<div style="margin-top:3rem;border-radius:12px;overflow:hidden;box-shadow:0 4px 16px rgba(61,48,41,0.08);">' . $maps_embed . '</div>';
    }

    return $output;
}
add_shortcode('soma_contact_info', 'soma_contact_info_shortcode');

// [soma_social_links] — Liens sociaux
function soma_social_links_shortcode() {
    $instagram = get_theme_mod('soma_instagram', '');
    $facebook  = get_theme_mod('soma_facebook', '');

    $output = '<ul class="soma-social-links">';
    if ($instagram) {
        $output .= '<li><a href="' . esc_url($instagram) . '" target="_blank" rel="noopener noreferrer" aria-label="Instagram"><svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zM12 0C8.741 0 8.333.014 7.053.072 2.695.272.273 2.69.073 7.052.014 8.333 0 8.741 0 12c0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98C8.333 23.986 8.741 24 12 24c3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98C15.668.014 15.259 0 12 0zm0 5.838a6.162 6.162 0 100 12.324 6.162 6.162 0 000-12.324zM12 16a4 4 0 110-8 4 4 0 010 8zm6.406-11.845a1.44 1.44 0 100 2.881 1.44 1.44 0 000-2.881z"/></svg></a></li>';
    }
    if ($facebook) {
        $output .= '<li><a href="' . esc_url($facebook) . '" target="_blank" rel="noopener noreferrer" aria-label="Facebook"><svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg></a></li>';
    }
    $output .= '</ul>';
    return $output;
}
add_shortcode('soma_social_links', 'soma_social_links_shortcode');

// [soma_payment] — Affiche le formulaire WP Simple Pay ou un lien vers /paiement/
function soma_payment_shortcode($atts) {
    $atts = shortcode_atts(array('text' => 'Payer en ligne'), $atts);
    $form_id = get_theme_mod('soma_simplepay_form_id', '');

    if ($form_id && shortcode_exists('simpay')) {
        return do_shortcode('[simpay id="' . esc_attr($form_id) . '"]');
    }

    // Fallback : lien vers la page /paiement/
    return '<a href="/paiement/" class="wp-block-button__link soma-stripe-btn">' . esc_html($atts['text']) . '</a>';
}
add_shortcode('soma_payment', 'soma_payment_shortcode');

// Garder la rétro-compatibilité
add_shortcode('soma_stripe_button', 'soma_payment_shortcode');

// [soma_rdv_button text="Prendre rendez-vous"] — Bouton Cal.com
function soma_rdv_button_shortcode($atts) {
    $atts = shortcode_atts(array('text' => 'Prendre rendez-vous'), $atts);
    return '<a href="#" class="wp-block-button__link soma-rdv-btn">' . esc_html($atts['text']) . '</a>';
}
add_shortcode('soma_rdv_button', 'soma_rdv_button_shortcode');

// [soma_marquee] — Bandeau défilant
function soma_marquee_shortcode($atts) {
    $atts = shortcode_atts(array(
        'items' => 'Massage énergétique crânien,Bien-être holistique,Coiffure,Soins personnalisés,Reconnexion à soi,Avignon',
    ), $atts);

    $items = explode(',', $atts['items']);
    $output = '<div class="soma-marquee"><div class="soma-marquee-inner">';

    foreach ($items as $item) {
        $output .= '<span class="soma-marquee-item">' . esc_html(trim($item)) . '</span>';
    }

    $output .= '</div></div>';
    return $output;
}
add_shortcode('soma_marquee', 'soma_marquee_shortcode');

// [soma_floating_cta text="Prendre rendez-vous"] — Bouton flottant
function soma_floating_cta_shortcode($atts) {
    $atts = shortcode_atts(array('text' => 'Prendre rendez-vous'), $atts);
    $icon = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>';
    return '<a href="#" class="soma-floating-cta soma-rdv-btn" style="background-color:var(--soma-terracotta)!important;border:none!important;">' . $icon . esc_html($atts['text']) . '</a>';
}
add_shortcode('soma_floating_cta', 'soma_floating_cta_shortcode');

/* ============================================
   Astra hooks — Footer social icons + crédits
   ============================================ */
function soma_astra_footer_social() {
    $instagram = get_theme_mod('soma_instagram', '');
    if ($instagram) {
        echo '<div style="text-align:center;padding:1rem 0;">';
        echo do_shortcode('[soma_social_links]');
        echo '</div>';
    }
}
add_action('astra_footer_content_top', 'soma_astra_footer_social');

// Replace Astra default copyright with TempoHub credit
add_filter('astra_footer_copyright_default', function() {
    return 'Propulsé par <a href="https://tempo-hub.fr" target="_blank" rel="noopener noreferrer">TempoHub</a>';
});
add_filter('astra_get_option_footer-copyright-editor', function() {
    return 'Propulsé par <a href="https://tempo-hub.fr" target="_blank" rel="noopener noreferrer">TempoHub</a>';
});

/* ============================================
   Astra hooks — Instagram icon in header
   ============================================ */
function soma_header_instagram() {
    $instagram = get_theme_mod('soma_instagram', 'https://www.instagram.com/soma.avignon/');
    if (!$instagram) return;

    echo '<div class="soma-header-social">';
    echo '<a href="' . esc_url($instagram) . '" target="_blank" rel="noopener noreferrer" aria-label="Instagram">';
    echo '<svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zM12 0C8.741 0 8.333.014 7.053.072 2.695.272.273 2.69.073 7.052.014 8.333 0 8.741 0 12c0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98C8.333 23.986 8.741 24 12 24c3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98C15.668.014 15.259 0 12 0zm0 5.838a6.162 6.162 0 100 12.324 6.162 6.162 0 000-12.324zM12 16a4 4 0 110-8 4 4 0 010 8zm6.406-11.845a1.44 1.44 0 100 2.881 1.44 1.44 0 000-2.881z"/></svg>';
    echo '</a>';
    echo '</div>';
}
add_action('astra_main_header_bar_top', 'soma_header_instagram');

/* ============================================
   Astra hooks — Floating CTA on all pages
   ============================================ */
function soma_add_floating_cta() {
    echo do_shortcode('[soma_floating_cta]');
}
add_action('astra_body_bottom', 'soma_add_floating_cta');

/* ============================================
   Masquer le titre de page sur l'accueil
   ============================================ */
function soma_remove_front_page_title() {
    if (is_front_page()) {
        add_filter('astra_the_title_enabled', '__return_false');
    }
}
add_action('wp', 'soma_remove_front_page_title');

/* ============================================
   Excerpt length
   ============================================ */
function soma_excerpt_length($length) {
    return 25;
}
add_filter('excerpt_length', 'soma_excerpt_length');

/* ============================================
   Add image sizes
   ============================================ */
function soma_image_sizes() {
    add_image_size('service-card', 700, 460, true);
    add_image_size('gallery-thumb', 400, 400, true);
}
add_action('after_setup_theme', 'soma_image_sizes');
