<?php
// require_once __DIR__ . '/../app/util/Response.php';

class Company
{
	// public function create_headhunting_agency($appSession, $commercial_name, $name, $phone, $email, $user_name, $password, $lang_id)
	// {
	// 	$msg = $appSession->getTier()->createMessage();
	// 	$message = [];

	// 	$sql = "SELECT id, phone, email, user_name FROM res_user WHERE (user_name='" . str_replace("'", "''", $user_name) . "'";
	// 	if ($email != "") {
	// 		$sql = $sql . " OR email = '" . str_replace("'", "''", $email) . "'";
	// 	}
	// 	if ($phone != "") {
	// 		$sql = $sql . " OR phone = '" . str_replace("'", "''", $phone) . "'";
	// 	}
	// 	$sql = $sql . ")";
	// 	$msg->add("query", $sql);

	// 	$result = $appSession->getTier()->getTable($msg);
	// 	$numrows = $result->getRowCount();

	// 	if ($numrows > 0) {
	// 		$row = $result->getRow(0);
	// 		if ($row->getString("user_name") == $user_name) {
	// 			$message = [
	// 				'status' => false,
	// 				'message' => "Tên tài khoản của bạn đã tồn tại."
	// 			];
	// 		} else if ($row->getString("email") != "" && $row->getString("email") == $email) {
	// 			$message = [
	// 				'status' => false,
	// 				'message' => "Email của bạn đã tồn tại."
	// 			];
	// 		} else if ($row->getString("phone") != "" && $row->getString("phone") == $phone) {
	// 			$message = [
	// 				'status' => false,
	// 				'message' => "Số điện thoại của bạn đã tồn tại."
	// 			];
	// 		}
	// 	} else {
	// 		$user_id = $appSession->getTool()->getId();
	// 		$s = $appSession->getTool()->toHash("sha256", "[" . $user_id . "]" . $password);
	// 		$len = $appSession->getTool()->lenght($password);
	// 		for ($i = 0; $i < $len; $i++) {
	// 			$s = $s . chr($i + 48);
	// 		}
	// 		$password = $appSession->getTool()->toHash("md5", $s);
	// 		$company_id = $appSession->getTool()->getId();

	// 		$sql = "INSERT INTO res_user(";
	// 		$sql = $sql . "id";
	// 		$sql = $sql . ", company_id";
	// 		$sql = $sql . ", status";
	// 		$sql = $sql . ", name";
	// 		$sql = $sql . ", user_name";
	// 		$sql = $sql . ", password";
	// 		$sql = $sql . ", email";
	// 		$sql = $sql . ", phone";
	// 		$sql = $sql . ", thousands_sep";
	// 		$sql = $sql . ", decimal_point";
	// 		$sql = $sql . ", date_format";
	// 		$sql = $sql . ", create_date";
	// 		$sql = $sql . ", write_date";
	// 		$sql = $sql . ", create_uid";
	// 		$sql = $sql . ", write_uid";
	// 		$sql = $sql . ", lang_id";
	// 		$sql = $sql . ", actived";
	// 		$sql = $sql . " )VALUES(";
	// 		$sql = $sql . "'" . $user_id . "'";
	// 		$sql = $sql . ", '" . $company_id . "'";
	// 		$sql = $sql . ", 0";
	// 		$sql = $sql . ", '" . str_replace("'", "''", $name) . "'";
	// 		$sql = $sql . ", '" . str_replace("'", "''", $user_name) . "'";
	// 		$sql = $sql . ", '" . str_replace("'", "''", $password) . "'";
	// 		$sql = $sql . ", '" . str_replace("'", "''", $email) . "'";
	// 		$sql = $sql . ", '" . str_replace("'", "''", $phone) . "'";
	// 		$sql = $sql . ", ','";
	// 		$sql = $sql . ", '.'";
	// 		$sql = $sql . ", 'YYYY-MM-DD'";
	// 		$sql = $sql . ", '" . $appSession->getTier()->getDateString() . "'";
	// 		$sql = $sql . ", '" . $appSession->getTier()->getDateString() . "'";
	// 		$sql = $sql . ", '" . $user_id . "'";
	// 		$sql = $sql . ", '" . $user_id . "'";
	// 		$sql = $sql . ", '" . $lang_id . "'";
	// 		$sql = $sql . ", 0";
	// 		$sql = $sql . ")";
	// 		$msg->add("query", $sql);
	// 		$result = $appSession->getTier()->exec($msg);

