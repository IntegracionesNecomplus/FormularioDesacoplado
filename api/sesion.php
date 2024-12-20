<?php

    include '../config/functions.php';

    $amount = $_GET['amount'];
    $channel = $_GET['channel'];

    $token = generateToken();
    $sesion = generateSesion($amount, $token, $channel);
    $purchaseNumber = generatePurchaseNumber();

    $data = array(
        "sesionKey" => $sesion,
        "merchantId" => VISA_MERCHANT_ID,
        "purchaseNumber" => $purchaseNumber,
        "amount" => $amount,
        "channel" => $channel
    );

    echo json_encode($data);

?>