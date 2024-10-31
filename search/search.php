<?php
class OrekaSearch
{
  public $search = ''; // search query
  public $searched = false; // if search api called
  public $total = 0; // total results
  public $page = 1; // page number
  public $ids = []; // posts id's
  public $output = '_NOT_YET_SET_'; // api output

  function __construct()
  {
    add_action('pre_get_posts', array(&$this, 'do_search'), 100);
    add_action('the_posts', array(&$this, 'process_search'));
  }

  function do_search( $query )
  {
    if ($query->is_main_query() == false) { return $query; }

    // get search settings
    $orekasearch_main_options = get_option( 'orekasearch_main' );
    $orekasearch_activation = $orekasearch_main_options['activation'];
    $orekasearch_form_replacement = $orekasearch_main_options['form_replacement'];
    if ($orekasearch_activation != 'activation') { return $query; }

    // prevent bots to send request to oreka
    if (isset($_SERVER['HTTP_USER_AGENT']) && preg_match('/bot|crawl|slurp|spider|mediapartners/i', $_SERVER['HTTP_USER_AGENT'])) { return $query; }

    if(!$query->is_search() || !$query->is_main_query() || !get_query_var('s', false)) { return $query; }

    // get server settings
    $orekasearch_server_options = get_option( 'orekasearch_server' );
    $orekasearch_customer_id = $orekasearch_server_options['customer_id'];
    $orekasearch_project_id = $orekasearch_server_options['project_id'];
    $orekasearch_catalog_id = $orekasearch_server_options['catalog_id'];
    $orekasearch_token = $orekasearch_server_options['token'];

    $q = get_query_var('s', false);
    $this->page = isset($query->query_vars['paged']) && $query->query_vars['paged'] > 0 ? $query->query_vars['paged'] : 1;
    $req_size = isset($query->query_vars['posts_per_page']) && $query->query_vars['posts_per_page'] > 0 ? $query->query_vars['posts_per_page'] : 16;
    $req_from = ($this->page - 1) * $req_size + 1;
    $url = "https://api.dolphinai.ir/customers/".$orekasearch_customer_id."/projects/".$orekasearch_project_id."/searchapi/api/v1/search/catalogs/".$orekasearch_catalog_id."/documents";

    // extra check if search is based on aggregations
    if ($q == '*') { $url = $url . '/aggregations'; $q = ''; set_query_var('s', ''); } else { $url = $url.(parse_url($url, PHP_URL_QUERY) ? '&' : '?').'Query=' . urlencode($q); }

    // generate url
    $url = $url.(parse_url($url, PHP_URL_QUERY) ? '&' : '?').'Offset=' . $req_from;
    $url = $url.(parse_url($url, PHP_URL_QUERY) ? '&' : '?').'PageSize=' . $req_size;

    $get_url_facet = isset($_GET['facets']) ? urlencode(str_replace('\"','"',$_GET['facets'])) : '';
    $get_url_relevantfacets = isset($_GET['relevantfacets']) ? urlencode(str_replace('\"','"',$_GET['relevantfacets'])) : '';
    $get_url_sorts = isset($_GET['sorts']) ? urlencode(str_replace('\"','"',$_GET['sorts'])) : '';

    if($get_url_facet <> '' ) { $url = $url.(parse_url($url, PHP_URL_QUERY) ? '&' : '?').'facets=' . $get_url_facet; }
    if($get_url_relevantfacets <> '' ) { $url = $url.(parse_url($url, PHP_URL_QUERY) ? '&' : '?').'relevantfacets=' . $get_url_relevantfacets; }
    if($get_url_sorts <> '' ) { $url = $url.(parse_url($url, PHP_URL_QUERY) ? '&' : '?').'sorts=' . $get_url_sorts; }

    // get data from
    $headers = array( 'headers' => array( 'Accept' => 'text/plain', 'Content-Type' => 'application/json', 'Authorization' => 'Bearer ' . $orekasearch_token ) );
    $output =wp_remote_get( $url, $headers );
    $outputCode = wp_remote_retrieve_response_code( $output );
    $output = wp_remote_retrieve_body($output);

    // check for auth token validation
    if ($outputCode == 400) { if (strpos($output,'Invalid JWT Token')) { echo wp_send_json_error(array('error' => 'invalid token')); return false; } }

    // replace default search if oreka service did not respond
    if ($outputCode <> 200) { return $query; }

    // decode results
    $output = json_decode($output);

    // replace default search if output is not valid json !
    if (json_last_error() !== JSON_ERROR_NONE) { return $query; }

    // set output variables
    $total = $output->info->totaldocumentscount;
    $docs = $output->documents;
    $docsSize = sizeof($docs);

    // set output
    $this->output = $output;

    // parse post id's
    $ids = array();
    foreach ($docs as $item) { array_push($ids, $item->contents->ID); }

    // override wordpress query variables
    $query->query_vars['s'] = '';
    unset( $query->query['s'] );
    $query->query_vars['paged'] = 1;
    $query->query_vars['posts_per_page'] = $req_size;
    $query->set( 'post__in', $ids );
    $this->searched = true;
    $this->search = $q;
    $this->total = $total;
    $this->ids = $ids;

    return $query;
  }

	function process_search($posts)
	{
		global $wp_query;

    // get search settings
    $orekasearch_main_options = get_option( 'orekasearch_main' );
    $orekasearch_woocommerce = $orekasearch_main_options['woocommerce'];

		if ($this->searched) {
      $this->searched = false;
			$wp_query->max_num_pages = ceil($this->total / $wp_query->query_vars['posts_per_page']);
			$wp_query->found_posts = $this->total;
			$wp_query->query_vars['paged'] = $this->page;
			$wp_query->query_vars['s'] = $this->search;
      if (!isset($_GET['orderby']) AND $orekasearch_woocommerce == 'woocommerce') { usort($posts, array(&$this, 'sort_posts')); }
		}
		return $posts;
	}

	function sort_posts($a, $b)
	{
		return array_search($b->ID, $this->ids) > array_search($a->ID, $this->ids) ? -1 : 1;
	}

}
?>