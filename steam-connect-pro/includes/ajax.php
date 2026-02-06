<?php
if (!defined('ABSPATH')) exit;

add_action('wp_ajax_scp_check_online_status', 'scp_check_online_status');
add_action('wp_ajax_nopriv_scp_check_online_status', 'scp_check_online_status');

function scp_check_online_status() {
    if (empty($_POST['steamids'])) {
        wp_send_json_error();
    }

    $steamids = sanitize_text_field($_POST['steamids']);
    $api_key = SCP_STEAM_API_KEY;

    $url = "https://api.steampowered.com/ISteamUser/GetPlayerSummaries/v2/?key={$api_key}&steamids={$steamids}";
    $response = wp_remote_get($url);
    if (is_wp_error($response)) {
        wp_send_json_error();
    }

    $data = json_decode(wp_remote_retrieve_body($response), true);
    $result = [];

    if (!empty($data['response']['players'])) {
        foreach ($data['response']['players'] as $player) {
            $result[$player['steamid']] = ($player['personastate'] > 0) ? 'Online' : 'Offline';
        }
    }

    wp_send_json_success($result);
}
