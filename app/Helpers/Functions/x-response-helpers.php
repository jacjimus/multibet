<?php

//response - text
function x_res_text($text, int $code=200)
{
    return response(x_str($text, 0, 1), $code)
    -> header('Content-Type', 'text/plain');
}

//response - json
function x_res_json($data, int $code=200)
{
    return response()->json($data, $code);
}

//response - file
function x_res_file($path, array $headers=null, bool $download=false, string $name=null)
{
    //check file - abort 404 if missing
    if (!x_is_file($path)) {
        abort(404, "File not found! ($path)");
    }

    //set headers - Content-Type
    $headers = x_arr($headers);
    x_array_norm_keys($headers, [$key = 'Content-Type']);
    if (!x_has_key($headers, $key)) {
        $type = x_file_mime_type($path);
        if ($type = trim($type)) {
            $headers[$key] = $type;
        }
    }

    //download response
    if ($download) {
        $name = ($name = trim($name)) ? $name : basename($path);

        return response()->download($path, $name, $headers);
    }

    //file response
    return response()->file($path, $headers);
}

//response - view
function x_res_view(string $view, array $data=null, int $code=200)
{
    response()->view($view, x_arr($data), $code);
}
