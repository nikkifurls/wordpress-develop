<?php

/**
 * Tests for public methods of WP_Date_Query.
 *
 * See query/dateQuery.php for tests that require WP_Query.
 *
 * @group datequery
 * @group date
 * @covers WP_Date_Query
 */
class Tests_Date_Query extends WP_UnitTestCase {
	/**
	 * @var WP_Date_Query $q
	 */
	public $q;

	public function set_up() {
		parent::set_up();
		unset( $this->q );
		$this->q = new WP_Date_Query( array( 'm' => 2 ) );
	}

	/**
	 * Cleans up.
	 */
	public function tear_down() {
		// Reset the timezone option to the default value.
		update_option( 'timezone_string', '' );

		parent::tear_down();
	}

	public function test_construct_date_query_empty() {
		$q = new WP_Date_Query( array() );
		$this->assertSame( 'AND', $q->relation );
		$this->assertSame( 'post_date', $q->column );
		$this->assertSame( '=', $q->compare );
		$this->assertSame( array(), $q->queries );
	}

	public function test_construct_date_query_non_array() {
		$q = new WP_Date_Query( 'foo' );
		$this->assertSame( 'AND', $q->relation );
		$this->assertSame( 'post_date', $q->column );
		$this->assertSame( '=', $q->compare );
		$this->assertSame( array(), $q->queries );
	}

	public function test_construct_relation_or_lowercase() {
		$q = new WP_Date_Query(
			array(
				'relation' => 'or',
			)
		);

		$this->assertSame( 'OR', $q->relation );
	}

	public function test_construct_relation_invalid() {
		$q = new WP_Date_Query(
			array(
				'relation' => 'foo',
			)
		);

		$this->assertSame( 'AND', $q->relation );
	}

	public function test_construct_query_not_an_array_of_arrays() {
		$q = new WP_Date_Query(
			array(
				'before' => array(
					'year'  => 2008,
					'month' => 6,
				),
			)
		);

		$expected = array(
			0          => array(
				'before'   => array(
					'year'  => 2008,
					'month' => 6,
				),
				'column'   => 'post_date',
				'compare'  => '=',
				'relation' => 'AND',
			),
			'column'   => 'post_date',
			'compare'  => '=',
			'relation' => 'AND',
		);

		$this->assertSame( $expected, $q->queries );
	}

	public function test_construct_query_contains_non_arrays() {
		$q = new WP_Date_Query(
			array(
				'foo',
				'bar',
				array(
					'before' => array(
						'year'  => 2008,
						'month' => 6,
					),
				),
			)
		);

		$expected = array(
			array(
				'before'   => array(
					'year'  => 2008,
					'month' => 6,
				),
				'column'   => 'post_date',
				'compare'  => '=',
				'relation' => 'AND',
			),
			'column'   => 'post_date',
			'compare'  => '=',
			'relation' => 'AND',
		);

		$this->assertSame( $expected, $q->queries );
	}

	public function test_get_compare_empty() {
		$q = new WP_Date_Query( array() );
		$this->assertSame( '=', $q->get_compare( array() ) );
	}

	public function test_get_compare_equals() {
		$q = new WP_Date_Query( array() );

		$found = $q->get_compare(
			array(
				'compare' => '=',
			)
		);
		$this->assertSame( '=', $found );
	}

	public function test_get_compare_not_equals() {
		$q = new WP_Date_Query( array() );

		$found = $q->get_compare(
			array(
				'compare' => '!=',
			)
		);
		$this->assertSame( '!=', $found );
	}

	public function test_get_compare_greater_than() {
		$q = new WP_Date_Query( array() );

		$found = $q->get_compare(
			array(
				'compare' => '>',
			)
		);
		$this->assertSame( '>', $found );
	}

	public function test_get_compare_greater_than_or_equal_to() {
		$q = new WP_Date_Query( array() );

		$found = $q->get_compare(
			array(
				'compare' => '>=',
			)
		);
		$this->assertSame( '>=', $found );
	}

	public function test_get_compare_less_than() {
		$q = new WP_Date_Query( array() );

		$found = $q->get_compare(
			array(
				'compare' => '<',
			)
		);
		$this->assertSame( '<', $found );
	}

	public function test_get_compare_less_than_or_equal_to() {
		$q = new WP_Date_Query( array() );

		$found = $q->get_compare(
			array(
				'compare' => '<=',
			)
		);
		$this->assertSame( '<=', $found );
	}

	public function test_get_compare_in() {
		$q = new WP_Date_Query( array() );

		$found = $q->get_compare(
			array(
				'compare' => 'IN',
			)
		);
		$this->assertSame( 'IN', $found );
	}

	public function test_get_compare_not_in() {
		$q = new WP_Date_Query( array() );

		$found = $q->get_compare(
			array(
				'compare' => 'NOT IN',
			)
		);
		$this->assertSame( 'NOT IN', $found );
	}

