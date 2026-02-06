<?php
if (!defined('ABSPATH')) exit;

function scp_get_steam_user_info($steam_id) {
    if (!$steam_id) return false;

    $cache_key = 'scp_user_info_' . $steam_id;
    $cached = get_transient($cache_key);
    if ($cached !== false) return $cached;

    $api_key = SCP_STEAM_API_KEY;

    $profile_url = "https://api.steampowered.com/ISteamUser/GetPlayerSummaries/v2/?key={$api_key}&steamids={$steam_id}";
    $profile_res = wp_remote_get($profile_url);
    if (is_wp_error($profile_res)) return false;

    $profile_data = json_decode(wp_remote_retrieve_body($profile_res), true);
    if (empty($profile_data['response']['players'][0])) return false;
    $player = $profile_data['response']['players'][0];

    $level_url = "https://api.steampowered.com/IPlayerService/GetSteamLevel/v1/?key={$api_key}&steamid={$steam_id}";
    $level_res = wp_remote_get($level_url);
    $level_data = json_decode(wp_remote_retrieve_body($level_res), true);
    $level = isset($level_data['response']['player_level']) ? intval($level_data['response']['player_level']) : 0;

    $user_info = [
        'steamid'     => $player['steamid'],
        'name'        => $player['personaname'],
        'profile_url' => $player['profileurl'],
        'avatar'      => $player['avatarfull'],
        'online'      => $player['personastate'],
        'level'       => $level
    ];

    set_transient($cache_key, $user_info, 6 * HOUR_IN_SECONDS);

    return $user_info;
}
