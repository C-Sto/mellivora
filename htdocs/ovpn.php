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
  message_generic('Downloaded!', 'If you haven’t used OpenVPN before, follow the guide below and if you get really stuck see an organiser who can help.', false, false, false);
}

section_head('Download OpenVPN Configuration');
echo '<p>Your OpenVPN configuration file is contained within an archive along with a script for MacOS and Linux users.</p>
<p>
<form method="post" action="/ovpn">
  <input type="submit" name="download_ovpn" id="download_ovpn" class="btn btn-primary" value="Download Team Configuration">
</form>
</p>';

section_subhead('How to use OpenVPN');
echo '<p>To access most of the WACTF challenges you must connect to our VPN. Download the OpenVPN config file above and open the archive.</p>

<h2>Windows</h2>
<p>Your archive should look something like this:</p>
<img src="/img/vpn/windows/1.png" alt="vpn-windows" />
<p>Start OpenVPN if you haven\'t already (it opens into the tray). Right click the OpenVPN icon and select "Import file":</p>
<img src="/img/vpn/windows/2.png" alt="vpn-windows" />
<p>Find and select the OpenVPN configuration file with the word "windows" in the name:</p>
<img src="/img/vpn/windows/3.png" alt="vpn-windows" />
<p>It should import successfully like so:</p>
<img src="/img/vpn/windows/4.png" alt="vpn-windows" />
<p>Right click on the icon again, and select "Connect":</p>
<img src="/img/vpn/windows/5.png" alt="vpn-windows" />
<p>A window will appear momentarily while the VPN connect. It will dissapear after a few second:</p>
<img src="/img/vpn/windows/6.png" alt="vpn-windows" />
<p>Done! The OpenVPN icon should turn green and you might get a notification that says you are connected:</p>
<img src="/img/vpn/windows/7.png" alt="vpn-windows" />


<h2>MacOS</h2>
<p></p>
<p></p>

<h2>Kali Linux</h2>
<p>Once you\'ve installed OpenVPN, open a terminal and navigate to the directory containing your OpenVPN config file and the <pre>update_resolv_conf.sh</pre> script. Then run:</p>
<code>openvpn team-X-mac-linux.ovpn</code>
<p>This will execute the <pre>update_resolv_conf</pre> script too which is necessary for DNS to work.</p>
<p><strong>Note: If the script throws errors, reboot and try again.</strong></p>
<p>You should be good to go when you see the <pre>Initialization Sequence Completed</pre> message.</p>';

foot();