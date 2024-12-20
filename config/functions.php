<?php
    include 'config.inc.php';

    function generateToken() {
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => VISA_URL_SECURITY,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_HTTPHEADER => array(
            "Accept: */*",
            'Authorization: '.'Basic '.base64_encode(VISA_USER.":".VISA_PWD)
            ),
        ));
        $response = curl_exec($curl);
        curl_close($curl);
        return $response;
    }

    function generateSesion($amount, $token, $channel) {
        $session = array(
            'channel' => $channel,
            'amount' => $amount,
            'antifraud' => array(
                'clientIp' => $_SERVER['REMOTE_ADDR'],
                'merchantDefineData' => array(
                    'MDD4' => 'integraciones@necomplus.com',
                    'MDD32' => '0171225',
                    'MDD75' => 'Registrado',
                    'MDD77' => 178
                ),
            ),
            'dataMap' => array(
                'cardholderCity' => 'Lima',
                'cardholderCountry' => 'PE',
                'cardholderAddress' => 'Av Principal A-5. Campoy',
                'cardholderPostalCode' => '15046',
                'cardholderState' => 'LIM',
                'cardholderPhoneNumber' => '986322205'
            ),
        );
        $json = json_encode($session);
        $response = json_decode(postRequest(VISA_URL_SESSION, $json, $token));
        return $response->sessionKey;
    }

    function generateAuthorization($amount, $purchaseNumber, $transactionToken, $token) {
        $data = array(
            'captureType' => 'manual',
            'channel' => VISA_CHANNEL,
            'countable' => true,
            'order' => array(
                'amount' => $amount,
                'currency' => VISA_CURRENCY,
                'purchaseNumber' => $purchaseNumber,
                'tokenId' => $transactionToken
            ),
            'dataMap' => array(
                'urlAddress' => 'https://desarrolladores.niubiz.com.pe/',
                'partnerIdCode' => '',
                'serviceLocationCityName' => 'LIMA',
                'serviceLocationCountrySubdivisionCode' => 'LIMA',
                'serviceLocationCountryCode' => 'PER',
                'serviceLocationPostalCode' => '15074'
            )
        );
        $json = json_encode($data);
        $session = json_decode(postRequest(VISA_URL_AUTHORIZATION, $json, $token));
        return $session;
    }

    function postRequest($url, $postData, $token) {
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_HTTPHEADER => array(
                'Authorization: '.$token,
                'Content-Type: application/json'
            ),
            CURLOPT_POSTFIELDS => $postData
        ));
        $response = curl_exec($curl);
        curl_close($curl);
        return $response;
    }

    function generatePurchaseNumber(){
        return rand(1, 999999999999);
    }

    function generateTokenization($transactionToken, $token) {
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => VISA_URL_TOKENIZATION.$transactionToken,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_HTTPHEADER => array(
                'Authorization: '.$token,
                'Content-Type: application/json'
            ),
        ));
        $response = curl_exec($curl);
        curl_close($curl);
        return $response;
    }