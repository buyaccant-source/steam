<?php
if (!defined('ABSPATH')) exit;

function scp_steam_connect_shortcode() {
    $is_logged_in = is_user_logged_in();
    $user_id = get_current_user_id();
    $steam_id = get_user_meta($user_id, 'steam_id', true);

    $current_url = esc_url(home_url(add_query_arg([], $_SERVER['REQUEST_URI'])));
    $openid_url = 'https://steamcommunity.com/openid/login?' . http_build_query([
        'openid.ns'         => 'http://specs.openid.net/auth/2.0',
        'openid.mode'       => 'checkid_setup',
        'openid.return_to'  => home_url('/steam-auth-callback') . '?redirect=' . urlencode($current_url),
        'openid.realm'      => home_url(),
        'openid.identity'   => 'http://specs.openid.net/auth/2.0/identifier_select',
        'openid.claimed_id' => 'http://specs.openid.net/auth/2.0/identifier_select'
    ]);

    ob_start(); ?>
    <div class="scp-box">
        <?php if ($is_logged_in && $steam_id):
            $steam_user = scp_get_steam_user_info($steam_id);
            if ($steam_user): ?>
                <div class="scp-profile">
                    <img src="<?php echo esc_url($steam_user['avatar']); ?>" class="scp-avatar">
                    <div>
                        <strong><?php echo esc_html($steam_user['name']); ?></strong><br>
                        Level: <?php echo intval($steam_user['level']); ?><br>
                        <a href="<?php echo esc_url($steam_user['profile_url']); ?>" target="_blank">View Profile</a>
                    </div>
                </div>
            <?php endif; ?>
            <form method="POST" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                <input type="hidden" name="action" value="scp_disconnect_steam">
                <?php wp_nonce_field('scp_disconnect_action', 'scp_disconnect_nonce'); ?>
                <button type="submit" class="scp-btn scp-btn-danger">Disconnect Steam</button>
            </form>
        <?php elseif ($is_logged_in): ?>
            <a href="<?php echo esc_url($openid_url); ?>" class="scp-btn">Connect Steam Account</a>
        <?php else: ?>
            <a href="<?php echo wp_login_url($current_url); ?>" class="scp-btn">Login to connect Steam</a>
        <?php endif; ?>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode('steam_connect_button', 'scp_steam_connect_shortcode');

add_action('init', function () {
    if (
        isset($_GET['openid_mode']) &&
        $_GET['openid_mode'] === 'id_res' &&
        strpos($_SERVER['REQUEST_URI'], '/steam-auth-callback') !== false &&
        isset($_GET['openid_claimed_id']) &&
        preg_match('#^https://steamcommunity.com/openid/id/(\d+)$#', $_GET['openid_claimed_id'], $matches)
    ) {
        $steam_id = $matches[1];
        $current_user_id = get_current_user_id();

        if ($current_user_id && $steam_id) {
            update_user_meta($current_user_id, 'steam_id', sanitize_text_field($steam_id));
            $redirect_url = isset($_GET['redirect']) ? esc_url_raw($_GET['redirect']) : home_url('/?steam_connected=1');
            wp_redirect($redirect_url);
            exit;
        } else {
            wp_redirect(home_url('/?steam_error=1'));
            exit;
        }
    }
});

add_action('admin_post_scp_disconnect_steam', 'scp_handle_disconnect');
add_action('admin_post_nopriv_scp_disconnect_steam', 'scp_handle_disconnect');

function scp_handle_disconnect() {
    if (!is_user_logged_in() || !check_admin_referer('scp_disconnect_action', 'scp_disconnect_nonce')) {
        wp_redirect(home_url('/?steam_disconnect_error=1'));
        exit;
    }

    $user_id = get_current_user_id();
    delete_user_meta($user_id, 'steam_id');

    wp_redirect(home_url('/?steam_disconnected=1'));
    exit;
}
