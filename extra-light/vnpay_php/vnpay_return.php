<?php
require_once(ABSPATH."vnpay_php/config.php");  
require_once(ABSPATH.'api/Wallet.php' );
require_once(ABSPATH.'api/Sale.php' );

$vnp_SecureHash = $_GET['vnp_SecureHash'];
$inputData = array();
foreach ($_GET as $key => $value) {
	if (substr($key, 0, 4) == "vnp_") {
		$inputData[$key] = $value;
	}
}

unset($inputData['vnp_SecureHash']);
ksort($inputData);
$i = 0;
$hashData = "";
foreach ($inputData as $key => $value) {
	if ($i == 1) {
		$hashData = $hashData . '&' . urlencode($key) . "=" . urlencode($value);
	} else {
		$hashData = $hashData . urlencode($key) . "=" . urlencode($value);
		$i = 1;
	}
}

$secureHash = hash_hmac('sha512', $hashData, $vnp_HashSecret);

$vnp_TxnRef = $_GET['vnp_TxnRef'];
$vnp_Amount = $_GET['vnp_Amount'];
$vnp_OrderInfo = $_GET['vnp_OrderInfo'];
$vnp_ResponseCode = $_GET['vnp_ResponseCode'];
$vnp_TransactionNo = $_GET['vnp_TransactionNo'];
$vnp_BankCode = $_GET['vnp_BankCode'];
$vnp_PayDate = $_GET['vnp_PayDate'];
$vnp_PayDate = $_GET['vnp_PayDate'];
if ($secureHash == $vnp_SecureHash) 
{
	if ($_GET['vnp_ResponseCode'] == '00') 
	{
		$arr = $appSession->getTool()->split($vnp_TxnRef, '_');
		if(count($arr) > 2)
		{
			$wallet = new Wallet($appSession);
			if($arr[1] == "WALLET")
			{
				
				$wallet->add($arr[2], $arr[3],$vnp_Amount/100, $vnp_TransactionNo , $vnp_OrderInfo." - ".$vnp_BankCode);
				
				header('Location: '.URl.'account/account/?type=wallet');
				exit();
			}else if($arr[0] == "APP_ORDER" || $arr[0] == "WEB_ORDER")
			{
				$amount = $vnp_Amount/100;
				$sale_id = $arr[2];
				$builder = $appSession->getTier()->createBuilder("account_payment_line_local");
				$builder->setName("account_payment_line_local");
				$user_id = $appSession->getConfig()->getProperty("user_id");
				$builder->add("id", $appSession->getTool()->getId());
				$builder->add("create_uid", $user_id);
				$builder->add("write_uid", $user_id);
				$builder->add("create_date", $appSession->getTier()->getDateString(), 'f');
				$builder->add("write_date", $appSession->getTier()->getDateString(), 'f');
				$builder->add("line_id", $sale_id);
				$builder->add("payment_id", $vnp_BankCode);
				$builder->add("currency_id", "23");
				$builder->add("rel_id", $sale_id);
				$builder->add("receipt_no", $appSession->getTool()->findReceiptNo($appSession->getTier(), $appSession->getConfig()->getProperty("company_id"), "account_payment_line_local"));
				$builder->add("receipt_date", $appSession->getTier()->getDateString(), 'f');
				$builder->add("amount", $amount);
				$builder->add("status", 0);
				$builder->add("description", $vnp_TransactionNo);
				$builder->add("company_id", $appSession->getConfig()->getProperty("company_id"));
				
				$sql = $appSession->getTier()->getInsert($builder);
				$msg->add("query", $sql);
				$appSession->getTier()->exec($msg);
				if($arr[0] == "WEB_ORDER")
				{
					echo "<script>window.opener.location.reload(); window.close();</script>";
					exit();
				}
			}
		}
		echo "OK";
	} else {
		echo "GD Khong thanh cong";
	}
}else 
{
	echo "Chu ky khong hop le";
}
?>