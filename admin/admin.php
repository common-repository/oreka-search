<?php
class OrekaSearchAdmin
{

  function __construct()
  {
    add_action('admin_init', array( $this, 'orekasearch_admin_init' ));
    add_action('admin_menu', array( $this, 'orekasearch_add_admin_menu' ), 9);
    add_action( 'load-index.php', function(){ add_action( 'admin_notices', array( $this, 'orekasearch_display_admin_notice' ), 100); });
    $this->orekasearch_remove_all_admin_notices();
  }

  function orekasearch_admin_init()
  {
    add_action("wp_ajax_orekasearch_admin_get", array( $this, 'orekasearch_admin_ajax_get'));
    add_action("wp_ajax_orekasearch_admin_set_1", array( $this, 'orekasearch_admin_ajax_set_1'));
    add_action("wp_ajax_orekasearch_admin_set_2", array( $this, 'orekasearch_admin_ajax_set_2'));
    add_action("wp_ajax_orekasearch_admin_preingest", array( $this, 'orekasearch_admin_ajax_preingest'));
    add_action("wp_ajax_orekasearch_admin_ingest", array( $this, 'orekasearch_admin_ajax_ingest'));
    add_action("wp_ajax_orekasearch_admin_clear", array( $this, 'orekasearch_admin_ajax_clear'));
    add_action("wp_ajax_orekasearch_admin_set_filters", array( $this, 'orekasearch_admin_ajax_set_filters'));
    add_action("wp_ajax_orekasearch_admin_analytics", array( $this, 'orekasearch_admin_ajax_analytics'));
  }

  function orekasearch_add_admin_menu()
  {
    add_menu_page( 'Oreka Search', 'Oreka Search', 'administrator', 'orekasearch', array( $this, 'orekasearch_display_admin_page' ), plugins_url( '../theme/icon_menu.png', __FILE__ ), 100 );
  }

  function orekasearch_remove_all_admin_notices()
  {
    if ( isset($_GET['page']) AND strpos(sanitize_key($_GET['page']), 'orekasearch') !== false) {
      add_action('in_admin_header', function () {
        remove_all_actions('admin_notices');
        remove_all_actions('user_admin_notices');
        remove_all_actions('network_admin_notices');
        remove_all_actions('all_admin_notices');
      }, 99);
    }
  }

  function orekasearch_display_admin_notice()
  {
    $orekasearch_main_options = get_option( 'orekasearch_main' );
    $orekasearch_activation = $orekasearch_main_options['activation'];
    if ($orekasearch_activation !== 'activation') { return false; }

    $class = 'notice notice-info is-dismissible';
    $message = ' سرویس جستجوی اورکا فعال است و جایگزین جستجوی پیشفرض وردپرس/ووکامرس شده است.';
    $logo = plugins_url( '../theme/icon_menu.png', __FILE__ );
    printf( '<div class="%1$s"><p><img src="%2$s" style="width:20px; display:inline-block; padding-left:10px; float:right;"> %3$s</p></div>', esc_attr( $class ), esc_html( $logo ), esc_html( $message ) );
  }

  function orekasearch_display_admin_page()
  {
    wp_enqueue_script( 'VueJS', plugin_dir_url( __FILE__ ).'../theme/js/vue.min.js', false, '1.6.2' );
    wp_enqueue_script( 'OrekaSearchAdmin', plugin_dir_url( __FILE__ ).'../theme/js/admin.js', ['VueJS', 'jquery'], '1.6.2' );
    wp_enqueue_style( 'OrekaSearchAdmin', plugin_dir_url( __FILE__ ).'../theme/css/admin.css', false, '1.6.2' );
    require_once plugin_dir_path( __FILE__ ) . 'admin_page.php';
  }

  function orekasearch_admin_ajax_get ()
  {
    $output = $this->orekasearch_admin_internal_output();
    echo wp_send_json_success($output); wp_die(); return null;
  }

  function orekasearch_admin_ajax_set_1 ()
  {
    $oreka['customer_id'] = sanitize_key($_POST['oreka']['customer_id']);
    $oreka['project_id'] = sanitize_key($_POST['oreka']['project_id']);
    $oreka['catalog_id'] = sanitize_key($_POST['oreka']['catalog_id']);
    $oreka['token'] = sanitize_key($_POST['oreka']['token']);

    update_option('orekasearch_server', $oreka);
    $output = $this->orekasearch_admin_internal_output();
    echo wp_send_json_success($output); wp_die(); return null;
  }

