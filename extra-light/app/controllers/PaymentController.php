<?php

namespace AppLight\Controllers;

use App\Http\Controllers\Controller;

class PaymentController extends Controller
{
    public $appSession;
    public $msg;
    public $session_user_id;
    public $session_company_id;
    public function __construct()
    {
        global $appSession;
        $this->appSession = $appSession;
        $this->msg = $this->appSession->getTier()->createMessage();
        $this->session_user_id = $this->appSession->getConfig()->getProperty("session_user_id");
        $this->session_company_id = $this->appSession->getConfig()->getProperty("session_company_id");
    }

    // START BANKS
    public function get_banks(Request $request, Response $response)
    {

        $banks = [
            ["ABBANK", "Ngân hàng thương mại cổ phần An Bình (ABBANK)", "ABBANK.png"],
            ["ACB",    "Ngân hàng ACB", "ACB.png"],
            ["AGRIBANK", "Ngân hàng Nông nghiệp (Agribank)", "AGRIBANK.png"],
            ["BACABANK", "Ngân Hàng TMCP Bắc Á", "BACABANK.png"],
            ["BIDV", "Ngân hàng đầu tư và phát triển Việt Nam (BIDV)", "BIDV.png"],
            ["DONGABANK", "Ngân hàng Đông Á (DongABank)", "DONGABANK.png"],
            ["EXIMBANK", "Ngân hàng EximBank", "EXIMBANK.png"],
            ["HDBANK", "Ngan hàng HDBank", "HDBANK.png"],
            ["IVB", "Ngân hàng TNHH Indovina (IVB)", "IVB.png"],
            ["MBBANK", "Ngân hàng thương mại cổ phần Quân đội", "MBBANK.png"],
            ["MSBANK", "Ngân hàng Hàng Hải (MSBANK)", "MSBANK.png"],
            ["NAMABANK", "Ngân hàng Nam Á (NamABank)", "NAMABANK.png"],
            ["NCB", "Ngân hàng Quốc dân (NCB)", "NCB.png"],
            ["OCB", "Ngân hàng Phương Đông (OCB)", "OCB.png"],
            ["OJB", "Ngân hàng Đại Dương (OceanBank)", "OJB.png"],
            ["PVCOMBANK", "Ngân hàng TMCP Đại Chúng Việt Nam", "PVCOMBANK.png"],
            ["SACOMBANK", "Ngân hàng TMCP Sài Gòn Thương Tín (SacomBank)", "SACOMBANK.png"],
            ["SAIGONBANK", "Ngân hàng thương mại cổ phần Sài Gòn Công Thương", "SAIGONBANK.png"],
            ["SCB", "Ngân hàng TMCP Sài Gòn (SCB)", "SCB.png"],
            ["SHB", "Ngân hàng Thương mại cổ phần Sài Gòn - Hà Nội(SHB)", "SHB.png"],
            ["TECHCOMBANK", "Ngân hàng Kỹ thương Việt Nam (TechcomBank)", "TECHCOMBANK.png"],
            ["TPBANK", "Ngân hàng Tiên Phong (TPBank)", "TPBANK.png"],
            ["VPBANK", "Ngân hàng Việt Nam Thịnh vượng (VPBank)", "VPBANK.png"],
            ["SEABANK", "Ngân Hàng TMCP Đông Nam Á", "SEABANK.png"],
            ["VIB", "Ngân hàng Thương mại cổ phần Quốc tế Việt Nam (VIB)", "VIB.png"],
            ["VIETABANK", "Ngân hàng TMCP Việt Á", "VIETABANK.png"],
            ["VIETBANK", "Ngân hàng thương mại cổ phần Việt Nam Thương Tín", "VIETBANK.png"],
            ["VIETCOMBANK", "Ngân hàng Ngoại thương (Vietcombank)", "VIETCOMBANK.png"],
            ["VIETINBANK", "Ngân hàng Công thương (Vietinbank)", "VIETINBANK.png"],
            ["BIDC", "Ngân Hàng BIDC", "BIDC.PNG"],
            ["LAOVIETBANK", "NGÂN HÀNG LIÊN DOANH LÀO - VIỆT", "LAOVIETBANK.png"],
            ["WOORIBANK", "Ngân hàng TNHH MTV Woori Việt Nam", "WOORIBANK.png"],
            ["AMEX", "American Express", "AMEX.png"],
            ["VISA", "Thẻ quốc tế Visa", "VISA.png"],
            ["MASTERCARD", "Thẻ quốc tế MasterCard", "MASTERCARD.png"],
            ["JCB", "Thẻ quốc tế JCB", "JCB.png"],
            ["UPI", "UnionPay International", "UPI.png"],
            ["VNMART", "Ví điện tử VnMart", "VNMART.png"],
            ["VNPAYQR", "Cổng thanh toán VNPAYQR", "VNPAYQR.png"],
            ["1PAY", "Ví điện tử 1Pay", "1PAY.png"],
            ["FOXPAY", "Ví điện tử FOXPAY", "FOXPAY.png"],
            ["VIMASS", "Ví điện tử Vimass", "VIMASS.png"],
            ["VINID", "Ví điện tử VINID", "VINID.png"],
            ["VIVIET", "Ví điện tử Ví Việt", "VIVIET.png"],
            ["VNPTPAY", "Ví điện tử VNPTPAY", "VNPTPAY.png"],
            ["YOLO", "Ví điện tử YOLO", "YOLO.png"],
            ["VIETCAPITALBANK", "Ngân Hàng Bản Việt", "VIETCAPITALBANK.png"],
        ];

        $data = array();
        for ($i = 0; $i < count($banks); $i++) {
            $arr = array();

            $arr['id'] = $banks[$i][0];
            $arr['name'] = $banks[$i][1];
            $arr['logo'] = $banks[$i][2];
            $data[] = $arr;
        }

        $message = [
            'status' => true,
            'data' => ['banks' => $data],
            'message' => "Lấy danh sách banks thành công."
        ];


        return $this->appSession->getTier()->response($message, $response);
    }

