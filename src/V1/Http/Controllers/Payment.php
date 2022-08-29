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
 * Paymen section for customer repayment
*/

class Payment extends BaseController
{

    public function __construct()
    {
        /* initiate logger with controller name */
        parent::__construct('payment');
    }

    /**
     * Customer apply for Loan
     * @param Request
     * @return Array
     */
    public function payNow(Request $request)
    {
        $params = $request->all();

        $messages = [
            'required' => 'Request cannot be handled due to missing :attribute value',
        ];

        $rule = [
            'applicationId' =>   'required',
            'customerId'    =>  'required',
            'amount'    =>  'required'
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

        $applcationId       =  $params['applicationId'];
        $customerId       =  $params['customerId'];
        $amount =   $params['amount'];

        try {

            /* Check if loan is nill*/
            $paidAmount = PaymentSchedule::where([
                'application_id'    =>  $applcationId,
                'status'    =>  'PAID'
            ])->orderBy('id', 'asc')
            ->sum('paid_amount');

            $loanDetails = LoanApplication::where([
                'id'    =>  $applcationId,
                'customer_id'   =>  $customerId
            ])->first();

            if ($loanDetails->application_status !== 'APPROVED') {
                $error = [
                    "code"  =>  400,
                    "status"    =>  'failed',
                    "message"   =>  'Your loan application is not approved yet'
                ];

                $this->logger->error(Utils::json($params), $error);

                return response()->json($error, 200);
            }

            if ($paidAmount >= $loanDetails->amount) {

                $error = [
                    "code"  =>  400,
                    "status"    =>  'failed',
                    "message"   =>  'Your loan is nill, No further payment required.'
                ];

                $this->logger->error(Utils::json($params), $error);

                return response()->json($error, 200);
            }

            $getSchedule = PaymentSchedule::where([
                'application_id'    =>  $applcationId,
                'status'    =>  'PENDING'
            ])->orderBy('id', 'asc')
            ->first();

            /* Customer add a repayment with amount greater or equal to the scheduled repayment */
            if($amount < $getSchedule->schedule_amount) {

                $error = [
                    "code"  =>  400,
                    "status"    =>  'failed',
                    "message"   =>  "Payment amount should not be less than $getSchedule->schedule_amount $"
                ];

                $this->logger->error(Utils::json($params), $error);

                return response()->json($error, 200);
            }

            $today = new \DateTimeImmutable(
                gmdate('Y-m-d H:i:s', time()),
                new \DateTimeZone("UTC")
            );

            \DB::beginTransaction();

            $getSchedule->paid_amount = $amount;
            $getSchedule->paid_date =   $today->format('Y-m-d h:i:s');
            $getSchedule->status = 'PAID';

            $getSchedule->save();

            $paidAmount = PaymentSchedule::where([
                'application_id'    =>  $applcationId,
                'status'    =>  'PAID'
            ])->orderBy('id', 'asc')
            ->sum('paid_amount');

            /* If paid extra amount then devide remaining amount with pending schedule */
            if($amount > $getSchedule->schedule_amount) {

                $remainingAmount = ($loanDetails->amount - $paidAmount);

                $pendingSchedule = PaymentSchedule::where([
                    'application_id'    =>  $applcationId,
                    'status'    =>  'PENDING'
                ])->orderBy('id', 'asc')
                ->get();

                //dd(count($pendingSchedule));

                $remainingTerm = count($pendingSchedule);
                $emiAmount = ($remainingAmount / $remainingTerm);
                $emiAmount = number_format($emiAmount, 2, '.', '');

                /* Calculate Last Remaining Installment */
                $totalInstallmentAmount = number_format($emiAmount * ($remainingTerm), 2, '.','');
                $reminderAmount = number_format($remainingAmount - $totalInstallmentAmount, 2, '.','');
                $lastBit    =   $emiAmount + ($reminderAmount);

                $i = 0;
                foreach ($pendingSchedule as $schedule) {
                    $i ++;
                    if ($i == $remainingTerm) {
                        $emiAmount = $lastBit;
                    }

                    if ($emiAmount == 0) {
                        $schedule->status = 'NILL';
                    }
                    $schedule->schedule_amount  =   $emiAmount;
                    $schedule->save();
                }
            }

            if ($loanDetails->amount == $paidAmount) {

                $loanDetails->loan_status = 'PAID';
                $loanDetails->save();

                \DB::commit();

                $response = [
                    "code"  =>  200,
                    "status"    =>  'success',
                    "message"   =>  'Congratulations! Your loan is nill'
                ];

                $this->logger->info(Utils::json($params), $response);

                return response()->json($response, 200);
            }

            \DB::commit();

            $response = [
                "code"  =>  200,
                "status"    =>  'success',
                "message"   =>  'Payment successfull'
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
