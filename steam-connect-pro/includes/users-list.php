<?php
if (!defined('ABSPATH')) exit;

function scp_connected_users_shortcode() {
    global $wpdb;

    $users = $wpdb->get_results("
        SELECT user_id, meta_value AS steam_id
        FROM {$wpdb->usermeta}
        WHERE meta_key = 'steam_id'
    ");

    if (!$users) {
        return '<p>هیچ کاربری هنوز حساب Steam خود را متصل نکرده است.</p>';
    }

    ob_start(); ?>
    <div class="scp-users-grid">
        <?php foreach ($users as $user):
            $steam_data = scp_get_steam_user_info($user->steam_id);
            if (!$steam_data) continue;

            $steam_level = intval($steam_data['level']);
            $level_class = 'scp-level-basic';

            if ($steam_level >= 11 && $steam_level <= 30) {
                $level_class = 'scp-level-bronze';
            } elseif ($steam_level >= 31 && $steam_level <= 60) {
                $level_class = 'scp-level-silver';
            } elseif ($steam_level >= 61 && $steam_level <= 100) {
                $level_class = 'scp-level-gold';
            } elseif ($steam_level >= 101) {
                $level_class = 'scp-level-platinum';
            }
            ?>
            <div class="scp-user-card" data-steamid="<?php echo esc_attr($steam_data['steamid']); ?>">
                <img src="<?php echo esc_url($steam_data['avatar']); ?>" class="scp-avatar">
                <div class="scp-user-info">
                    <div class="scp-name"><?php echo esc_html($steam_data['name']); ?></div>

                   <span class="scp-level-badge <?php echo esc_attr($level_class); ?>" data-tooltip="Steam Level">
    Lv. <?php echo esc_html($steam_level); ?>
</span>


                    <div class="scp-status">
    <span class="scp-online-status <?php echo ($steam_data['online'] > 0 ? 'online' : 'offline'); ?>">
        <?php echo ($steam_data['online'] > 0 ? 'Online' : 'Offline'); ?>
    </span>
</div>


                    <a href="<?php echo esc_url($steam_data['profile_url']); ?>" target="_blank" class="scp-profile-link">
                        View Profile
                    </a>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode('steam_connected_users', 'scp_connected_users_shortcode');
