<?php

namespace App\Services\Fstats;

use App\Traits\HasRequest;
use Exception;

class FsSession
{
    //traits
    use HasRequest;

    //vars
    protected $_cache = 'fs-session';

    protected $_session;

    protected $_session_lifetime = 60 * 60 * 24 * 30; //30 days (seconds)

    private $_session_key = 'cartalyst_sentinel';

    private $_credentials;

    private $_auth = false;

    //get session data
    public function getSession()
    {
        //init session
        if (!x_is_assoc($session = $this->_session)) {
            if (!x_is_assoc($session = x_cache_get($this->_cache, null))) {
                $session = [];
            }
        }

        //result - session
        return $this->_session = $session;
    }

    //delete session
    public function deleteSession()
    {
        //delete cache
        x_cache_delete($this->_cache);

        //unset session
        $this->_session = null;
        x_dump(' - session deleted.');
    }

    //update session
    public function updateSession(array $response=null)
    {
        //get current session data
        $session = $this->getSession();
        $old = empty($session) ? null : md5(json_encode($session)); //old session hash

        //set session cookie
        if (x_has_key($response, 'set_cookie') && ($set_cookie = trim($response['set_cookie']))) {
            //parse cookie
            if (x_is_assoc($cookie = $this->parseCookie($set_cookie))) {
                //update session cookie data
                $session = array_replace($session, $cookie);
            }
        }

        //update session if changed
        $new = empty($session) ? null : md5(json_encode($session)); //new session hash
        if ($new != $old && x_is_assoc($session)) {
            //cache updated session data
            $key = $this->_cache;
            if (!x_cache_set($key, $session, $this->_session_lifetime)) {
                throw new Exception(sprintf('Failed to save cache data! (%s)', $key));
            }

            //update current session
            $this->_session = $session;
            x_dump(' - session updated');
        }

        //result - updated session
        return $session;
    }

    //parse response cookie
    public function parseCookie(string $set_cookie)
    {
        //parse buffer
        $buffer = [];

        //split cookie string - key value pairs
        $pairs = x_split(';', $set_cookie, $c, $remove_empty=true, $trim_string=true);

        //parse key value pairs
        foreach ($pairs as $pair) {
            $key = null;
            $val = null;

            //split key & value pair string - parts [key, value]
            $parts = x_split('=', $pair, $c, 1, 1);
            foreach ($parts as $i => $part) {
                if ($i) {
                    $key = $parts[$i - 1];
                }
                $val = $part;
            }

            //set key & value
            if (($x = strpos($key, ',')) !== false) {
                $key = trim(substr($key, $x + 1));
            }
            $key = trim($key);
            $val = urldecode($val);

            //buffer key & value
            if ($key && $val) {
                $buffer[$key] = $val;
            }
        }

        //result - parsed cookie data [key => value...]
        return !empty($buffer) ? $buffer : null;
    }
}
