<?php
namespace api;
use R;

class stats {
    public static function casino($result) {
        global $user;
        $record = R::dispense('statscasino');
        $record->user_id = $user->user['id'];
        $record->profit = $result*-1;
        $record->date = date('U');
        R::store($record);
        return true;
    }
    public static function apples($result, $moves) {
        global $user;
        $record = R::dispense('statsapples');
        $record->user_id = $user->user['id'];
        $record->profit = $result*-1;
        $record->moves = $moves;
        $record->date = date('U');
        R::store($record);
        return true;
    }
    public static function aviator($result, $moves) {
        global $user;
        $record = R::dispense('statsaviator');
        $record->user_id = $user->user['id'];
        $record->profit = $result*-1;
        $record->moves = $moves;
        $record->date = date('U');
        R::store($record);
        return true;
    }
}