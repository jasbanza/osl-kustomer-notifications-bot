<?php
    include 'config.php';
    ignore_user_abort(true);                       //very important!


    $conversationId = $_POST['conversationId'];

    if(!isset($_POST['conversationId'])){
      die('missing conversationId');
    }

    if(!isset($_POST['internalSecret'])){
      die('missing secret');
    }
    if($_POST['internalSecret'] != $config['INTERNAL_SECRET']){
      sleep(3);
      die('invalid secret');
    }


    $arrInfo = conversation_get_info($conversationId);

    if($arrInfo){
      sleep(10);
      $txt = "<i>⭐ A new conversation has started!</i>";

      // only include "admins" team
      if($arrInfo['assignedTeam'] == "61c25e3a92453f62dccbcaaf"){
        $isAssigned = false;
        if($arrInfo['assignedUser']){
          $isAssigned = true;
          $displayName = get_displayName_from_userId($arrInfo['assignedUser']);
          $txt = $txt."\nAgent: <b>".$displayName ."</b>";
        }

        $txt = $txt."\n\n<code>".$conversationId."</code>";
        tg_send_message($txt);

        if(!$isAssigned){
          check_assigned($conversationId,15,10);
        }
      }
    }
    // end TEMP

    //check_assigned($conversationId, 30, 10);

    function check_assigned($conversationId, $numAttempts, $retryTimeout){
      sleep($retryTimeout);
      // get assigned user id
      $userId = conversation_get_assigned_userId($conversationId); // userId will be null if not assigned

      // if assigned user id is found, proceed with name lookup
      if($userId){
        $displayName = get_displayName_from_userId($userId);
        tg_send_message("👤 Assigned to: <b>".$displayName."</b>\n\n<code>".$conversationId."</code>");
        exit;
      }

      // try again...
      if($numAttempts > 0){
        check_assigned($conversationId,--$numAttempts, $retryTimeout);
      }
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

    function conversation_get_info($conversationId){
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

      $arrResponse = json_decode($response, true);

      $arrReturn = ["assignedUser" => null, "assignedTeam" => null]; // to return
      if(isset($arrResponse['data']) && isset($arrResponse['data']['attributes'])){
        // assigned user
        if(isset($arrResponse['data']['attributes']['assignedUsers']) && isset($arrResponse['data']['attributes']['assignedUsers'][0])){
          $arrReturn['assignedUser'] = $arrResponse['data']['attributes']['assignedUsers'][0];
        }
        // assigned team
        if(isset($arrResponse['data']['attributes']['assignedTeams']) && isset($arrResponse['data']['attributes']['assignedTeams'][0])){
          $arrReturn['assignedTeam'] = $arrResponse['data']['attributes']['assignedTeams'][0];
        }
      }
      return $arrReturn;
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
      $url = 'https://api.telegram.org/bot'.$config['TG_BOT_TOKEN'].'/sendMessage?parse_mode=html';
      $post_body = tg_bot_msg_post_body($telegram_chat_id, $msg_text);
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

?>
