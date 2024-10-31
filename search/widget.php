<?php

class OrekaSearchWidget extends WP_Widget {

	public function __construct() {
    // widget options
		$widget_ops = array(
			'classname' => 'orekasearch_widget',
			'description' => 'اورکا : سرویس هوشمند جستجو',
		);

    // add vuejs
    add_action( 'wp_enqueue_scripts', function(){ wp_enqueue_script( 'VueJS', plugin_dir_url( __FILE__ ).'../theme/js/vue.min.js', false, '1.6.2' ); });

    // init widget
		parent::__construct( 'orekasearch_widget', 'اورکا : فیلتر‌‌ها', $widget_ops );
	}

	public function widget( $args, $instance ) {
    global $OrekaSearch;
    global $wp;

    if (isset($_SERVER['HTTP_USER_AGENT']) && preg_match('/bot|crawl|slurp|spider|mediapartners/i', $_SERVER['HTTP_USER_AGENT'])) { return ''; }

    // check if results are ready to show
    if (is_object($OrekaSearch->output))
    { $this->_orekasearch_search_results($args, $instance); }
    else {
        $this->_orekasearch_no_search($args, $instance);
    }
	}

  public function _orekasearch_no_search ($args, $instance)
  {
    global $OrekaSearch;
    global $wp;
    global $woocommerce;

    // get active filters
    $must_be_facets = ['pa_manufacturer','product_cat','price','stock'];
    $active_url_facets_to_parse = [];
    $active_url_relfacets_to_parse = [];
    $temp = $woocommerce->query->get_main_tax_query();
    foreach ($temp as $key => $value) {
      if (isset($value['field']) && $value['field'] == 'slug') {
        $terms = $value['terms'];
        foreach ($terms as $term_key => $term_value) {
          $retm_trans = get_term_by('slug',$term_value,$value['taxonomy']);
          $terms[$term_key] = $retm_trans->name;
        }
        if (in_array($value['taxonomy'],$must_be_facets))
        {
          $active_url_facets_to_parse[] = (object) array('name' => $value['taxonomy'], 'fixval' => implode('|',$terms));
        } else{
          $active_url_relfacets_to_parse[] = (object) array('name' =>wc_attribute_label($value['taxonomy']), 'fixval' => implode('|',$terms));
        }
      }
    }

    // get filters from oreka
    if (!is_object($OrekaSearch->output) OR $OrekaSearch->output->info->totaldocumentscount == 0)
    {
      // get connection variables
      $orekasearch_server_options = get_option( 'orekasearch_server' );
      $orekasearch_main_options = get_option( 'orekasearch_main' );
      $orekasearch_customer_id = $orekasearch_server_options['customer_id'];
      $orekasearch_project_id = $orekasearch_server_options['project_id'];
      $orekasearch_catalog_id = $orekasearch_server_options['catalog_id'];
      $orekasearch_token = $orekasearch_server_options['token'];
      $orekasearch_woocommerce = $orekasearch_main_options['woocommerce'];

      // generate url
      $url = "https://api.dolphinai.ir/customers/".$orekasearch_customer_id."/projects/".$orekasearch_project_id."/searchapi/api/v1/search/catalogs/".$orekasearch_catalog_id."/documents/aggregations";
      if (sizeof($active_url_facets_to_parse) > 0) { $url = $url.(parse_url($url, PHP_URL_QUERY) ? '&' : '?').'facets=' . urlencode(json_encode($active_url_facets_to_parse)); }
      if (sizeof($active_url_relfacets_to_parse) > 0) { $url = $url.(parse_url($url, PHP_URL_QUERY) ? '&' : '?').'relevantfacets=' . urlencode(json_encode($active_url_relfacets_to_parse)); }

      // get data from oreka
      $headers = array( 'headers' => array( 'Accept' => 'text/plain', 'Content-Type' => 'application/json', 'Authorization' => 'Bearer ' . $orekasearch_token ) );
      $output = wp_remote_retrieve_body( wp_remote_get( $url, $headers ) );
      $output = json_decode($output);
    } else {
      $output = $OrekaSearch->output;
    }

    // convert search string to * & check if woocommerce mode is active to set post type
    $wp->query_vars['s'] = '*';
    if ($orekasearch_woocommerce == 'woocommerce') { $wp->query_vars['post_type'] = 'product'; }

    // prepare params to pass to vuejs
    $params = array(
      'search' => '*',
      'documents' => $output,
      'url' => home_url(),
      'params' => $wp->query_vars,
      'url_params_facets' => json_encode($active_url_facets_to_parse),
      'url_params_relfacets' => json_encode($active_url_relfacets_to_parse),
      'cacheDocuments' => $this->_orekasearch_aggregation_cache(),
      'filters_list' => $this->_orekasearch_filters_list(),
    );

    // inlclude widget js & css files
    wp_enqueue_script( 'OrekaSearchWidget', plugin_dir_url( __FILE__ ).'../theme/js/widget.js', ['VueJS', 'jquery'], '1.6.2' );
    wp_localize_script( 'OrekaSearchWidget', 'search_params', $params );
    wp_enqueue_style( 'OrekaSearchWidget', plugin_dir_url( __FILE__ ).'../theme/css/widget.css', false, '1.6.2' );

    // echo widget header
		echo $args['before_widget'];
		echo $args['before_title'] . apply_filters( 'widget_title', 'فیلتر‌ها' ) . $args['after_title'];
?>
<style>[v-cloak] {display: none;}</style>
<span id="OrekaSearchWidget" v-cloak>
    <div class="aggregation_filter">
      <p><b>جستجو در میان فیلتر‌ها</b></p>
      <div class="aggregation_filter_wrapper"><input type="text" v-model="filter" placeholder="به طور مثال :‌ برند ، بلوتوث ، رنگ و ..."><div class="icon"></div></div>
      <hr>
    </div>
    <div class="aggregation" v-for="agg in fiiltered_aggregations" v-bind:class="{'close': agg.isopen === false}">
      <p v-on:click="toggle(agg)" ><b>{{agg.title}}</b></p>
      <ul><li v-for="items in agg.items"><a v-bind:href="items.url" v-bind:class="{'checked': items.check}">{{items.name}} ({{items.count}})</a></li></ul>
      <hr>
    </div>
    <div class="aggregation relaggregation" v-for="relagg in fiiltered_relaggregations" v-bind:class="{'close': relagg.isopen === false}">
      <p v-on:click="toggle(relagg)"><b>{{relagg.title}}</b></p>
      <ul>
        <li v-for="items in relagg.items">
          <a v-bind:href="items.url" v-bind:class="{'checked': items.check}" v-if="items.count > 0">{{items.name}} ({{items.count}})</a>
          <div v-bind:href="items.url" v-bind:class="{'checked': items.check}" v-if="items.count < 1">{{items.name}} ({{items.count}})</div>
        </li>
      </ul>
      <hr>
    </div>
</span>
<?php
    // echo widget footer
		echo $args['after_widget'];
  }

