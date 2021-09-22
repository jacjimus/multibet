<?php
/**
 * PHP Settings.
 */

//error reporting
error_reporting(E_ALL);
ini_set('display_errors', 'On');

//float precision fix
ini_set('precision', 10);
ini_set('serialize_precision', 10);

/*
//extensions
if (!extension_loaded('gd')){
    if (!dl('gd.so')) abort(500, 'Extension "gd" not loaded!');
}
*/