	public function test_get_compare_between() {
		$q = new WP_Date_Query( array() );

		$found = $q->get_compare(
			array(
				'compare' => 'BETWEEN',
			)
		);
		$this->assertSame( 'BETWEEN', $found );
	}

	public function test_get_compare_not_between() {
		$q = new WP_Date_Query( array() );

		$found = $q->get_compare(
			array(
				'compare' => 'NOT BETWEEN',
			)
		);
		$this->assertSame( 'NOT BETWEEN', $found );
	}

	public function test_validate_column_post_date() {
		global $wpdb;
		$q = new WP_Date_Query( array() );

		$this->assertSame( $wpdb->posts . '.post_date', $q->validate_column( 'post_date' ) );
	}

	public function test_validate_column_post_date_gmt() {
		global $wpdb;
		$q = new WP_Date_Query( array() );

		$this->assertSame( $wpdb->posts . '.post_date_gmt', $q->validate_column( 'post_date_gmt' ) );
	}

	public function test_validate_column_post_modified() {
		global $wpdb;
		$q = new WP_Date_Query( array() );

		$this->assertSame( $wpdb->posts . '.post_modified', $q->validate_column( 'post_modified' ) );
	}

	public function test_validate_column_post_modified_gmt() {
		global $wpdb;
		$q = new WP_Date_Query( array() );

		$this->assertSame( $wpdb->posts . '.post_modified_gmt', $q->validate_column( 'post_modified_gmt' ) );
	}

	public function test_validate_column_comment_date() {
		global $wpdb;
		$q = new WP_Date_Query( array() );

		$this->assertSame( $wpdb->comments . '.comment_date', $q->validate_column( 'comment_date' ) );
	}

	public function test_validate_column_comment_date_gmt() {
		global $wpdb;
		$q = new WP_Date_Query( array() );

		$this->assertSame( $wpdb->comments . '.comment_date_gmt', $q->validate_column( 'comment_date_gmt' ) );
	}

	public function test_validate_column_invalid() {
		global $wpdb;
		$q = new WP_Date_Query( array() );

		$this->assertSame( $wpdb->posts . '.post_date', $q->validate_column( 'foo' ) );
	}

	/**
	 * @ticket 25775
	 */
	public function test_validate_column_with_date_query_valid_columns_filter() {
		$q = new WP_Date_Query( array() );

		add_filter( 'date_query_valid_columns', array( $this, 'date_query_valid_columns_callback' ) );

		$this->assertSame( 'my_custom_column', $q->validate_column( 'my_custom_column' ) );

		remove_filter( 'date_query_valid_columns', array( $this, 'date_query_valid_columns_callback' ) );
	}

	public function date_query_valid_columns_callback( $columns ) {
		$columns[] = 'my_custom_column';
		return $columns;
	}

	/**
	 * @ticket 25775
	 */
	public function test_validate_column_prefixed_column_name() {
		$q = new WP_Date_Query( array() );

		$this->assertSame( 'foo.bar', $q->validate_column( 'foo.bar' ) );
	}

	/**
	 * @ticket 25775
	 */
	public function test_validate_column_prefixed_column_name_with_illegal_characters() {
		$q = new WP_Date_Query( array() );

		$this->assertSame( 'foo.bar', $q->validate_column( 'f"\'oo\/.b;:()ar' ) );
	}

	public function test_build_value_value_null() {
		global $wpdb;
		$q = new WP_Date_Query( array() );

		$this->assertFalse( $q->build_value( 'foo', null ) );
	}

	/**
	 * @ticket 29801
	 */
	public function test_build_value_compare_in() {
		$q = new WP_Date_Query( array() );

		// Single integer.
		$found = $q->build_value( 'IN', 4 );
		$this->assertSame( '(4)', $found );

		// Single non-integer.
		$found = $q->build_value( 'IN', 'foo' );
		$this->assertFalse( $found );

		// Array of integers.
		$found = $q->build_value( 'IN', array( 1, 4, 7 ) );
		$this->assertSame( '(1,4,7)', $found );

		// Array containing non-integers.
		$found = $q->build_value( 'IN', array( 1, 'foo', 7 ) );
		$this->assertSame( '(1,7)', $found );
	}

	/**
	 * @ticket 29801
	 */
	public function test_build_value_compare_not_in() {
		$q = new WP_Date_Query( array() );

		// Single integer.
		$found = $q->build_value( 'NOT IN', 4 );
		$this->assertSame( '(4)', $found );

		// Single non-integer.
		$found = $q->build_value( 'NOT IN', 'foo' );
		$this->assertFalse( $found );

		// Array of integers.
		$found = $q->build_value( 'NOT IN', array( 1, 4, 7 ) );
		$this->assertSame( '(1,4,7)', $found );

		// Array containing non-integers.
		$found = $q->build_value( 'NOT IN', array( 1, 'foo', 7 ) );
		$this->assertSame( '(1,7)', $found );
	}

