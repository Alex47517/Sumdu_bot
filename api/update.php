<?php
namespace api;
class update {
    public static $update_id;
    public static $message;
    public static $chat;
    public static $from;
    public static $callback_data;
    public static $date;
    public function __construct($request) {
        self::$update_id = $request['update_id'];
        if ($request['callback_query']) {
            self::$date = date('U');
            self::$from = $request['callback_query']['from'];
            self::$chat = $request['callback_query']['message']['chat'];
            self::$callback_data = $request['callback_query']['data'];
        } else {
            self::$date = $request['message']['date'];
            self::$message = $request['message'];
            self::$from = $request['message']['from'];
            self::$chat = $request['message']['chat'];
        }
    }
}