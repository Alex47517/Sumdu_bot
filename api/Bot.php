<?php
namespace api;
class Bot {
    private static $token;
    public function __construct($bot_token) {
        self::$token = $bot_token;
    }
    public static function request($method, $params, $associative = false) {
        $context = stream_context_create(array(
            'http' => array('ignore_errors' => true),
        ));
        $params = http_build_query($params);
        $url = 'https://api.telegram.org/bot'.self::$token.'/'.$method.'?'.$params;
        $response = json_decode(file_get_contents($url, false, $context), $associative);
        if (!$response->ok && !$associative) {
            Log::admin('TG_API_ERROR', '♨ <b>Telegram API</b>:
<b>Error</b> '.$response->error_code.'. '.$response->description.'');
            return false;
        }
        return $response;
    }
    public static function getUserProfilePhotos($user_id, $offset = 0, $limit = 1) {
        $params = [
            'user_id' => $user_id,
            'offset' => $offset,
            'limit' => $limit,
        ];
        return self::request('getUserProfilePhotos', $params, true);
    }
    public static function getFile($file_id) {
        $params = [
            'file_id' => $file_id,
        ];
        return self::request('getFile', $params, true);
    }
    public static function storeFile($file_path, $store_path, $filename = null) {
        $file_from_tgrm = "https://api.telegram.org/file/bot".self::$token."/".$file_path;
        $array = explode('.', $file_path);
        $ext = end($array);
        if(!$filename) $filename = time().".".$ext; else $filename = $filename.'.'.$ext;
        if (copy($file_from_tgrm, $store_path."/".$filename)) return $filename; else return false;
    }
}
