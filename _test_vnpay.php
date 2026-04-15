<?php
require_once 'configs/env.php';
require_once 'services/VNPayService.php';

$booking = ['id' => 130, 'total_price' => 144000000];
$orderInfo = 'Thanh toan tour BK000130';
$returnUrl = 'http://localhost/Quantri/?action=vnpay-return';

echo VNPayService::createPaymentUrl($booking, $orderInfo, $returnUrl);
