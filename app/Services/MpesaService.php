<?php

namespace App\Services;

use App\Models\Payment;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Http;

class MpesaService
{
    //attrs
    private $_error_log_file;

    //constructor
    public function __construct()
    {
        $this->_error_log_file = storage_path('logs/mpesa-service-error.log');
    }

    //config
    public function config(string $path=null, $default=null)
    {
        return x_array_get($path, x_arr(config('services.mpesa')), $default);
    }

    //get key
    public function getKey(string $key)
    {
        $mode = $this->config('mode');
        if ($mode == 'sandbox') {
            $value = $this->config('sandbox.' . $key, 'undefined');
            if ($value == 'undefined') {
                $value = $this->config($key);
            }
        } else {
            $value = $this->config($key);
        }

        return $value;
    }

    //get password
    public function getPassword()
    {
        $timestamp = now()->format('YmdHms');
        $passkey = $this->getKey('passkey');
        $business_shortcode = $this->getKey('shortcode');
        $password = base64_encode($business_shortcode . $passkey . $timestamp);

        return $password;
    }

    //stk push
    public function stkPushRequest($phone, $amount=1, $ref, $description='STK Push')
    {
        $url = $this->getKey('stk_push');
        $headers = [
            'Authorization' => 'Bearer ' . $this->generateAccessToken(),
        ];
        $data = [
            'BusinessShortCode' => $this->getKey('shortcode'),
            'Password' => $this->getPassword(),
            'Timestamp' => now()->format('YmdHms'),
            'TransactionType' => 'CustomerPayBillOnline',
            'Amount' => $amount,
            'PartyA' => $phone,
            'PartyB' => $this->getKey('shortcode'),
            'PhoneNumber' => $phone,
            'CallBackURL' => $this->getKey('confirmation_url'),
            'AccountReference' => $ref,
            'TransactionDesc' => $description,
        ];
        $res = Http::withHeaders($headers)->post($url, $data);
        $data = $res->json();
        if ($res->successful()) {
            return $data;
        }
        if (x_has_key($data, 'errorCode')) {
            $err = '[' . $data['errorCode'] . '] ' . $data['errorMessage'];
        } else {
            $err = 'Unsupported access token response: [' . $res->status() . '] ' . $res->body();
        }

        throw new Exception($err);
    }

    //stk check
    public function stkPushCheck($CheckoutRequestID)
    {
        $url = $this->getKey('stk_query');
        $headers = [
            'Authorization' => 'Bearer ' . $this->generateAccessToken(),
        ];
        $data = [
            'BusinessShortCode' => $this->getKey('shortcode'),
            'Password' => $this->getPassword(),
            'Timestamp' => now()->format('YmdHms'),
            'CheckoutRequestID' => $CheckoutRequestID,
        ];
        $res = Http::withHeaders($headers)->post($url, $data);

        return $res->json();
    }

    //get access token
    public function generateAccessToken()
    {
        $consumer_key = $this->getKey('consumer_key');
        $consumer_secret = $this->getKey('consumer_secret');
        $credentials = base64_encode($consumer_key . ':' . $consumer_secret);
        $url = $this->getKey('oauth');
        $headers = [
            'Authorization' => 'Basic ' . $credentials,
        ];
        $res = Http::withHeaders($headers)->get($url);
        if ($res->successful()) {
            $data = $res->json();
            if (x_has_key($data, 'access_token') && x_has_key($data, 'expires_in') && ($token = x_tstr($data['access_token']))) {
                return $token;
            }
        }

        throw new Exception('Unsupported access token response: [' . $res->status() . '] ' . $res->body());
    }

    //register callback urls
    public function registerUrl($response_type='Completed')
    {
        $url = $this->getKey('register_url');
        $headers = [
            'Authorization' => 'Bearer ' . $this->generateAccessToken(),
        ];
        $data = [
            'ShortCode' => $this->getKey('shortcode'),
            'ResponseType' => $response_type,
            'ConfirmationURL' => $this->getKey('confirmation_url'),
            'ValidationURL' => $this->getKey('validation_url'),
        ];
        $res = Http::withHeaders($headers)->post($url, $data);

        return $res->json();
    }

    //stk callback transaction
    public function stkCallback($input)
    {
        if (!x_has_key($input, $tmp = 'Body')) {
            return;
        }
        if (!x_has_key($body = $input[$tmp], $tmp = 'stkCallback')) {
            return;
        }
        $data = $body[$tmp];
        if (x_has_key($data, 'ResultCode') && $data['ResultCode'] == '0' && x_has_key($data, 'CheckoutRequestID') && x_has_key($data, $tmp = 'CallbackMetadata')) {
            $CheckoutRequestID = $data['CheckoutRequestID'];
            $meta = $data[$tmp];
            if (x_has_key($meta, $tmp = 'Item') && x_is_list($items = $meta[$tmp])) {
                $tmp = [];
                foreach ($items as $item) {
                    if (x_has_key($item, 'Name') && x_has_key($item, 'Value')) {
                        if ($item['Name'] == 'Amount') {
                            $tmp['amount'] = $item['Value'];
                        }
                        if ($item['Name'] == 'MpesaReceiptNumber') {
                            $tmp['receipt'] = $item['Value'];
                        }
                        if ($item['Name'] == 'TransactionDate') {
                            $tmp['date'] = x_date_parse($item['Value'], 'YmdHis')->format('Y-m-d H:i:s');
                        }
                        if ($item['Name'] == 'PhoneNumber') {
                            $tmp['phone'] = $item['Value'];
                        }
                    }
                }
                if (!empty($tmp)) {
                    $tmp['CheckoutRequestID'] = $CheckoutRequestID;

                    return $tmp;
                }
            }
        }
    }

