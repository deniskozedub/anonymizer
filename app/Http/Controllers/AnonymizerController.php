<?php

namespace App\Http\Controllers;

use App\Traits\ParseHeaderTrait;
use Illuminate\Http\Request;

class AnonymizerController extends Controller
{
    use ParseHeaderTrait;

    public function index(Request $request)
    {
        if (!$request->has('uri')){
            return 'Invalid URI';
        }
        $data_url = parse_url($request->get('uri'));
        $data_url['path'] = isset($data_url['path']) ? $data_url['path'] : '/';
        $data_url['query'] = isset($data_url['query']) ? "?$data_url[query]" : '';

        if ($data_url['scheme'] === 'http'){
            $fp = fsockopen("tcp://$data_url[host]", 80);
        }else{
            $fp = fsockopen("ssl://$data_url[host]", 443);
        }

        if (!$fp){
            return 'Something went wrong';
        }

        $out = "$_SERVER[REQUEST_METHOD] $data_url[path]$data_url[query] $_SERVER[SERVER_PROTOCOL]\r\n";
        $out .= "Host: $data_url[host]\r\n";
        $out .= "User-Agent: $_SERVER[HTTP_USER_AGENT]\r\n";
        $out .= "Accept: $_SERVER[HTTP_ACCEPT]\r\n";
        $out .= "Connection: Close\r\n\r\n";

        fwrite($fp, $out);
        $body = '';
        while (!feof($fp)) {
            $body .=  fgets($fp, 128);
        }
        fclose($fp);

        list($stringHeaders, $body) = explode("\r\n\r\n",$body);
        $headers =  $this->parse_headers($stringHeaders);
        list($contentType, $contentCharset) = explode(';', $headers['Content-Type']
                                                                           ?? $headers['content-type']);

      /*  if ($contentType === 'text/html'){
            // обработка html
        }elseif ($contentType === 'text/css'){
            // обработка css
        }else{
            // иначе
        }*/


        return $body;

    }

}
