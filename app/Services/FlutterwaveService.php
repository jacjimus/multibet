<?php

namespace App\Services;

use App\Models\Payment;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Http;

class FlutterwaveService
{
    //config
    public function config(string $path=null, $default=null)
    {
        return x_array_get($path, x_arr(config('services.flutterwave')), $default);
    }

    //get key
    public function getKey(string $key='public')
    {
        $mode = $this->config('mode');

        return $this->config($mode == 'test' ? 'test.' . $key : $key);
    }

    //trans ref
    public function transRef($id)
    {
        return bcrypt('flutterwave-' . $id);
    }

    //check trans ref
    public function transRefId($ref, $id)
    {
        if (!(($id = x_int($id)) >= 1)) {
            return;
        }

        return password_verify('flutterwave-' . $id, $ref) ? $id : null;
    }

    //verify & save payment
    public function verify($trans_id)
    {
        //verify
        $url = str_replace(':trans_id', $trans_id, 'https://api.flutterwave.com/v3/transactions/:trans_id/verify');
        $headers = [
            'Authorization' => 'Bearer ' . $this->getKey('secret'),
        ];
        $res = Http::withHeaders($headers)->get($url);
        $data = $res->successful() && x_has_key($tmp = $res->json(), 'data') && x_is_assoc($tmp = $tmp['data']) ? $tmp : null;

        //transaction data
        if (x_has_key($data, 'tx_ref')) {
            $tx_ref = $data['tx_ref'];
            $uid = x_array_get('meta.uid', $data);

            //validate transaction (set user id)
            if ($id = $this->transRefId($tx_ref, $uid)) {

                //payment
                $date = ($tmp = x_array_get('created_at', $data)) ? Carbon::createFromTimestamp(strtotime($tmp))->format('Y-m-d H:i:s') : null;
                $payment_data = [
                    'user_id' => $id,
                    'ref' => $trans_id,
                    'date' => $date,
                    'type' => 'premium',
                    'amount' => x_array_get('amount', $data),
                    'currency' => x_array_get('currency', $data),
                    'provider' => 'flutterwave',
                    'name' => x_array_get('customer.name', $data),
                    'email' => x_array_get('customer.email', $data),
                    'phone' => x_array_get('customer.phone_number', $data),
                    'account' => (string) x_array_get('customer.id', $data),
                    'data' => json_encode($data),
                ];

                //existing payment
                $payment = Payment::where([
                    'user_id' => $id,
                    'ref' => $trans_id,
                    'date' => $date
                ])->first();

                //return existing payment
                if ($payment) {
                    return $payment;
                }

                //create payment if not exist
                try {
                    $payment = new Payment($payment_data);
                    $payment->save();

                    return $payment;
                } catch (Exception $e) {
                    $err = $e->getMessage() . "\r\n" . json_encode($e->errors());
                    x_file_put(storage_path('logs/flutterwave.log'), $err);
                }
            }
        }
    }
}
