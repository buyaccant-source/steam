jQuery(document).ready(function ($) {
    function updateSteamStatuses() {
        let steamIds = [];

        $('.scp-user-card').each(function () {
            steamIds.push($(this).data('steamid'));
        });

        if (steamIds.length === 0) return;

        $.post(scpAjax.ajax_url, {
            action: 'scp_check_online_status',
            steamids: steamIds.join(',')
        }, function (response) {
            if (response.success) {
                $.each(response.data, function (steamid, status) {
                    const el = $('.scp-user-card[data-steamid="' + steamid + '"] .scp-online-status');
                    el.text(status);
                    el.removeClass('online offline').addClass(status === 'Online' ? 'online' : 'offline');
                });
            }
        });
    }

    updateSteamStatuses();
    setInterval(updateSteamStatuses, 30000);
});
