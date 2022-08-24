<?php
function menu() {
    global $user;
    global $chat;
    $user->update('display');
    $chat->sendMessage('🔅 <b>Ви завершили усі активні сессії</b>'); die();
}
function args_error($action) {
    global $chat;
    $file = R::load('commandfiles', $action['file_id']);
    $chat->sendMessage('♨ <b>Недостатньо аргументів</b>

<b>Команда: </b>'.$file['name'].'
<b>Синтаксис: </b>'.$file['syntax'].'

<em>'.$file['info'].'</em>'); die();
}
function custom_error($title, $description, $exit = false) {
    global $chat;
    if ($exit) $exit_text = '<em>Для виходу - /start</em>'; else $exit_text = null;
    $chat->sendMessage('♨ <b>'.$title.'</b>

'.$description.'

'.$exit_text); die();
}
function translit_sef($value) {
    $converter = array(
        'а' => 'a',    'б' => 'b',    'в' => 'v',    'г' => 'g',    'д' => 'd',
        'е' => 'e',    'ё' => 'e',    'ж' => 'zh',   'з' => 'z',    'и' => 'i',
        'й' => 'y',    'к' => 'k',    'л' => 'l',    'м' => 'm',    'н' => 'n',
        'о' => 'o',    'п' => 'p',    'р' => 'r',    'с' => 's',    'т' => 't',
        'у' => 'u',    'ф' => 'f',    'х' => 'h',    'ц' => 'c',    'ч' => 'ch',
        'ш' => 'sh',   'щ' => 'sch',  'ь' => '',     'ы' => 'y',    'ъ' => '',
        'э' => 'e',    'ю' => 'yu',   'я' => 'ya',   'і' => 'i',    'ї' => 'iy',

        'А' => 'A',    'Б' => 'B',    'В' => 'V',    'Г' => 'G',    'Д' => 'D',
        'Е' => 'E',    'Ё' => 'E',    'Ж' => 'Zh',   'З' => 'Z',    'И' => 'I',
        'Й' => 'Y',    'К' => 'K',    'Л' => 'L',    'М' => 'M',    'Н' => 'N',
        'О' => 'O',    'П' => 'P',    'Р' => 'R',    'С' => 'S',    'Т' => 'T',
        'У' => 'U',    'Ф' => 'F',    'Х' => 'H',    'Ц' => 'C',    'Ч' => 'Ch',
        'Ш' => 'Sh',   'Щ' => 'Sch',  'Ь' => '',     'Ы' => 'Y',    'Ъ' => '',
        'Э' => 'E',    'Ю' => 'Yu',   'Я' => 'Ya',   'І' => 'I',    'Ї' => 'Iy',
    );

    $value = mb_strtolower($value);
    $value = strtr($value, $converter);
    $value = mb_ereg_replace('[^-0-9a-z]', '-', $value);
    $value = mb_ereg_replace('[-]+', '-', $value);
    $value = trim($value, '-');

    return $value;
}
function translit_file($filename) {
    $converter = array(
        'а' => 'a',    'б' => 'b',    'в' => 'v',    'г' => 'g',    'д' => 'd',
        'е' => 'e',    'ё' => 'e',    'ж' => 'zh',   'з' => 'z',    'и' => 'i',
        'й' => 'y',    'к' => 'k',    'л' => 'l',    'м' => 'm',    'н' => 'n',
        'о' => 'o',    'п' => 'p',    'р' => 'r',    'с' => 's',    'т' => 't',
        'у' => 'u',    'ф' => 'f',    'х' => 'h',    'ц' => 'c',    'ч' => 'ch',
        'ш' => 'sh',   'щ' => 'sch',  'ь' => '',     'ы' => 'y',    'ъ' => '',
        'э' => 'e',    'ю' => 'yu',   'я' => 'ya',   'і' => 'i',    'ї' => 'iy',

        'А' => 'A',    'Б' => 'B',    'В' => 'V',    'Г' => 'G',    'Д' => 'D',
        'Е' => 'E',    'Ё' => 'E',    'Ж' => 'Zh',   'З' => 'Z',    'И' => 'I',
        'Й' => 'Y',    'К' => 'K',    'Л' => 'L',    'М' => 'M',    'Н' => 'N',
        'О' => 'O',    'П' => 'P',    'Р' => 'R',    'С' => 'S',    'Т' => 'T',
        'У' => 'U',    'Ф' => 'F',    'Х' => 'H',    'Ц' => 'C',    'Ч' => 'Ch',
        'Ш' => 'Sh',   'Щ' => 'Sch',  'Ь' => '',     'Ы' => 'Y',    'Ъ' => '',
        'Э' => 'E',    'Ю' => 'Yu',   'Я' => 'Ya',   'І' => 'I',    'Ї' => 'Iy',
    );
    $new = '';
    $file = pathinfo(trim($filename));
    if (!empty($file['dirname']) && @$file['dirname'] != '.') {
        $new .= rtrim($file['dirname'], '/') . '/';
    }
    if (!empty($file['filename'])) {
        $file['filename'] = str_replace(array(' ', ','), '-', $file['filename']);
        $file['filename'] = strtr($file['filename'], $converter);
        $file['filename'] = mb_ereg_replace('[-]+', '-', $file['filename']);
        $file['filename'] = trim($file['filename'], '-');
        $new .= $file['filename'];
    }
    if (!empty($file['extension'])) {
        $new .= '.' . $file['extension'];
    }
    return $new;
}
function new_command($command) {
    if ($command['text']) $add .= '
// Text: '.$command['text'].' #';
    if ($command['display']) $add .= '
// Display: '.$command['display'].' #';
    if ($command['callback']) $add .= '
// Callback: '.$command['callback'].' #';
    $filename = translit_sef($command['name']).'_custom.php';
    $file_data = '<?php
//
// Command: '.$command['name'].' #'.$add.'
// Info: '.$command['info'].' #
// Syntax: '.$command['syntax'].' #
// Args: '.$command['args'].' #
// Rank: '.$command['rank'].' #
//
'.$command['code'];
    $new_command_file = fopen(__DIR__.'/commands/'.$filename, 'w+');
    fwrite($new_command_file, $file_data);
    fclose($new_command_file);
    return true;
}