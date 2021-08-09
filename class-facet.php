<?php

class FacetWP_Facet_Future_Past extends FacetWP_Facet
{

    /**
     * Give the facet a label
     */
    function __construct() {
        $this->label = __( 'Future Past', 'fwp' );
    }


    /**
     * Pull the facet choices from the facetwp_index DB table
     */
    function load_values( $params ) {
        global $wpdb;

        $facet = $params['facet'];
        $from_clause = $wpdb->prefix . 'facetwp_index f';
        $where_clause = $params['where_clause'];

        // Facet in "OR" mode
        $where_clause = $this->get_where_clause( $facet );

        $from_clause = apply_filters( 'facetwp_facet_from', $from_clause, $facet );
        $where_clause = apply_filters( 'facetwp_facet_where', $where_clause, $facet );
        $now = date( 'Y-m-d H:i:s' );

        $sql = "
        SELECT (CASE WHEN f.facet_value > '$now' THEN 'future' ELSE 'past' END) AS type, COUNT(DISTINCT f.post_id) AS counter
        FROM $from_clause
        WHERE f.facet_name = '{$facet['name']}' $where_clause
        GROUP BY type";

        $results = $wpdb->get_results( $sql, ARRAY_A );

        $output = [
            [
                'facet_value' => 'future',
                'facet_display_value' => 'Future',
                'counter' => 0
            ],
            [
                'facet_value' => 'past',
                'facet_display_value' => 'Past',
                'counter' => 0
            ]
        ];

        foreach ( $results as $row ) {
            $row_num = ( 'future' == $row['type'] ) ? 0 : 1;
            $output[ $row_num ]['counter'] = $row['counter'];
        }

        return $output;
    }


    /**
     * Display the facet HTML (here we're just inheriting from Radio facets)
     */
    function render( $params ) {
        return FWP()->helper->facet_types['radio']->render( $params );
    }


    /**
     * Apply the filtering logic
     */
    function filter_posts( $params ) {
        global $wpdb;

        $output = [];
        $facet = $params['facet'];
        $selected_values = $params['selected_values'];

        $now = date( 'Y-m-d H:i:s' );
        $compare = implode( ',', $selected_values );
        $compare = ( 'future' == $compare ) ? '>' : '<=';

        $sql = $wpdb->prepare( "SELECT DISTINCT post_id
            FROM {$wpdb->prefix}facetwp_index
            WHERE facet_name = %s",
            $facet['name']
        );

        $output = facetwp_sql( $sql . " AND facet_value $compare '$now'", $facet );

        return $output;
    }


    /**
     * Load the necessary front-end script(s) for handling user interactions
     */
    function front_scripts() {
        FWP()->display->assets['future-past-front.js'] = plugins_url( '', __FILE__ ) . '/assets/js/front.js';
    }


    /**
     * Register custom UI settings (here we're inheriting the existing "label_any" setting)
     */
    function settings_html() {
        $this->render_setting( 'label_any' );
    }
}
