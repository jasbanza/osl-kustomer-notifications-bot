<?php
include 'config.php';
include 'db.php';

$json = file_get_contents('php://input');

$data_json = json_decode($json, true);
$data = [];

// request validation
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (strpos($_SERVER["CONTENT_TYPE"], "application/x-www-form-urlencoded") !== false) {
        $data = $_POST;
    } elseif (strpos($_SERVER["CONTENT_TYPE"], "application/json") !== false) {
        $data = $data_json;
    } else {
        die("Request failed. Expected HTTP POST 'CONTENT_TYPE' to be 'application/x-www-form-urlencoded' OR 'application/json'.");
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $data = $_GET;
} else {
    die("Request failed. Expected 'REQUEST_METHOD' to be 'POST' or 'GET'.");
}


//
// // authentication with secret
if (!isset($_SERVER["HTTP_X_KUSTOMER_WEBHOOK_EVENT"])) {
    die('Not from kustomer');
}
//
// if ($data["secret"] !== $config['ws_secret']) {
//     die('Secret incorrect or not provided!');
// }

// validation


/*
  Determine telegram chat_id, with the following priority:
  - 1) If "chat_id" is provided in request, use its value
  - 2) If "chat" (chat name) is provided in the config file, use its value,
        e.g. $config['telegram']['chat_ids']['<<<SOME CHAT NAME>>>'] = "-1231424125";
  - 3) cross reference device against config's chat_ids keys.
  - 4) use default chat_id if still not found.
*/

$telegram_chat_id = $config['TG_CHAT_ID'];

function tg_bot_msg_post_body($chat_id, $text)
{
    return "chat_id=".$chat_id."&text=".$text;
}

/* Invoke Telegram Notification Bot */
$url = 'https://api.telegram.org/bot'.$config['TG_BOT_TOKEN'].'/sendMessage';
$post_body = tg_bot_msg_post_body($telegram_chat_id, 'A new conversation has started!');

echo '<br><br>- Telegram bot: send message...';

echo '<br><br>- BOT API response:';
$ch = curl_init();

curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $post_body);

curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($ch);
if (curl_errno($ch)) {
    echo curl_error($ch);
}
curl_close($ch);
echo $response;
echo '<br><br>';
echo '- end';