	// 		$sql = "INSERT INTO res_company(";
	// 		$sql = $sql . "id";
	// 		$sql = $sql . ", company_id";
	// 		$sql = $sql . ", parent_id";
	// 		$sql = $sql . ", status";
	// 		$sql = $sql . ", name";
	// 		$sql = $sql . ", commercial_name";
	// 		$sql = $sql . ", phone";
	// 		$sql = $sql . ", email";
	// 		$sql = $sql . ", create_date";
	// 		$sql = $sql . ", write_date";
	// 		$sql = $sql . ", create_uid";
	// 		$sql = $sql . ", write_uid";
	// 		$sql = $sql . " )VALUES(";
	// 		$sql = $sql . "	'" . $company_id . "'";
	// 		$sql = $sql . ", '" . $company_id . "'";
	// 		$sql = $sql . ", 'ROOT'";
	// 		$sql = $sql . ", 0";
	// 		$sql = $sql . ", '" . str_replace("'", "''", $name) . "'";
	// 		$sql = $sql . ", '" . str_replace("'", "''", $commercial_name) . "'";
	// 		$sql = $sql . ", '" . str_replace("'", "''", $phone) . "'";
	// 		$sql = $sql . ", '" . str_replace("'", "''", $email) . "'";
	// 		$sql = $sql . ", '" . $appSession->getTier()->getDateString() . "'";
	// 		$sql = $sql . ", '" . $appSession->getTier()->getDateString() . "'";
	// 		$sql = $sql . ", '" . $user_id . "'";
	// 		$sql = $sql . ", '" . $user_id . "'";
	// 		$sql = $sql . ")";

	// 		$msg->add("query", $sql);
	// 		$result = $appSession->getTier()->exec($msg);

	// 		$id = $appSession->getTool()->getId();
	// 		$sql = "INSERT INTO res_user_company(";
	// 		$sql = $sql . "id";
	// 		$sql = $sql . ", company_id";
	// 		$sql = $sql . ", status";
	// 		$sql = $sql . ", user_id";
	// 		$sql = $sql . ", group_id";
	// 		$sql = $sql . ", create_date";
	// 		$sql = $sql . ", write_date";
	// 		$sql = $sql . ", create_uid";
	// 		$sql = $sql . ", write_uid";
	// 		$sql = $sql . " )VALUES(";
	// 		$sql = $sql . "'" . $user_id . "'";
	// 		$sql = $sql . ", '" . $company_id . "'";
	// 		$sql = $sql . ", 0";
	// 		$sql = $sql . ", '" . $user_id . "'";
	// 		$sql = $sql . ", 'daabee1b-58a6-4a37-af3c-9f42bf20aad0'";
	// 		$sql = $sql . ", '" . $appSession->getTier()->getDateString() . "'";
	// 		$sql = $sql . ", '" . $appSession->getTier()->getDateString() . "'";
	// 		$sql = $sql . ", '" . $user_id . "'";
	// 		$sql = $sql . ", '" . $user_id . "'";
	// 		$sql = $sql . ")";
	// 		$msg->add("query", $sql);
	// 		$result = $appSession->getTier()->exec($msg);

	// 		$message = [
	// 			'status' => true,
	// 			'message' => "Đăng ký tài khoản thành công."
	// 		];
	// 	}

	// 	return json_encode($message);
	// }

	// public function create_hr_internal_team($appSession, $commercial_name, $name, $phone, $email, $user_name, $password, $lang_id)
	// {
	// 	$msg = $appSession->getTier()->createMessage();
	// 	$message = [];

	// 	$sql = "SELECT id, phone, email, user_name FROM res_user WHERE (user_name='" . str_replace("'", "''", $user_name) . "'";
	// 	if ($email != "") {
	// 		$sql = $sql . " OR email = '" . str_replace("'", "''", $email) . "'";
	// 	}
	// 	if ($phone != "") {
	// 		$sql = $sql . " OR phone = '" . str_replace("'", "''", $phone) . "'";
	// 	}
	// 	$sql = $sql . ")";
	// 	$msg->add("query", $sql);

	// 	$result = $appSession->getTier()->getTable($msg);
	// 	$numrows = $result->getRowCount();

