<?php


namespace App\Traits;


trait ParseHeaderTrait
{
    final private function parse_headers($headers)
    {
        $headers = preg_replace('/^\r\n/m', '', $headers);
        $headers = preg_replace('/\r\n\s+/m', ' ', $headers);
        preg_match_all('/^([^: ]+):\s(.+?(?:\r\n\s(?:.+?))*)?\r\n/m', $headers . "\r\n", $matches);

        $result = array();
        foreach ($matches[1] as $key => $value)
            $result[$value] = (array_key_exists($value, $result) ? $result[$value] . "\n" : '') . $matches[2][$key];

        return $result;
    }
}
