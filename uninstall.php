<?php
	// If uninstall is not called from WordPress, exit
if ( !defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit();
}
	// Drop a custom db table
	global $wpdb;
	$table = $wpdb->prefix."si_rating_review";
	$wpdb->query("DROP TABLE IF EXISTS $table");
?>