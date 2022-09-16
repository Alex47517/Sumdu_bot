<?php
namespace api;
class update {
    public static $update_id;
    public static $message;
    public static $message_id;
    public static $chat;
    public static $from;
    public static $callback_data;
    public static $date;
    public static $reply;
    public static $reply_user_id;
    public static $btn_id;
    public static $new_chat_user_id;
    public static $new_chat_member;
    public function __construct($request) {
        self::$update_id = $request['update_id'];
        if ($request['callback_query']) {
            self::$date = date('U');
            self::$from = $request['callback_query']['from'];
            self::$chat = $request['callback_query']['message']['chat'];
            self::$callback_data = $request['callback_query']['data'];
            self::$btn_id = $request['callback_query']['message']['message_id'];
        } else {
            self::$date = $request['message']['date'];
            self::$message = $request['message'];
            self::$message_id = $request['message']['message_id'];
            self::$from = $request['message']['from'];
            self::$chat = $request['message']['chat'];
        }
        if ($request['message']['reply_to_message']) {
            self::$reply = $request['message']['reply_to_message'];
            self::$reply_user_id = $request['message']['reply_to_message']['from']['id'];
        }
        if ($request['message']['new_chat_member']['id']) {
            self::$new_chat_user_id = $request['message']['new_chat_member']['id'];
            self::$new_chat_member = $request['message']['new_chat_member'];
        }
    }
}