  public function _orekasearch_search_results ($args, $instance) {
    global $OrekaSearch;
    global $wp;

    // prepare params to pass to vuejs
    $params = array(
      'search' => $OrekaSearch->search,
      'documents' => $OrekaSearch->output,
      'url' => home_url(),
      'params' => $wp->query_vars,
      'url_params_relfacets' => '[]',
      'url_params_facets' => '[]',
      'cacheDocuments' => $this->_orekasearch_aggregation_cache(),
      'filters_list' => $this->_orekasearch_filters_list(),
    );

    // inlclude widget js & css files
    wp_enqueue_script( 'OrekaSearchWidget', plugin_dir_url( __FILE__ ).'../theme/js/widget.js', ['VueJS', 'jquery'], '1.6.2' );
    wp_localize_script( 'OrekaSearchWidget', 'search_params', $params );
    wp_enqueue_style( 'OrekaSearchWidget', plugin_dir_url( __FILE__ ).'../theme/css/widget.css', false, '1.6.2' );

    // echo widget header
		echo $args['before_widget'];
		echo $args['before_title'] . apply_filters( 'widget_title', 'فیلتر‌ها' ) . $args['after_title'];
?>
<style>[v-cloak] {display: none;}</style>
<span id="OrekaSearchWidget" v-cloak>
    <div class="aggregation_filter">
      <p><b>جستجو در میان فیلتر‌ها</b></p>
      <div class="aggregation_filter_wrapper"><input type="text" v-model="filter" placeholder="به طور مثال :‌ برند ، بلوتوث ، رنگ و ..."><div class="icon"></div></div>
      <hr>
    </div>
    <div class="aggregation" v-for="agg in fiiltered_aggregations" v-bind:class="{'close': agg.isopen === false}">
      <p v-on:click="toggle(agg)" ><b>{{agg.title}}</b></p>
      <ul><li v-for="items in agg.items"><a v-bind:href="items.url" v-bind:class="{'checked': items.check}">{{items.name}} ({{items.count}})</a></li></ul>
      <hr>
    </div>
    <div class="aggregation relaggregation" v-for="relagg in fiiltered_relaggregations" v-bind:class="{'close': relagg.isopen === false}">
      <p v-on:click="toggle(relagg)"><b>{{relagg.title}}</b></p>
      <ul>
        <li v-for="items in relagg.items">
          <a v-bind:href="items.url" v-bind:class="{'checked': items.check}" v-if="items.count > 0">{{items.name}} ({{items.count}})</a>
          <div v-bind:href="items.url" v-bind:class="{'checked': items.check}" v-if="items.count < 1">{{items.name}} ({{items.count}})</div>
        </li>
      </ul>
      <hr>
    </div>
</span>
<?php
    // echo widget footer
		echo $args['after_widget'];
  }

