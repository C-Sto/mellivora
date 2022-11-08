<?php

require('../include/mellivora.inc.php');

enforce_authentication();

$user = db_select_one(
    'users',
    array(
        'team_name',
        'email',
        'enabled',
        'competing',
        'country_id',
        '2fa_status',
        'discord_status'
    ),
    array('id' => $_SESSION['id'])
);

head(lang_get('profile'));

section_subhead(
    lang_get('profile_settings'),
    '| <a href="user?id='.htmlspecialchars($_SESSION['id']).'">'.lang_get('view_public_profile').'</a>',
    false
);

form_start('actions/profile');
form_input_text('Email', $user['email'], array('disabled'=>true));
form_input_text('Team name', $user['team_name'], array('disabled'=>true));

$opts = db_query_fetch_all('SELECT * FROM countries ORDER BY country_name ASC');
form_select($opts, 'Country', 'id', $user['country_id'], 'country_name');

form_hidden('action', 'edit');
form_button_submit(lang_get('save_changes'));
form_end();

section_subhead(
    lang_get('link_discord_account'),
    '| <a href="https://discord.gg/Ucc9VWhKWp" target=”_blank”>Join the Discord</a>', false);

if ($user['discord_status'] == 'unlinked') {
    form_start('actions/profile');
    form_hidden('action', 'discord_link');
    form_button_submit(lang_get('link_discord'));
    form_end();
} else {
    message_inline_blue('Your Discord account has been linked.', false);
    
    $url = 'https://base.blakemccullough.com/getdiscord' . '?' . http_build_query(array('teamid' => $user['team_name']));
    $headers = array('X-API-Key: ' . CONST_DISCORD_API_KEY);

    $crl = curl_init($url);
    curl_setopt($crl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($crl, CURLOPT_TIMEOUT, 4);
    curl_setopt($crl, CURLOPT_HTTPHEADER, $headers);
    $res = curl_exec($crl);

    if (curl_getinfo($crl, CURLINFO_RESPONSE_CODE) == 200) {
    
        curl_close($crl);
        $results_json = json_decode($res);

        echo '<h4>Members linked to this team:</h4>';
        foreach ($results_json->Results as $name) {
            echo '<li>' . $name . '</li>';
        }

    } else {

        # If the call fails, don't list anything.
        curl_close($crl);
    }

    form_start('actions/profile');
    form_hidden('action', 'discord_link');
    form_button_submit(lang_get('relink_discord'));
    form_end();
}

section_subhead(lang_get('two_factor_auth'), lang_get('using_totp'));
form_start('actions/profile');
if ($user['2fa_status'] == 'generated') {
    form_generic('QR', '<img src="'.get_two_factor_auth_qr_url().'" alt="QR" title="'.lang_get('scan_with_totp_app').'" />');
    form_input_text('Code');
    form_hidden('action', '2fa_enable');
    form_button_submit(lang_get('enable_two_factor_auth'));
}

else if ($user['2fa_status'] == 'disabled') {
    form_hidden('action', '2fa_generate');
    form_button_submit(lang_get('generate_codes'));
}

else if ($user['2fa_status'] == 'enabled') {
    form_generic('QR', '<img src="'.get_two_factor_auth_qr_url().'" alt="QR" title="'.lang_get('scan_with_totp_app').'" />');
    form_hidden('action', '2fa_disable');
    form_button_submit(lang_get('disable_two_factor_auth'), 'danger');
}
form_end();

section_subhead(lang_get('reset_password'));
form_start('actions/profile');
form_input_password('Current password');
form_input_password('New password');
form_input_password('New password again');
form_hidden('action', 'reset_password');
form_input_captcha();
form_button_submit(lang_get('reset_password'), 'warning');
form_end();

foot();
