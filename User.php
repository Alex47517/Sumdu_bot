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
        $user = R::dispense('users');
        $user->tg_id = $from['id'];
        $user->nick = $nick;
        $user->username = $from['username'];
        $user->rank = 'USER';
        $user->balance = 15;
        R::store($user);
        $this->user = $user;
        return true;
    }
    public function update($param, $value = null) {
        $this->user[$param] = $value;
        R::store($this->user);
        return true;
    }

}