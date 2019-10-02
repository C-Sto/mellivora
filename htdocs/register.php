<?php

require('../include/mellivora.inc.php');

if (user_is_logged_in()) {
    redirect(Config::get('MELLIVORA_CONFIG_REGISTER_REDIRECT_TO'));
    exit();
}

prefer_ssl();

head('Register');

?>

<h2>Please see an organiser.</h2>

<?php
foot();
