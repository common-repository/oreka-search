<?php
class OrekaSearchIngestion
{
  public $search = '';

  function __construct()
  {
    // check if ingestion is enable in plugin option
    $orekasearch_main_options = get_option( 'orekasearch_main' );
    $orekasearch_ingestion = $orekasearch_main_options['ingestion'];
    $orekasearch_woocommerce = $orekasearch_main_options['woocommerce'];
    if ($orekasearch_ingestion !== 'ingestion') { return false; }

    add_action('save_post', array(&$this, 'orekasearch_ingestion_save_post'), 10, 3);
		add_action('delete_post', array(&$this, 'orekasearch_ingestion_delete_post'), 10, 1);
		add_action('trash_post', array(&$this, 'orekasearch_ingestion_delete_post'), 10, 1);
		add_action('transition_post_status', array(&$this, 'orekasearch_ingestion_transition_post'), 10, 3);
    if ($orekasearch_woocommerce == 'woocommerce') {
      add_action( 'woocommerce_product_set_stock_status', array(&$this, 'orekasearch_ingestion_woocommerce_stock_update'), 99, 1);
    }
  }

  function orekasearch_ingestion_save_post($post_id, $post, $update)
	{
		if (!is_object($post)) { $post = get_post($post_id); }
		if ($post->post_status == 'publish') { $this->orekasearch_ingestion_add($post->ID, $update); } else { $this->orekasearch_ingestion_remove($post->ID); }
	}

	function orekasearch_ingestion_transition_post($new_status, $old_status, $post)
	{
		if ($new_status != 'publish' && $new_status != $old_status) { $this->orekasearch_ingestion_remove($post->ID); }
	}

	function orekasearch_ingestion_delete_post($post_id)
	{
		if (is_object($post_id)) { $post = $post_id; } else { $post = get_post($post_id); }
		$this->orekasearch_ingestion_remove($post->ID);
	}

  function orekasearch_ingestion_woocommerce_stock_update($product_id) {
    $this->orekasearch_ingestion_add($product_id, 1);
  }

