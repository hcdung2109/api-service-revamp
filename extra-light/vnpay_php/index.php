<?php
require_once(ABSPATH . "vnpay_php/config.php");
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
date_default_timezone_set('Asia/Ho_Chi_Minh');

$vnp_TxnRef = "";
if (isset($_REQUEST['order_id'])) {
    $vnp_TxnRef = $_REQUEST['order_id'];
}
$vnp_OrderInfo = "";
if (isset($_REQUEST['order_desc'])) {
    $vnp_OrderInfo = $_REQUEST['order_desc'];
}

$vnp_OrderType = "";
if (isset($_REQUEST['order_type'])) {
    $vnp_OrderType = $_REQUEST['order_type'];
}
$vnp_Amount = "";
if (isset($_REQUEST['amount'])) {
    $vnp_Amount = $_REQUEST['amount'] * 100;
}
$vnp_Locale = "vn";
if (isset($_REQUEST['language'])) {
    $vnp_Locale = $_REQUEST['language'];
}
$vnp_BankCode = "";
if (isset($_REQUEST['bank_code'])) {
    $vnp_BankCode = $_REQUEST['bank_code'];
}
$redirect = "";
if (isset($_REQUEST['redirect'])) {
    $redirect = $_REQUEST['redirect'];
}

$vnp_IpAddr = $_SERVER['REMOTE_ADDR'];
//Add Params of 2.0.1 Version
$vnp_ExpireDate = $expire;
//Billing
$vnp_Bill_Mobile = "";
$vnp_Bill_Email = "";
$fullName = "";
if (isset($fullName) && trim($fullName) != '') {
    $name = explode(' ', $fullName);
    $vnp_Bill_FirstName = array_shift($name);
    $vnp_Bill_LastName = array_pop($name);
}
$vnp_Bill_Address = "";
$vnp_Bill_City = "";
$vnp_Bill_Country = "";
$vnp_Bill_State = "";
// Invoice
$vnp_Inv_Phone = "";
$vnp_Inv_Email = "";
$vnp_Inv_Customer = "";
$vnp_Inv_Address = "";
$vnp_Inv_Company = "";
$vnp_Inv_Taxcode = "";
$vnp_Inv_Type = "";
$inputData = array(
    "vnp_Version" => "2.1.0",
    "vnp_Amount" => $vnp_Amount,
    "vnp_Command" => "pay",
    "vnp_CreateDate" => date('YmdHis'),
    "vnp_CurrCode" => "VND",
    "vnp_Locale" => "vn",
    "vnp_OrderInfo" => $vnp_OrderInfo,
    "vnp_OrderType" => "other",
    "vnp_ReturnUrl" => $vnp_Returnurl,
    "vnp_IpAddr" => $vnp_IpAddr,
    "vnp_TmnCode" => $vnp_TmnCode,
    "vnp_TxnRef" => $vnp_TxnRef,
    "vnp_ExpireDate" => $vnp_ExpireDate,
);

if (isset($vnp_BankCode) && $vnp_BankCode != "") {
    $inputData['vnp_BankCode'] = $vnp_BankCode;
}
if (isset($vnp_Bill_State) && $vnp_Bill_State != "") {
    $inputData['vnp_Bill_State'] = $vnp_Bill_State;
}

ksort($inputData);
$query = "";
$i = 0;
$hashdata = "";
foreach ($inputData as $key => $value) {
    if ($i == 1) {
        $hashdata .= '&' . urlencode($key) . "=" . urlencode($value);
    } else {
        $hashdata .= urlencode($key) . "=" . urlencode($value);
        $i = 1;
    }
    $query .= urlencode($key) . "=" . urlencode($value) . '&';
}

$vnp_Url = $vnp_Url . "?" . $query;

if (isset($vnp_HashSecret)) {
    $vnpSecureHash =   hash_hmac('sha512', $hashdata, $vnp_HashSecret); //  
    $vnp_Url .= 'vnp_SecureHash=' . $vnpSecureHash;
}

$returnData = array(
    'code' => '00', 'message' => 'success', 'data' => $vnp_Url
);
if ($redirect == "1") {
    header('Location: ' . $vnp_Url);
    die();
} else {
    echo json_encode($returnData);
}
