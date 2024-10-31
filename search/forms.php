<?php
class OrekaSearchForm
{
  function __construct()
  {
    // check if form replacement is active in plugin options
    $orekasearch_main_options = get_option( 'orekasearch_main' );
    $orekasearch_form_replacement = $orekasearch_main_options['form_replacement'];
    if ($orekasearch_form_replacement === 'form_replacement')
    {
      add_filter( 'get_search_form', array(&$this, 'oreka_searrch_form') );
      add_filter( 'get_product_search_form', array(&$this, 'oreka_searrch_form') );
    }
    add_action( 'wp_ajax_oreka_search_autocomplete_ajax', array(&$this, 'oreka_search_autocomplete_ajax') );
    add_action( 'wp_ajax_nopriv_oreka_search_autocomplete_ajax', array(&$this, 'oreka_search_autocomplete_ajax') );
  }

  function oreka_searrch_form() {
    // add vue & css
    wp_enqueue_script( 'VueJS', plugin_dir_url( __FILE__ ).'../theme/js/vue.min.js', false, '1.6.2' );
    wp_enqueue_script( 'OrekaSearchForm', plugin_dir_url( __FILE__ ).'../theme/js/form.js', ['VueJS', 'jquery'],  '1.6.2' );
    wp_enqueue_style( 'OrekaSearchForm', plugin_dir_url( __FILE__ ).'../theme/css/forms.css', false, '1.6.2' );

    // form data
    $pre_b = esc_url( home_url( '/'  ) );
    $pre_q = esc_attr( get_search_query() );

    // check if ajax is enable in plugin options
    $orekasearch_main_options = get_option( 'orekasearch_main' );
    $orekasearch_ajax = $orekasearch_main_options['ajax'];
    if ($orekasearch_ajax === 'ajax') { $ajax = 'true'; } else { $ajax = 'false'; }

    // check if form replacement is active in plugin options
    $orekasearch_woocommerce = $orekasearch_main_options['woocommerce'];
    if ($orekasearch_woocommerce === 'woocommerce' )
    { $orekasearch_woocommerce_mode = "<input type='hidden' name='post_type' value='product'>";  $titlesuggestions = 'پیشنهاد برای جستجوی محصول'; }
    else { $orekasearch_woocommerce_mode = ""; $titlesuggestions = 'نتایج یافت شده'; }

    // load params
    $params = array( 'ajax' => $ajax, 'ajaxurl' => admin_url('admin-ajax.php'), 'ajax_nonce' => wp_create_nonce('oreka_search_autocomplete_ajax'), 'q' => $pre_q );
    wp_localize_script( 'OrekaSearchForm', 'ajax_object', $params );

    $img = plugins_url( '../theme/logo_forms.png', __FILE__ );

    return "
    <form role='search' method='get' class='oreka_search_form' action='$pre_b' id='OrekaSearchForm' ref='OrekaSearchForm' v-bind:class='{ loaded: true }' @mouseover='mouseovered' @mouseleave='mouseleaved'>
      <input type='search' id='oreka-search-field' class='search-field' placeholder='جستجو کنید' name='s' v-model='search' v-on:keyup='searchTimeOut()' autocomplete='off'>
      <button type='submit' id='oreka-search-submit' v-cloak><img src='$img'> جستجو</button>
      <input type='hidden' name='oreka' value='oreka'>
      $orekasearch_woocommerce_mode
      <div id='oreka-search-icon'></div>
      <div class='oreka_overlay_container' v-cloak>
        <transition name='fade'>
          <div class='oreka_overlay' v-if='showoverlay'>
          <div class='loading' v-if='loading'>در حال جستجو</div>
          <div class='noresult' v-if='noresult'>نتیجه‌ای یافت نشد !</div>
            <span v-if='didyoumean'><b>آیا منظور شما این بود؟</b></span>
            <span v-for='item in didyoumean'>
              <a v-on:click='clicked(item.expression)'>{{item.expression}}</a>
            </span>
            <hr v-if='didyoumean'>
            <span v-if='querysuggestions'><b>پیشنهاد برای تکمیل جستجو</b></span>
            <span v-for='item in querysuggestions'>
              <a v-on:click='clicked(item.expression)'>{{item.expression}}</a>
            </span>
            <hr v-if='querysuggestions'>
            <span v-if='titlesuggestions'><b>$titlesuggestions</b></span>
            <span v-for='item in titlesuggestions'>
              <a v-on:click='clicked(item.title)'>{{item.title}}</a>
            </span>
            <div class='copyright' v-if='!loading & !noresult'><a href='https://www.dolphinai.ir/' target='_blank'>قدرت گرفته از سرویس جستجوی هوشمند اورکا</a></div>
          </div>
        </transition>
      </div>
    </form>
    <style>[v-cloak] {display: none;}</style>
    ";
  }

  function oreka_search_autocomplete_ajax(){
    check_ajax_referer( 'oreka_search_autocomplete_ajax', 'security' );
    $search = filter_input(INPUT_POST, 'search');

    // get connection variables
    $orekasearch_server_options = get_option( 'orekasearch_server' );
    $orekasearch_customer_id = $orekasearch_server_options['customer_id'];
    $orekasearch_project_id = $orekasearch_server_options['project_id'];
    $orekasearch_catalog_id = $orekasearch_server_options['catalog_id'];
    $orekasearch_token = $orekasearch_server_options['token'];

    // generate url
    $url = "https://api.dolphinai.ir/customers/".$orekasearch_customer_id."/projects/".$orekasearch_project_id."/searchapi/api/v1/search/catalogs/".$orekasearch_catalog_id."/suggestion/didyoumean";
    $url = $url.(parse_url($url, PHP_URL_QUERY) ? '&' : '?').'QueryString=' . urlencode($search);

    // get data from
    $headers = array( 'headers' => array( 'Accept' => 'text/plain', 'Content-Type' => 'application/json', 'Authorization' => 'Bearer ' . $orekasearch_token ) );
    $output =wp_remote_get( $url, $headers );
    $outputCode = wp_remote_retrieve_response_code( $output );
    $output = wp_remote_retrieve_body($output);

    // check for auth token validation
    if ($outputCode == 400) { if (strpos($output,'Invalid JWT Token')) { echo wp_send_json_error(array('error' => 'invalid token')); return false; } }

    // check if status code is 200
    if ($outputCode <> 200) { echo wp_send_json_error(array('error' => 'not 200 but ' . $outputCode, 'url' => $url, 'output' => $output)); return false; }

    // check if output is valid json
    $output = json_decode($output);
    if (json_last_error() !== JSON_ERROR_NONE) { echo wp_send_json_error(array('error' => 'not json', 'url' => $url)); return false; }

    // send output
    echo wp_send_json_success($output);
    wp_die();
  }

}
?>