<?php

namespace AspireRESTAPI\V1\Http\Controllers;

use Validator;
use Illuminate\Http\Response;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

use AspireRESTAPI\V1\Http\Controllers\Utility\Utils;
use AspireRESTAPI\V1\Http\Controllers\BaseController;

use AspireRESTAPI\V1\Models\Sql\LoanApplication;
use AspireRESTAPI\V1\Models\Sql\PaymentSchedule;

/**
 * @author : Riyaz Patwegar <riyaz_patwegar@yahoo.com>
 * @since : 27-08-2022
 * Admin section for loan application approval
*/

class Admin extends BaseController
{

    public function __construct()
    {
        /* initiate logger with controller name */
        parent::__construct('admin');
    }

    /**
     * Admin get loan applications
     * @param Request
     * @return Array
     */
    public function getLoanApplications(Request $request)
    {
        $params = $request->all();

        $messages = [
            'required' => 'Request cannot be handled due to missing :attribute value',
        ];

        $rule = [
            'adminId' =>   'required'
        ];

        $validator = Validator::make($params, $rule, $messages);

        if ($validator->fails()) {
            $errors = $validator->errors();

            foreach ($errors->all() as $error) {
                $errorResponse = [
                    'code' => 601,
                    'status' => 'failed',
                    'message' => $error
                ];

                $this->logger->error(Utils::json($params), $errorResponse);

                return response()->json($errorResponse, 400);
            }
        }

        $adminId       =  $params['adminId'];

        try {

            /* Get customer applicatins*/
            $getApplications = LoanApplication::with(['Schedule'])->get();

            if ($getApplications->isEmpty()) {

                $error = [
                    "code"  =>  400,
                    "status"    =>  'failed',
                    "message"   =>  'No Data Found'
                ];

                $this->logger->error(Utils::json($params), $error);

                return response()->json($error, 200);
            }

            $response = [
                "code"  =>  200,
                "status"    =>  'success',
                "message"   =>  'success',
                "data"  => $getApplications
            ];

            $this->logger->info(Utils::json($params), $response);

            return response()->json($response, 200);

        } catch (\Throwable $e) {

            $error = [
                "code"  =>  400,
                "status"    =>  'failed',
                "message"   =>  $e->getMessage() ?? 'Something went wrong, Please try again'
            ];

            $this->logger->error(Utils::json($params), $error);

            return response()->json($error, 400);
        }
    }

    /**
     * Admin Approve Loan
     * @param Request
     * @return Array
     */
    public function approveLoan(Request $request)
    {
        $params = $request->all();

        $messages = [
            'required' => 'Request cannot be handled due to missing :attribute value',
        ];

        $rule = [
            'applicationId' =>   'required',
            'adminId'   =>  'required'
        ];

        $validator = Validator::make($params, $rule, $messages);

        if ($validator->fails()) {
            $errors = $validator->errors();

            foreach ($errors->all() as $error) {
                $errorResponse = [
                    'code' => 601,
                    'status' => 'failed',
                    'message' => $error
                ];

                $this->logger->error(Utils::json($params), $errorResponse);

                return response()->json($errorResponse, 400);
            }
        }

        $applicationId       =  $params['applicationId'];
        $adminId       =  $params['adminId'];

        $today = new \DateTimeImmutable(
            gmdate('Y-m-d H:i:s', time()),
            new \DateTimeZone("UTC")
        );

        try {

            $checkApplcation = LoanApplication::where([
                'id'    =>  $applicationId
            ])->first();

            if($checkApplcation == null) {
                $error = [
                    "code"  =>  400,
                    "status"    =>  'failed',
                    "message"   =>  'No Application Found'
                ];

                $this->logger->error(Utils::json($params), $error);

                return response()->json($error, 400);
            }

            if ($checkApplcation->application_status == 'APPROVED') {
                $error = [
                    "code"  =>  400,
                    "status"    =>  'failed',
                    "message"   =>  'Application already approved'
                ];

                $this->logger->error(Utils::json($params), $error);

                return response()->json($error, 200);
            }

            \DB::beginTransaction();

            /* Update application status to APPROVED */
            $applcation = LoanApplication::where([
                'id'    =>  $applicationId
            ])->update([
                'approved_at'   =>  $today->format('Y-m-d h:i:s'),
                'approved_by'   =>  $adminId,
                'application_status'   =>  'APPROVED'
            ]);

            /* Devide loan amount into terms */
            $term = $checkApplcation->term;
            $loanAmount =   $checkApplcation->amount;
            $emiAmount  =   ($loanAmount / $term);
            $emiAmount  =   number_format($emiAmount, 2, '.', '');

            /* Calculate Last Remaining Installment */
            $totalInstallmentAmount = number_format($emiAmount * ($term), 2, '.','');
            $reminderAmount = number_format($loanAmount - $totalInstallmentAmount, 2, '.','');

            /* We have to put all remaining amount into last schedule which is not devidable into terms*/
            $lastBit    =   $emiAmount + ($reminderAmount);

            $schedules = [];

            $nextDate = strtotime($today->format('Y-m-d'));

            /* Insert Schedul Details Based on Term Duration */
            for ($i = 0; $i < $term; $i++) {

                /* We have to put all remaining amount into last schedule which is not devidable into terms*/
                /* Add remaining amount to last scheduled payment */
                if ($i == ($term-1) ) {
                    $emiAmount = $lastBit;
                }

                /* Make dynamically weekly schedule  */
                $nextDate = strtotime("+7 day", $nextDate);

                $emiScheduel = [
                    'application_id'   =>  $applicationId,
                    'schedule_date' =>  date('Y-m-d h:i:s', $nextDate),
                    'schedule_amount'   =>  $emiAmount,
                    //'status'    =>  'PENDING'         // Default column value has been set
                ];

                array_push($schedules, $emiScheduel);
            }

            /* Generate EMI Schedule */
            PaymentSchedule::insert($schedules);

            \DB::commit();

            $response = [
                "code"  =>  200,
                "status"    =>  'success',
                "message"   =>  'Application has been approved'
            ];

            $this->logger->info(Utils::json($params), $response);

            return response()->json($response, 200);

        } catch (\Throwable $e) {

            \DB::rollback();

            $error = [
                "code"  =>  400,
                "status"    =>  'failed',
                "message"   =>  $e->getMessage() ?? 'Something went wrong, Please try again'
            ];

            $this->logger->error(Utils::json($params), $error);

            return response()->json($error, 400);
        }
    }
}
