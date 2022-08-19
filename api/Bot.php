<?php
namespace api;
class Bot {
    private static $token;
    public function __construct($bot_token) {
        self::$token = $bot_token;
    }
    public static function request($method, $params) {
        $context = stream_context_create(array(
            'http' => array('ignore_errors' => true),
        ));
        $params = http_build_query($params);
        $url = 'https://api.telegram.org/bot'.self::$token.'/'.$method.'?'.$params;
        $response = json_decode(file_get_contents($url, false, $context));
        if (!$response->ok) {
            Log::admin('TG_API_ERROR', '♨ <b>Telegram API</b>:
<b>Error</b> '.$response->error_code.'. '.$response->description.'');
            return false;
        }
        return $response;
    }
}
