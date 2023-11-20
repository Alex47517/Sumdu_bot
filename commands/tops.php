<?php
//
// Command: Топ #
// Text: !топ /top #
// Info: Виводить топ, доступні топи: баланс/діаманти/вікторина #
// Syntax: !топ [назва] [кількість користувачів*] #
// Args: 1 #
// Rank: USER #
//
if ($cmd[1] == 'баланс' or $cmd[1] == 'balance') {
    $default_limit = 5;
    if ($cmd[2]) $limit = round($cmd[2]); else $limit = $default_limit;
    if (!$limit) $limit = $default_limit;
    $top_users = array_values(R::find('users', 'ORDER BY `balance` DESC LIMIT ?', [$limit]));
    $text = '💰 <b>Топ по балансу:</b>
';
    foreach ($top_users as $key => $top_user) {
        $text .= getEmojiNum(($key+1)).' <a href="tg://user?id='.$top_user['tg_id'].'">'.$top_user['nick'].'</a> -> <b>'.$top_user['balance'].'💰</b>
';
    }
    $chat->sendMessage($text);
}
if ($cmd[1] == 'діаманти' or $cmd[1] == 'diamonds' or $cmd[1] == 'алмазы' or $cmd[1] == 'алмази') {
    $default_limit = 5;
    if ($cmd[2]) $limit = round($cmd[2]); else $limit = $default_limit;
    if (!$limit) $limit = $default_limit;
    $top_users = array_values(R::find('users', 'ORDER BY `diamonds` DESC LIMIT ?', [$limit]));
    $text = '💎 <b>Топ по діамантах:</b>
';
    foreach ($top_users as $key => $top_user) {
        $text .= getEmojiNum(($key+1)).' <a href="tg://user?id='.$top_user['tg_id'].'">'.$top_user['nick'].'</a> -> <b>'.$top_user['diamonds'].'💎</b>
';
    }
    $chat->sendMessage($text);
}
if ($cmd[1] == 'вікторина' or $cmd[1] == 'quiz') {
    $themes = [
        1 => 'програмування',
        2 => 'моделювання',
        3 => 'телеком',
        4 => 'математика',
        5 => 'електроніка'
    ];

    $text = "🧠 <b>Топ по вікторинам:</b>\n\n";

    foreach ($themes as $themeId => $themeName) {
        $text .= '<b>'.mb_convert_case($themeName, MB_CASE_TITLE, "UTF-8").':</b>
';

        // Вибірка топ-3 користувачів по кожній темі
        $topUsers = R::getAll("
        SELECT u.nick, COUNT(*) as correct_answers
        FROM quizresults qr
        JOIN quiz q ON qr.question = q.id
        JOIN users u ON qr.user = u.id
        WHERE q.theme = ? AND qr.succes = 1
        GROUP BY qr.user
        ORDER BY correct_answers DESC
        LIMIT 3
    ", [$themeId]);

        foreach ($topUsers as $key => $topUser) {
            $text .= getEmojiNum(($key+1)). " " . $topUser['nick'] . " -> " . $topUser['correct_answers'] . "\n";
        }

        $text .= "\n";
    }
    $chat->sendMessage($text);
}