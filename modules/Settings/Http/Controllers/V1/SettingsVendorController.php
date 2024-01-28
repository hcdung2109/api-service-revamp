<?php

namespace Digisource\Settings\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use Digisource\Common\Utils\Utils;
use Digisource\Core\Constant\Constant;
use Digisource\Core\Constant\Status;
use Digisource\Settings\Entities\CommissionsCalculationMetric;
use Digisource\Users\Entities\ResUserCompany;
use Digisource\Users\Entities\User;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use PHPMailer\PHPMailer\Exception;

class SettingsVendorController extends Controller
{
    // START SETTINGS COMMISSION METRIC
    public function get_vendor_commission_metric(Request $request)
    {
        $param = $request->all();

        $p =  $param['p'] ?? 1;
        $ps =  $param['ps'];

        $user = auth()->user();

        $result = CommissionsCalculationMetric::select('id','name','description')
                                    ->where('status', 0)
                                    ->where('company_id', $user->company_id)
                                    ->paginate($ps, ['*'], 'page', $p);

        $this->addData($result);
        return $this->getResponse();
    }

    public function create_vendor_commission_metric(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|max:256',
            'description' => 'required|max:1000'
        ]);

        if ($validator->fails()) {
            $this->validateFails(new ValidationException($validator));
            return $this->getResponse();
        }

        $data = $request->all();
        $user = auth()->user();

        $name = $data['name'];
        $description = $data['description'];

        $new = new CommissionsCalculationMetric();
        $new->id = uniqid();
        $new->create_uid = $user->id;
        $new->write_uid = $user->id;
        $new->create_date = date('Y-m-d H:i:s');
        $new->write_date = date('Y-m-d H:i:s');
        $new->name = $name;
        $new->description = $description;
        $new->company_id = $user->company_id;
        $new->status = 0;
        $new->save();

        $this->setMessage("Tạo user thành công");
        return $this->getResponse();
    }

    public function update_vendor_commission_metric(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|max:256',
            'description' => 'required|max:1000'
        ]);

        if ($validator->fails()) {
            $this->validateFails(new ValidationException($validator));
            return $this->getResponse();
        }

        $data = $request->all();
        $user = auth()->user();

        $name = $data['name'];
        $description = $data['description'];

        $item = CommissionsCalculationMetric::where('status',0)->find($id);

        if ($user == null) {
            $this->setMessage("User không tồn tại.");
        } else {
            $item->name = $name;
            $item->description = $description;
            $item->save();
        }

        return $this->getResponse();
    }

    public function get_vendor_commission_metric_by_id(Request  $request, $id)
    {
        $item = CommissionsCalculationMetric::where('status',0)->find($id);

        if ($item == null) {
            $this->setMessage("Vendor commisstion metric không tồn tại.");
        } else {
            $this->addData(['vendor_commission_metric' => $item]);
        }

        return $this->getResponse();
    }

    public function delete_vendor_commission_metric($id)
    {
        $item = CommissionsCalculationMetric::where('status',0)->find($id);

        if ($item == null) {
            $this->setMessage("Vendor commisstion metric không tồn tại.");
        } else {
            $item->status = 1;
            $item->save();

            $this->setMessage("Xóa vendor commisstion metric thành công.");

        }

        return $this->getResponse();
    }
    // END SETTINGS COMMISSION METRIC

}
