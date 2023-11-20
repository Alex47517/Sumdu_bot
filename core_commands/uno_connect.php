<?php
use api\chat as chat;
if ($cmd[0] == '/start' && $cmd[1] == 'uno') {
    require __DIR__.'/../commands/uno.php';
}
if ($cmd[0] == '/start' && $cmd[1] == 'durak') {
    require __DIR__.'/../commands/durak.php';
}