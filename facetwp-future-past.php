<?php
/*
Plugin Name: FacetWP - Future Past
Description: Let user filter by future or past posts
Version: 0.1
Author: FacetWP, LLC
Author URI: https://facetwp.com/
*/

defined( 'ABSPATH' ) or exit;


add_filter( 'facetwp_facet_types', function( $types ) {
    include( dirname( __FILE__ ) . '/class-facet.php' );
    $types['future_past'] = new FacetWP_Facet_Future_Past();
    return $types;
});
