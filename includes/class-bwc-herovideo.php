<?php
if (!defined('ABSPATH')) exit;

class BWC_Herovideo_Plugin {

  private static $videos_ids = [];

  public function __construct() {
    // WPBakery mapping (UI)
    add_action('vc_before_init', [$this, 'create_shortcode']);

    // Shortcodes (nouveau + alias rétro-compat)
    add_shortcode('hero_video', [$this, 'render_shortcode']);
    add_shortcode('bwc_video_desktop', [$this, 'render_shortcode']);
    add_shortcode('bosca_video_desktop', [$this, 'render_shortcode']);

    // Assets
    add_action('wp_enqueue_scripts', [$this, 'register_assets']);
  }

  public function register_assets() {
    wp_register_style('bwc-herovideo', BWC_HEROVIDEO_URL . 'public/css/style.css', [], BWC_HEROVIDEO_VERSION);
    wp_register_script('bwc-herovideo', BWC_HEROVIDEO_URL . 'public/js/bwc-herovideo.js', ['jquery'], BWC_HEROVIDEO_VERSION, true);
  }

  public function create_shortcode() {
    if (!defined('WPB_VC_VERSION')) return;

    vc_map([
      'name'        => __('Hero Video', 'wpb-bwc-herovideo'),
      'base'        => 'hero_video',
      'description' => __('Vidéo hero + bandeau événements, avec fallback image', 'wpb-bwc-herovideo'),
      'category'    => __('BWC Modules', 'wpb-bwc-herovideo'),
      'icon'        => 'dashicons-video-alt3',
      'params'      => [
        [
          'type'        => 'textfield',
          'heading'     => __('ID video', 'wpb-bwc-herovideo'),
          'param_name'  => 'id_video',
          'description' => __('ID unique pour les contrôles audio', 'wpb-bwc-herovideo'),
        ],
        [
          'type'        => 'textfield',
          'heading'     => __('URL Desktop', 'wpb-bwc-herovideo'),
          'param_name'  => 'url_desktop',
        ],
        [
          'type'        => 'textfield',
          'heading'     => __('URL Mobile', 'wpb-bwc-herovideo'),
          'param_name'  => 'url_mobile',
        ],
        [
          'type'        => 'attach_image',
          'heading'     => __('Image Desktop (fallback/poster)', 'wpb-bwc-herovideo'),
          'param_name'  => 'poster_desktop',
          'description' => __('Affichée si la vidéo échoue + utilisée comme poster', 'wpb-bwc-herovideo'),
        ],
        [
          'type'        => 'attach_image',
          'heading'     => __('Image Mobile (fallback/poster)', 'wpb-bwc-herovideo'),
          'param_name'  => 'poster_mobile',
          'description' => __('Affichée si la vidéo échoue + utilisée comme poster', 'wpb-bwc-herovideo'),
        ],
        [
          'type'        => 'textfield',
          'heading'     => __('Titre', 'wpb-bwc-herovideo'),
          'param_name'  => 'titre',
        ],
        [
          'type'        => 'textfield',
          'heading'     => __('Sous-titre', 'wpb-bwc-herovideo'),
          'param_name'  => 'stitre',
        ],
        [
          'type'        => 'textfield',
          'heading'     => __('Saison', 'wpb-bwc-herovideo'),
          'param_name'  => 'saison',
        ],
        [
          'type'        => 'textfield',
          'heading'     => __('Element ID', 'wpb-bwc-herovideo'),
          'param_name'  => 'element_id',
          'group'       => __('Extra', 'wpb-bwc-herovideo'),
        ],
        [
          'type'        => 'textfield',
          'heading'     => __('Extra class name', 'wpb-bwc-herovideo'),
          'param_name'  => 'extra_class',
          'group'       => __('Extra', 'wpb-bwc-herovideo'),
        ],
      ],
    ]);
  }

  private function sanitize_classes($classes) {
    $classes = trim((string)$classes);
    if ($classes === '') return '';
    $parts = preg_split('/\s+/', $classes);
    $safe  = array_map('sanitize_html_class', $parts);
    return implode(' ', array_filter($safe));
  }

