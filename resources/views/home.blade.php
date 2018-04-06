<?php

session_start();

$protocol = 'http://';
if (isset($_SERVER['HTTPS']) &&
    ($_SERVER['HTTPS'] == 'on' || $_SERVER['HTTPS'] == 1) ||
    isset($_SERVER['HTTP_X_FORWARDED_PROTO']) &&
    $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https') {
    $protocol = 'https://';
}
else {
    $protocol = 'http://';
}


//function redirect($url) {
//    ob_start();
//    header('Location: '.$url);
//    ob_end_flush();
//    die();
//}

/*
 * You can acquire an OAuth 2.0 client ID and client secret from the
 * {{ Google Cloud Console }} <{{ https://cloud.google.com/console }}>
 * For more information about using OAuth 2.0 to access Google APIs, please see:
 * <https://developers.google.com/youtube/v3/guides/authentication>
 * Please ensure that you have enabled the YouTube Data API for your project.
 */
$OAUTH2_CLIENT_ID = '598074830904-mcrdtbi7b6vs866c400k9fcqk5h0bgd4.apps.googleusercontent.com';
$OAUTH2_CLIENT_SECRET = 'z2v75CLEqjxdD9Pv0u7ddftD';

$client = new Google_Client();
$client->setClientId($OAUTH2_CLIENT_ID);
$client->setClientSecret($OAUTH2_CLIENT_SECRET);
$client->setScopes('https://www.googleapis.com/auth/youtube');
$redirect = filter_var($protocol . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'],
    FILTER_SANITIZE_URL);
$client->setRedirectUri($redirect);

// Define an object that will be used to make all API requests.
$youtube = new Google_Service_YouTube($client);

// Check if an auth token exists for the required scopes
$tokenSessionKey = 'token-' . $client->prepareScopes();
if (isset($_GET['code'])) {
  if (strval($_SESSION['state']) !== strval($_GET['state'])) {
    die('The session state did not match.');
  }

  $client->authenticate($_GET['code']);
  $_SESSION[$tokenSessionKey] = $client->getAccessToken();
  header('Location: ' . $redirect);
}

if (isset($_SESSION[$tokenSessionKey])) {
  $client->setAccessToken($_SESSION[$tokenSessionKey]);
}

// Check to ensure that the access token was successfully acquired.
if ($client->getAccessToken()) {
    $bcastsResponse = $youtube->liveBroadcasts->listLiveBroadcasts(
        'id,snippet',
        array(
            'broadcastStatus' => 'active'
        )
    );

    if (count($bcastsResponse['items']) > 0) {
        $item = $bcastsResponse['items'][0];
        $liveChatId = $item['snippet']['liveChatId'];
        $boundStreamId = $item['contentDetails']['boundStreamId'];

        $streamsResp = $youtube->liveStreams->listLiveStreams(
            'id,snippet',
            array(
                'id' => $boundStreamId
            )
        );
        $streamName = $streamsResp['cdn']['ingestionInfo']['streamName'];

        $url = filter_var(
            $protocol . $_SERVER['HTTP_HOST'] . "/watch?liveChatId=". $liveChatId . "&videoId=" . $streamName,
            FILTER_SANITIZE_URL);

        redirect($url);
    }
} elseif ($OAUTH2_CLIENT_ID == 'REPLACE_ME') {
  $htmlBody = <<<END
  <h3>Client Credentials Required</h3>
  <p>
    You need to set <code>\$OAUTH2_CLIENT_ID</code> and
    <code>\$OAUTH2_CLIENT_ID</code> before proceeding.
  <p>
END;
} else {
  // If the user hasn't authorized the app, initiate the OAuth flow
  $state = mt_rand();
  $client->setState($state);
  $_SESSION['state'] = $state;

  $authUrl = $client->createAuthUrl();
  $htmlBody = <<<END
  <h3>Google Authorization Required</h3>
  <p>You need to <a href="$authUrl">click here</a> to proceed.<p>
END;
}
?>

<!doctype html>
<html>
<head>
<title>My Live Broadcasts</title>
</head>
<body>
  <?=$htmlBody?>
</body>
</html>