  public function _orekasearch_aggregation_cache()
  {
    $cache = get_option( 'orekasearch_cache' );

    // check if you need to refresh cache
    $get = false;
    if ($cache == false || $cache == '') { $get = true; }
    else { $timer = $cache['timer']; if ($timer < time() - 3600) { $get = true; } }

    // refresh cache
    if ($get)
    {
      // get connection variables
      $orekasearch_server_options = get_option( 'orekasearch_server' );
      $orekasearch_customer_id = $orekasearch_server_options['customer_id'];
      $orekasearch_project_id = $orekasearch_server_options['project_id'];
      $orekasearch_catalog_id = $orekasearch_server_options['catalog_id'];
      $orekasearch_token = $orekasearch_server_options['token'];

      // generate url
      $url = "https://api.dolphinai.ir/customers/".$orekasearch_customer_id."/projects/".$orekasearch_project_id."/searchapi/api/v1/search/catalogs/".$orekasearch_catalog_id."/documents/aggregations";

      // get data from oreka
      $headers = array( 'headers' => array( 'Accept' => 'text/plain', 'Content-Type' => 'application/json', 'Authorization' => 'Bearer ' . $orekasearch_token ) );
      $output = wp_remote_retrieve_body( wp_remote_get( $url, $headers ) );
      $docs = json_decode($output);

      // update option
      $temp['timer'] = time();
      $temp['docs'] = serialize($docs);
      update_option('orekasearch_cache', $temp);
    } else {
      $docs = unserialize($cache['docs']);
    }
    return $docs;
  }

  function _orekasearch_filters_list ()
  {
    // get list of filters from db
    $orekasearch_filters = get_option( 'orekasearch_filters' );
    if ($orekasearch_filters == false or $orekasearch_filters == '') { return array(); }

    // convert string 'true/false' to boolean true/false for vuejs
    foreach ($orekasearch_filters as $key => $filter) { if ($orekasearch_filters[$key]['show'] == 'true') { $orekasearch_filters[$key]['show'] = true; } else { $orekasearch_filters[$key]['show'] = false; } }
    return $orekasearch_filters;
  }


}
?>