<?php

class RestMenu{
  
  function __construct(){
 
    add_action('rest_api_init', [$this,'register_custom_menu_api_route']);
  }


  function register_custom_menu_api_route() {
      register_rest_route(
          'wp/v2', // Define the base namespace
          '/menus/(?P<id>\d+)', // Define the endpoint URL structure, capturing the menu ID
          array(
              'methods'             => 'GET', // Allow only GET requests
              'callback'            => [ $this, 'get_menu_by_id' ], // Callback function to get the menu data
              'permission_callback' => '__return_true', // Make the endpoint public (no authentication)
          )
      );
  }


  function get_menu_by_id( $data ) {
      // Get the menu ID from the route parameter
      $menu_id = $data['id'];

      // Get the menu object
      $menu = wp_get_nav_menu_object($menu_id);

      if (!$menu) {
          return new WP_Error('menu_not_found', 'Menu not found', array('status' => 404));
      }

      // Get the menu items
      $menu_items = wp_get_nav_menu_items($menu_id);

      $items = array();
      foreach ($menu_items as $item) {
          $items[] = array(
              'id'        => $item->ID,
              'title'     => $item->title,
              'url'       => $item->url,
              'parent_id' => $item->menu_item_parent,
              'type'      => $item->type,
              'description' => $item->description,
              'classes'     => $item->classes,
              'attr_title'  => $item->attr_title,
              'target'      => $item->target
          );
      }

      // Return the menu and its items
      return array(
          'id'     => $menu->term_id,
          'name'   => $menu->name,
          'slug'   => $menu->slug,
          'items'  => $items,
      );
  }
}
new RestMenu();
?>