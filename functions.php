<?php

require_once __dir__ . "/inc/class-rest-menu.php";

// Adds theme support for post formats.
if ( ! function_exists( 'wp_nextjs_post_format_setup' ) ) :
	/**
	 * Adds theme support for post formats.
	 *
	 *
	 * @return void
	 */
	function wp_nextjs_post_format_setup() {
		add_theme_support( 'post-thumbnails'  );
    
    register_nav_menus(
        array(
            'header_menu' => __( 'Header Menu' ),  // Main navigation menu in the header
            'footer_menu' => __( 'Footer Menu' ),  // Footer navigation menu
        )
    );
    
	}

endif;
add_action( 'after_setup_theme', 'wp_nextjs_post_format_setup' );

/**
 * we will pouplate the ACF Flexible Content(FC) as per the key of FC key
*/
function populate_as_per_fc_key($fc_key, $data, $res){
  if( function_exists( "fc_populate_".$fc_key ) ){
    $fn = "fc_populate_".$fc_key;

    return $fn($data, $res);
  }
  return $data;
  
}

function fc_populate_related_services($data, $res){
  global $post;
  $settings = $data["config"];
  $settings["exclude"] = [$post->ID];

  $posts = get_posts($settings);

  
 $data["post_services"] = $posts;
return $data;
}

function fc_populate_Breadcrumbs($data, $res){
  $breadcrumb = [];
  if( !empty( $res['yoast_head_json']['schema']['@graph'] ) ){
    $graph = $res['yoast_head_json']['schema']['@graph'];
    foreach( $graph as $items ){
      if( $items["@type"] == "BreadcrumbList" ){
        $breadcrumb = $items["itemListElement"];
      }
    }
  }
  $data["items"] = $breadcrumb;
  return $data;
}


function expose_related_post_fields($data, $post, $context) {

  $res= $data->get_data();

  
  if( !empty( $res["acf"]["sections"] ) ){
    foreach ($res["acf"]["sections"] as $key => &$object ) {
        $object = populate_as_per_fc_key($object["acf_fc_layout"],$object, $res);
        
        foreach( $object as $attr => &$value  ){
          
          if( strpos($attr, "post_") === 0  ){
            if( $value === false ) continue;
         
            foreach( $value as $index => &$post ){
               if( is_numeric($post) ){
              
                }elseif( ! empty( $post->ID)){
                  $post->link = get_the_permalink($post->ID);
                  $post->acf = get_fields($post->ID);

                }
            }
          }
        }
    }
  }
  
  $data->set_data($res);
  
  
  return $data;
}

add_filter('rest_prepare_page', 'expose_related_post_fields', 10, 3);

function enable_page_excerpts() {
    add_post_type_support('page', 'excerpt');
}
add_action('init', 'enable_page_excerpts');


function custom_rest_api_url($url) {
    return 'https://cms.fivehertz.co.in/wp-json/';
}
add_filter('rest_url', 'custom_rest_api_url');

?>