	public function test_build_value_compare_between_single_integer() {
		$q = new WP_Date_Query( array() );

		$found = $q->build_value( 'BETWEEN', 4 );
		$this->assertSame( '4 AND 4', $found );
	}

	/**
	 * @ticket 29801
	 */
	public function test_build_value_compare_between_single_non_numeric() {
		$q = new WP_Date_Query( array() );

		$found = $q->build_value( 'BETWEEN', 'foo' );
		$this->assertFalse( $found );
	}

	/**
	 * @ticket 29801
	 */
	public function test_build_value_compare_between_array_with_other_than_two_items() {
		$q = new WP_Date_Query( array() );

		$found = $q->build_value( 'BETWEEN', array( 2, 3, 4 ) );
		$this->assertFalse( $found );
	}

	/**
	 * @ticket 29801
	 */
	public function test_build_value_compare_between_incorrect_array_key() {
		$q = new WP_Date_Query( array() );

		$found = $q->build_value(
			'BETWEEN',
			array(
				2 => 4,
				3 => 5,
			)
		);

		$this->assertSame( '4 AND 5', $found );
	}

	/**
	 * @ticket 29801
	 */
	public function test_build_value_compare_between_array_contains_non_numeric() {
		$q = new WP_Date_Query( array() );

		$found = $q->build_value( 'BETWEEN', array( 2, 'foo' ) );
		$this->assertFalse( $found );
	}

	public function test_build_value_compare_between() {
		$q = new WP_Date_Query( array() );

		$found = $q->build_value( 'BETWEEN', array( 2, 3 ) );
		$this->assertSame( '2 AND 3', $found );
	}

	public function test_build_value_compare_not_between_single_integer() {
		$q = new WP_Date_Query( array() );

		$found = $q->build_value( 'NOT BETWEEN', 4 );
		$this->assertSame( '4 AND 4', $found );
	}

	/**
	 * @ticket 29801
	 */
	public function test_build_value_compare_not_between_single_non_numeric() {
		$q = new WP_Date_Query( array() );

		$found = $q->build_value( 'NOT BETWEEN', 'foo' );
		$this->assertFalse( $found );
	}

	/**
	 * @ticket 29801
	 */
	public function test_build_value_compare_not_between_array_with_other_than_two_items() {
		$q = new WP_Date_Query( array() );

		$found = $q->build_value( 'NOT BETWEEN', array( 2, 3, 4 ) );
		$this->assertFalse( $found );
	}

	/**
	 * @ticket 29801
	 */
	public function test_build_value_compare_not_between_incorrect_array_key() {
		$q = new WP_Date_Query( array() );

		$found = $q->build_value(
			'NOT BETWEEN',
			array(
				2 => 4,
				3 => 5,
			)
		);

		$this->assertSame( '4 AND 5', $found );
	}

	/**
	 * @ticket 29801
	 */
	public function test_build_value_compare_not_between_array_contains_non_numeric() {
		$q = new WP_Date_Query( array() );

		$found = $q->build_value( 'NOT BETWEEN', array( 2, 'foo' ) );
		$this->assertFalse( $found );
	}

	public function test_build_value_compare_not_between() {
		$q = new WP_Date_Query( array() );

		$found = $q->build_value( 'NOT BETWEEN', array( 2, 3 ) );
		$this->assertSame( '2 AND 3', $found );
	}

	public function test_build_value_compare_default_value_integer() {
		$q = new WP_Date_Query( array() );

		$found = $q->build_value( 'foo', 5 );
		$this->assertSame( 5, $found );
	}

	/**
	 * @ticket 29801
	 */
	public function test_build_value_compare_default_value_non_numeric() {
		$q = new WP_Date_Query( array() );

		$found = $q->build_value( 'foo', 'foo' );
		$this->assertFalse( $found );
	}

	public function test_build_mysql_datetime_datetime_non_array() {
		$q = new WP_Date_Query( array() );

		// This might be a fragile test if it takes longer than 1 second to run.
		$found    = $q->build_mysql_datetime( 'foo' );
		$expected = gmdate( 'Y-m-d H:i:s', false );
		$this->assertSame( $expected, $found );
	}

	/**
	 * @ticket 41782
	 *
	 * @dataProvider data_build_mysql_datetime
	 *
	 * @param array|string $datetime       Array or string date input.
	 * @param string       $expected       Expected built result.
	 * @param bool         $default_to_max Flag to default missing values to max.
	 */
	public function test_build_mysql_datetime( $datetime, $expected, $default_to_max = false ) {
		$q = new WP_Date_Query( array() );

		$found = $q->build_mysql_datetime( $datetime, $default_to_max );

		$message = "Expected {$expected}, got {$found}";
		$this->assertEqualsWithDelta( strtotime( $expected ), strtotime( $found ), 10, $message );
	}

