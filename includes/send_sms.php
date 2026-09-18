<?php

function sendSMS($number,$message){

$apiKey = "INkhM8GH7utUbqv3EWYDj9clLaPBCso1mxSXFzr6inZAd2Tp5wI36nlyK1LVivoASfGPu9YrMeO0kcDs";

$fields = array(
    "sender_id" => "FSTSMS",
    "message" => $message,
    "language" => "english",
    "route" => "p",
    "numbers" => $number,
);

$curl = curl_init();

curl_setopt_array($curl, array(
    CURLOPT_URL => "https://www.fast2sms.com/dev/bulkV2",
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => json_encode($fields),
    CURLOPT_HTTPHEADER => array(
        "authorization: $apiKey",
        "accept: */*",
        "cache-control: no-cache",
        "content-type: application/json"
    ),
));

$response = curl_exec($curl);
curl_close($curl);

return $response;
}
?>
