<?php

namespace App\Request;

class HttpRequestBuilder
{
    public static function build(array $data): string
    {
        $request = $data['method'] . ' ' . $data['path'] . " HTTP/1.1\r\n";
        $request .= "Connection: keep-alive\r\n";
        $request .= "Accept: */*\r\n";
        $request .= isset($data['body']) ? ("Content-Type: application/json\r\n\r\n" . json_encode($data['body'])) : '';

        return $request;
    }
}