	public function data_build_mysql_datetime() {
		return array(
			array( '2019-06-04T08:18:24+03:00', '2019-06-04 05:18:24' ),
			array( '2019-06-04T05:18:24+00:00', '2019-06-04 05:18:24' ),
			array( array(), current_time( 'Y' ) . '-01-01 00:00:00' ),
			array( array( 'year' => 2011 ), '2011-12-31 23:59:59', true ),
			array( array( 'year' => 2011 ), '2011-01-01 00:00:00' ),
			array( '2011', '2011-01-01 00:00:00' ),
			array( '2011-02', '2011-02-01 00:00:00' ),
			array( '2011-02-03', '2011-02-03 00:00:00' ),
			array( '2011-02-03 13:30', '2011-02-03 13:30:00' ),
			array( '2011-02-03 13:30:35', '2011-02-03 13:30:35' ),
		);
	}

	/**
	 * @ticket 41782
	 *
	 * @dataProvider data_build_mysql_datetime_with_custom_timezone
	 *
	 * @param array|string $datetime       Array or string date input.
	 * @param string       $expected       Expected built result.
	 * @param bool         $default_to_max Flag to default missing values to max.
	 */
	public function test_build_mysql_datetime_with_custom_timezone( $datetime, $expected, $default_to_max = false ) {
		update_option( 'timezone_string', 'Europe/Helsinki' );

		$q = new WP_Date_Query( array() );

		$found = $q->build_mysql_datetime( $datetime, $default_to_max );

		$message = "Expected {$expected}, got {$found}";
		$this->assertEqualsWithDelta( strtotime( $expected ), strtotime( $found ), 10, $message );
	}

	public function data_build_mysql_datetime_with_custom_timezone() {
		return array(
			array( '2019-06-04T08:18:24+03:00', '2019-06-04 08:18:24' ),
			array( '2019-06-04T05:18:24+00:00', '2019-06-04 08:18:24' ),
		);
	}

	/**
	 * @ticket 41782
	 */
	public function test_build_mysql_datetime_with_relative_date() {
		update_option( 'timezone_string', 'Europe/Helsinki' );

		$q = new WP_Date_Query( array() );

		$yesterday = new DateTimeImmutable( '-1 day', wp_timezone() );
		$expected  = $yesterday->format( 'Y-m-d H:i:s' );
		$found     = $q->build_mysql_datetime( '-1 day' );

		$message = "Expected {$expected}, got {$found}";
		$this->assertEqualsWithDelta( strtotime( $expected ), strtotime( $found ), 10, $message );
	}

	public function test_build_time_query_insufficient_time_values() {
		$q = new WP_Date_Query( array() );

		$this->assertFalse( $q->build_time_query( 'post_date', '=' ) );
	}

	/**
	 * @ticket 34228
	 */
	public function test_build_time_query_should_not_discard_hour_0() {
		global $wpdb;
		$q = new WP_Date_Query( array() );

		$found = $q->build_time_query( 'post_date', '=', 0, 10 );

		$this->assertStringContainsString( '%H', $wpdb->remove_placeholder_escape( $found ) );
	}

	public function test_build_time_query_compare_in() {
		$q = new WP_Date_Query( array() );

		// Just hour.
		$found = $q->build_time_query( 'post_date', 'IN', array( 1, 2 ) );
		$this->assertSame( 'HOUR( post_date ) IN (1,2)', $found );

		// Skip minute.
		$found = $q->build_time_query( 'post_date', 'IN', array( 1, 2 ), null, 6 );
		$this->assertSame( 'HOUR( post_date ) IN (1,2) AND SECOND( post_date ) IN (6)', $found );

		// All three.
		$found = $q->build_time_query( 'post_date', 'IN', array( 1, 2 ), array( 3, 4, 5 ), 6 );
		$this->assertSame( 'HOUR( post_date ) IN (1,2) AND MINUTE( post_date ) IN (3,4,5) AND SECOND( post_date ) IN (6)', $found );
	}

	public function test_build_time_query_compare_not_in() {
		$q = new WP_Date_Query( array() );

		// Just hour.
		$found = $q->build_time_query( 'post_date', 'NOT IN', array( 1, 2 ) );
		$this->assertSame( 'HOUR( post_date ) NOT IN (1,2)', $found );

		// Skip minute.
		$found = $q->build_time_query( 'post_date', 'NOT IN', array( 1, 2 ), null, 6 );
		$this->assertSame( 'HOUR( post_date ) NOT IN (1,2) AND SECOND( post_date ) NOT IN (6)', $found );

		// All three.
		$found = $q->build_time_query( 'post_date', 'NOT IN', array( 1, 2 ), array( 3, 4, 5 ), 6 );
		$this->assertSame( 'HOUR( post_date ) NOT IN (1,2) AND MINUTE( post_date ) NOT IN (3,4,5) AND SECOND( post_date ) NOT IN (6)', $found );
	}

