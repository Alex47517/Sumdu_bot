<?php
namespace api;
class update {
    public static $update_id;
    public static $message;
    public static $chat;
    public function __construct($request) {
        self::$update_id = $request['update_id'];
        self::$message = $request['message'];
        self::$chat = $request['message']['chat'];
    }
}