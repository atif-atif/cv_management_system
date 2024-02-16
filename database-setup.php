<?php
global $wpdb;
$table_name = $wpdb->prefix . 'candidate_data';
$charset_collate = $wpdb->get_charset_collate();

$sql = "CREATE TABLE $table_name (
    id mediumint(9) NOT NULL AUTO_INCREMENT,
    name varchar(100) NOT NULL,
    email varchar(100) NOT NULL,
    resume_url varchar(255) NOT NULL,
    PRIMARY KEY  (id)
) $charset_collate;";

require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
dbDelta($sql);
?>