	public function test_build_time_query_compare_between() {
		$q = new WP_Date_Query( array() );

		// Just hour.
		$found = $q->build_time_query( 'post_date', 'BETWEEN', array( 1, 2 ) );
		$this->assertSame( 'HOUR( post_date ) BETWEEN 1 AND 2', $found );

		// Skip minute.
		$found = $q->build_time_query( 'post_date', 'BETWEEN', array( 1, 2 ), null, array( 6, 7 ) );
		$this->assertSame( 'HOUR( post_date ) BETWEEN 1 AND 2 AND SECOND( post_date ) BETWEEN 6 AND 7', $found );

		// All three.
		$found = $q->build_time_query( 'post_date', 'BETWEEN', array( 1, 2 ), array( 3, 4 ), array( 6, 7 ) );
		$this->assertSame( 'HOUR( post_date ) BETWEEN 1 AND 2 AND MINUTE( post_date ) BETWEEN 3 AND 4 AND SECOND( post_date ) BETWEEN 6 AND 7', $found );
	}

	public function test_build_time_query_compare_not_between() {
		$q = new WP_Date_Query( array() );

		// Just hour.
		$found = $q->build_time_query( 'post_date', 'NOT BETWEEN', array( 1, 2 ) );
		$this->assertSame( 'HOUR( post_date ) NOT BETWEEN 1 AND 2', $found );

		// Skip minute.
		$found = $q->build_time_query( 'post_date', 'NOT BETWEEN', array( 1, 2 ), null, array( 6, 7 ) );
		$this->assertSame( 'HOUR( post_date ) NOT BETWEEN 1 AND 2 AND SECOND( post_date ) NOT BETWEEN 6 AND 7', $found );

		// All three.
		$found = $q->build_time_query( 'post_date', 'NOT BETWEEN', array( 1, 2 ), array( 3, 4 ), array( 6, 7 ) );
		$this->assertSame( 'HOUR( post_date ) NOT BETWEEN 1 AND 2 AND MINUTE( post_date ) NOT BETWEEN 3 AND 4 AND SECOND( post_date ) NOT BETWEEN 6 AND 7', $found );
	}

	public function test_build_time_query_hour_only() {
		$q = new WP_Date_Query( array() );

		$found = $q->build_time_query( 'post_date', '=', 5 );
		$this->assertSame( 'HOUR( post_date ) = 5', $found );
	}

	public function test_build_time_query_minute_only() {
		$q = new WP_Date_Query( array() );

		$found = $q->build_time_query( 'post_date', '=', null, 5 );
		$this->assertSame( 'MINUTE( post_date ) = 5', $found );
	}

	public function test_build_time_query_second_only() {
		$q = new WP_Date_Query( array() );

		$found = $q->build_time_query( 'post_date', '=', null, null, 5 );
		$this->assertSame( 'SECOND( post_date ) = 5', $found );
	}

	public function test_build_time_query_hour_and_second() {
		$q = new WP_Date_Query( array() );

		$found = $q->build_time_query( 'post_date', '=', 5, null, 5 );
		$this->assertFalse( $found );
	}

	public function test_build_time_query_hour_minute() {
		global $wpdb;
		$q = new WP_Date_Query( array() );

		$found = $q->build_time_query( 'post_date', '=', 5, 15 );

		// $compare value is floating point - use regex to account for
		// varying precision on different PHP installations.
		$this->assertMatchesRegularExpression( "/DATE_FORMAT\( post_date, '%H\.%i' \) = 5\.150*/", $wpdb->remove_placeholder_escape( $found ) );
	}

	public function test_build_time_query_hour_minute_second() {
		global $wpdb;
		$q = new WP_Date_Query( array() );

		$found = $q->build_time_query( 'post_date', '=', 5, 15, 35 );

		// $compare value is floating point - use regex to account for
		// varying precision on different PHP installations.
		$this->assertMatchesRegularExpression( "/DATE_FORMAT\( post_date, '%H\.%i%s' \) = 5\.15350*/", $wpdb->remove_placeholder_escape( $found ) );
	}

	public function test_build_time_query_minute_second() {
		global $wpdb;
		$q = new WP_Date_Query( array() );

		$found = $q->build_time_query( 'post_date', '=', null, 15, 35 );

		// $compare value is floating point - use regex to account for
		// varying precision on different PHP installations.
		$this->assertMatchesRegularExpression( "/DATE_FORMAT\( post_date, '0\.%i%s' \) = 0\.15350*/", $wpdb->remove_placeholder_escape( $found ) );
	}

