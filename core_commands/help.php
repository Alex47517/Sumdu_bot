<?php
$initiators = ['!справка', '!довідка'];
if (in_array($cmd[0], $initiators)) {
    if ($cmd[1]) {
        $action = R::findOne('actions', 'initiator = ?', [$cmd[1]]);
        if (!$action) {
            $file = R::findOne('commandfiles', 'name = ?', [$cmd[1]]);
            if (!$file) $file = R::findOne('commandfiles', 'filename = ?', [$cmd[1]]);
        } else {
            $file = R::load('commandfiles', $action['file_id']);
        }
        if (!$file) custom_error('Помилка 404', 'Команда не знайдена');
        $send_initiators = '';
        $actions = R::getAll('SELECT * FROM actions WHERE `file_id` = ?', [$file['id']]);
        foreach ($actions as $action) {
            $send_initiators .= '🔹 <code>'.$action['initiator'].'</code> ['.$action['type'].'], '.$action['args'].'
';
        }
        if (!in_array($file['rank'], ['MODER', 'ChatAdmin', 'ADMIN', 'OWNER'])) $file['rank'] = 'USER';
        $chat->sendMessage('ℹ Довідка по команді <b>'.$file['name'].'</b>

<b>Опис: </b>'.$file['info'].'
<b>Файл: </b>'.$file['filename'].'
<b>Мінімальний ранг: </b>'.$file['rank'].'

<b>Синтаксис: </b>'.$file['syntax'].'

<b>Ініціатори (ініціатор [тип], кількість аргументів):</b>
'.$send_initiators);
    } else {
        $commands = R::getAll('SELECT * FROM commandfiles');
        $text = '📄 <b>Список модулів:</b>
Щоб прочитати довідку - пишіть: !довідка [назва команди]
';
        foreach ($commands as $command) {
            $text .= '<code>'.$command['name'].'</code>
';
        }
        $chat->sendMessage($text);
    }
    die();
}