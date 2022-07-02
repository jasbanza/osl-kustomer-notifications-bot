<?php

/* needs the following permissions to lookup admin username: org.user.read, org.permission.user.read */

include 'config.php';

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
} else {
    die("Request failed. Expected 'REQUEST_METHOD' to be 'POST'");
}

// Check that it is from kustomer
if (!isset($_SERVER["HTTP_X_KUSTOMER_WEBHOOK_EVENT"])) {
    die('Not from kustomer');
}

$conversationId = $data['data']['id'];

$ch = curl_init();
$url = "https://".$config['SERVER']."/kustomer_notifications/processor.php";

curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_POST, true);
$post_body = array(
    'conversationId' => $conversationId,
    'internalSecret' => $config['INTERNAL_SECRET']
);

curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post_body));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_TIMEOUT_MS, $config['SPAWN_PROCESS_TIMOUT_MS']);      //just some very short timeout
curl_exec($ch);
curl_close($ch);