      // START VENDOR
      public function _banks(Request $request, Response $response)
      {

          $banks = [
              ["ABBANK", "Ngân hàng thương mại cổ phần An Bình (ABBANK)", "ABBANK.png"],
              ["ACB",    "Ngân hàng ACB", "ACB.png"],
              ["AGRIBANK", "Ngân hàng Nông nghiệp (Agribank)", "AGRIBANK.png"],
              ["BACABANK", "Ngân Hàng TMCP Bắc Á", "BACABANK.png"],
              ["BIDV", "Ngân hàng đầu tư và phát triển Việt Nam (BIDV)", "BIDV.png"],
              ["DONGABANK", "Ngân hàng Đông Á (DongABank)", "DONGABANK.png"],
              ["EXIMBANK", "Ngân hàng EximBank", "EXIMBANK.png"],
              ["HDBANK", "Ngan hàng HDBank", "HDBANK.png"],
              ["IVB", "Ngân hàng TNHH Indovina (IVB)", "IVB.png"],
              ["MBBANK", "Ngân hàng thương mại cổ phần Quân đội", "MBBANK.png"],
              ["MSBANK", "Ngân hàng Hàng Hải (MSBANK)", "MSBANK.png"],
              ["NAMABANK", "Ngân hàng Nam Á (NamABank)", "NAMABANK.png"],
              ["NCB", "Ngân hàng Quốc dân (NCB)", "NCB.png"],
              ["OCB", "Ngân hàng Phương Đông (OCB)", "OCB.png"],
              ["OJB", "Ngân hàng Đại Dương (OceanBank)", "OJB.png"],
              ["PVCOMBANK", "Ngân hàng TMCP Đại Chúng Việt Nam", "PVCOMBANK.png"],
              ["SACOMBANK", "Ngân hàng TMCP Sài Gòn Thương Tín (SacomBank)", "SACOMBANK.png"],
              ["SAIGONBANK", "Ngân hàng thương mại cổ phần Sài Gòn Công Thương", "SAIGONBANK.png"],
              ["SCB", "Ngân hàng TMCP Sài Gòn (SCB)", "SCB.png"],
              ["SHB", "Ngân hàng Thương mại cổ phần Sài Gòn - Hà Nội(SHB)", "SHB.png"],
              ["TECHCOMBANK", "Ngân hàng Kỹ thương Việt Nam (TechcomBank)", "TECHCOMBANK.png"],
              ["TPBANK", "Ngân hàng Tiên Phong (TPBank)", "TPBANK.png"],
              ["VPBANK", "Ngân hàng Việt Nam Thịnh vượng (VPBank)", "VPBANK.png"],
              ["SEABANK", "Ngân Hàng TMCP Đông Nam Á", "SEABANK.png"],
              ["VIB", "Ngân hàng Thương mại cổ phần Quốc tế Việt Nam (VIB)", "VIB.png"],
              ["VIETABANK", "Ngân hàng TMCP Việt Á", "VIETABANK.png"],
              ["VIETBANK", "Ngân hàng thương mại cổ phần Việt Nam Thương Tín", "VIETBANK.png"],
              ["VIETCOMBANK", "Ngân hàng Ngoại thương (Vietcombank)", "VIETCOMBANK.png"],
              ["VIETINBANK", "Ngân hàng Công thương (Vietinbank)", "VIETINBANK.png"],
              ["BIDC", "Ngân Hàng BIDC", "BIDC.PNG"],
              ["LAOVIETBANK", "NGÂN HÀNG LIÊN DOANH LÀO - VIỆT", "LAOVIETBANK.png"],
              ["WOORIBANK", "Ngân hàng TNHH MTV Woori Việt Nam", "WOORIBANK.png"],
              ["AMEX", "American Express", "AMEX.png"],
              ["VISA", "Thẻ quốc tế Visa", "VISA.png"],
              ["MASTERCARD", "Thẻ quốc tế MasterCard", "MASTERCARD.png"],
              ["JCB", "Thẻ quốc tế JCB", "JCB.png"],
              ["UPI", "UnionPay International", "UPI.png"],
              ["VNMART", "Ví điện tử VnMart", "VNMART.png"],
              ["VNPAYQR", "Cổng thanh toán VNPAYQR", "VNPAYQR.png"],
              ["1PAY", "Ví điện tử 1Pay", "1PAY.png"],
              ["FOXPAY", "Ví điện tử FOXPAY", "FOXPAY.png"],
              ["VIMASS", "Ví điện tử Vimass", "VIMASS.png"],
              ["VINID", "Ví điện tử VINID", "VINID.png"],
              ["VIVIET", "Ví điện tử Ví Việt", "VIVIET.png"],
              ["VNPTPAY", "Ví điện tử VNPTPAY", "VNPTPAY.png"],
              ["YOLO", "Ví điện tử YOLO", "YOLO.png"],
              ["VIETCAPITALBANK", "Ngân Hàng Bản Việt", "VIETCAPITALBANK.png"],
          ];

          $data = array();
          for ($i = 0; $i < count($banks); $i++) {
              $arr = array();

              $arr['id'] = $banks[$i][0];
              $arr['name'] = $banks[$i][1];
              $arr['logo'] = $banks[$i][2];
              $data[] = $arr;
          }

          $message = [
              'status' => true,
              'data' => ['banks' => $data],
              'message' => "Lấy danh sách banks thành công."
          ];


          return $this->appSession->getTier()->response($message, $response);
      }
}
