<?php

class OrekaSearchSort extends WP_Widget {

	public function __construct() {
    // widget options
		$widget_ops = array(
			'classname' => 'orekasearch_sort',
			'description' => 'اورکا : سرویس هوشمند جستجو',
		);

    // add vuejs
    add_action( 'wp_enqueue_scripts', function(){ wp_enqueue_script( 'VueJS', plugin_dir_url( __FILE__ ).'../theme/js/vue.min.js', false, '1.6.2' ); });

    // init widget
		parent::__construct( 'orekasearch_sort', 'اورکا : مرتب سازی', $widget_ops );
	}

	public function widget( $args, $instance ) {
    global $OrekaSearch;
    global $wp;

    if (is_object($OrekaSearch->output)) { $this->_orekasearch_with_results_sort($args, $instance); }
	}

  public function _orekasearch_with_results_sort ($args, $instance) {
    global $OrekaSearch;
    global $wp;
    global $woocommerce;

    // prepare params to pass to vuejs
    $params = array(
      'search' => $OrekaSearch->search,
      'documents' => $OrekaSearch->output,
      'url' => home_url(),
      'params' => $wp->query_vars,
    );

    // inlclude widget js & css files
    wp_enqueue_script( 'OrekaSearchSort', plugin_dir_url( __FILE__ ).'../theme/js/sort.js', ['VueJS', 'jquery'], '1.6.2' );
    wp_localize_script( 'OrekaSearchSort', 'sort_params', $params );
    wp_enqueue_style( 'OrekaSearchSort', plugin_dir_url( __FILE__ ).'../theme/css/sort.css', false, '1.6.2' );

    // echo widget header
		echo $args['before_widget'];
		echo $args['before_title'] . apply_filters( 'widget_title', 'مرتب‌سازی' ) . $args['after_title'];
?>
<style>[v-cloak] {display: none;}</style>
<span id="OrekaSearchSort" v-cloak>
    <select v-model="sort" @change="changeSort()">
    <option v-for="item in sorts" :value="item.value">{{item.title}}</option>
    </select>
</span>
<?php
    // echo widget footer
		echo $args['after_widget'];
  }



}
?>