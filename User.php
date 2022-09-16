<?php

class User {
    public $user;
    public function loadByTGID($tg_id) {
        $user = R::findOne('users', 'tg_id = ?', [$tg_id]);
        if ($user) {
            $this->user = $user;
            return true;
        } else {
            return false;
        }
    }
    public function loadByID($id) {
        $user = R::load('users', $id);
        if ($user) {
            $this->user = $user;
            return true;
        } else {
            return false;
        }
    }
    public function loadByNick($tg_id) {
        $user = R::findOne('users', 'nick = ?', [$tg_id]);
        if ($user) {
            $this->user = $user;
            return true;
        } else {
            return false;
        }
    }
    public function loadByUsername($tg_id) {
        $user = R::findOne('users', 'username = ?', [$tg_id]);
        if ($user) {
            $this->user = $user;
            return true;
        } else {
            return false;
        }
    }
    public function newUser($from) {
        $user = R::findOne('users', 'tg_id = ?', [$from['id']]);
        if ($user) {
            $this->user = $user;
            return false;
        }
        if ($from['username']) {
            $nick = $from['username'];
        } else {
            $nick = $from['id'];
        }
        Bank::add(4985);
        $user = R::dispense('users');
        $user->tg_id = $from['id'];
        $user->nick = $nick;
        $user->username = $from['username'];
        $user->rank = 'USER';
        $user->balance = 15;
        $user->reg_date = date('d.m.y');
        R::store($user);
        $this->user = $user;
        return true;
    }
    public function update($param, $value = null) {
        $this->user[$param] = $value;
        R::store($this->user);
        return true;
    }
    public function LocalStorageSet($key, $value) {
        $add = [$key => $value];
        if ($this->user['tmp']) {
            $tmp = json_decode($this->user['tmp'], true);
            $new_tmp = array_merge($tmp, $add);
        } else $new_tmp = $add;
        $this->update('tmp', json_encode($new_tmp));
        return true;
    }
    public function LocalStorageGet($key) {
        $tmp = json_decode($this->user['tmp'], true);
        return $tmp[$key];
    }
    public function LocalStorageClear() {
        $this->update('tmp');
        return true;
    }
    public function addBal($add) {
        if (Bank::add($add*-1)) {
            $new_user_balance = $this->user['balance']+$add;
            $this->update('balance', $new_user_balance);
        } else {
            custom_error('Банк просить вибачення', 'Але на жаль він збанкрутував');
        }
    }
    public function addToBlackList($time) {
        if ($time == 0) $store = 1; else $store = date('U')+round($time);
        $this->update('blacklist', ($store));
        return true;
    }
}