	/**
	 * @ticket 25834
	 * @expectedIncorrectUsage WP_Date_Query
	 */
	public function test_validate_date_query_before_after() {
		// Valid values.
		$valid_args = array(
			array(
				'month' => 2,
				'year'  => 2014,
			),
			array(
				'day'  => 8,
				'year' => 2014,
			),
		);

		foreach ( $valid_args as $args ) {
			$this->assertTrue( $this->q->validate_date_values( array( 'before' => $args ) ) );
			$this->assertTrue( $this->q->validate_date_values( array( 'after' => $args ) ) );
		}

		// Invalid values.
		$invalid_args = array(
			array(
				'month' => 13,
			),
			array(
				'day' => 32,
			),
			array(
				'minute' => 60,
			),
			array(
				'second' => 60,
			),
			array(
				'week' => 54,
			),
		);

		foreach ( $invalid_args as $args ) {
			$this->assertFalse( $this->q->validate_date_values( array( 'before' => $args ) ) );
			$this->assertFalse( $this->q->validate_date_values( array( 'after' => $args ) ) );
		}
	}

	/**
	 * @ticket 25834
	 * @expectedIncorrectUsage WP_Date_Query
	 */
	public function test_validate_date_query_before_after_with_month() {
		// Both are valid.
		$args = array(
			'before' => array(
				'month' => 2,
				'year'  => 2014,
			),
			'month'  => 10,
		);
		$this->assertTrue( $this->q->validate_date_values( $args ) );

		// 'before' is invalid, 'month' is valid.
		$args = array(
			'before' => array(
				'month' => 13,
				'year'  => 2014,
			),
			'month'  => 10,
		);
		$this->assertFalse( $this->q->validate_date_values( $args ) );

		// 'before' is valid, 'month' is invalid.
		$args = array(
			'before' => array(
				'month' => 10,
				'year'  => 2014,
			),
			'month'  => 14,
		);
		$this->assertFalse( $this->q->validate_date_values( $args ) );

		// Both are invalid.
		$args = array(
			'before' => array(
				'month' => 14,
				'year'  => 2014,
			),
			'month'  => 14,
		);
		$this->assertFalse( $this->q->validate_date_values( $args ) );
	}

	/**
	 * @ticket 25834
	 * @expectedIncorrectUsage WP_Date_Query
	 */
	public function test_validate_date_values_week() {
		// Valid values.
		$weeks = range( 1, 53 );
		foreach ( $weeks as $week ) {
			$this->assertTrue( $this->q->validate_date_values( array( 'week' => $week ) ) );
		}

		// Invalid values.
		$weeks = array( -1, 0, 54 );
		foreach ( $weeks as $week ) {
			$this->assertFalse( $this->q->validate_date_values( array( 'week' => $week ) ) );
		}

		// Valid combinations.
		$weeks = array(
			array(
				'week' => 52,
				'year' => 2012,
			),
			array(
				'week' => 53,
				'year' => 2009,
			),
		);

		foreach ( $weeks as $week_args ) {
			$this->assertTrue( $this->q->validate_date_values( $week_args ) );
		}

		// Invalid combinations.
		$weeks = array(
			// 2012 has 52 weeks.
			array(
				'week' => 53,
				'year' => 2012,
			),

			// 2013 has 53 weeks.
			array(
				'week' => 54,
				'year' => 2009,
			),
		);

		foreach ( $weeks as $week_args ) {
			$this->assertFalse( $this->q->validate_date_values( $week_args ) );
		}
	}

	/**
	 * @ticket 25834
	 * @expectedIncorrectUsage WP_Date_Query
	 */
	public function test_validate_date_values_month() {
		// Valid values.
		$months = range( 1, 12 );
		foreach ( $months as $month ) {
			$this->assertTrue( $this->q->validate_date_values( array( 'month' => $month ) ) );
		}

		// Invalid values.
		$months = array( -1, 0, 13, 'string who wants to be a int' );
		foreach ( $months as $month ) {
			$this->assertFalse( $this->q->validate_date_values( array( 'month' => $month ) ) );
		}
	}

	/**
	 * @ticket 25834
	 * @expectedIncorrectUsage WP_Date_Query
	 */
	public function test_validate_date_values_day() {
		// Valid values.
		$days = range( 1, 31 );
		foreach ( $days as $day ) {
			$this->assertTrue( $this->q->validate_date_values( array( 'day' => $day ) ) );
		}

		// Invalid values.
		$days = array( -1, 32 );
		foreach ( $days as $day ) {
			$this->assertFalse( $this->q->validate_date_values( array( 'day' => $day ) ) );
		}

		// Valid combinations.
		$days = array(
			array(
				'day'   => 29,
				'month' => 2,
				'year'  => 2008,
			),
			array(
				'day'   => 28,
				'month' => 2,
				'year'  => 2009,
			),
		);

		foreach ( $days as $args ) {
			$this->assertTrue( $this->q->validate_date_values( $args ) );
		}

		// Invalid combinations.
		$days = array(
			// February 2008 has 29 days.
			array(
				'day'   => 30,
				'month' => 2,
				'year'  => 2008,
			),

			// February 2009 has 29 days.
			array(
				'day'   => 29,
				'month' => 2,
				'year'  => 2009,
			),
		);

		foreach ( $days as $args ) {
			$this->assertFalse( $this->q->validate_date_values( $args ) );
		}
	}

