<?php

require('../include/mellivora.inc.php');

enforce_authentication();
head('AD');

if (isset($_POST['ad_init'])) {
    if (az_create_env($_SESSION['id'])) {
        //invalidate status cache, since it should have changed here
        invalidate_cache(CONST_CACHE_NAME_AZUSERSTATUS . $_SESSION['id']);
    }
}

if (isset($_POST['ad_revert'])) {
    if (az_revert_env($_SESSION['id'])) {
        invalidate_cache(CONST_CACHE_NAME_AZUSERSTATUS . $_SESSION['id']);
    }
}

if (isset($_POST['ad_start'])) {
    if (az_start_env($_SESSION['id'])) {
        invalidate_cache(CONST_CACHE_NAME_AZUSERSTATUS . $_SESSION['id']);
    }
}

if (Config::get('AZ_CLIENT_ID') !== '') {
    // AD stuff configured, cross fingers pls
    // cache status to avoid people spamming refresh and making the server perform like dogshit
    // player won't see an update until the cache expires, but we also won't be drowning the server in IO. Balance.
    $status = cache_array_get(CONST_CACHE_NAME_AZUSERSTATUS . $_SESSION['id'], Config::get('MELLIVORA_CONFIG_CACHE_TIME_AZSTATUS'));
    if ($status === false) {
        //message_inline_red('Cache miss');
        //cache miss, guess we do the unperformant thing
        $status = az_get_team_status($_SESSION['id']);
        cache_array_save($status, CONST_CACHE_NAME_AZUSERSTATUS . $_SESSION['id']);
    } else {
        //message_inline_red('Cache hit');
    }

    section_head('Access Hyrule Network');
    echo '<p>Click this button to start the N64 that is running the enterprise network</p>
    <p>
    <form method="post" action="/ad">';
    if ($status->status === "Ready") {
        echo '<input type="submit" name="ad_revert" id="ad_revert" class="btn btn-primary" value="Play the song of time (revert lab)">';
    } else if ($status->status === "Reverting") {
        echo '<input type="submit" name="ad_revert" id="ad_revert" class="btn btn-primary" value="Play the song of time (revert lab)" disabled>';
    } else if ($status->status === "Stopped") {
        echo '<input type="submit" name="ad_start" id="ad_start" class="btn btn-primary" value="Turn on Nintendo">';
    } else if ($status->status === "Starting") {
        echo '<input type="submit" name="ad_start" id="ad_start" class="btn btn-primary" value="Turn on Nintendo" disabled>';
    } else {
        echo '<input type="submit" name="ad_init" id="ad_init" class="btn btn-primary" value="Turn on Nintendo">';
    }
    echo '
    </form>
    </p>
    <p>
    If the Nintendo Status has not changed 10 minutes after you clicked the button, please ask an adult for help.';
    echo "<p><strong>Nintendo Status:</strong> ";
    switch ($status->status) {
        case 'Ready':
            echo "Online";
            break;
        case 'Requested':
            echo "N64 has been turned on, our network tech will need some time to blow all the dust out of the carts. Please be patient.";
            break;
        case 'NotYetRequested':
            echo "Offline";
            break;
        case 'Reverting':
            echo "The song of time has been played - your progress has not been saved, and time is turning back to the start of day 0!";
            break;
        case 'Stopped':
            echo "Offline (your progress has been saved, Turn on the Nintendo to pick up where you left off).";
            break;
        case 'Starting':
            echo "N64 is booting back up, won't be long before you get your mimika- er, Ocarina";
            break;
        default:
            echo "error, too much Donkey Kong, not enough Banjo Kazooie (please ask an organiser why this happened). [$status->status]";
            break;
    }
    echo "</p>";
    //status is online, give player some credzzzzz and hope that being a sysadmin is easy
    if ($status->status === "Ready") {
        echo "<p><strong><a href='$status->bastionShareableLink' target='_blank'>Click here to start your quest!</a></strong></p>
<p><strong>Username:</strong> $status->username</p>
<p><strong>Password:</strong> $status->password</p>";
    }
} else {
    section_head('AD Not configured. Lol.');
}


foot();