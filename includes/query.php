<?php
/**
 * Template functions used by Better Search
 *
 * @package Better_Search
 */

/**
 * Returns an array with the first and last indices to be displayed on the page.
 *
 * @since   2.0.0
 *
 * @param   array $search_info    Search query.
 * @param   bool  $boolean_mode   Set BOOLEAN mode for FULLTEXT searching.
 * @param   bool  $bydate         Sort by date.
 * @return  array   First and last indices to be displayed on the page
 */
function bsearch_sql_prepare( $search_info, $boolean_mode, $bydate ) {
	global $wpdb;

	// Initialise some variables.
	$fields       = '';
	$where        = '';
	$join         = '';
	$groupby      = '';
	$orderby      = '';
	$limits       = '';
	$match_fields = '';

	// If post_types is empty or contains a query string then use parse_str else consider it comma-separated.
	$post_types_from_db = bsearch_get_option( 'post_types' );

	if ( ! empty( $post_types_from_db ) && is_array( $post_types_from_db ) ) {
		$post_types = $post_types_from_db;
	} elseif ( ! empty( $post_types_from_db ) && false === strpos( $post_types_from_db, '=' ) ) {
		$post_types = explode( ',', $post_types_from_db );
	} else {
		parse_str( $post_types_from_db, $post_types );  // Save post types in $post_types variable.
	}

	// If post_types is empty or if we want all the post types.
	if ( empty( $post_types ) || 'all' === $post_types_from_db ) {
		$post_types = get_post_types(
			array(
				'public' => true,
			)
		);
	}

	$n = '%';

	if ( count( $search_info ) > 1 ) {

		$search_terms    = $search_info[1];
		$no_search_terms = count( $search_terms );

		// Fields to return.
		$fields = ' ID, 0 AS score ';

		// Create the WHERE Clause.
		$where  = ' AND ( ';
		$where .= $wpdb->prepare(
			' ((post_title LIKE %s) OR (post_content LIKE %s)) ',
			$n . $search_terms[0] . $n,
			$n . $search_terms[0] . $n
		);

		for ( $i = 1; $i < $no_search_terms; $i++ ) {
			$where .= $wpdb->prepare(
				' AND ((post_title LIKE %s) OR (post_content LIKE %s)) ',
				$n . $search_terms[ $i ] . $n,
				$n . $search_terms[ $i ] . $n
			);
		}

		$where .= $wpdb->prepare(
			' OR (post_title LIKE %s) OR (post_content LIKE %s) ',
			$n . $search_terms[0] . $n,
			$n . $search_terms[0] . $n
		);

		$where .= ' ) ';

		$where .= " AND (post_status = 'publish' OR post_status = 'inherit')";

		// Array of post types.
		$where .= " AND $wpdb->posts.post_type IN ('" . join( "', '", $post_types ) . "') ";

		// Create the ORDERBY Clause.
		$orderby = ' post_date DESC ';

	} else {
		$weight_title   = bsearch_get_option( 'weight_title' );
		$weight_content = bsearch_get_option( 'weight_content' );

		// Set BOOLEAN Mode.
		$boolean_mode = ( $boolean_mode ) ? ' IN BOOLEAN MODE' : '';

		$field_args = array(
			$search_info[0],
			$weight_title,
			$search_info[0],
			$weight_content,
		);

		$fields = ' ID';

		// Create the base MATCH part of the FIELDS clause.
		$field_score  = ", (MATCH(post_title) AGAINST ('%s' {$boolean_mode} ) * %d ) + ";
		$field_score .= "(MATCH(post_content) AGAINST ('%s' {$boolean_mode} ) * %d ) ";
		$field_score .= 'AS score ';

		$field_score = $wpdb->prepare( $field_score, $field_args ); // WPCS: unprepared SQL ok.

		/**
		 * Filter the MATCH part of the FIELDS clause of the query.
		 *
		 * @since   2.0.0
		 *
		 * @param string   $field_score     The MATCH section of the FIELDS clause of the query, i.e. score
		 * @param string   $search_info[0]  Search query
		 * @param int      $weight_title    Weight of title
		 * @param int      $weight_content  Weight of content
		 */
		$field_score = apply_filters( 'bsearch_posts_match_field', $field_score, $search_info[0], $weight_title, $weight_content );

		$fields .= $field_score;

		/**
		 * Filter the SELECT clause of the query.
		 *
		 * @since   2.0.0
		 *
		 * @param string   $fields          The SELECT clause of the query.
		 * @param string   $search_info[0]  Search query
		 */
		$fields = apply_filters( 'bsearch_posts_fields', $fields, $search_info[0] );

		// Construct the MATCH part of the WHERE clause.
		$match = " AND MATCH (post_title,post_content) AGAINST ('%s' {$boolean_mode} ) ";

		$match = $wpdb->prepare( $match, $search_info[0] ); // WPCS: unprepared SQL ok.

		/**
		 * Filter the MATCH clause of the query.
		 *
		 * @since   2.0.0
		 *
		 * @param string   $match       The MATCH section of the WHERE clause of the query
		 * @param string   $search_info[0]  Search query
		 */
		$match = apply_filters( 'bsearch_posts_match', $match, $search_info[0] );

		// Construct the WHERE clause.
		$where = $match;

		$where .= " AND (post_status = 'publish' OR post_status = 'inherit')";

		// Array of post types.
		if ( $post_types ) {
			$where .= " AND $wpdb->posts.post_type IN ('" . join( "', '", $post_types ) . "') ";
		}

		// ORDER BY clause.
		if ( $bydate ) {
			$orderby = ' post_date DESC ';
		} else {
			$orderby = ' score DESC ';
		}
	}

	/**
	 * Filter the WHERE clause of the query.
	 *
	 * @since   2.0.0
	 *
	 * @param string   $where       The WHERE clause of the query
	 * @param string   $search_info[0]  Search query
	 */
	$where = apply_filters( 'bsearch_posts_where', $where, $search_info[0] );

	/**
	 * Filter the ORDER BY clause of the query.
	 *
	 * @since   2.0.0
	 *
	 * @param string   $orderby         The ORDER BY clause of the query
	 * @param string   $search_info[0]  Search query
	 */
	$orderby = apply_filters( 'bsearch_posts_orderby', $orderby, $search_info[0] );

	/**
	 * Filter the GROUP BY clause of the query.
	 *
	 * @since   2.0.0
	 *
	 * @param string   $groupby         The GROUP BY clause of the query
	 * @param string   $search_info[0]  Search query
	 */
	$groupby = apply_filters( 'bsearch_posts_groupby', $groupby, $search_info[0] );

	/**
	 * Filter the JOIN clause of the query.
	 *
	 * @since   2.0.0
	 *
	 * @param string   $join        The JOIN clause of the query
	 * @param string   $search_info[0]  Search query
	 */
	$join = apply_filters( 'bsearch_posts_join', $join, $search_info[0] );

	/**
	 * Filter the JOIN clause of the query.
	 *
	 * @since   2.0.0
	 *
	 * @param string   $limits          The JOIN clause of the query
	 * @param string   $search_info[0]  Search query
	 */
	$limits = apply_filters( 'bsearch_posts_limits', $limits, $search_info[0] );

	if ( ! empty( $groupby ) ) {
		$groupby = 'GROUP BY ' . $groupby;
	}
	if ( ! empty( $orderby ) ) {
		$orderby = 'ORDER BY ' . $orderby;
	}

	$sql = "SELECT DISTINCT $fields FROM $wpdb->posts $join WHERE 1=1 $where $groupby $orderby $limits";

	/**
	 * Filter MySQL string used to fetch results.
	 *
	 * @since   1.3
	 *
	 * @param   string  $sql            MySQL string
	 * @param   array   $search_info    Search query
	 * @param   bool    $boolean_mode   Set BOOLEAN mode for FULLTEXT searching
	 * @param   bool    $bydate         Sort by date?
	 */
	return apply_filters( 'bsearch_sql_prepare', $sql, $search_info, $boolean_mode, $bydate );
}
