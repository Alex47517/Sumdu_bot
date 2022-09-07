<?php
class Bank {
    public static function add($sum) {
        $bank = R::findOne('settings', 'name = ?', ['bank']);
        $bank->value += $sum;
        if ($bank['value'] < 0) return false;
        R::store($bank);
        return true;
    }
    public static function get() {
        $bank = R::findOne('settings', 'name = ?', ['bank']);
        return $bank['value'];
    }
}