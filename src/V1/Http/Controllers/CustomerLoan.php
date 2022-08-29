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
 * Customer section for loan application and repayment
*/

class CustomerLoan extends BaseController
{

    public function __construct()
    {
        /* initiate logger with controller name */
        parent::__construct('customer-loan');
    }

    /**
     * Customer apply for Loan
     * @param Request
     * @return Array
     */
    public function apply(Request $request)
    {
        $params = $request->all();

        $messages = [
            'required' => 'Request cannot be handled due to missing :attribute value',
        ];

        $rule = [
            'customerId' =>   'required',
            'amount' => 'required',
            'term'  =>  'required',
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

        $customerId       =  $params['customerId'];
        $term   =  intval($params['term']);
        $amount =   $params['amount'];

        $today = new \DateTimeImmutable(
            gmdate('Y-m-d H:i:s', time()),
            new \DateTimeZone("UTC")
        );

        try {

            /* Check if previous loan not PAID */
            $isExist = LoanApplication::where([
                'customer_id'  =>  $customerId
            ])->where('application_status', '!=', 'PAID')
            ->first();

            if ($isExist !== null) {

                $error = [
                    "code"  =>  400,
                    "status"    =>  'failed',
                    "message"   =>  'You already have previous loan'
                ];

                $this->logger->error(Utils::json($params), $error);

                return response()->json($error, 200);
            }

            \DB::beginTransaction();

            /* Add customer applicatin details */
            $loan = new LoanApplication;

            $loan->customer_id =   $customerId;
            $loan->applied_at = $today->format('Y-m-d h:i:s');
            $loan->amount = $amount;
            $loan->term =   $term;
            //application_status = 'PENDING';   // Default column value has been set
            //loan_status = 'PENDING';      // Default column value has been set
            //approved_by = 0;            // Default column value has been set

            $loan->save();

            $applcationId = $loan->id;

            \DB::commit();

            $response = [
                "code"  =>  200,
                "status"    =>  'success',
                "message"   =>  'Loan has been applied successfully',
                "data"  =>  [
                    'application_id'   =>  $applcationId,
                ]
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

    /**
     * Customer get loan status
     * @param Request
     * @return Array
     */
    public function getLoanStatus(Request $request)
    {
        $params = $request->all();

        $messages = [
            'required' => 'Request cannot be handled due to missing :attribute value',
        ];

        $rule = [
            'customerId' =>   'required',
            'viewPolicyCheck' => 'required'
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

        $customerId       =  $params['customerId'];
        $viewPolicyCheck = $params['viewPolicyCheck'];

        if($viewPolicyCheck !== true) {
            $error = [
                "code"  =>  400,
                "status"    =>  'failed',
                "message"   =>  'Customer need to check view policy'
            ];

            $this->logger->error(Utils::json($params), $error);

            return response()->json($error, 200);
        }

        try {

            $getStatus = LoanApplication::with(['Schedule'])
            ->where([
                'customer_id'    =>  $customerId
            ])->first();

            if ($getStatus == null) {
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
                "data"  =>  $getStatus
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
}