	/**
	 * @ticket 25834
	 * @expectedIncorrectUsage WP_Date_Query
	 */
	public function test_validate_date_values_hour() {
		// Valid values.
		$hours = range( 0, 23 );
		foreach ( $hours as $hour ) {
			$this->assertTrue( $this->q->validate_date_values( array( 'hour' => $hour ) ) );
		}

		// Invalid values.
		$hours = array( -1, 24, 25, 'string' );
		foreach ( $hours as $hour ) {
			$this->assertFalse( $this->q->validate_date_values( array( 'hour' => $hour ) ) );
		}
	}

	/**
	 * @ticket 25834
	 * @expectedIncorrectUsage WP_Date_Query
	 */
	public function test_validate_date_values_minute() {
		// Valid values.
		$minutes = range( 0, 59 );
		foreach ( $minutes as $minute ) {
			$this->assertTrue( $this->q->validate_date_values( array( 'minute' => $minute ) ) );
		}

		// Invalid values.
		$minutes = array( -1, 60 );
		foreach ( $minutes as $minute ) {
			$this->assertFalse( $this->q->validate_date_values( array( 'minute' => $minute ) ) );
		}
	}

	/**
	 * @ticket 25834
	 * @expectedIncorrectUsage WP_Date_Query
	 */
	public function test_validate_date_values_second() {
		// Valid values.
		$seconds = range( 0, 59 );
		foreach ( $seconds as $second ) {
			$this->assertTrue( $this->q->validate_date_values( array( 'second' => $second ) ) );
		}

		// Invalid values.
		$seconds = array( -1, 60 );
		foreach ( $seconds as $second ) {
			$this->assertFalse( $this->q->validate_date_values( array( 'second' => $second ) ) );
		}
	}

	/**
	 * @ticket 25834
	 * @expectedIncorrectUsage WP_Date_Query
	 */
	public function test_validate_date_values_day_of_week() {
		// Valid values.
		$days_of_week = range( 1, 7 );
		foreach ( $days_of_week as $day_of_week ) {
			$this->assertTrue( $this->q->validate_date_values( array( 'dayofweek' => $day_of_week ) ) );
		}

		// Invalid values.
		$days_of_week = array( -1, 0, 8 );
		foreach ( $days_of_week as $day_of_week ) {
			$this->assertFalse( $this->q->validate_date_values( array( 'dayofweek' => $day_of_week ) ) );
		}
	}

	/**
	 * @ticket 28063
	 * @expectedIncorrectUsage WP_Date_Query
	 */
	public function test_validate_date_values_day_of_week_iso() {
		// Valid values.
		$days_of_week = range( 1, 7 );
		foreach ( $days_of_week as $day_of_week ) {
			$this->assertTrue( $this->q->validate_date_values( array( 'dayofweek_iso' => $day_of_week ) ) );
		}

		// Invalid values.
		$days_of_week = array( -1, 0, 8 );
		foreach ( $days_of_week as $day_of_week ) {
			$this->assertFalse( $this->q->validate_date_values( array( 'dayofweek_iso' => $day_of_week ) ) );
		}
	}

	/**
	 * @ticket 25834
	 * @expectedIncorrectUsage WP_Date_Query
	 */
	public function test_validate_date_values_day_of_year() {
		// Valid values.
		$days_of_year = range( 1, 366 );
		foreach ( $days_of_year as $day_of_year ) {
			$this->assertTrue( $this->q->validate_date_values( array( 'dayofyear' => $day_of_year ) ) );
		}

		// Invalid values.
		$days_of_year = array( -1, 0, 367 );
		foreach ( $days_of_year as $day_of_year ) {
			$this->assertFalse( $this->q->validate_date_values( array( 'dayofyear' => $day_of_year ) ) );
		}
	}

	/**
	 * @ticket 31001
	 */
	public function test_validate_date_values_should_process_array_value_for_year() {
		$p1 = self::factory()->post->create( array( 'post_date' => '2015-01-12 00:00:00' ) );
		$p2 = self::factory()->post->create( array( 'post_date' => '2013-01-12 00:00:00' ) );

		$q = new WP_Query(
			array(
				'date_query' => array(
					array(
						'compare' => 'BETWEEN',
						'year'    => array( 2012, 2014 ),
					),
				),
				'fields'     => 'ids',
			)
		);

		$this->assertSame( array( $p2 ), $q->posts );
	}

