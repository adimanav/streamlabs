<?php

session_start();

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
$redirect = filter_var('http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'],
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
$htmlBody = "";
// Check to ensure that the access token was successfully acquired.
if ($client->getAccessToken()) {

    if (isset($_GET['videoId'])) {
        try {
                $src="https://www.youtube.com/embed/" . $_GET['videoId'];
                $htmlBody .= <<<END
                <iframe width="420" height="315"
                src="$src">
                </iframe>
END;
        } catch (Google_Service_Exception $e) {
        $htmlBody .= sprintf('<p>A service error occurred: <code>%s</code></p>',
        htmlspecialchars($e->getMessage()));
        } catch (Google_Exception $e) {
        $htmlBody .= sprintf('<p>An client error occurred: <code>%s</code></p>',
        htmlspecialchars($e->getMessage()));
        }
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
  <h3>Authorization Required</h3>
  <p>You need to <a href="$authUrl">authorize access</a> before proceeding.<p>
END;
}
?>

<!doctype html>
<html>
<head>
<title>My Live Broadcasts</title>
    <script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1/jquery.min.js"></script>
    <script type="text/javascript">
        var instanse = false;
        var nextPageToken = "";
        var pollingIntervalMillis = 3000;
        var currdata;

        function updateChat(){
            if(!instanse){
                instanse = true;
                $.get("api/listmessages/<?php echo $_GET['liveChatId']; ?>/" + nextPageToken, function (data, status) {
                    if (status == 'success') {
                        currdata = data;
                        nextPageToken = data['nextPageToken'];
                        var items = data['items'];
                        for (var i = 0; i < items.length; i++) {
                            var item = items[i];
                            $('#chat-area').append($("<p>" + item['authorChannelId'] + ": " + item['messageText'] + "</p>"));
                        }
                        document.getElementById('chat-area').scrollTop = document.getElementById('chat-area').scrollHeight;
                    }
                    instanse = false;
                }, "json");
            }
            else {
                setTimeout(updateChat, pollingIntervalMillis);
            }
        }

    </script>
</head>
<body onload="setInterval('updateChat()', pollingIntervalMillis)">
    <table>
        <tr>
            <td>
  <?=$htmlBody?>
            </td>
            <td>
  <div id="page-wrap">

      <div id="chat-wrap"><div id="chat-area"></div></div>

  </div>
            </td>
        </tr>
    </table>
</body>
</html>