	// 	if ($numrows > 0) {
	// 		$row = $result->getRow(0);
	// 		if ($row->getString("user_name") == $user_name) {
	// 			$message = [
	// 				'status' => false,
	// 				'message' => "Tên tài khoản của bạn đã tồn tại."
	// 			];
	// 		} else if ($row->getString("email") != "" && $row->getString("email") == $email) {
	// 			$message = [
	// 				'status' => false,
	// 				'message' => "Email của bạn đã tồn tại."
	// 			];
	// 		} else if ($row->getString("phone") != "" && $row->getString("phone") == $phone) {
	// 			$message = [
	// 				'status' => false,
	// 				'message' => "Số điện thoại của bạn đã tồn tại."
	// 			];
	// 		}
	// 	} else {
	// 		$user_id = $appSession->getTool()->getId();
	// 		$s = $appSession->getTool()->toHash("sha256", "[" . $user_id . "]" . $password);
	// 		$len = $appSession->getTool()->lenght($password);
	// 		for ($i = 0; $i < $len; $i++) {
	// 			$s = $s . chr($i + 48);
	// 		}
	// 		$password = $appSession->getTool()->toHash("md5", $s);
	// 		$company_id = $appSession->getTool()->getId();

	// 		$sql = "INSERT INTO res_user(";
	// 		$sql = $sql . "id";
	// 		$sql = $sql . ", company_id";
	// 		$sql = $sql . ", status";
	// 		$sql = $sql . ", name";
	// 		$sql = $sql . ", user_name";
	// 		$sql = $sql . ", password";
	// 		$sql = $sql . ", email";
	// 		$sql = $sql . ", phone";
	// 		$sql = $sql . ", thousands_sep";
	// 		$sql = $sql . ", decimal_point";
	// 		$sql = $sql . ", date_format";
	// 		$sql = $sql . ", create_date";
	// 		$sql = $sql . ", write_date";
	// 		$sql = $sql . ", create_uid";
	// 		$sql = $sql . ", write_uid";
	// 		$sql = $sql . ", lang_id";
	// 		$sql = $sql . ", actived";
	// 		$sql = $sql . " )VALUES(";
	// 		$sql = $sql . "'" . $user_id . "'";
	// 		$sql = $sql . ", '" . $company_id . "'";
	// 		$sql = $sql . ", 0";
	// 		$sql = $sql . ", '" . str_replace("'", "''", $name) . "'";
	// 		$sql = $sql . ", '" . str_replace("'", "''", $user_name) . "'";
	// 		$sql = $sql . ", '" . str_replace("'", "''", $password) . "'";
	// 		$sql = $sql . ", '" . str_replace("'", "''", $email) . "'";
	// 		$sql = $sql . ", '" . str_replace("'", "''", $phone) . "'";
	// 		$sql = $sql . ", ','";
	// 		$sql = $sql . ", '.'";
	// 		$sql = $sql . ", 'YYYY-MM-DD'";
	// 		$sql = $sql . ", '" . $appSession->getTier()->getDateString() . "'";
	// 		$sql = $sql . ", '" . $appSession->getTier()->getDateString() . "'";
	// 		$sql = $sql . ", '" . $user_id . "'";
	// 		$sql = $sql . ", '" . $user_id . "'";
	// 		$sql = $sql . ", '" . $lang_id . "'";
	// 		$sql = $sql . ", 0";
	// 		$sql = $sql . ")";
	// 		$msg->add("query", $sql);
	// 		$result = $appSession->getTier()->exec($msg);

	// 		$sql = "INSERT INTO res_company(";
	// 		$sql = $sql . "id";
	// 		$sql = $sql . ", company_id";
	// 		$sql = $sql . ", parent_id";
	// 		$sql = $sql . ", status";
	// 		$sql = $sql . ", name";
	// 		$sql = $sql . ", commercial_name";
	// 		$sql = $sql . ", phone";
	// 		$sql = $sql . ", email";
	// 		$sql = $sql . ", create_date";
	// 		$sql = $sql . ", write_date";
	// 		$sql = $sql . ", create_uid";
	// 		$sql = $sql . ", write_uid";
	// 		$sql = $sql . " )VALUES(";
	// 		$sql = $sql . "	'" . $company_id . "'";
	// 		$sql = $sql . ", '" . $company_id . "'";
	// 		$sql = $sql . ", 'ROOT'";
	// 		$sql = $sql . ", 0";
	// 		$sql = $sql . ", '" . str_replace("'", "''", $name) . "'";
	// 		$sql = $sql . ", '" . str_replace("'", "''", $commercial_name) . "'";
	// 		$sql = $sql . ", '" . str_replace("'", "''", $phone) . "'";
	// 		$sql = $sql . ", '" . str_replace("'", "''", $email) . "'";
	// 		$sql = $sql . ", '" . $appSession->getTier()->getDateString() . "'";
	// 		$sql = $sql . ", '" . $appSession->getTier()->getDateString() . "'";
	// 		$sql = $sql . ", '" . $user_id . "'";
	// 		$sql = $sql . ", '" . $user_id . "'";
	// 		$sql = $sql . ")";