  function orekasearch_admin_ajax_set_2 ()
  {
    $oreka['activation'] = rest_sanitize_boolean($_POST['oreka']['activation']);
    $oreka['form_replacement'] = rest_sanitize_boolean($_POST['oreka']['form_replacement']);
    $oreka['ajax'] = rest_sanitize_boolean($_POST['oreka']['ajax']);
    $oreka['ingestion'] = rest_sanitize_boolean($_POST['oreka']['ingestion']);
    $oreka['woocommerce'] = rest_sanitize_boolean($_POST['oreka']['woocommerce']);
    $oreka['metatable'] = rest_sanitize_boolean($_POST['oreka']['metatable']);

    $temp['activation'] = $oreka['activation'] == true ? 'activation' : '';
    $temp['form_replacement'] = $oreka['form_replacement'] == true ? 'form_replacement' : '';
    $temp['ajax'] = $oreka['ajax'] == true ? 'ajax' : '';
    $temp['ingestion'] = $oreka['ingestion'] == true ? 'ingestion' : '';
    $temp['woocommerce'] = $oreka['woocommerce'] == true ? 'woocommerce' : '';
    $temp['metatable'] = $oreka['metatable'] == true ? 'metatable' : '';
    if ($temp['woocommerce'] == 'woocommerce') { $temp['metatable'] = ''; }

    update_option('orekasearch_main', $temp);
    $output = $this->orekasearch_admin_internal_output();
    echo wp_send_json_success($output); wp_die(); return null;
  }

  function orekasearch_admin_ajax_preingest ()
  {
    $ingest_chunks = 300;
    $output = $this->orekasearch_admin_internal_output();
    if ($output['state'] != '_not_activated_' && $output['state'] != '_active_' ) {
      $output['msg_type'] = 'alert'; $output['msg'] = 'خطا : امکان همگام‌سازی اطلاعات از روی سرویس اورکا به علت عدم اتصال صحیح وجود ندارد.';
      echo wp_send_json_success($output); wp_die(); return null;
    }

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

    if ($orekasearch_woocommerce_mode)
    {
      $query = $wpdb->prepare("SELECT * FROM %s WHERE post_status = 'publish' AND post_type = 'product'", $table_post);
      $results = $wpdb->get_results($query);
    } else {
      $query = $wpdb->prepare("SELECT * FROM %s WHERE post_status = 'publish'", $table_post);
      $results = $wpdb->get_results($query);
    }

    $output['msg_type'] = 'success'; $output['msg'] = 'همگام‌سازی اطلاعات در حال اجرا میباشد. لطفا منتظر بمانید.';
    $output['parts'] = sizeof(array_chunk($results,$ingest_chunks));
    echo wp_send_json_success($output); wp_die(); return null;
  }

