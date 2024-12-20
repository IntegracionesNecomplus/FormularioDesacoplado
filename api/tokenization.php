<?php

    include '../config/functions.php';

    $transactionToken = $_POST['transactionToken'];

    $token = generateToken();
    $tokenization = generateTokenization($transactionToken, $token);

    echo json_encode($tokenization);

?>