	// 		$msg->add("query", $sql);
	// 		$result = $appSession->getTier()->exec($msg);

	// 		$id = $appSession->getTool()->getId();
	// 		$sql = "INSERT INTO res_user_company(";
	// 		$sql = $sql . "id";
	// 		$sql = $sql . ", company_id";
	// 		$sql = $sql . ", status";
	// 		$sql = $sql . ", user_id";
	// 		$sql = $sql . ", group_id";
	// 		$sql = $sql . ", create_date";
	// 		$sql = $sql . ", write_date";
	// 		$sql = $sql . ", create_uid";
	// 		$sql = $sql . ", write_uid";
	// 		$sql = $sql . " )VALUES(";
	// 		$sql = $sql . "'" . $user_id . "'";
	// 		$sql = $sql . ", '" . $company_id . "'";
	// 		$sql = $sql . ", 0";
	// 		$sql = $sql . ", '" . $user_id . "'";
	// 		$sql = $sql . ", '72d2d2d8-437f-408d-af4c-54c909b575a9'";
	// 		$sql = $sql . ", '" . $appSession->getTier()->getDateString() . "'";
	// 		$sql = $sql . ", '" . $appSession->getTier()->getDateString() . "'";
	// 		$sql = $sql . ", '" . $user_id . "'";
	// 		$sql = $sql . ", '" . $user_id . "'";
	// 		$sql = $sql . ")";
	// 		$msg->add("query", $sql);
	// 		$result = $appSession->getTier()->exec($msg);

	// 		$message = [
	// 			'status' => true,
	// 			'message' => "Đăng ký tài khoản thành công."
	// 		];
	// 	}

	// 	return json_encode($message);
	// }

	// public function create_freelancer($appSession, $commercial_name, $name, $phone, $email, $user_name, $password, $lang_id)
	// {
	// 	$msg = $appSession->getTier()->createMessage();
	// 	$message = [];

	// 	$sql = "SELECT id, phone, email, user_name FROM res_user WHERE (user_name='" . str_replace("'", "''", $user_name) . "'";
	// 	if ($email != "") {
	// 		$sql = $sql . " OR email = '" . str_replace("'", "''", $email) . "'";
	// 	}
	// 	if ($phone != "") {
	// 		$sql = $sql . " OR phone = '" . str_replace("'", "''", $phone) . "'";
	// 	}
	// 	$sql = $sql . ")";
	// 	$msg->add("query", $sql);

	// 	$result = $appSession->getTier()->getTable($msg);
	// 	$numrows = $result->getRowCount();

	// 	if ($numrows > 0) {
	// 		$row = $result->getRow(0);
	// 		if ($row->getString("user_name") == $user_name) {
	// 			$message = [
	// 				'status' => false,
	// 				'message' => "Tên tài khoản của bạn đã tồn tại."
	// 			];
	// 		} else if ($row->getString("email") != "" && $row->getString("email") == $email) {
	// 			$message = [
	// 				'status' => false,
	// 				'message' => "Email của bạn đã tồn tại."
	// 			];
	// 		} else if ($row->getString("phone") != "" && $row->getString("phone") == $phone) {
	// 			$message = [
	// 				'status' => false,
	// 				'message' => "Số điện thoại của bạn đã tồn tại."
	// 			];
	// 		}
	// 	} else {
	// 		$user_id = $appSession->getTool()->getId();
	// 		$s = $appSession->getTool()->toHash("sha256", "[" . $user_id . "]" . $password);
	// 		$len = $appSession->getTool()->lenght($password);
	// 		for ($i = 0; $i < $len; $i++) {
	// 			$s = $s . chr($i + 48);
	// 		}
	// 		$password = $appSession->getTool()->toHash("md5", $s);
	// 		$company_id = $appSession->getTool()->getId();

