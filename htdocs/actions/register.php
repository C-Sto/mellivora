<?php

require('../../include/mellivora.inc.php');

$redirect_url = array_get($_POST, 'redirect');

if (user_is_logged_in()) {
    redirect($redirect_url);
}

message_error('Mellivora registration not available. See an organiser.');
