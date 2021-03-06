<?php
    require __DIR__ .  '/vendor/autoload.php';
    MercadoPago\SDK::setAccessToken("APP_USR-6317427424180639-042406-6aee9711d6bc4207c2dc79590031b6f0-469485398");

    $merchant_order = null;

    switch($_GET["topic"]) {
        case "payment":
            $payment = MercadoPago\Payment::find_by_id($_GET["id"]);
            // Get the payment and the corresponding merchant_order reported by the IPN.
            $merchant_order = MercadoPago\MerchantOrder::find_by_id($payment->order->id);
            break;
        case "merchant_order":
            $merchant_order = MercadoPago\MerchantOrder::find_by_id($_GET["id"]);
            break;
    }

    // Log
    $log  = " - topic: ".$_GET['topic'].PHP_EOL.
    " - id: ".$_GET['id'].PHP_EOL.
    " - preference_id: ".$merchant_order->preference_id.PHP_EOL.
    " - payment_id: ".$merchant_order->payments[0]->id.PHP_EOL.
    " - payment_status: ".$merchant_order->payments[0]->status.PHP_EOL.
    " - ".date("F j, Y, g:i a").PHP_EOL.
    "-------------------------------------------------------------------------------".PHP_EOL;
    file_put_contents('./log.txt', $log, FILE_APPEND);

    $paid_amount = 0;
    foreach ($merchant_order->payments as $payment) {
        if ($payment->status == 'approved'){
            $paid_amount += $payment->transaction_amount;
        }
    }

    // If the payment's transaction amount is equal (or bigger) than the merchant_order's amount you can release your items
    if($paid_amount >= $merchant_order->total_amount){
        if (count($merchant_order->shipments)>0) { // The merchant_order has shipments
            if($merchant_order->shipments[0]->status == "ready_to_ship") {
                print_r("Pago total realizado, realizar envío.");
            }
        } else { // The merchant_order don't has any shipments
            print_r("Pago total realizado.");
        }
    } else {
        print_r("Pago total no realizado.");
    }

    

?>