	// 		$sql = "INSERT INTO res_user(";
	// 		$sql = $sql . "id";
	// 		$sql = $sql . ", company_id";
	// 		$sql = $sql . ", status";
	// 		$sql = $sql . ", name";
	// 		$sql = $sql . ", user_name";
	// 		$sql = $sql . ", password";
	// 		$sql = $sql . ", email";
	// 		$sql = $sql . ", phone";
	// 		$sql = $sql . ", thousands_sep";
	// 		$sql = $sql . ", decimal_point";
	// 		$sql = $sql . ", date_format";
	// 		$sql = $sql . ", create_date";
	// 		$sql = $sql . ", write_date";
	// 		$sql = $sql . ", create_uid";
	// 		$sql = $sql . ", write_uid";
	// 		$sql = $sql . ", lang_id";
	// 		$sql = $sql . ", actived";
	// 		$sql = $sql . " )VALUES(";
	// 		$sql = $sql . "'" . $user_id . "'";
	// 		$sql = $sql . ", '" . $company_id . "'";
	// 		$sql = $sql . ", 0";
	// 		$sql = $sql . ", '" . str_replace("'", "''", $name) . "'";
	// 		$sql = $sql . ", '" . str_replace("'", "''", $user_name) . "'";
	// 		$sql = $sql . ", '" . str_replace("'", "''", $password) . "'";
	// 		$sql = $sql . ", '" . str_replace("'", "''", $email) . "'";
	// 		$sql = $sql . ", '" . str_replace("'", "''", $phone) . "'";
	// 		$sql = $sql . ", ','";
	// 		$sql = $sql . ", '.'";
	// 		$sql = $sql . ", 'YYYY-MM-DD'";
	// 		$sql = $sql . ", '" . $appSession->getTier()->getDateString() . "'";
	// 		$sql = $sql . ", '" . $appSession->getTier()->getDateString() . "'";
	// 		$sql = $sql . ", '" . $user_id . "'";
	// 		$sql = $sql . ", '" . $user_id . "'";
	// 		$sql = $sql . ", '" . $lang_id . "'";
	// 		$sql = $sql . ", 0";
	// 		$sql = $sql . ")";
	// 		$msg->add("query", $sql);
	// 		$result = $appSession->getTier()->exec($msg);

	// 		$sql = "INSERT INTO res_company(";
	// 		$sql = $sql . "id";
	// 		$sql = $sql . ", company_id";
	// 		$sql = $sql . ", parent_id";
	// 		$sql = $sql . ", status";
	// 		$sql = $sql . ", name";
	// 		$sql = $sql . ", commercial_name";
	// 		$sql = $sql . ", phone";
	// 		$sql = $sql . ", email";
	// 		$sql = $sql . ", create_date";
	// 		$sql = $sql . ", write_date";
	// 		$sql = $sql . ", create_uid";
	// 		$sql = $sql . ", write_uid";
	// 		$sql = $sql . " )VALUES(";
	// 		$sql = $sql . "	'" . $company_id . "'";
	// 		$sql = $sql . ", '" . $company_id . "'";
	// 		$sql = $sql . ", 'ROOT'";
	// 		$sql = $sql . ", 0";
	// 		$sql = $sql . ", '" . str_replace("'", "''", $name) . "'";
	// 		$sql = $sql . ", '" . str_replace("'", "''", $commercial_name) . "'";
	// 		$sql = $sql . ", '" . str_replace("'", "''", $phone) . "'";
	// 		$sql = $sql . ", '" . str_replace("'", "''", $email) . "'";
	// 		$sql = $sql . ", '" . $appSession->getTier()->getDateString() . "'";
	// 		$sql = $sql . ", '" . $appSession->getTier()->getDateString() . "'";
	// 		$sql = $sql . ", '" . $user_id . "'";
	// 		$sql = $sql . ", '" . $user_id . "'";
	// 		$sql = $sql . ")";

	// 		$msg->add("query", $sql);
	// 		$result = $appSession->getTier()->exec($msg);

