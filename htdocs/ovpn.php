<?php

require('../include/mellivora.inc.php');

enforce_authentication();

if (isset($_POST['download_ovpn'])) {
  
  if (!is_numeric($_SESSION['id'])) {
    die('This shouldn\'t have happened. See an organiser.'); // bail out
  }

  // this is outside the web root
  $filename = Config::get('MELLIVORA_CONFIG_PATH_BASE') . DIRECTORY_SEPARATOR . 'ovpn' . DIRECTORY_SEPARATOR . 'ovpn-team-' . $_SESSION['id'] . '.7z';

  if(file_exists($filename)) {

    // https://stackoverflow.com/questions/2882472/php-send-file-to-user
    //Get file type and set it as Content Type
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    header('Content-Type: ' . finfo_file($finfo, $filename));
    finfo_close($finfo);

    //Use Content-Disposition: attachment to specify the filename
    header('Content-Disposition: attachment; filename='.basename($filename));

    //No cache
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');

    //Define file size
    header('Content-Length: ' . filesize($filename));

    ob_clean();
    flush();
    readfile($filename);
    
    header('Location: ' . Config::get('MELLIVORA_CONFIG_SITE_URL') . '/ovpn?success');
    exit;

  } else {
    die('Couldn\'t find the OpenVPN config file for your team. See an organiser.');
  }
}

head('Download OpenVPN Configuration');

if (isset($_GET['success'])) {
  message_generic('Downloading...', 'If you haven’t used OpenVPN before, follow the guide below and if you get really stuck see an organiser who can help.', false, false, false);
}

section_head('Download OpenVPN Configuration');
echo '<p>Your OpenVPN configuration file is contained within an encrypted <a href="https://www.7-zip.org/download.html">7zip</a> archive.</p><p>The password is the same as your scoreboard login (the one that was emailed to you).</p>
<p>
<form method="post" action="/ovpn">
  <input type="submit" name="download_ovpn" id="download_ovpn" class="btn btn-primary" value="Download Team Configuration">
</form>
</p>';

section_subhead('How to use OpenVPN');
echo '<p>To access most of the WACTF challenges you must connect to our VPN. We have provided the OpenVPN configuration file for you to do this above.</p><p>OpenVPN is a software you must install and then select your configuration file.</p>';

foot();