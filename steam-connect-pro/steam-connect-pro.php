<?php
/*
Plugin Name: Steam Connect Pro
Description: Connect Steam accounts and display connected users publicly.
Version: 1.0
Author: You
*/

if (!defined('ABSPATH')) exit;

define('SCP_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('SCP_PLUGIN_URL', plugin_dir_url(__FILE__));
define('SCP_STEAM_API_KEY', 'D28E65925488C0064AF060A968DA9701'); // 🔴 API Key

require_once SCP_PLUGIN_PATH . 'includes/api.php';
require_once SCP_PLUGIN_PATH . 'includes/auth.php';
require_once SCP_PLUGIN_PATH . 'includes/users-list.php';
require_once SCP_PLUGIN_PATH . 'includes/ajax.php';

add_action('wp_enqueue_scripts', function () {
    wp_enqueue_style('scp-style', SCP_PLUGIN_URL . 'assets/css/style.css');
    wp_enqueue_script('scp-online', SCP_PLUGIN_URL . 'assets/js/online-status.js', ['jquery'], null, true);
    wp_localize_script('scp-online', 'scpAjax', [
        'ajax_url' => admin_url('admin-ajax.php')
    ]);
});
