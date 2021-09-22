<?php

namespace App\Http\Controllers;

use Exception;

class MpesaController extends Controller
{
    //handle transaction callback
    public function callback()
    {

        //callback input
        $request = request();
        $input = $request->all();

        //save payment
        $s = app('App\Services\MpesaService');
        $payment = $s->stkCallbackPayment($s->stkCallback($input));

        //log callback
        if ($s->getKey('log_callback')) {
            $log = storage_path('logs/mpesa-callback.log');
            $content = json_encode([
                'method' => $request->method(),
                'input' => $input,
                'payment' => $payment ? $payment->id : null,
            ]) . "\r\n\r\n";
            x_file_put($log, $content, FILE_APPEND);
        }

        //response
        return x_res_json([
            'ResultCode' => 0,
            'ResultDesc' => 'Request successful.',
        ]);
    }

    //stk push
    public function stkPush()
    {
        try {
            //request
            $request = request();

            //validate phone number
            if (!$request->has('phone_number')) {
                throw new Exception('Undefined phone number.');
            }
            $input = ['phone_number' => $request->input('phone_number')];
            $rules = ['phone_number' => ['phone_number:KE']];
            if (!x_validate($input, $rules, null, $valid, $validator)) {
                x_throw_validation($validator);
            }

            //check session user
            if (!($user = app('UserService')->getUser())) {
                throw new Exception('Undefined session user.');
            }

            //mpesa service
            $s = app('App\Services\MpesaService');

            //stk push
            $amount = $s->getKey('stk_amount');
            $phone_number = $valid['phone_number'];
            $tmp = str_replace('+', '', $phone_number);
            $res = $s->stkPushRequest($tmp, $amount, config('app.name'));

            //stk cache set
            $key = md5($res['CheckoutRequestID']);
            $data = $res;
            $data['user_id'] = $user->id;
            $data['amount'] = $amount;
            $data['phone_number'] = $phone_number;
            $s->stkCacheSet($key, $data);

            //response
            return x_res_json([
                'message' => "We have initiated MPESA payment on phone number $phone_number for KES $amount."
                    . ' Please check and complete payment to continue.',
                'phone_number' => $phone_number,
                'amount' => $amount,
                'trans_id' => $key,
            ]);
        } catch (Exception $e) {
            $err = $e->getMessage();
            if (method_exists($e, 'errors')) {
                $err = '';
                foreach (array_values($e->errors()) as $item) {
                    $err .= implode("\r\n", $item);
                }
            }

            return x_res_json(['message' => $err], 400);
        }
    }

    //stk poll
    public function stkPoll($trans_id)
    {
        //check transaction id (stk cache key)
        if (!(x_cache_has($trans_id) && x_is_assoc($data = x_cache_get($trans_id)) && isset($data['CheckoutRequestID']))) {
            return x_res_json(['status' => 'invalid']); //invalid
        }

        //mpesa service
        $s = app('App\Services\MpesaService');

        //check stk status
        $res = $s->stkPushCheck($data['CheckoutRequestID']);
        $message = x_has_key($res, 'ResultDesc') ? x_tstr($res['ResultDesc']) : 'Undefined ResultDesc.';

        //pending
        if (x_has_key($res, 'errorCode') && $res['errorCode'] == '500.001.1001') {
            return x_res_json(['status' => 'pending', 'message' => $message]); //response
        }

        //cancelled
        if (x_has_key($res, 'ResultCode') && in_array(x_tstr($res['ResultCode']), ['1032', '1031'])) {
            $s->stkCacheDone($trans_id); //done

            return x_res_json(['status' => 'cancelled', 'message' => $message]); //response
        }

        //timeout
        if (x_has_key($res, 'ResultCode') && $res['ResultCode'] == '1037') {
            $s->stkCacheDone($trans_id); //done

            return x_res_json(['status' => 'timeout', 'message' => $message]); //response
        }

        //success
        if (x_has_key($res, 'ResultCode') && $res['ResultCode'] == '0') {
            $s->stkCacheDone($trans_id, 'success'); //done|unsaved=success

            return x_res_json(['status' => 'success', 'message' => 'Payment received successfully.']); //response
        }

        //unknown
        $s->stkCacheDone($trans_id, 'unknown'); //done|unsaved=unknown

        return x_res_json(['status' => 'unknown', 'message' => $message]); //response
    }
}
