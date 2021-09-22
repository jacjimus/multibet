<?php

namespace App\Http\Controllers;

use App\Models\ContactForm;
use App\Notifications\ContactFormNotification;
use App\Traits\HasConsole;
use Exception;
use Illuminate\Http\File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use Image;
use PHP_ICO;

class NcmsController extends Controller
{
    //traits
    use HasConsole;

    //test
    public function test()
    {
        dd('x_pid', $pid = x_pid(), x_pid_get($pid, $cmd), $cmd);

        return x_res_json(['message' => 'Hello world!']);
    }

    //test
    public function xtest()
    {
        $email = 'johndoe2021@yopmail.com';
        $status = [];
        $status[] = trans('auth.user-created');
        $status[] = trans('verification.sent-email', ['email' => $email]);

        return x_res_json(['message' => x_join($status, ' ')]);
    }

    //flutterwave handler
    public function flutterwave()
    {

        //callback request
        $request = request();

        //check transaction id
        if (!($request->has('transaction_id') && ($trans_id = x_tstr($request->input('transaction_id'))))) {
            return x_res_json(['message' => 'Invalid transaction callback request.'], 400);
        }

        //flutterwave service
        $s = app('App\Services\FlutterwaveService');

        //verify (save) transaction
        $payment = $s->verify($trans_id);

        //invalid transaction Id
        if (!$payment) {
            return x_res_json(['message' => 'Invalid transaction Id! (' . $trans_id . ')'], 400);
        }

        //success - redirect home
        return redirect('/');
    }

    //contact form
    public function contactForm()
    {
        //save contact form
        $input = request()->input();
        $contact_form = new ContactForm($input);
        if (!$contact_form->save()) {
            return x_res_json(['message' => trans('message.contact-form-error')], 500);
        }

        //send notification
        if (x_is_email($address = config('mail.contact-form-address'))) {
            $notification = new ContactFormNotification($contact_form);
            Notification::route('mail', $address)
            -> notify($notification);
        }

        //success
        return x_res_json(['message' => trans('message.contact-form-success')]);
    }

    //show view
    public function showView($path)
    {
        return x_res_view($path, request()->all());
    }

    //logout
    public function logout()
    {
        app('App\Http\Controllers\Auth\LoginController')->logout(request());

        return redirect('/');
    }

    //favicon
    public function favicon()
    {
        //favicon path
        $path = storage_path('app/public/favicon.ico');

        //create favicon
        if (request()->has('fresh') || !x_is_file($path)) {
            //set vars
            $size = 64;
            $png = dirname($path) . '/favicon.png';
            $icon = resource_path(config('app.icon'));

            //check icon
            if (!x_is_file($icon)) {
                abort(404, 'App icon not found!');
            }

            //create png
            Image::make($icon)
            -> resize($size, $size, function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            })
            -> save($png);

            //convert png to ico
            $ico_lib = new PHP_ICO($png);
            $ico_lib->save_ico($path);

            //delete png
            x_file_delete($png);
        }

        //response
        return $this->file($path, ['Content-Type' => 'image/x-icon']);
    }

    //app image
    public function image()
    {
        return $this->pubPngFile(resource_path(config('app.image')));
    }

    //app logo
    public function logo()
    {
        return $this->pubPngFile(resource_path(config('app.logo')));
    }

    //app icon
    public function icon()
    {
        return $this->pubPngFile(resource_path(config('app.icon')));
    }

    //file response - app/public/*.png
    public function pubPngFile($source)
    {
        //check source - abort 404 is missing
        if (!x_is_file($source)) {
            abort(404, "File not found! ($source)");
        }

        //create png image
        $path = storage_path('app/public') . '/' . x_file_name($source) . '.png';
        if (request()->has('fresh') || !x_is_file($path)) {
            Image::make($source)->save($path);
        }

        //response
        return $this->file($path, ['Content-Type' => 'image/png']);
    }

    //file response
    public function file($path, array $headers=null)
    {
        return x_res_file($path, $headers);
    }

    //php info
    public function info()
    {
        phpinfo();
        exit();
    }

    //link filesystem
    public function link()
    {
        $buffer = [];
        $buffer[] = 'Filesystem Link';
        $buffer[] = ($line = '----------------------------------------------------------');
        if (x_is_assoc($links = config('filesystems.links'))) {
            foreach ($links as $link => $target) {
                try {
                    $res = x_symlink($target, $link);
                    $res = $res ? 'success' : 'fail';
                    $buffer[] = "Symlink '$link' - '$target' = $res";
                } catch (Exception $e) {
                    $buffer[] = "Symlink Error '$link' - '$target': " . $e->getMessage();
                }
            }
        }
        $buffer[] = 'done.';

        return x_res_text(x_join($buffer, PHP_EOL));
    }

    //console run
    public function console(string $cmd=null, string $type='artisan')
    {
        //check vars
        if (!($cmd = trim($cmd))) {
            return x_res_text('Kindly specify a command to run.', 400);
        }
        if (!($type = trim($type))) {
            return x_res_text('Kindly specify a command type.', 400);
        }

        //console
        $console = $this->getConsoleService();

        //run command
        switch ($type) {
            //artisan
            case 'artisan':
                $res = $console->runArtisan($cmd);

                break;

            //exec
            case 'exec':
                $res = $console->runExec($cmd);

                break;

            //not supported
            default:
                return x_res_text("Unsupported command type '$type'!", 400);
        }

        //output buffer
        $eol = PHP_EOL;
        $buffer = [];
        $buffer[] = "Console: $type $cmd";
        $buffer[] = ($line = '----------------------------------------------------------');
        $output = $console->output;
        if (is_array($output)) {
            $output = trim(x_join($output, $eol));
        } else {
            $output = x_str($output, 1, 1);
        }
        if ($output) {
            $buffer[] = $output;
            $buffer[] = $line;
        }
        $buffer[] = ($console->success ? 'Success' : 'Failure') . '! Exit Code: ' . $console->exit;
        if (!$console->success && ($error = trim($console->error))) {
            $buffer[] = $line;
            $buffer[] = "Error: $error";
        }

        //output response
        return x_res_text(x_join($buffer, $eol));
    }
}