	// 		$id = $appSession->getTool()->getId();
	// 		$sql = "INSERT INTO res_user_company(";
	// 		$sql = $sql . "id";
	// 		$sql = $sql . ", company_id";
	// 		$sql = $sql . ", status";
	// 		$sql = $sql . ", user_id";
	// 		$sql = $sql . ", group_id";
	// 		$sql = $sql . ", create_date";
	// 		$sql = $sql . ", write_date";
	// 		$sql = $sql . ", create_uid";
	// 		$sql = $sql . ", write_uid";
	// 		$sql = $sql . " )VALUES(";
	// 		$sql = $sql . "'" . $user_id . "'";
	// 		$sql = $sql . ", '" . $company_id . "'";
	// 		$sql = $sql . ", 0";
	// 		$sql = $sql . ", '" . $user_id . "'";
	// 		$sql = $sql . ", '5909db88-7ae5-473c-f426-9c483f352d9a'";
	// 		$sql = $sql . ", '" . $appSession->getTier()->getDateString() . "'";
	// 		$sql = $sql . ", '" . $appSession->getTier()->getDateString() . "'";
	// 		$sql = $sql . ", '" . $user_id . "'";
	// 		$sql = $sql . ", '" . $user_id . "'";
	// 		$sql = $sql . ")";
	// 		$msg->add("query", $sql);
	// 		$result = $appSession->getTier()->exec($msg);

	// 		$message = [
	// 			'status' => true,
	// 			'message' => "Đăng ký tài khoản thành công."
	// 		];
	// 	}

	// 	return json_encode($message);
	// }

	// public function login($appSession, $user, $pass)
	// {
	// 	$message = [];
	// 	$msg = $appSession->getTier()->createMessage();

	// 	$sql = "SELECT d2.company_id, d2.id, d2.password, d2.user_name, d2.name, d2.date_format, d2.thousands_sep, d2.time_format, d2.decimal_point, d2.avatar, d2.lang_id, d3.code AS company_code, d3.name AS company_name, d3.commercial_name FROM res_user_company d1 LEFT OUTER JOIN res_user d2 ON(d1.user_id = d2.id) LEFT OUTER JOIN res_company d3 ON(d1.company_id = d3.id) WHERE d1.status =0 AND d2.status =0 AND (d2.user_name='" . $user . "' OR d2.email='" . $user . "' OR d2.phone='" . $user . "') AND d2.actived=0";
	// 	$msg->add("query", $sql);
	// 	$result = $appSession->getTier()->getTable($msg);
	// 	$numrows = $result->getRowCount();

	// 	$data = array();
	// 	if ($numrows > 0) {
	// 		$row = $result->getRow(0);
	// 		$user_id = $row->getString("id");
	// 		$s = $appSession->getTool()->toHash("sha256", "[" . $user_id . "]" . $pass);
	// 		$len = strlen($pass);
	// 		for ($i = 0; $i < $len; $i++) {
	// 			$s = $s . chr($i + 48);
	// 		}
	// 		$pass = $appSession->getTool()->toHash("md5", $s);

	// 		if ($pass == $row->getString("password")) {

	// 			$data['user_id'] = $user_id;
	// 			$data['name'] =  $row->getString("name");
	// 			$data['user_name'] = $row->getString("user_name");
	// 			$data['company_id'] = $row->getString("company_id");
	// 			$data['company_code'] = $row->getString("company_code");
	// 			$data['company_name'] = $row->getString("company_name");
	// 			$data['commercial_name'] = $row->getString("commercial_name");

	// 			$lang_id = $row->getString("lang_id");
	// 			if ($lang_id == "") {
	// 				$lang_id = "vi";
	// 			}
	// 			$data['lang_id'] = $lang_id;

	// 			//$token = TokenHandler::getSignedJWTForUser($data['user_id'], $data['user_name'], $data['company_id']);

	// 			$message = [
	// 				'status' => true,
	// 				'data' => ['user' => $data, 'token' => $token],
	// 				'message' => "Đăng nhập thành công."
	// 			];
	// 		} else {
	// 			$message = [
	// 				'status' => false,
	// 				'message' => "Mật khẩu không đúng."
	// 			];
	// 		}
	// 	} else {
	// 		$message = [
	// 			'status' => false,
	// 			'message' => "Tài khoản của bạn không tồn tại."
	// 		];
	// 	}
	// 	return json_encode($message);
	// }

	// public function active($appSession, $user_id)
	// {
	// 	$msg = $appSession->getTier()->createMessage();
	// 	$sql = "UPDATE res_user SET status =0 WHERE id='" . $user_id . "'";
	// 	$msg->add("query", $sql);
	// 	$result = $appSession->getTier()->exec($msg);
	// 	return true;
	// }
}