    //stk transaction payment data
    public function stkCallbackPayment($data)
    {
        if (!(x_has_key($data, 'CheckoutRequestID') && x_has_key($data, 'receipt'))) {
            return;
        }
        $trans_id = md5($data['CheckoutRequestID']);
        if (x_cache_has($trans_id) && x_is_assoc($cache = x_cache_get($trans_id))) {
            $user_id = $cache['user_id'];
            $ref = $data['receipt'];
            $date = $data['date'];
            if (!($user = User::find($user_id))) {
                throw new Exception("STK Transaction user ($user_id) not found!");
            }
            $user_premium_cache = md5('user-premium-' . $user->id);

            //payment data
            $payment_data = [
                'trans_id' => $trans_id,
                'user_id' => $user_id,
                'ref' => $ref,
                'date' => $date,
                'type' => 'stk-callback',
                'amount' => $data['amount'],
                'currency' => 'KES',
                'provider' => 'mpesa',
                'name' => $user->name,
                'email' => $user->email,
                'phone' => (string) $data['phone'],
                'account' => null,
                'data' => null,
            ];

            //existing payment
            if ($payment = Payment::where([
                'user_id' => $user_id,
                'ref' => $ref,
                'date' => $date,
            ])->first()) {

                //delete user premium cache
                x_cache_delete($user_premium_cache);

                //update cache saved
                $this->stkCacheSet($trans_id, ['saved' => 1]);

                //result - payment
                return $payment;
            }

            //new payment
            try {
                //create payment
                $payment = new Payment($payment_data);
                $payment->save();

                //delete user premium cache
                x_cache_delete($user_premium_cache);

                //update cache saved
                $this->stkCacheSet($trans_id, ['saved' => 1]);

                //result - payment
                return $payment;
            } catch (Exception $e) {
                $err = $e->getMessage() . "\r\n" . json_encode($e->errors()) . "\r\n";
                x_file_put($this->_error_log_file, $err, FILE_APPEND);
            }
        }
    }

    //stk cache set
    public function stkCacheSet(string $key, array $value)
    {
        $cache = x_cache_has($key) && x_is_assoc($cache = x_cache_get($key)) ? $cache : [];
        if (x_is_assoc($value)) {
            $cache = array_replace_recursive($cache, $value);
            x_cache_set($key, $cache);
            $stk_cache = x_arr(x_cache_get('stk-cache'));
            if (!in_array($key, $stk_cache)) {
                $stk_cache[] = $key;
            }
            x_cache_set('stk-cache', $stk_cache);

            return 1;
        }
    }

    //stk cache done
    public function stkCacheDone($key, $unsaved=null)
    {
        if (!($key = x_tstr($key))) {
            return;
        }
        $cache = x_cache_has($key) && x_is_assoc($cache = x_cache_get($key)) ? $cache : [];
        if (is_string($unsaved) && ($unsaved = x_tstr($unsaved))) {
            $cache[$unsaved] = 1;
            if (isset($cache['saved']) && $cache['saved']) {
                $cache['done'] = 1;
            }
        } else {
            $cache['done'] = 1;
        }

        return $this->stkCacheSet($key, $cache);
    }

    //service cleanup
    public function cleanup()
    {
        //cleanup stk cache
        $stk_cache_key = 'stk-cache';
        if (x_cache_has($stk_cache_key) && x_is_list($arr = x_cache_get($stk_cache_key), 0)) {
            $unsets = [];
            foreach ($arr as $i => $key) {
                if (
                    x_cache_has($key)
                    && x_is_assoc($cache = x_cache_get($key))
                    && (isset($cache['done']) && $cache['done'] || (
                        (isset($cache['success']) && $cache['success'] || isset($cache['unknown']) && $cache['unknown'])
                        && isset($cache['saved']) && $cache['saved']
                    ))
                ) {
                    x_cache_delete($key);
                    $unsets[] = $i;
                }
            }
            if (!empty($unsets)) {
                foreach ($unsets as $i) {
                    unset($arr[$i]);
                }
            }
            if (empty($arr)) {
                x_cache_delete($stk_cache_key);
            } else {
                x_cache_set($stk_cache_key, $arr);
            }
        }

        //result
        return 1;
    }
}
