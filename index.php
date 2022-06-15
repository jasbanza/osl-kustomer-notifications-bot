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

$conversationId = $data['data']['id'];

// After conversation is created, it is assigned. so wait for 10 seconds and check who it is assigned to:
sleep(15);

$userId = conversation_get_assigned_userId($conversationId); // userId will be null if not assigned

if($userId){
  $displayName = get_displayName_from_userId($userId);

  tg_send_message("A new conversation has started!\n\nAssigned to: ".$displayName);
} else {
  tg_send_message("A new conversation has started!");
}


function conversation_get_assigned_userId($conversationId){
  global $config;
  $ch = curl_init();
  $url = 'https://api.kustomerapp.com/v1/conversations/'.$conversationId;

  curl_setopt($ch, CURLOPT_URL, $url);
  curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    'Accept: application/json',
    'Authorization: Bearer '.$config['KUSTOMER_API_KEY']
  ));

  $response = curl_exec($ch);
  if (curl_errno($ch)) {
      echo curl_error($ch);
  }
  curl_close($ch);

  $userId = null;
  $arrResponse = json_decode($response, true);

  if(isset($arrResponse['data']) && isset($arrResponse['data']['attributes']) && isset($arrResponse['data']['attributes']['assignedUsers']) && isset($arrResponse['data']['attributes']['assignedUsers'][0])){
    $userId = $arrResponse['data']['attributes']['assignedUsers'][0];
  }
  return $userId;
}

function get_displayName_from_userId($userId){
  global $config;
  $ch = curl_init();
  $url = 'https://api.kustomerapp.com/v1/users/'.$userId;

  curl_setopt($ch, CURLOPT_URL, $url);
  curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    'Accept: application/json',
    'Authorization: Bearer '.$config['KUSTOMER_API_KEY']
  ));

  $response = curl_exec($ch);
  if (curl_errno($ch)) {
      echo curl_error($ch);
  }
  curl_close($ch);

  $arrResponse = json_decode($response, true);
  $displayName = null;
  if(isset($arrResponse['data']) && isset($arrResponse['data']['attributes']) && isset($arrResponse['data']['attributes']['displayName'])){
    $displayName = $arrResponse['data']['attributes']['displayName'];
  }
  return $displayName;
}

/* Invoke Telegram Notification Bot */

function tg_bot_msg_post_body($chat_id, $text)
{
    return "chat_id=".$chat_id."&text=".$text;
}

function tg_send_message($msg_text){
  global $config;

  $telegram_chat_id = $config['TG_CHAT_ID'];
  $url = 'https://api.telegram.org/bot'.$config['TG_BOT_TOKEN'].'/sendMessage';
  $post_body = tg_bot_msg_post_body($telegram_chat_id, $msg_text);

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
}

/*
convo: 62a883d57f0b6f3ad9b2ad38
user:
{
    "id": "62a85b5df3cac1f924b02744",
    "name": "kustomer.conversation.create",
    "org": "61c24319fe91dd301449781a",
    "partition": "61c24319fe91dd301449781a",
    "data": {
        "type": "conversation",
        "id": "62a85b5950c96c031db652ea",
        "attributes": {
            "name": "Click here to speak to an OSL admin",
            "channels": [],
            "status": "open",
            "messageCount": 0,
            "noteCount": 0,
            "satisfaction": 0,
            "satisfactionLevel": {
                "sentByTeams": [],
                "answers": []
            },
            "createdAt": "2022-06-14T09:56:45.152Z",
            "updatedAt": "2022-06-14T09:56:45.152Z",
            "lastActivityAt": "2022-06-14T09:56:45.153Z",
            "spam": false,
            "ended": false,
            "tags": [],
            "suggestedTags": [],
            "predictions": [],
            "suggestedShortcuts": [],
            "firstMessageOut": {
                "createdByTeams": []
            },
            "assignedUsers": [
                "620c4386f1346613f49604a1"
            ],
            "assignedTeams": [],
            "firstResponse": {
                "createdByTeams": [],
                "assignedTeams": [],
                "assignedUsers": []
            },
            "firstResponseSinceLastDone": {
                "createdByTeams": [],
                "assignedTeams": [],
                "assignedUsers": []
            },
            "lastResponse": {
                "createdByTeams": [],
                "assignedTeams": [],
                "assignedUsers": []
            },
            "firstDone": {
                "createdByTeams": [],
                "assignedTeams": [],
                "assignedUsers": []
            },
            "lastDone": {
                "createdByTeams": [],
                "assignedTeams": [],
                "assignedUsers": []
            },
            "direction": "in",
            "outboundMessageCount": 0,
            "inboundMessageCount": 0,
            "rev": 1,
            "priority": 3,
            "defaultLang": "en_us",
            "locale": "US",
            "roleGroupVersions": [],
            "accessOverride": [],
            "assistant": {
                "fac": {
                    "reasons": []
                },
                "assistantId": [
                    "620c43865f0fe105ba29979b"
                ],
                "status": "running"
            },
            "phase": "excluded"
        },
        "relationships": {
            "messages": {
                "links": {
                    "self": "/v1/conversations/62a85b5950c96c031db652ea/messages"
                }
            },
            "org": {
                "links": {
                    "self": "/v1/orgs/61c24319fe91dd301449781a"
                },
                "data": {
                    "type": "org",
                    "id": "61c24319fe91dd301449781a"
                }
            },
            "customer": {
                "data": {
                    "type": "customer",
                    "id": "62a63293255450b4d870cf11"
                },
                "links": {
                    "self": "/v1/customers/62a63293255450b4d870cf11"
                }
            },
            "brand": {
                "data": {
                    "type": "brand",
                    "id": "61c243261402b84ceba21242"
                },
                "links": {
                    "self": "/v1/brands/61c243261402b84ceba21242"
                }
            }
        },
        "links": {
            "self": "/v1/conversations/62a85b5950c96c031db652ea"
        }
    },
    "createdAt": "2022-06-14T09:56:45.159Z",
    "persist": true
}


*/
