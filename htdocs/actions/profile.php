<?php

require('../../include/mellivora.inc.php');

enforce_authentication(
    CONST_USER_CLASS_USER,
    true
);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    validate_xsrf_token($_POST[CONST_XSRF_TOKEN_KEY]);

    if ($_POST['action'] == 'edit') {

        db_update(
          'users',
          array(
             'country_id'=>$_POST['country']
          ),
          array(
             'id'=>$_SESSION['id']
          )
        );

        redirect('profile?generic_success=1');
    }

    else if ($_POST['action'] == 'discord_link') {

        $team = db_select_one('users', array('team_name'), array('id' => $_SESSION['id']));
        $url = 'https://base.blakemccullough.com/adddiscord' . '?' . http_build_query(array('teamid' => $team['team_name']));

        $crl = curl_init($url);
        curl_setopt($crl, CURLOPT_TIMEOUT, 10);
        curl_setopt($crl, CURLOPT_HEADER, true);
        curl_setopt($crl, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($crl, CURLOPT_RETURNTRANSFER, true);
        curl_exec($crl);

        if (curl_getinfo($crl, CURLINFO_RESPONSE_CODE) == 200) {

            $redirectURL = curl_getinfo($crl, CURLINFO_EFFECTIVE_URL);
            curl_close($crl);

            db_update(
                'users',
                array(
                    'discord_status'=>'linked'
                ),
                array(
                    'id'=>$_SESSION['id']
                )
            );

            header('Location: ' . $redirectURL);
            die();

        } else {

            curl_close($crl);
            message_error('Error linking Discord account.');
        }

    }

    else if ($_POST['action'] == '2fa_generate') {

        db_insert(
            'two_factor_auth',
            array(
                'user_id'=>$_SESSION['id'],
                'secret'=>generate_two_factor_auth_secret(32)
            )
        );

        db_update(
            'users',
            array(
                '2fa_status'=>'generated'
            ),
            array(
                'id'=>$_SESSION['id']
            )
        );

        redirect('profile?generic_success=1');
    }

    else if ($_POST['action'] == '2fa_enable') {

        if (!validate_two_factor_auth_code($_POST['code'])) {
            message_error('Incorrect code');
        }

        db_update(
            'users',
            array(
                '2fa_status'=>'enabled'
            ),
            array(
                'id'=>$_SESSION['id']
            )
        );

        redirect('profile?generic_success=1');
    }

    else if ($_POST['action'] == '2fa_disable') {

        db_update(
            'users',
            array(
                '2fa_status'=>'disabled'
            ),
            array(
                'id'=>$_SESSION['id']
            )
        );

        db_delete(
            'two_factor_auth',
            array(
                'user_id'=>$_SESSION['id']
            )
        );

        redirect('profile?generic_success=1');
    }

    else if ($_POST['action'] == 'reset_password') {

        $user = db_select_one(
            'users',
            array('passhash'),
            array('id'=>$_SESSION['id'])
        );

        if (!check_passhash($_POST['current_password'], $user['passhash'])) {
            message_error('Current password was incorrect.');
        }

        if (!strlen($_POST['new_password'])) {
            message_error('Password cannot be empty.');
        }

        if ($_POST['new_password'] != $_POST['new_password_again']) {
            message_error('Passwords did not match.');
        }

        $new_passhash = make_passhash($_POST['new_password']);

        $password_set = db_update(
            'users',
            array(
                'passhash'=>$new_passhash
            ),
            array(
                'id'=>$_SESSION['id']
            )
        );

        if (!$password_set) {
            message_error('Password not set.');
        }

        redirect('profile?generic_success=1');
    }

}
