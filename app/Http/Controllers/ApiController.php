<?php
namespace App\Http\Controllers;

use app\LiveChatMessage;
 
class ApiController extends Controller
{
    public function listMessages($liveChatId, $pageToken="")
    {
        $result = array("message" => "hi");

//        $OAUTH2_CLIENT_ID = '598074830904-mcrdtbi7b6vs866c400k9fcqk5h0bgd4.apps.googleusercontent.com';
//        $OAUTH2_CLIENT_SECRET = 'z2v75CLEqjxdD9Pv0u7ddftD';
//
//        $client = new Google_Client();
//        $client->setClientId($OAUTH2_CLIENT_ID);
//        $client->setClientSecret($OAUTH2_CLIENT_SECRET);
//        $client->setScopes('https://www.googleapis.com/auth/youtube');
//        $redirect = filter_var('http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'],
//            FILTER_SANITIZE_URL);
//        $client->setRedirectUri($redirect);
//
//        // Define an object that will be used to make all API requests.
//        $youtube = new Google_Service_YouTube($client);
//
//        $tokenSessionKey = 'token-' . $client->prepareScopes();
//        if (isset($_SESSION[$tokenSessionKey])) {
//            $client->setAccessToken($_SESSION[$tokenSessionKey]);
//        }
//
//        // Check to ensure that the access token was successfully acquired.
//        if ($client->getAccessToken()) {
//            $arr = array();
//            if ($pageToken !== "") {
//                $arr['pageToken'] = $pageToken;
//            }
//            $response = $youtube->liveChatMessages->listLiveChatMessages(
//                $liveChatId, 'id,snippet', $arr);
//
//            $result = [
//                'nextPageToken' => $response['nextPageToken'],
//                'pollingIntervalMillis' => $response['pollingIntervalMillis'],
//                'items' => array()
//            ];
//
//            $i = 0;
//            foreach ($response['items'] as $responseItem) {
//                $msg = array(
//                    'id' => $responseItem['id'],
//                    'liveChatId' => $responseItem['snippet']['liveChatId'],
//                    'authorChannelId' => $responseItem['snippet']['authorChannelId'],
//                    'messageText' => $responseItem['textMessageDetails']['messageText']
//                );
//                $result['items'][$i] = $msg;
//            }
//        }

        return response()->json($result);
    }
}
?>