	/**
	 * @ticket 31001
	 */
	public function test_validate_date_values_should_process_array_value_for_day() {
		$p1 = self::factory()->post->create( array( 'post_date' => '2015-01-12 00:00:00' ) );
		$p2 = self::factory()->post->create( array( 'post_date' => '2015-01-10 00:00:00' ) );

		$q = new WP_Query(
			array(
				'date_query' => array(
					array(
						'compare' => 'BETWEEN',
						'day'     => array( 9, 11 ),
					),
				),
				'fields'     => 'ids',
			)
		);

		$this->assertSame( array( $p2 ), $q->posts );
	}

	/**
	 * @ticket 31001
	 * @expectedIncorrectUsage WP_Date_Query
	 */
	public function test_validate_date_values_should_process_array_value_for_day_when_values_are_invalid() {
		$p1 = self::factory()->post->create( array( 'post_date' => '2015-01-12 00:00:00' ) );
		$p2 = self::factory()->post->create( array( 'post_date' => '2015-01-10 00:00:00' ) );

		$q = new WP_Query(
			array(
				'date_query' => array(
					array(
						'compare' => 'BETWEEN',
						'day'     => array( 9, 32 ),
					),
				),
				'fields'     => 'ids',
			)
		);

		// MySQL ignores the invalid clause.
		$this->assertSame( array( $p1, $p2 ), $q->posts );
	}

	/**
	 * @covers WP_Date_Query::get_sql
	 */
	public function test_relation_in_query_and() {
		$date_query = array(
			'relation' => 'AND',
			array(
				'before'    => array(
					'year'  => 2021,
					'month' => 9,
					'day'   => 20,
				),
				'after'     => array(
					'year'  => 2019,
					'month' => 2,
					'day'   => 25,
				),
				'inclusive' => true,
			),
			array(
				'before'    => array(
					'year'  => 2016,
					'month' => 9,
					'day'   => 11,
				),
				'after'     => array(
					'year'  => 2014,
					'month' => 5,
					'day'   => 12,
				),
				'inclusive' => false,
			),
		);

		$q = new WP_Date_Query( $date_query );

		$sql = $q->get_sql();

		$parts = mb_split( '\)\s+AND\s+\(', $sql );
		$this->assertIsArray( $parts, 'SQL query cannot be split into multiple parts using operator AND.' );
		$this->assertCount( 2, $parts, 'SQL query does not contain correct number of AND operators.' );

		$this->assertStringNotContainsString( 'OR', $sql, 'SQL query contains conditions joined by operator OR.' );
	}

	/**
	 * @covers WP_Date_Query::get_sql
	 */
	public function test_relation_in_query_or() {
		$date_query = array(
			'relation' => 'OR',
			array(
				'before'    => array(
					'year'  => 2021,
					'month' => 9,
					'day'   => 20,
				),
				'after'     => array(
					'year'  => 2019,
					'month' => 2,
					'day'   => 25,
				),
				'inclusive' => true,
			),
			array(
				'before'    => array(
					'year'  => 2016,
					'month' => 9,
					'day'   => 11,
				),
				'after'     => array(
					'year'  => 2014,
					'month' => 5,
					'day'   => 12,
				),
				'inclusive' => false,
			),
		);

		$q = new WP_Date_Query( $date_query );

		$sql = $q->get_sql();

		$this->assertStringContainsString( 'OR', $sql, 'SQL query does not contain conditions joined by operator OR.' );

		$parts = mb_split( '\)\s+OR\s+\(', $sql );
		$this->assertIsArray( $parts, 'SQL query cannot be split into multiple parts using operator OR.' );
		$this->assertCount( 2, $parts, 'SQL query does not contain correct number of OR operators.' );

		// Checking number of occurrences of AND while skipping the one at the beginning.
		$this->assertSame( 2, substr_count( substr( $sql, 5 ), 'AND' ), 'SQL query does not contain expected number conditions joined by operator AND.' );
	}

	/**
	 * @covers WP_Date_Query::get_sql
	 */
	public function test_relation_in_query_unsupported() {
		$date_query = array(
			'relation' => 'UNSUPPORTED',
			array(
				'before'    => array(
					'year'  => 2021,
					'month' => 9,
					'day'   => 20,
				),
				'after'     => array(
					'year'  => 2019,
					'month' => 2,
					'day'   => 25,
				),
				'inclusive' => true,
			),
			array(
				'before'    => array(
					'year'  => 2016,
					'month' => 9,
					'day'   => 11,
				),
				'after'     => array(
					'year'  => 2014,
					'month' => 5,
					'day'   => 12,
				),
				'inclusive' => false,
			),
		);

		$q = new WP_Date_Query( $date_query );

		$sql = $q->get_sql();

		$parts = mb_split( '\)\s+AND\s+\(', $sql );
		$this->assertIsArray( $parts, 'SQL query cannot be split into multiple parts using operator AND.' );
		$this->assertCount( 2, $parts, 'SQL query does not contain correct number of AND operators.' );

		$this->assertStringNotContainsString( 'OR', $sql, 'SQL query contains conditions joined by operator OR.' );
	}
}
