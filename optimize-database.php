<?php
/**
 * Plugin Name: Optimize Database
 * Plugin URI: http://joshuafredrickson.com
 * Description: Automatically optimize the database every day.
 * Version: 1.0.4
 * Requires PHP: 5.6
 * Author: Joshua Fredrickson
 * Author URI: http://joshuafredrickson.com
 */

namespace OptimizeDatabase;

use function add_action;
use function register_activation_hook;
use function register_deactivation_hook;
use function wp_next_scheduled;
use function wp_schedule_event;

/**
 * If the cron schedule doesn't yet exist, create it.
 */
function check_crons()
{
    if (! wp_next_scheduled('op_optimize_cron')) {
        wp_schedule_event(time(), 'daily', 'op_optimize_cron');
    }
}

/**
 * Plugin activation
 */
register_activation_hook(__FILE__, function () {
    \OptimizeDatabase\check_crons();
});

/**
 * If the cron hook exists, remove it.
 */
register_deactivation_hook(__FILE__, function () {
    if (wp_next_scheduled('op_optimize_cron')) {
        wp_clear_scheduled_hook('op_optimize_cron');
    }
});

/**
 * Do the actual optimization.
 */
add_action('op_optimize_cron', function () {
    global $wpdb;

    $sql = 'SHOW TABLE STATUS FROM ' . DB_NAME;
    $tables = $wpdb->get_results($sql, ARRAY_A);

    foreach ($tables as $t) {
        if (! empty($t['Data_free'])) {
            $wpdb->query("OPTIMIZE TABLE {$t['Name']}");
        }
    }

    return;
});

/**
 * Kick it all off.
 */
check_crons();
