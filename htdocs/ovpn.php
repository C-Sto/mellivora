<?php

require('../include/mellivora.inc.php');

enforce_authentication();

if (isset($_POST['download_ovpn'])) {
  
  if (!is_numeric($_SESSION['id'])) {
    die('This shouldn\'t have happened. See an organiser.'); // bail out
  }

  // this is outside the web root
  $filename = Config::get('MELLIVORA_CONFIG_PATH_BASE') . DIRECTORY_SEPARATOR . 
  				'ovpn' . DIRECTORY_SEPARATOR . 
  				'team-' . $_SESSION['id'] . DIRECTORY_SEPARATOR . 
  				'team-' . $_SESSION['id'] . '_client.zip';

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
  <input type="submit" name="download_ovpn" id="download_ovpn" class="btn btn-primary" value="Download Your Teams Configuration Archive">
</form>
</p>';

section_subhead('Windows');
echo '<p>Start OpenVPN if you haven\'t already (it opens into the tray). Right click the OpenVPN icon and select "Import file":</p>
<img src="/img/vpn/windows/2.png" alt="vpn-windows" style="margin-bottom: 1em" />
<p>Find and select the OpenVPN configuration file with the word "windows" in the name:</p>
<img src="/img/vpn/windows/3.png" alt="vpn-windows" style="margin-bottom: 1em" />
<p>It should import successfully like so:</p>
<img src="/img/vpn/windows/4.png" alt="vpn-windows" style="margin-bottom: 1em" />
<p>Right click on the icon again, and select "Connect":</p>
<img src="/img/vpn/windows/5.png" alt="vpn-windows" style="margin-bottom: 1em" />
<p>A window will appear momentarily while the VPN connects. It will dissapear after a few second:</p>
<img src="/img/vpn/windows/6.png" alt="vpn-windows" style="margin-bottom: 1em" />
<p>Done! The OpenVPN icon should turn green and you might get a notification that says you are connected:</p>
<img src="/img/vpn/windows/7.png" alt="vpn-windows" style="margin-bottom: 1em" />
';

section_subhead('MacOS');
echo '<p>Start Tunnelblick if you haven\'t already. It might ask you for your computer password (<strong>not</strong> WACTF password) and then show you this screen. Click "I have configuration files" and then "OK":</p>
<img src="/img/vpn/mac/4.png" alt="vpn-mac" style="margin-bottom: 1em; max-width: 600px" />
<img src="/img/vpn/mac/5.png" alt="vpn-mac" style="margin-bottom: 1em; max-width: 600px" />
<p>Tunnelblick should open the configuration window, but if not, you can find it in the notification bar too:</p>
<img src="/img/vpn/mac/6.png" alt="vpn-mac" style="margin-bottom: 1em; max-width: 600px" />
<p>Find and drag the OpenVPN configuration file with the word "mac" in the name and click "Install":</p>
<img src="/img/vpn/mac/7.png" alt="vpn-mac" style="margin-bottom: 1em; max-width: 600px" />
<p>There will be several warnings... Don\'t worry about them:</p>
<img src="/img/vpn/mac/8.png" alt="vpn-mac" style="margin-bottom: 1em; max-width: 600px" />
<img src="/img/vpn/mac/9.png" alt="vpn-mac" style="margin-bottom: 1em; max-width: 600px" />
<p>Install the configuration for yourself and enter your computer password (<strong>not</strong> WACTF password) when prompted:</p>
<img src="/img/vpn/mac/10.png" alt="vpn-mac" style="margin-bottom: 1em; max-width: 600px" />
<img src="/img/vpn/mac/11.png" alt="vpn-mac" style="margin-bottom: 1em; max-width: 600px" />
<p>Finally, in the notification bar, click the configuration to connect to the VPN:</p>
<img src="/img/vpn/mac/12.png" alt="vpn-mac" style="margin-bottom: 1em; max-width: 600px" />
<p>Done! You should see a notification that says you are connected:</p>
<img src="/img/vpn/mac/13.png" alt="vpn-mac" style="margin-bottom: 1em; max-width: 600px" />
';

section_subhead('Kali Linux');
echo '<p>Once you\'ve installed OpenVPN, open a terminal and navigate to the directory containing your OpenVPN config file and the <code>update_resolv_conf.sh</code> script. Then run:</p>
<pre>
chmod +x update_resolv_conf.sh
openvpn team-X-mac-linux.ovpn
</pre>
<p>This will execute the <code>update_resolv_conf</code> script too which is necessary for DNS to work.</p>
<p><strong>Note: If the script throws errors, <code>reboot</code> and try again.</strong></p>
<p>You should be good to go when you see the message <code>Initialization Sequence Completed</code> appear in the terminal.</p>';

section_head('What Now?');
echo 'Maybe check out the Misc 1 challenge?';

foot();