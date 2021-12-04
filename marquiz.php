<?php

//marquiz.ru by Mufik v0.1 for Smartsender

ini_set('max_execution_time', '1700');
set_time_limit(1700);
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Content-Type: application/json; charset=utf-8');
http_response_code(200);

//------------------
$ssToken = "";
//------------------

function send_bearer($url, $token, $type = "GET", $param = []){
	
		
$descriptor = curl_init($url);

 curl_setopt($descriptor, CURLOPT_POSTFIELDS, json_encode($param));
 curl_setopt($descriptor, CURLOPT_RETURNTRANSFER, 1);
 curl_setopt($descriptor, CURLOPT_HTTPHEADER, array('User-Agent: M-Soft Integration', 'Content-Type: application/json', 'Authorization: Bearer '.$token)); 
 curl_setopt($descriptor, CURLOPT_CUSTOMREQUEST, $type);

    $itog = curl_exec($descriptor);
    curl_close($descriptor);

   		 return $itog;
		
}

$input = json_decode(file_get_contents('php://input'), TRUE); //convert JSON into array

if ($input["extra"]["href"] != NULL) {
    $allQuery = explode("&", explode("?", $input["extra"]["href"])[1]);
    if (is_array($allQuery)) {
        foreach ($allQuery as $oneQuery) {
            $tempQuery = explode("=", $oneQuery);
            if ($tempQuery[0] == "ssId") {
                $userId = $tempQuery[1];
            } else {
                $sendVar["values"][$tempQuery[0]] = $tempQuery[1];
            }
            unset($tempQuery);
        }
    }
}
if (is_array($input["answers"])) {
    foreach ($input["answers"] as $answer) {
        $sendVar["values"][$answer["q"]] = $answer["a"];
    }
}
if (is_array($input["contacts"])) {
    foreach ($input["contacts"] as $contactsKey => $contactsValue) {
        $sendVar["values"][$contactsKey] = $contactsValue;
    }
}
if (is_array($input["extra"]["cookies"])) {
    foreach ($input["extra"]["cookies"] as $cookieKey => $cookieValue) {
        $sendVar["values"][$cookieKey] = $cookieValue;
    }
}
if ($input["extra"]["href"] != NULL) {
    $sendVar["values"]["quiz_href"] = $input["extra"]["href"];
}
if ($input["extra"]["ip"] != NULL) {
    $sendVar["values"]["quiz_ip"] = $input["extra"]["ip"];
}
if ($input["quiz"]["name"] != NULL) {
    $sendVar["values"]["quiz_name"] = $input["quiz"]["name"];
}

if ($userId != NULL) {
    $sendResult = json_decode(send_bearer("https://api.smartsender.com/v1/contacts/".$userId, $ssToken, "PUT", $sendVar), true);
}
if ($sendResult["state"] !== true) {
    if ($input["contacts"]["email"] != NULL) {
        $searchContact = json_decode(send_bearer("https://api.smartsender.com/v1/contacts/search?page=1&limitation=20&term=".urlencode($input["contacts"]["email"]), $ssToken), true);
        if (is_array($searchContact["collection"])) {
            foreach ($searchContact["collection"] as $contacts) {
                if ($contacts["email"] == $input["contacts"]["email"]) {
                    $userId = $contacts["id"];
                    break;
                }
            }
        }
    }
    if ($userId != NULL) {
        $sendResult = json_decode(send_bearer("https://api.smartsender.com/v1/contacts/".$userId, $ssToken, "PUT", $sendVar), true);
    }
}
if ($sendResult["state"] !== true) {
    if ($input["contacts"]["phone"] != NULL) {
        $searchContact = json_decode(send_bearer("https://api.smartsender.com/v1/contacts/search?page=1&limitation=20&term=".urlencode($input["contacts"]["phone"]), $ssToken), true);
        if (is_array($searchContact["collection"])) {
            foreach ($searchContact["collection"] as $contacts) {
                if ($contacts["phone"] == $input["contacts"]["phone"]) {
                    $userId = $contacts["id"];
                    break;
                }
            }
        }
    }
    if ($userId != NULL) {
        $sendResult = json_decode(send_bearer("https://api.smartsender.com/v1/contacts/".$userId, $ssToken, "PUT", $sendVar), true);
    }
}


$result["state"] = true;

echo json_encode($result);