  // call ingestion add/update/upsert api
  function orekasearch_ingestion_add($post_id, $update)
  {
    // check if in woocommerce mode
    $result = get_post($post_id);
    if (!$result->post_status == 'publish')  { return true; }

    // check if woocommerce mode is enable in plugin options
    $orekasearch_main_options = get_option( 'orekasearch_main' );
    $orekasearch_woocommerce = $orekasearch_main_options['woocommerce'];
    $orekasearch_metatable = $orekasearch_main_options['metatable'];
    if ($orekasearch_woocommerce === 'woocommerce' ) { $orekasearch_woocommerce_mode = true;  } else { $orekasearch_woocommerce_mode = false; }

    // add main post
    global $wpdb;
    $table_post = $wpdb->posts;
    $table_meta = $wpdb->postmeta;
    $final_result = [];

    // get woocommerce products details if in  woocommerce mode
    if ($orekasearch_woocommerce_mode) {
      $must_be_facets = ['pa_manufacturer','product_cat','price','stock'];

      // double check if it is not product variation
      if ($result->post_type != 'product')  { return true; }

      $product = wc_get_product($result->ID);
      $result->id = strval($result->ID);
      $result->ID = strval($result->ID);

      // get url and image url
      $result->url = get_permalink(intval($result->ID));
      $result->image_url = $product->get_image();

      // add price
      $result->price = $product->get_price();
      if ($result->price == "") { $result->price = "0"; }
      $result->price = intval($result->price);

      // add stock status
      $result->stock = get_post_meta( $result->ID, '_stock_status', true ) == 'instock' ? 'موجود' : 'ناموجود';

      // add total sales
      $result->total_sales = get_post_meta( $result->ID, 'total_sales', true ) ? intval(get_post_meta( $result->ID, 'total_sales', true )) : 0;

      // add extra info
      $result->_is_fake = get_post_meta( $result->ID, '_is_fake', true ) == 'no' ? 'خیر' : 'بله';
      $result->_virtual = get_post_meta( $result->ID, '_virtual', true ) == 'no' ? 'خیر' : 'بله';
      $result->_downloadable = get_post_meta( $result->ID, '_downloadable', true ) == 'no' ? 'خیر' : 'بله';
      $result->_upcoming = get_post_meta( $result->ID, '_upcoming', true ) == 'no' ? 'خیر' : 'بله';
      $result->_is_on_sale = $product->is_on_sale() ? 'بله' : 'خیر' ;

      // add category
      $result->product_cat = [];
      $product_cats = get_the_terms( $result->ID, 'product_cat' );
      if ($product_cats)
      {
        foreach ($product_cats as $cat_value) { $result->product_cat[] = $cat_value->name; }
        if (sizeof($product_cats) == 0) { $result->product_cat[] = 'نامشخص'; }
      } else {
        $result->product_cat[] = 'نامشخص';
      }

      // add rel aggregations (terms)
      $temp_t = [];
      $temp_t_t = '';

      foreach( $product->get_attributes() as $attr_name => $attr ){
        if ($attr == '') { continue; }
        if ($attr->get_terms() != null)
        {
          foreach( $attr->get_terms() as $term ){
            if (in_array($attr_name,$must_be_facets))
            {
              if (!isset($result->{$attr_name})) { $result->{$attr_name} = []; }
              $result->{$attr_name}[] = $this->orekasearch_prepare_string($term->name);
            } else {
              $temp_t[] = [ 'key' => wc_attribute_label( $attr_name ) ,  'value' => $term->name ];
              $temp_t_t .= wc_attribute_label( $attr_name ) . ' ' . $term->name . ' ';
            }
          }
        } else {
          $data = $attr->get_data();
          if ($data['value'] != '') {
            if (in_array($data['name'],$must_be_facets))
            {
              if (!isset($result->{$data['name']})) { $result->{$data['name']} = []; }
              $result->{$data['name']}[] = $this->orekasearch_prepare_string($data['value']);
            } else {
              $temp_t[] = [ 'key' => $data['name'] ,  'value' => $this->orekasearch_prepare_string($data['value']) ];
              $temp_t_t .= $data['name'] . ' ' . $data['value'] . ' ';
            }
          }
        }
      }

      // set relevant facets
      $result->dolphin_relevant_facets = $temp_t;
      $result->dolphin_relevant_text = $temp_t_t;

      // double check for facets
      foreach ($must_be_facets as $facet_value) {
        if (!isset($result->{$facet_value})) { $result->{$facet_value} = 'نامشخص'; }
      }

      // add to results array
      $final_result[] = $result;
    } else {
      // if not in woocommerce mode ...

      // get url and image url
      foreach ($results as $result)
      {
        $result->url = get_permalink(intval($result->ID));
        $result->image_url = get_the_post_thumbnail(intval($result->ID),'medium');
      }

      // check if meta table ingestion is active
      if ($orekasearch_metatable == 'metatable') {
        $temp_id = $result->ID;
        $temp_q = $wpdb->prepare("SELECT * FROM %s WHERE post_id = '%d'", $table_meta, $temp_id);
        $temp_r = $wpdb->get_results($temp_q);
        foreach ($temp_r as $value)
        {
          $value_key = $value->meta_key;
          $value_value = $value->meta_value;
          $result->{$value_key} =  $this->orekasearch_prepare_string($value_value);
        }
        $final_result[] = $result;
      } else {
        $final_result[] = $result;
      }

      foreach ($final_result as $key => $value) {
        $final_result[$key]->id = strval($value->ID);
        $final_result[$key]->ID = strval($value->ID);
      }
    }

    // decode
    $final_result = json_encode($final_result);

    // get server settings
    $orekasearch_server_options = get_option( 'orekasearch_server' );
    $orekasearch_customer_id = $orekasearch_server_options['customer_id'];
    $orekasearch_project_id = $orekasearch_server_options['project_id'];
    $orekasearch_catalog_id = $orekasearch_server_options['catalog_id'];
    $orekasearch_token = $orekasearch_server_options['token'];

    // batch ingest
    $url = "https://api.dolphinai.ir/customers/".$orekasearch_customer_id."/projects/".$orekasearch_project_id."/ingestionapi/api/v1/data/catalogs/".$orekasearch_catalog_id."/batch/all";
    $headers = array( 'headers' => array( 'Accept' => 'text/plain', 'Content-Type' => 'application/json', 'Authorization' => 'Bearer ' . $orekasearch_token ) );
    $headers['method'] = 'POST';
    $headers['data_format'] = 'body';
    $headers['body'] = $final_result;
    $output =wp_remote_post( $url, $headers );
    $outputCode = wp_remote_retrieve_response_code( $output );
    $output = wp_remote_retrieve_body($output);
  }

  // call ingestion remove api
  function orekasearch_ingestion_remove($post_id)
  {
    // check for auto-draft
    $post = get_post($post_id);
    if ($post->post_status == 'auto-draft') { return false; }

    // prepare server variables
    $orekasearch_server_options = get_option( 'orekasearch_server' );
    $orekasearch_customer_id = $orekasearch_server_options['customer_id'];
    $orekasearch_project_id = $orekasearch_server_options['project_id'];
    $orekasearch_catalog_id = $orekasearch_server_options['catalog_id'];
    $orekasearch_token = $orekasearch_server_options['token'];

    // send delete request
    $data = []; $data[] = strval($post_id);
    $data = json_encode($data);
    $url = "https://api.dolphinai.ir/customers/".$orekasearch_customer_id."/projects/".$orekasearch_project_id."/ingestionapi/api/v1/data/catalogs/".$orekasearch_catalog_id."/batch";
    $headers = array( 'headers' => array( 'Accept' => 'text/plain', 'Content-Type' => 'application/json', 'Authorization' => 'Bearer ' . $orekasearch_token ) );
    $headers['method'] = 'DELETE';
    $headers['data_format'] = 'body';
    $headers['body'] = $data;
    $output =wp_remote_request( $url, $headers );
    $outputCode = wp_remote_retrieve_response_code( $output );
    $output = wp_remote_retrieve_body($output);
  }

  function orekasearch_prepare_string($string)
  {
    $string = str_replace(array("\n","\r"), ' ', $string);
    return $string;
  }
}
?>