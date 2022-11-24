<?php

function az_get_token()
{
  $c = cache_array_get(CONST_CACHE_NAME_AZTOK, 1800); //expiry is 1h, cache for 30 mins just in case
  if ($c === false) { //$c is a string when cache exists
    //Oauth dance
    try {
      $curl = curl_init();
      $tenant = Config::get('AZ_TENANT');
      $clientID = Config::get('AZ_CLIENT_ID');
      $clientSecret = Config::get('AZ_CLIENT_SECRET');
      $scope = Config::get('AZ_SCOPE');
      curl_setopt_array(
        $curl,
        array(
          CURLOPT_URL => "https://login.microsoftonline.com/$tenant/oauth2/v2.0/token",
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_SSL_VERIFYPEER => false,
          CURLOPT_ENCODING => "",
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 30,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => "POST",
          CURLOPT_POSTFIELDS => "grant_type=client_credentials&client_id=$clientID&client_secret=$clientSecret&scope=$scope",
          CURLOPT_HTTPHEADER => array(
            "content-type: application/x-www-form-urlencoded"
          ),
        )
      );
      $content = curl_exec($curl);
      //$err = curl_error($curl); //probs should actually handle the error here lol
      curl_close($curl);
    } catch (Exception $e) {
      message_inline_red('Caught exception getting Azure token');
      throw $e;
    }
    $d = json_decode($content);
    cache_array_save($d->access_token, CONST_CACHE_NAME_AZTOK);
    $c = $d->access_token;
  } else {
    //anything we do only when it's cached - maybe check the expiry or something to double check. Can't think of it right now, but I am not very smart
  }

  return $c;
}

function az_revert_env($teamid)
{
  //check status first, don't send req if status indicates it's reverting already
  $stats = az_get_team_status($teamid);
  //NULL means the status get failed, so same outcome as it being already requested. Don't send request.
  if ($stats !== NULL && $stats->status === 'Ready') {
    try {
      $curl = curl_init();
      $tok = az_get_token();
      $magicapi = Config::get('AZ_API_ADDR');
      curl_setopt_array(
        $curl,
        array(
          CURLOPT_URL => "https://$magicapi/api/Lab/$teamid/revert",
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => "",
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 30,
          CURLOPT_POSTFIELDS => "{}",
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => "POST",
          CURLOPT_HTTPHEADER => array(
            "content-type: application/json",
            "Authorization: Bearer " . $tok
          ),
        )
      );
      // curl_setopt($curl, CURLINFO_HEADER_OUT, true);
      // curl_setopt($curl, CURLOPT_VERBOSE, true);
      curl_exec($curl);
      //if we get a 202, life is good
      $rcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
      //message_inline_red(print_r(curl_getinfo($curl)));
      curl_close($curl);
      if ($rcode === 202) {
        return true;
      }

      return false;

    } catch (Exception $e) {
      message_inline_red('Caught exception reverting az env');
      throw $e;
    }
  }
}

function az_create_env($teamid)
{
  //check status first, don't send req if status indicates it's starting/started
  $stats = az_get_team_status($teamid);
  //NULL means the status get failed, so same outcome as it being already requested. Don't send request.
  if ($stats !== NULL && $stats === 'NotYetRequested') {
    try {
      $curl = curl_init();
      $tok = az_get_token();
      $magicapi = Config::get('AZ_API_ADDR');
      curl_setopt_array(
        $curl,
        array(
          CURLOPT_URL => "https://$magicapi/api/Lab/$teamid",
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => "",
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 30,
          CURLOPT_POSTFIELDS => "{}",
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => "POST",
          CURLOPT_HTTPHEADER => array(
            "content-type: application/json",
            "Authorization: Bearer " . $tok
          ),
        )
      );
      //curl_setopt($curl, CURLINFO_HEADER_OUT, true);
      //curl_setopt($curl, CURLOPT_VERBOSE, true);
      curl_exec($curl);
      //if we get a 202, life is good
      $rcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
      //message_inline_red(print_r(curl_getinfo($curl)));
      curl_close($curl);
      if ($rcode === 202) {
        return true;
      }

      return false;

    } catch (Exception $e) {
      message_inline_red('Caught exception creating az env');
      throw $e;
    }
  }
  //return false (unreachable)
}

function az_get_team_status($teamid)
{
  try {
    $curl = curl_init();
    $tok = az_get_token();
    $magicapi = Config::get('AZ_API_ADDR');
    curl_setopt_array(
      $curl,
      array(
        CURLOPT_URL => "https://$magicapi/api/Lab/$teamid",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "GET",
        CURLOPT_HTTPHEADER => array(
          "Authorization: Bearer " . $tok
        ),
      )
    );
    // curl_setopt($curl, CURLINFO_HEADER_OUT, true);
    // curl_setopt($curl, CURLOPT_VERBOSE, true);
    $content = curl_exec($curl);
    if (curl_getinfo($curl, CURLINFO_HTTP_CODE) === 403) {
      message_inline_red("status curl got 403, refreshing AZ token... refresh the page, and let an organiser know if this happens again");
      invalidate_cache(CONST_CACHE_NAME_AZTOK);
    }

    curl_close($curl);
    //message_inline_red(print_r(json_decode($content), true));
    return json_decode($content);

  } catch (Exception $e) {
    message_inline_red('Caught exception getting status');
    throw $e;
  }

  //return false (unreachable)
}