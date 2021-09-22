<?php

namespace App\Providers;

use Exception;
use Illuminate\Support\ServiceProvider;

class ConfigSetupProvider extends ServiceProvider
{
    /**
     * Register runtime config setup.
     *
     * @return void
     */
    public function register()
    {
        //setup app.url
        $url = trim(config('app.url'), '/');
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            dump(['app.url' => $url]);

            throw new Exception('Config app.url is invalid!');
        }
        config(['app.url' => $url]);

        //setup app.region
        $region = strtoupper(trim(config('app.region')));
        if (!array_key_exists($region, config('region_codes'))) {
            dump(['app.region' => $region]);

            throw new Exception('Config app.region is invalid!');
        }
        config(['app.region' => $region]);

        //setup app.currency
        $currency = strtoupper(trim(config('app.currency')));
        if (!array_key_exists($currency, config('currency_codes'))) {
            dump(['app.currency' => $region]);

            throw new Exception('Config app.currency is invalid!');
        }
        config(['app.currency' => $currency]);

        //update configs - replace
        if (is_array($items = config()->all())) {
            //app package
            $package = trim(config('app.package'));

            //default assets
            $default_assets = [
                'assets/images/icon.png',
                'assets/images/logo.png',
                'assets/images/app.png',
            ];

            //method - replace
            $__replace = function ($val) use (&$__replace, &$package, &$default_assets) {
                if (is_array($val)) {
                    //recursion
                    foreach ($val as $key => $value) {
                        $val[$key] = $__replace($value);
                    }
                } else {
                    //replace "config::*"
                    if (is_string($val) && preg_match('/^config\:\:([\w\.]+)$/s', trim($val), $matches)) {
                        $val = config($matches[1]);
                    }

                    //package - default assets
                    if (strlen($package) && in_array($val, $default_assets)) {
                        $tmp = str_replace('assets/images', "assets/images/$package", $val);
                        if (file_exists(resource_path($tmp))) {
                            $val = $tmp;
                        }
                    }
                }

                return $val;
            };

            //config replace
            foreach ($items as $key => $value) {
                config([$key => $__replace($value)]);
            }
        }
    }
}
