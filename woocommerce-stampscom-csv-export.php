<?php
/**
Plugin Name: WooCommerce Stamps.com CSV Order Export
Plugin URI: http://tysonarmstrong.com
Description: Export selected orders as CSV for Stamps.com
Author: Tyson Armstrong
Author URI: http://tysonarmstrong.com
Version: 0.1.0
*/

if ( ! defined( 'ABSPATH' ) ) {
  exit;
}

class WC_Stamps_Csv_Order_Export {

  public function __construct() {
    add_filter( 'bulk_actions-edit-shop_order', array($this,'register_bulk_action') );
    add_filter( 'handle_bulk_actions-edit-shop_order', array($this,'bulk_action_handler'), 10, 3 );
  }

  // Add "Export to Stamps.com" item to bulk actions
  public function register_bulk_action($bulk_actions) {
    $bulk_actions['wsce_export'] = __( 'Export to Stamps.com', 'wsce_export');
    return $bulk_actions;
  }

  // Handle bulk acttion and generate CSV file
  public function bulk_action_handler( $redirect_to, $doaction, $post_ids ) {
    if ( $doaction !== 'wsce_export' ) {
      return $redirect_to;
    }

    $file = fopen("php://output","w");
    $output_filename = 'wc_stamps_export.csv';
    header( 'Cache-Control: must-revalidate, post-check=0, pre-check=0' );
    header( 'Content-Description: File Transfer' );
    header( 'Content-type: text/csv' );
    header( 'Content-Disposition: attachment; filename=' . $output_filename );
    header( 'Expires: 0' );
    header( 'Pragma: public' );

    $headers = "Order ID (required),Order Date,Order Value,Requested Service,Ship To - Name,Ship To - Company,Ship To - Address 1,Ship To - Address 2,Ship To - Address 3,Ship To - State/Province,Ship To - City,Ship To - Postal Code,Ship To - Country,Ship To - Phone,Ship To - Email,Total Weight in Oz,Dimensions - Length,Dimensions - Width,Dimensions - Height,Notes - From Customer,Notes - Internal,Gift Wrap?,Gift Message";
    fputcsv($file,explode(',',$headers));

    foreach ( $post_ids as $post_id ) {
      // Perform action for each post.
      $order = wc_get_order($post_id);
      $data = array(
          $order->get_order_number(),
          date('m/d/Y',strtotime($order->get_date_paid())),
          $order->get_total(),
          $order->get_shipping_method(),
          $order->get_formatted_shipping_full_name(),
          $order->get_shipping_company(),
          $order->get_shipping_address_1(),
          $order->get_shipping_address_2(),
          '',
          $order->get_shipping_state(),
          $order->get_shipping_city(),
          $order->get_shipping_postcode(),
          $order->get_shipping_country(),
          $order->get_billing_phone(),
          $order->get_billing_email(),
          '', // Weight in ounces
          '', // Length
          '', // Width
          '', // Height
          $order->get_customer_note(),
          '', // Internal notes
          '', // Gift wrap
          '' // Gift message
          );
      fputcsv($file,$data);
    }
    fclose($file);

    exit;
  }
}

new WC_Stamps_Csv_Order_Export();