  public function render_shortcode($atts, $content = null, $tag = '') {
    $atts = shortcode_atts([
      'url_desktop'    => '',
      'url_mobile'     => '',
      'poster_desktop' => '',
      'poster_mobile'  => '',
      'extra_class'    => '',
      'element_id'     => '',
      'id_video'       => '',
      'titre'          => '',
      'stitre'         => '',
      'saison'         => '',
    ], $atts, 'hero_video');

    // Données
    $id_video          = sanitize_text_field($atts['id_video']);
    $url_desktop       = esc_url($atts['url_desktop']);
    $url_mobile        = esc_url($atts['url_mobile'] ?: $atts['url_desktop']);
    $poster_desktop_id = intval($atts['poster_desktop']);
    $poster_mobile_id  = intval($atts['poster_mobile']);
    $poster_desktop    = $poster_desktop_id ? wp_get_attachment_image_url($poster_desktop_id, 'full') : '';
    $poster_mobile     = $poster_mobile_id  ? wp_get_attachment_image_url($poster_mobile_id,  'full') : '';
    $titre             = wp_kses_post($atts['titre']);
    $stitre            = wp_kses_post($atts['stitre']);
    $saison            = wp_kses_post($atts['saison']);
    $extra_class       = $this->sanitize_classes($atts['extra_class']);
    $element_id        = sanitize_title($atts['element_id']);

    if ($id_video !== '') self::$videos_ids[] = $id_video;

    // Événements
    $toDate = new DateTime('now', wp_timezone());
    $args   = [
      'post_type'      => 'class',
      'posts_per_page' => -1,
      'order'          => 'ASC',
      'post_status'    => 'publish',
    ];
    $query      = new WP_Query($args);
    $allEvents  = [];

    if ($query->have_posts()) {
      while ($query->have_posts()) {
        $query->the_post();
        $eventID = get_the_ID();
        $name    = get_the_title();
        $meta    = get_post_meta($eventID);

        $ts = isset($meta['_wcs_timestamp'][0]) ? intval($meta['_wcs_timestamp'][0]) : 0;
        if ($ts > 0 && $ts >= $toDate->getTimestamp()) {
          $dateEvent = sprintf(
            '<span class="video-event-day">%s</span> <span class="video-event-dates-contents">%s</span>',
            esc_html( wp_date('l', $ts) ),
            esc_html( wp_date('d F', $ts) )
          );
          $timeEvent = wp_date('H:i', $ts);
          $link      = !empty($meta['_wcs_reservation_link'][0]) ? $meta['_wcs_reservation_link'][0] : ($meta['_wcs_productions_link'][0] ?? '');
          $city      = $meta['_wcs_city'][0]   ?? '';
          $coll      = $meta['_wcs_collab'][0] ?? '';

          $allEvents[] = [
            'name'   => $name,
            'date'   => $dateEvent,
            'link'   => esc_url($link),
            'city'   => esc_html($city),
            'collab' => esc_html($coll),
            'time'   => esc_html($timeEvent),
          ];
        }
      }
      wp_reset_postdata();
    }

    // Assets (seulement si shortcode présent)
    wp_enqueue_style('bwc-herovideo');
    wp_enqueue_script('bwc-herovideo');
    wp_localize_script('bwc-herovideo', 'WPbwc', [
      'videos_ids'        => array_values(array_unique(self::$videos_ids)),
      'fallbackTimeoutMs' => 4000,
    ]);

    $completeCalendar = (get_locale() === 'en_US') ? 'View Full Calendar' : 'Calendrier complet';

    // HTML — DESKTOP
    $output  = '';
    $output .= '<div class="video-container-desktop ' . esc_attr($extra_class) . '" ' . ($element_id ? 'id="'.esc_attr($element_id).'"' : '') . '>';
      $poster_attr = $poster_desktop ? ' poster="'.esc_url($poster_desktop).'"' : '';
      $output .= '<video id="desktop-' . esc_attr($id_video) . '" playsinline autoplay muted loop src="' . $url_desktop . '"' . $poster_attr . '></video>';
      if ($poster_desktop) {
        $output .= '<img id="desktop-fallback-' . esc_attr($id_video) . '" class="video-fallback video-fallback-desktop" src="' . esc_url($poster_desktop) . '" alt="' . esc_attr($titre ?: 'Hero fallback') . '">';
      }
    $output .= '</div>';

    // Overlay texte
    $output .= '<div class="header-container-desktop">';
      $output .= '<div class="header-text-desktop">';
        $output .= '<span class="header-text-title">' . $titre . '</span>';
        $output .= '<span class="header-text-stitle">' . $stitre . '</span>';
        $output .= '<span class="header-text-saison">SAISON </span><span class="header-text-saison-number">' . $saison . '</span>';
      $output .= '</div>';
    $output .= '</div>';

    // Bandeau événements
    $output .= '<div class="header-container-events">';
      $output .= '<div class="header-container-events__row">';
        foreach (array_slice($allEvents, 0, 7) as $value) {
          $output .= '<div class="event-container">';
            $output .= '<a href="' . $value['link'] . '">';
              $output .= '<span class="video-event-name">' . esc_html($value['name']) . '</span>';
              $output .= ($value['collab'] ? '<span class="video-event-collab">'. $value['collab'] .'</span>' : '<span class="video-event-collab-empty">&nbsp;</span>');
              $output .= '<span class="video-event-city">' . $value['city'] . '</span>';
              $output .= '<div class="video-event-dateandtime">';
                $output .= '<span class="video-event-date">' . $value['date'] . '</span>';
                if ($value['time'] !== '00:00') {
                  $output .= '<span class="video-event-time">' . $value['time'] . '</span>';
                }
              $output .= '</div>';
            $output .= '</a>';
          $output .= '</div>';
        }
        $output .= '<div class="event-container-complete">';
          $output .= '<a href="https://alexandracardinale.com/calendrier/">';
            $output .= '<span class="video-event-name">' . esc_html($completeCalendar) . '</span>';
            $output .= '<i class="fa fa-calendar"></i>';
          $output .= '</a>';
        $output .= '</div>';
      $output .= '</div>';
      $output .= '<div class="event-container-complete-line">';
        $output .= '<a href="https://alexandracardinale.com/calendrier/">';
          $output .= '<i class="fa fa-calendar"></i>';
          $output .= '<span class="video-event-name-line">' . esc_html($completeCalendar) . '</span>';
        $output .= '</a>';
      $output .= '</div>';
    $output .= '</div>';

    // HTML — MOBILE
    $output .= '<div class="video-container-mobile ' . esc_attr($extra_class) . '" ' . ($element_id ? 'id="'.esc_attr($element_id).'"' : '') . '>';
      $poster_m = $poster_mobile ?: $poster_desktop;
      $poster_m_attr = $poster_m ? ' poster="'.esc_url($poster_m).'"' : '';
      $output .= '<video id="mobile-' . esc_attr($id_video) . '" playsinline autoplay muted loop src="' . $url_mobile . '"' . $poster_m_attr . '></video>';
      if ($poster_m) {
        $output .= '<img id="mobile-fallback-' . esc_attr($id_video) . '" class="video-fallback video-fallback-mobile" src="' . esc_url($poster_m) . '" alt="' . esc_attr($titre ?: 'Hero fallback') . '">';
      }
      $output .= '<div class="header-container-mobile">';
        $output .= '<div class="header-blank-mobile"></div>';
        $output .= '<div class="header-text-mobile">' . do_shortcode($content) . '</div>';
      $output .= '</div>';
    $output .= '</div>';

    return $output;
  }
}

// Stub WPBakery aligné sur base = hero_video
if (class_exists('WPBakeryShortCode') && !class_exists('WPBakeryShortCode_Hero_Video')) {
  class WPBakeryShortCode_Hero_Video extends WPBakeryShortCode {}
}