  function orekasearch_admin_ajax_ingest ()
  {
    $ingest_chunks = 300;
    $output = $this->orekasearch_admin_internal_output();
    if ($output['state'] != '_not_activated_' && $output['state'] != '_active_' ) {
      $output['msg_type'] = 'alert'; $output['msg'] = 'خطا : امکان همگام‌سازی اطلاعات از روی سرویس اورکا به علت عدم اتصال صحیح وجود ندارد.';
      echo wp_send_json_success($output); wp_die(); return null;
    }

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

    $part = sanitize_key($_POST['part']);
    $part = intval($part) * $ingest_chunks;

    if ($orekasearch_woocommerce_mode)
    {
      $query = $wpdb->prepare("SELECT * FROM %s WHERE post_status = 'publish' AND post_type = 'product' LIMIT %d OFFSET %d", $table_post, $ingest_chunks, $part);
      $results = $wpdb->get_results($query);
    } else {
      $query = $wpdb->prepare("SELECT * FROM %s WHERE post_status = 'publish' LIMIT %d OFFSET %d", $table_post, $ingest_chunks, $part);
      $results = $wpdb->get_results($query);
    }

    // get woocommerce products details if in  woocommerce mode
    if ($orekasearch_woocommerce_mode) {
      foreach ($results as $result)
      {
        $must_be_facets = ['pa_manufacturer','product_cat','price','stock'];
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
                $temp_t[] = [ 'key' => wc_attribute_label( $attr_name ) ,  'value' => $this->orekasearch_prepare_string($term->name) ];
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
      }
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
        foreach ($results as $result)
        {
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
        }
      } else {
        $final_result = $results;
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
    $url = "https://api.dolphinai.ir/customers/".$orekasearch_customer_id."/projects/".$orekasearch_project_id."/ingestionapi/api/v1/data/catalogs/".$orekasearch_catalog_id."/batch";
    $headers = array( 'headers' => array( 'Accept' => 'text/plain', 'Content-Type' => 'application/json', 'Authorization' => 'Bearer ' . $orekasearch_token ) );
    $headers['method'] = 'POST';
    $headers['data_format'] = 'body';
    $headers['body'] = $final_result;
    $returned_output =wp_remote_post( $url, $headers );
    $returned_outputCode = wp_remote_retrieve_response_code( $returned_output );
    $returned_output = wp_remote_retrieve_body($returned_output);

    if ($returned_outputCode == 200) {
      $output['msg_type'] = 'success'; $output['msg'] = 'همگام‌سازی با موفقیت انجام شد. کد رهگیری : ' . $returned_output;
    } else {
      $output['msg_type'] = 'alert'; $output['msg'] = 'خطا : درخواست همگام‌سازی اطلاعات با خطا مواجه شد : ' . $returned_output;
    }

    echo wp_send_json_success($output); wp_die(); return null;

  }

  function orekasearch_admin_ajax_clear ()
  {
    $output = $this->orekasearch_admin_internal_output();
    if ($output['state'] != '_not_activated_' && $output['state'] != '_active_' )
    {
      $output['msg_type'] = 'alert'; $output['msg'] = 'خطا : امکان حذف اطلاعات از روی سرویس اورکا به علت عدم اتصال صحیح وجود ندارد.';
      echo wp_send_json_success($output); wp_die(); return null;
    }

    // get server settings
    $orekasearch_server_options = get_option( 'orekasearch_server' );
    $orekasearch_customer_id = $orekasearch_server_options['customer_id'];
    $orekasearch_project_id = $orekasearch_server_options['project_id'];
    $orekasearch_catalog_id = $orekasearch_server_options['catalog_id'];
    $orekasearch_token = $orekasearch_server_options['token'];

    // clear catalog
    $url = "https://api.dolphinai.ir/customers/".$orekasearch_customer_id."/projects/".$orekasearch_project_id."/ingestionapi/api/v1/data/catalogs/".$orekasearch_catalog_id."/documents";
    $headers = array( 'headers' => array( 'Accept' => 'text/plain', 'Content-Type' => 'application/json', 'Authorization' => 'Bearer ' . $orekasearch_token ) );
    $headers['method'] = 'DELETE';
    $headers['data_format'] = 'body';
    $headers['body'] = '';
    $returned_output =wp_remote_request( $url, $headers );
    $returned_outputCode = wp_remote_retrieve_response_code( $returned_output );
    $returned_output = wp_remote_retrieve_body($returned_output);

    if ($returned_outputCode == 204) {
      $output['msg_type'] = 'success'; $output['msg'] = 'درخواست حذف اطلاعات از روی سرویس اورکا با موفقیت صادر شد.';
    } else {
      $output['msg_type'] = 'alert'; $output['msg'] = 'خطا : درخواست حذف اطلاعات با خطا مواجه شد : ' . $returned_output;
    }
    echo wp_send_json_success($output); wp_die(); return null;
  }

  function orekasearch_admin_ajax_set_filters ()
  {
    $filters = isset( $_POST['oreka']['filters'] ) ? (array) $_POST['oreka']['filters'] : array();
    foreach ($filters as $key => $value) {
      $filters[$key] = array(
        'name' => sanitize_text_field($value['name']),
        'title' => sanitize_text_field($value['title']),
        'show' => sanitize_text_field($value['show'])
      );
    }

    update_option('orekasearch_filters', $filters);
    $output = $this->orekasearch_admin_internal_output();
    echo wp_send_json_success($output); wp_die(); return null;
  }

  function orekasearch_admin_ajax_analytics ()
  {
    // check if plugin is active
    $orekasearch_main_options = get_option( 'orekasearch_main' );
    if ($orekasearch_main_options['activation'] != 'activation') { return '1'; }

    $from = sanitize_key($_POST['from']);
    $to = sanitize_key($_POST['to']);
    $from = $from.'T00:00:00';
    $to = $to.'T23:59:59';

    // generate url
    $orekasearch_server_options = get_option( 'orekasearch_server' );
    $url = "https://api.dolphinai.ir/customers/".$orekasearch_server_options['customer_id']."/projects/".$orekasearch_server_options['project_id']."/searchapi/api/v1/search/catalogs/".$orekasearch_server_options['catalog_id'].'/analytics/all';
    $url = $url.(parse_url($url, PHP_URL_QUERY) ? '&' : '?').'ShowFacetDetails=true';
    $url = $url.(parse_url($url, PHP_URL_QUERY) ? '&' : '?').'TopN=5';
    $url = $url.(parse_url($url, PHP_URL_QUERY) ? '&' : '?').'FromRequestTime=' . $from;
    $url = $url.(parse_url($url, PHP_URL_QUERY) ? '&' : '?').'ToRequestTime=' . $to;

    // get data from
    $headers = array( 'headers' => array( 'Accept' => 'text/plain', 'Content-Type' => 'application/json', 'Authorization' => 'Bearer ' . $orekasearch_server_options['token'] ) );
    $output =wp_remote_get( $url, $headers );
    $outputCode = wp_remote_retrieve_response_code( $output );
    $output = wp_remote_retrieve_body($output);

    // check for result
    if ($outputCode != 200) { return $outputCode; }
    echo wp_send_json_success(json_decode($output)); wp_die(); return null;
  }

  function orekasearch_admin_internal_output ()
  {
    // state : _not_registered_ , _not_confirmd_ , _not_activated_ , _active_ // msg_type : alert , info , success
    $output = ['loading' => false, 'msg' => '', 'msg_type' => '', 'state' => '', 'customer_id' => '', 'project_id' => '', 'catalog_id' => '', 'token' => '', 'activation' => '', 'form_replacement' => '', 'ajax' => '', 'ingestion' => '', 'woocommerce' => '' ];
    $orekasearch_main_options = get_option( 'orekasearch_main' );
    $orekasearch_server_options = get_option( 'orekasearch_server' );
    $output['activation'] = $orekasearch_main_options['activation'] == 'activation' ? true : false;
    $output['form_replacement'] = $orekasearch_main_options['form_replacement'] == 'form_replacement' ? true : false;
    $output['ajax'] = $orekasearch_main_options['ajax'] == 'ajax' ? true : false;
    $output['ingestion'] = $orekasearch_main_options['ingestion'] == 'ingestion' ? true : false;
    $output['woocommerce'] = $orekasearch_main_options['woocommerce'] == 'woocommerce' ? true : false;
    $output['metatable'] = $orekasearch_main_options['metatable'] == 'metatable' ? true : false;
    $output['customer_id'] = $orekasearch_server_options['customer_id'];
    $output['project_id'] = $orekasearch_server_options['project_id'];
    $output['catalog_id'] = $orekasearch_server_options['catalog_id'];
    $output['token'] = $orekasearch_server_options['token'];
    $output['filters'] = $this->orekasearch_admin_internal_get_filters($output);
    $output['state'] = $this->orekasearch_admin_internal_get_state($output);
    if ($output['state'] == '_not_registered_') { $output['msg_type'] = 'info'; $output['msg'] = 'سرویس غیرفعال است. لطفا پروسه ثبت‌نام را تکمیل و تنظیمات اتصال را انجام دهید.'; }
    if ($output['state'] == '_not_confirmd_') { $output['msg_type'] = 'alert'; $output['msg'] = 'سرویس غیرفعال است. لطفا تنظیمات اتصال را مجددا بررسی کنید.'; }
    if ($output['state'] == '_not_activated_') { $output['msg_type'] = 'info'; $output['msg'] = 'سرویس غیرفعال است. از قسمت فعال‌سازی نسبت به مدیریت تنظیمات اقدام کنید.'; }
    if ($output['state'] == '_active_') { $output['msg_type'] = 'success'; $output['msg'] = 'سرویس فعال است.'; }

    if ($output['state'] == '_not_registered_' || $output['state'] == '_not_confirmd_' ) {
      if ($output['activation']) {
        $output['activation'] = false;
        $temp['activation'] = $output['activation'];
        $temp['form_replacement'] = $output['form_replacement'];
        $temp['ajax'] = $output['ajax'];
        $temp['ingestion'] = $output['ingestion'];
        $temp['woocommerce'] = $output['woocommerce'];
        $temp['metatable'] = $output['metatable'];
        update_option('orekasearch_main', $temp);
      }
    }

    return $output;
  }

  function orekasearch_admin_internal_get_state ($output)
  {
    // check if already registered
    if ( $output['customer_id'] == '' && $output['project_id'] == '' && $output['catalog_id'] == '' && $output['token'] == '' ) { return '_not_registered_'; }

    // generate url
    $url = "https://api.dolphinai.ir/customers/".$output['customer_id']."/projects/".$output['project_id']."/searchapi/api/v1/search/catalogs/".$output['catalog_id'];

    // get data from
    $headers = array( 'headers' => array( 'Accept' => 'text/plain', 'Content-Type' => 'application/json', 'Authorization' => 'Bearer ' . $output['token'] ) );
    $output =wp_remote_get( $url, $headers );
    $outputCode = wp_remote_retrieve_response_code( $output );
    $output = wp_remote_retrieve_body($output);

    // check for result
    if ($outputCode != 200) { return '_not_confirmd_'; }
    if ($output['activation'] == false ) { return '_not_activated_'; }
    return '_active_';
  }

  function orekasearch_prepare_string($string)
  {
    $string = str_replace(array("\n","\r"), ' ', $string);
    return $string;
  }

  function orekasearch_admin_internal_get_filters ($output)
  {
    if (!$output['woocommerce']) { return array(); }
    $orekasearch_filters = get_option( 'orekasearch_filters' );
    if ($orekasearch_filters == false or $orekasearch_filters == '')
    {
      $list_of_all_filters = [
        array('name'=>'price', 'title' => 'price', 'show' => "true"),
        array('name'=>'stock', 'title' => 'stock', 'show' => "true"),
        array('name'=>'total_sales', 'title' => 'total_sales', 'show' => "true"),
        array('name'=>'_is_fake', 'title' => '_is_fake', 'show' => "true"),
        array('name'=>'_virtual', 'title' => '_virtual', 'show' => "true"),
        array('name'=>'_downloadable', 'title' => '_downloadable', 'show' => "true"),
        array('name'=>'_upcoming', 'title' => '_upcoming', 'show' => "true"),
        array('name'=>'_is_on_sale', 'title' => '_is_on_sale', 'show' => "true"),
        array('name'=>'product_cat', 'title' => 'product_cat', 'show' => "true"),
        array('name'=>'pa_manufacturer', 'title' => 'pa_manufacturer', 'show' => "true"),
      ];
      global $wpdb;
      $table_post = $wpdb->posts;
      $query = $wpdb->prepare("SELECT * FROM %s WHERE post_type = 'product'", $table_post);
      $results = $wpdb->get_results($query);
      foreach( $results as $result ){
        $product = wc_get_product($result->ID);
        foreach( $product->get_attributes() as $attr_name => $attr ){
          if ($attr == '') { continue; }
          if ($attr->get_terms() != null)
          {
            if (!$this->in_array_r(wc_attribute_label( $attr_name ),$list_of_all_filters)) {  array_push($list_of_all_filters, array('name'=>wc_attribute_label( $attr_name ), 'title' => wc_attribute_label( $attr_name ), 'show' => "true"));  }
          } else {
            $data = $attr->get_data();
            if ($data['value'] != '') {
              if (!$this->in_array_r($data['name'],$list_of_all_filters)) {  array_push($list_of_all_filters, array('name'=>$data['name'], 'title' => $data['name'], 'show' => "true"));  }
            }
          }
        }
      }
      update_option('orekasearch_filters', $list_of_all_filters);
    }
    $orekasearch_filters = get_option( 'orekasearch_filters' );
    // convert string 'true/false' to boolean true/false for vuejs
    foreach ($orekasearch_filters as $key => $filter) { if ($orekasearch_filters[$key]['show'] == 'true') { $orekasearch_filters[$key]['show'] = true; } else { $orekasearch_filters[$key]['show'] = false; } }
    return $orekasearch_filters;
  }

  function in_array_r($needle, $haystack) {
    foreach ($haystack as $item) { if ($item['name'] == $needle) { return true; } }
    return false;
  }

}

?>