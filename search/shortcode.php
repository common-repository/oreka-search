<?php
class OrekaSearchShortcode
{
  function __construct()
  {
    $orekasearch_main_options = get_option( 'orekasearch_main' );
    $orekasearch_form_replacement = $orekasearch_main_options['form_replacement'];
    if ($orekasearch_form_replacement === 'form_replacement')
    {
      add_shortcode( 'oreka_search_form', array(&$this, 'orekasearch_shortcode_error') );
    } else {
      add_action('wp_enqueue_scripts', array(&$this, 'orekasearch_shortcode_register'));
      add_shortcode( 'oreka_search_form', array(&$this, 'orekasearch_shortcode_render') );
    }
  }

  function orekasearch_shortcode_error () {
    return 'جهت استفاده از این shortcode ابتدا باید جایگزینی فرم جستجو را غیر فعال کنید.';
  }

  function orekasearch_shortcode_register () {
    wp_dequeue_script('VueJS');
    wp_enqueue_script( 'VueJS', plugin_dir_url( __FILE__ ).'../theme/js/vue.min.js', false, '1.6.2' );
    wp_enqueue_script( 'OrekaSearchShortCode', plugin_dir_url( __FILE__ ).'../theme/js/form.js', ['VueJS', 'jquery'],  '1.6.2' );
    wp_enqueue_style( 'OrekaSearchShortCode', plugin_dir_url( __FILE__ ).'../theme/css/forms.css', false, '1.6.2' );
    // form data
    $pre_b = esc_url( home_url( '/'  ) );
    $pre_q = esc_attr( get_search_query() );
    // check if ajax is enable in plugin options
    $orekasearch_main_options = get_option( 'orekasearch_main' );
    $orekasearch_ajax = $orekasearch_main_options['ajax'];
    if ($orekasearch_ajax === 'ajax') { $ajax = 'true'; } else { $ajax = 'false'; }
    $params = array( 'ajax' => $ajax, 'ajaxurl' => admin_url('admin-ajax.php'), 'ajax_nonce' => wp_create_nonce('oreka_search_autocomplete_ajax'), 'q' => $pre_q );
    wp_localize_script( 'OrekaSearchShortCode', 'ajax_object', $params );
  }

  function orekasearch_shortcode_render( $atts, $content = '' ) {
    global $OrekaSearchForm;
    echo $OrekaSearchForm->oreka_searrch_form();
  }
}
?>