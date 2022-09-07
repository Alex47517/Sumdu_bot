<?php
$initiators = ['!оновити команди', '!обновить команды', '/update_commands'];
if (in_array($msg, $initiators)) {
    Permissions::Admin($user->user);
    $tg_response = $chat->sendMessage('⏳ <b>Оновлюємо список команд...</b>

<em>Пошук нових файлів...</em>');
    $sended_id = $tg_response->result->message_id;
    $dir = __DIR__.'/../commands/';
    $files = scandir($dir);
    $errors = 0;
    $finded = 0;
    $added = 0;
    $commands = 0;
    $all_added = [];
    foreach($files as $file) {
        if (($file !== '.') and ($file !== '..')) {
            if (R::findOne('commandfiles', 'filename = ?', [$file])) continue;
            $finded++;
            $script = file_get_contents($dir.''.$file);
            $command_name = explode('// Command: ', $script)[1];
            $command_name = explode(' #', $command_name)[0];
            $info_text = explode('// Info: ', $script)[1];
            $info_text = explode(' #', $info_text)[0];
            $args = explode('// Args: ', $script)[1];
            $args = explode(' #', $args)[0];
            $syntax = explode('// Syntax: ', $script)[1];
            $syntax = explode(' #', $syntax)[0];
            $rank = explode('// Rank: ', $script)[1];
            $rank = explode(' #', $rank)[0];
            $command_text_initiators = explode('// Text: ', $script)[1];
            $command_text_initiators = explode(' #', $command_text_initiators)[0];
            $text_initiators = explode(' ', $command_text_initiators);
            if (!$text_initiators[0]) $text_initiators = [];
            $command_display_initiators = explode('// Display: ', $script)[1];
            $command_display_initiators = explode(' #', $command_display_initiators)[0];
            $display_initiators = explode(' ', $command_display_initiators);
            if (!$display_initiators[0]) $display_initiators = [];
            $command_callback_initiators = explode('// Callback: ', $script)[1];
            $command_callback_initiators = explode(' #', $command_callback_initiators)[0];
            $callback_initiators = explode(' ', $command_callback_initiators);
            if (!$callback_initiators[0]) $callback_initiators = [];
            if (!$command_name or !$info_text or !is_numeric($args) or !$syntax) {
                $errors++;
                $chat->sendMessage('💢 <b>Виникла помилка при зчитуванні файлу команди</b>

Файл: <b>'.$file.'</b>

<em>Порушено правила опису команди:
Відсутня назва / інформація про команду / кількість аргументів / опис синтаксису</em>');
            } else {
                $add = [];
                $i = 0;
                foreach ($text_initiators as $key => $initiator) {
                    $add[$i] = ['name' => $command_name, 'rank' => $rank, 'initiator' => $initiator, 'args' => $args, 'type' => 'text', 'file' => $file]; $i++;
                }
                foreach ($display_initiators as $key => $initiator) {
                    $add[$i] = ['name' => $command_name, 'rank' => $rank, 'initiator' => $initiator, 'type' => 'display', 'file' => $file]; $i++;
                }
                foreach ($callback_initiators as $key => $initiator) {
                    $add[$i] = ['name' => $command_name, 'rank' => $rank, 'initiator' => $initiator, 'type' => 'callback', 'file' => $file]; $i++;
                }
                $db_file = R::dispense('commandfiles');
                $db_file->name = $command_name;
                $db_file->filename = $file;
                $db_file->info = $info_text;
                $db_file->syntax = $syntax;
                $db_file->rank = $rank;
                R::store($db_file);
                foreach ($add as $action) {
                    $command = R::dispense('actions');
                    $command->initiator = $action['initiator'];
                    $command->type = $action['type'];
                    $command->file_id = $db_file['id'];
                    if ($action['type'] == 'text') $command->args = $action['args'];
                    R::store($command);
                    $added++;
                }
                $all_added = array_merge($all_added, $add);
                $commands++;
            }
        }
    }
    foreach ($all_added as $add) {
        if ($add['type'] == 'text') $txt_args = '['.$args.'] '; else $txt_args = null;
        $added_init .= '🔹 <b>'.$add['name'].'</b> <em>['.$add['rank'].']</em>: '.$add['type'].' '.$txt_args.'"'.$add['initiator'].'" <em>із '.$add['file'].'</em>
';
    }
    if (!$added_init) $added_init = 'Відсутні';
    $text = '✅ <b>Список команд оновлено!</b>

Додано нових команд: <b>'.$commands.'</b>
Додано нових ініціаторів: <b>'.$added.'</b>
Помилок: <b>'.$errors.'</b>
Перевірено нових файлів: <b>'.$finded.'</b>

<b>Додані ініціатори:</b>
'.$added_init;
    if (!$errors) $chat->editMessageText($text, null, $sended_id); else $chat->sendMessage($text);
}