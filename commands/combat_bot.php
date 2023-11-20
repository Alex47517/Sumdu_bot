<?php
//
// Command: combat #
// Callback: combat-bot #
// Display: combat-bot #
// Info: Модуль для гри "Combat" (проти бота) #
// Syntax: - #
// Args: 0 #
// Rank: USER #
//
use api\{Bot as Bot, chat as chat, ChatMember as ChatMember, Log as Log, update as update};
function countAliveWarriors() {
    global $user;
    $aliveCount = 0;
    for ($i = 1; $user->LocalStorageGet('warrior_' . $i); $i++) {
        if ($user->LocalStorageGet('warrior_health_' . $i) > 0) {
            $aliveCount++;
        }
    }
    return $aliveCount;
}
function countBotAliveWarriors() {
    global $user;
    $aliveCount = 0;
    for ($i = 1; $user->LocalStorageGet('warrior_name_' . $i); $i++) {
        if ($user->LocalStorageGet('bot-warrior_health_' . $i) > 0) {
            $aliveCount++;
        }
    }
    return $aliveCount;
}
function getBlockedCords() {
    global $user;
    $coords = [];
    for ($i = 1; $user->LocalStorageGet('warrior_'.$i); $i++) {
        $coords[] = [$user->LocalStorageGet('warrior_location-x_'.$i), $user->LocalStorageGet('warrior_location-y_'.$i)];
    }
    for ($i = 1; $user->LocalStorageGet('bot-warrior_name_'.$i); $i++) {
        $coords[] = [$user->LocalStorageGet('bot-warrior_location-x_'.$i), $user->LocalStorageGet('bot-warrior_location-y_'.$i)];
    }
    return $coords;
}
function check_canMove($current, $destination, $n = 8) {
    $dx = $destination[0] - $current[0];
    $dy = $destination[1] - $current[1];

    if ($destination[0] < 0 || $destination[0] >= $n || $destination[1] < 0 || $destination[1] >= $n) {
        return false;
    }

    if ((abs($dx) <= 1) && (abs($dy) <= 1)) {
        // Получаем массив заблокированных клеток
        $blockedCords = getBlockedCords();
        foreach ($blockedCords as $coord) {
            if ($coord[0] == $destination[0] && $coord[1] == $destination[1]) {
                return false; // Эта клетка заблокирована
            }
        }
        return true; // Клетка свободна и находится на расстоянии в одну клетку
    }

    return false;
}
function getField() {
    global $user;
    for ($x = 0; $x < 8; $x++) {
        for ($y = 0; $y < 8; $y++) {
            $find = false;
            for ($i = 1; $user->LocalStorageGet('warrior_'.$i); $i++) {
                if ($user->LocalStorageGet('warrior_location-x_'.$i) == $x && $user->LocalStorageGet('warrior_location-y_'.$i) == $y) {
                    if ($user->LocalStorageGet('warrior_type_'.$i) == 'infantry') {
                        if ($user->LocalStorageGet('selected') != $i) $emoji = '🔸';
                        else $emoji = '🔶';
                    } else {
                        if ($user->LocalStorageGet('selected') != $i) $emoji = '🔹';
                        else $emoji = '🔷';
                    }
                    if ($user->LocalStorageGet('warrior_health_'.$i) <= 0) {
                        $emoji = '...';
                    }
                    $select_id = $i;
                    $find = true;
                } elseif ($user->LocalStorageGet('bot-warrior_location-x_'.$i) == $x && $user->LocalStorageGet('bot-warrior_location-y_'.$i) == $y) {
                    if ($user->LocalStorageGet('bot-warrior_type_'.$i) == 'infantry') {
                        $emoji = '🔻';
                    } else {
                        $emoji = '🔺';
                    }
                    if ($user->LocalStorageGet('bot-warrior_health_'.$i) <= 0) {
                        $emoji = '...';
                    }
                    $select_id = $i;
                    $find = true;
                }
            }
            if ($find && $emoji != '...') {
                if ($emoji == '🔻' or $emoji == '🔺') {
                    $keyboard[$y][$x]['text'] = $emoji;
                    $keyboard[$y][$x]['callback_data'] = 'combat-bot_attack_' . $select_id;
                } else {
                    $keyboard[$y][$x]['text'] = $emoji;
                    $keyboard[$y][$x]['callback_data'] = 'combat-bot_select_' . $select_id;
                }
            } else {
                $keyboard[$y][$x]['text'] = '...';
                $keyboard[$y][$x]['callback_data'] = 'combat-bot_coords_'.$x.'_'.$y;
            }
        }
    }
    return $keyboard;
}
function update() {
    global $user;
    global $chat;
    $keyboard = getField();
    $chat->editMessageText('⚔ <b>Combat</b>
Доступно ходів: <b>'.$user->LocalStorageGet('aliveCount').'</b>', ['inline_keyboard' => $keyboard], update::$btn_id);
}
function distance($point1, $point2) {
    $dx = abs($point1[0] - $point2[0]);
    $dy = abs($point1[1] - $point2[1]);
    // Возвращаем максимальное из двух расстояний, чтобы учесть движение по диагонали
    return max($dx, $dy);
}
function check_bot_move() {
    global $user, $chat;
    if ($user->LocalStorageGet('aliveCount') <= 0) {
        $aliveBotCount = countBotAliveWarriors();
        $user->LocalStorageSet('countBotAliveWarriors', $aliveBotCount);
        for ($i = $user->LocalStorageGet('NextBotIndex'); $i < $aliveBotCount; $i++) {
            $user->LocalStorageSet('NextBotIndex', ($i+1));
            sleep(1);
            BotMove(($i+1));
        }
        $user->LocalStorageSet('aliveCount', countAliveWarriors());
        update();
    }
}
function getBestMoveForBotWarrior($botId) {
    global $user, $chat;

    $botPosition = [
        $user->LocalStorageGet('bot-warrior_location-x_' . $botId),
        $user->LocalStorageGet('bot-warrior_location-y_' . $botId)
    ];
    $botType = $user->LocalStorageGet('bot-warrior_type_' . $botId);

    $closestPlayerWarrior = null;
    $minDistance = PHP_INT_MAX;

    for ($i = 1; $user->LocalStorageGet('warrior_' . $i); $i++) {
        $playerWarriorHealth = $user->LocalStorageGet('warrior_health_' . $i);
        // Пропустить, если воин мертв
        if ($playerWarriorHealth <= 0) {
            continue;
        }

        $playerWarriorPosition = [
            $user->LocalStorageGet('warrior_location-x_' . $i),
            $user->LocalStorageGet('warrior_location-y_' . $i)
        ];

        $currentDistance = distance($botPosition, $playerWarriorPosition);
        if ($currentDistance < $minDistance) {
            $minDistance = $currentDistance;
            $closestPlayerWarrior = $playerWarriorPosition;
        }
    }

    if (!$closestPlayerWarrior) {
        return null;  // Если нет живых воинов игрока
    }
    $deltaX = abs($botPosition[0] - $closestPlayerWarrior[0]);
    $deltaY = abs($botPosition[1] - $closestPlayerWarrior[1]);
    $actualDistance = max($deltaX, $deltaY);

// Если тип бота infantry и игрок находится рядом, возвращаем null
    if ($botType === 'infantry' && $actualDistance == 1) {
        return null;
    }

// Если тип бота archer и игрок находится в радиусе 2х клеток, возвращаем null
    if ($botType === 'archer' && $actualDistance <= 2) {
        return null;
    }

    // Определяем оптимальное расстояние в зависимости от типа воина бота
    $desiredDistance = $botType === 'infantry' ? 1 : 2;
    $bestMove = null;

    // Находим наилучший ход
    $dx = [-1, -1, -1, 0, 1, 1, 1, 0];
    $dy = [-1, 0, 1, 1, 1, 0, -1, -1];
    $bestDistance = PHP_INT_MAX;
    for ($i = 0; $i < 8; $i++) {
        $newX = $botPosition[0] + $dx[$i];
        $newY = $botPosition[1] + $dy[$i];
        if ($newX >= 0 && $newY >= 0 && $newX < 8 && $newY < 8) {
            $newPos = [$newX, $newY];
            $newDistance = distance($newPos, $closestPlayerWarrior);
            if (isPositionBlockedByBot($newPos)) {
                continue;
            }
            if ($botType == 'infantry') {
                if ($newDistance == $desiredDistance) {
                    $bestMove = $newPos;
                    $bestDistance = $newDistance;
                    break;  // Если найдено идеальное расстояние, завершаем цикл
                } elseif ($newDistance < $bestDistance) {
                    $bestMove = $newPos;  // Если нет идеального расстояния, выбираем наименьшее
                    $bestDistance = $newDistance;
                }
            } elseif ($botType == 'archer') {
                if ($newDistance == $desiredDistance) { // лучник пытается поддерживать дистанцию в 2 клетки
                    $bestMove = $newPos;
                    break;
                } elseif ($newDistance < $bestDistance && $newDistance > 2) {
                    // лучник пытается приблизиться, если игрок дальше чем на 2 клетки
                    $bestMove = $newPos;
                    $bestDistance = $newDistance;
                }
            }
        }
    }
    return $bestMove;
}
function mt_rant($min, $max, $exclude) {
    $numbers = range($min, $max);
    $key = array_search($exclude, $numbers);

    if ($key !== false) {
        unset($numbers[$key]);
    }

    return $numbers[array_rand($numbers)];
}
function isPositionBlockedByBot($position) {
    global $user;
    for ($i = 1; $user->LocalStorageGet('bot-warrior_name_' . $i); $i++) {
        $botX = $user->LocalStorageGet('bot-warrior_location-x_' . $i);
        $botY = $user->LocalStorageGet('bot-warrior_location-y_' . $i);
        if ($botX == $position[0] && $botY == $position[1]) {
            return true;
        }
    }
    return false;
}
function getClosestPlayerWarriorId($botId) {
    global $user;

    $botPosition = [
        $user->LocalStorageGet('bot-warrior_location-x_' . $botId),
        $user->LocalStorageGet('bot-warrior_location-y_' . $botId)
    ];

    $closestPlayerWarriorId = null;
    $minDistance = PHP_INT_MAX;

    for ($i = 1; $user->LocalStorageGet('warrior_' . $i); $i++) {
        $playerHealth = $user->LocalStorageGet('warrior_health_' . $i);

        if ($playerHealth > 0) {  // Только живые воины
            $playerWarriorPosition = [
                $user->LocalStorageGet('warrior_location-x_' . $i),
                $user->LocalStorageGet('warrior_location-y_' . $i)
            ];

            $currentDistance = distance($botPosition, $playerWarriorPosition);

            if ($currentDistance < $minDistance) {
                $minDistance = $currentDistance;
                $closestPlayerWarriorId = $i;
            }
        }
    }

    return $closestPlayerWarriorId;
}
function checkAliveWarriors() {
    global $user;

    $playerHasAliveWarriors = false;
    $botHasAliveWarriors = false;

    // Проверка живых воинов у игрока
    $player_warrior_id = 1;
    while ($user->LocalStorageGet('warrior_health_' . $player_warrior_id) !== null) {
        if ($user->LocalStorageGet('warrior_health_' . $player_warrior_id) > 0) {
            $playerHasAliveWarriors = true;
            break;
        }
        $player_warrior_id++;
    }

    // Проверка живых воинов у бота
    $bot_warrior_id = 1;
    while ($user->LocalStorageGet('bot-warrior_health_' . $bot_warrior_id) !== null) {
        if ($user->LocalStorageGet('bot-warrior_health_' . $bot_warrior_id) > 0) {
            $botHasAliveWarriors = true;
            break;
        }
        $bot_warrior_id++;
    }

    return [
        'playerHasAliveWarriors' => $playerHasAliveWarriors,
        'botHasAliveWarriors' => $botHasAliveWarriors
    ];
}
function end_attack($bot_id, $player_id, $type, $player_attack = false) {
    global $user, $chat;
    if ($player_attack) {
        //Атака гравця
        if (!mt_rand(0, 3)) {
            $protect_type = mt_rant(1, 3, $type);
        } else {
            $protect_type = mt_rand(1, 3);
        }
        $minus = mt_rand(($user->LocalStorageGet('warrior_level_'.$player_id)*5+5), ($user->LocalStorageGet('warrior_level_'.$player_id)*6+10));
        if ($type == $protect_type && $user->LocalStorageGet('bot-warrior_shield_'.$bot_id) > 0) {
            $new_shield_value = $user->LocalStorageGet('bot-warrior_shield_'.$bot_id)-$minus;
            if ($new_shield_value < 0) {
                $text = '❗ Щит захистив воїна, але він зламався';
                $user->LocalStorageSet('bot-warrior_shield_'.$bot_id, 0);
            } else {
                $text = '🛡 Ви пошкодили щит (-'.$minus.' міцності)';
                $user->LocalStorageSet('bot-warrior_shield_'.$bot_id, $new_shield_value);
            }
        } else {
            $new_hp_value = $user->LocalStorageGet('bot-warrior_health_'.$player_id)-$minus;
            if ($new_hp_value < 0) {
                $text = '💔 Ви вбили воїна';
                $user->LocalStorageSet('bot-warrior_health_'.$bot_id, 0);
            } else {
                $user->LocalStorageSet('bot-warrior_health_'.$bot_id, $new_hp_value);
                $text = '🩸 Воїна поранено! (-'.$minus.'❤) => '.$user->LocalStorageGet('bot-warrior_health_'.$bot_id).'❤';
            }
        }
        $chat->answerCallbackQuery($text, true);
    } else {
        if ($type > 0 && $user->LocalStorageGet('warrior_shield_'.$player_id) <= 0) {
            $chat->answerCallbackQuery('💢 Ти не можеш захиститися: в твого воїна нема щита');
            die();
        }
        if (!mt_rand(0, 3)) {
            $attack_type = mt_rant(1, 3, $type);
        } else {
            $attack_type = mt_rand(1, 3);
        }
        $minus = mt_rand(($user->LocalStorageGet('bot-warrior_level_'.$bot_id)*5+5), ($user->LocalStorageGet('bot-warrior_level_'.$bot_id)*6+10));
        if ($type == $attack_type && $type > 0) {
            $new_shield_value = $user->LocalStorageGet('warrior_shield_'.$player_id)-$minus;
            if ($new_shield_value < 0) {
                $text = '❗ Щит захистив твого воїна, але він зламався';
                $user->LocalStorageSet('warrior_shield_'.$player_id, 0);
            } else {
                $text = '🛡 Щит захистив тебе! (-'.$minus.' міцності)';
                $user->LocalStorageSet('warrior_shield_'.$player_id, $new_shield_value);
            }
        } else {
            $new_hp_value = $user->LocalStorageGet('warrior_health_'.$player_id)-$minus;
            if ($new_hp_value < 0) {
                $text = '💔 Твій воїн загинув';
                $user->LocalStorageSet('warrior_health_'.$player_id, 0);
            } else {
                $text = '🩸 Твого воїна поранено! (-'.$minus.'❤)';
                $user->LocalStorageSet('warrior_health_'.$player_id, $new_hp_value);
            }
        }
        $chat->answerCallbackQuery($text, true);
    }
    $result = checkAliveWarriors();
    if (!$result['playerHasAliveWarriors']) {
        $chat->editMessageText('⚔ <b>Combat</b> - гра завершена

Всі ваші воїни загинули. Ви програли <b>'.$user->LocalStorageGet('bet').'💰</b>', null, update::$btn_id);
        $user->LocalStorageClear();
        die();
    } elseif (!$result['botHasAliveWarriors']) {
        $chat->editMessageText('⚔ <b>Combat</b> - гра завершена

Всі воїни супротивника загинули. Ви виграли <b>'.$user->LocalStorageGet('bet').'💰</b>', null, update::$btn_id);
        $user->addBal($user->LocalStorageGet('bet')*2);
        $user->LocalStorageClear();
        die();
    }
    $chat->editMessageText('⏳', null, update::$btn_id);
    check_bot_move();
    update();
}
function attack($bot_warrior_id, $player_warrior_id) {
    global $user;
    global $chat;
    $bot_warrior = [
        'name' => $user->LocalStorageGet('bot-warrior_name_'.$bot_warrior_id),
        'type' => $user->LocalStorageGet('bot-warrior_type_'.$bot_warrior_id)
    ];
    $player_warrior = [
        'name' => $user->LocalStorageGet('warrior_name_'.$player_warrior_id),
        'type' => $user->LocalStorageGet('warrior_type_'.$player_warrior_id)
    ];
    if ($bot_warrior['type'] == 'infantry') $bot_type = '🗡';
    if ($bot_warrior['type'] == 'archer') $bot_type = '🎯';
    if ($player_warrior['type'] == 'infantry') $player_type = '🗡';
    if ($player_warrior['type'] == 'archer') $player_type = '🎯';
    $keyboard[0][0]['text'] = '🛡 Захистити голову';
    $keyboard[0][0]['callback_data'] = 'combat-bot_protect_'.$player_warrior_id.'_'.$bot_warrior_id.'_1';
    $keyboard[1][0]['text'] = '🛡 Захистити тулуб';
    $keyboard[1][0]['callback_data'] = 'combat-bot_protect_'.$player_warrior_id.'_'.$bot_warrior_id.'_2';
    $keyboard[2][0]['text'] = '🛡 Захистити ноги';
    $keyboard[2][0]['callback_data'] = 'combat-bot_protect_'.$player_warrior_id.'_'.$bot_warrior_id.'_3';
    $keyboard[3][0]['text'] = '💪 Без захисту';
    $keyboard[3][0]['callback_data'] = 'combat-bot_protect_'.$player_warrior_id.'_'.$bot_warrior_id.'_0';
    $chat->editMessageText('‼ <b>Ви атаковані!</b>
Оберіть захист

'.$bot_type.''.$bot_warrior['name'].' ⚔ '.$player_warrior['name'].''.$player_type.'', ['inline_keyboard' => $keyboard], update::$btn_id);
    die();
}
function canPlayerAttackBot($playerWarriorId, $botWarriorId) {
    global $user;

    // Получаем позиции игрока и бота
    $playerPosition = [
        $user->LocalStorageGet('warrior_location-x_' . $playerWarriorId),
        $user->LocalStorageGet('warrior_location-y_' . $playerWarriorId)
    ];

    $botPosition = [
        $user->LocalStorageGet('bot-warrior_location-x_' . $botWarriorId),
        $user->LocalStorageGet('bot-warrior_location-y_' . $botWarriorId)
    ];

    // Определяем тип воина игрока
    $playerWarriorType = $user->LocalStorageGet('warrior_type_' . $playerWarriorId);

    // Вычисляем расстояние между игроком и ботом
    $dist = distance($playerPosition, $botPosition);

    // Проверяем условия атаки в зависимости от типа воина
    if ($playerWarriorType == 'infantry' && $dist == 1) {
        return true;
    } elseif ($playerWarriorType == 'archer' && $dist <= 2) {
        return true;
    }
    return false;
}
function BotMove($botWarriorIndex) {
    global $user, $chat;
    if ($user->LocalStorageGet('bot-warrior_health_'.$botWarriorIndex) > 0) {
        $to_coords = getBestMoveForBotWarrior($botWarriorIndex);
        if ($to_coords) {
            $user->LocalStorageSet('bot-warrior_location-x_'.$botWarriorIndex, $to_coords[0]);
            $user->LocalStorageSet('bot-warrior_location-y_'.$botWarriorIndex, $to_coords[1]);
            update();
        } else {
            $w_id = getClosestPlayerWarriorId($botWarriorIndex);
            attack($botWarriorIndex, $w_id);
        }
    }
}

//======================================================================================================================

if ($ex_callback[0] == 'combat-bot') {
    if ($ex_callback[1] == 'start') {
        if (!$ex_callback[2]) {
            $user->LocalStorageClear();
            $user->LocalStorageSet('game', 'combat-bot');
            $user->LocalStorageSet('action', 'start');
            $user->LocalStorageSet('warriors', 0);
            $warriors = '';
        } else {
            $warriors = '
';
            $warrior = R::load('combatwarriors', $ex_callback[2]);
            if (!$warrior) { $chat->answerCallbackQuery('♨ Воїна не знайдено'); die(); }
            $user->LocalStorageSet('warriors', ($user->LocalStorageGet('warriors')+1));
            $user->LocalStorageSet('warrior_'.$user->LocalStorageGet('warriors'), $warrior['id']);
            for ($i = 1; $i <= $user->LocalStorageGet('warriors'); $i++) {
                $warrior = R::load('combatwarriors', $user->LocalStorageGet('warrior_'.$i));
                if ($warrior['type'] == 'infantry') $type = '🗡';
                if ($warrior['type'] == 'archer') $type = '🎯';
                $warriors .= $type.' '.$warrior['name'].' ('.$warrior['health'].'❤ | '.$warrior['shield'].'🛡)
';
            }
        }
        $keyboard[0][0]['text'] = '➕ Додати воїна ('.$user->LocalStorageGet('warriors').'/3)';
        $keyboard[0][0]['callback_data'] = 'combat-bot_add-warrior';
        if ($user->LocalStorageGet('warriors') > 0) {
            $keyboard[1][0]['text'] = '⚔ Розпочати';
            $keyboard[1][0]['callback_data'] = 'combat-bot_start-battle';
        }
        $chat->editMessageText('⚔ <b>Combat</b> - формування групи
'.$warriors, ['inline_keyboard' => $keyboard], update::$btn_id);
    }
    if ($ex_callback[1] == 'end-attack') {
        $user->LocalStorageSet('aliveCount', ($user->LocalStorageGet('aliveCount')-1));
        $user->LocalStorageSet('NextBotIndex', 0);
        $selected = $user->LocalStorageGet('selected');
        $user->LocalStorageSet('selected', null);
        end_attack($ex_callback[2], $selected, $ex_callback[3], true);
    }
    if ($ex_callback[1] == 'attack') {
        $bot_id = $ex_callback[2];
        if ($user->LocalStorageGet('selected')) {
            if (canPlayerAttackBot($user->LocalStorageGet('selected'), $bot_id)) {
                $bot_warrior = [
                    'name' => $user->LocalStorageGet('bot-warrior_name_'.$bot_id),
                    'type' => $user->LocalStorageGet('bot-warrior_type_'.$bot_id)
                ];
                $player_warrior = [
                    'name' => $user->LocalStorageGet('warrior_name_'.$user->LocalStorageGet('selected')),
                    'type' => $user->LocalStorageGet('warrior_type_'.$user->LocalStorageGet('selected'))
                ];
                if ($bot_warrior['type'] == 'infantry') $bot_type = '🗡';
                if ($bot_warrior['type'] == 'archer') $bot_type = '🎯';
                if ($player_warrior['type'] == 'infantry') $player_type = '🗡';
                if ($player_warrior['type'] == 'archer') $player_type = '🎯';
                $keyboard[0][0]['text'] = '🔪 Атака голови';
                $keyboard[0][0]['callback_data'] = 'combat-bot_end-attack_'.$bot_id.'_1';
                $keyboard[1][0]['text'] = '🔪 Атака тулуба';
                $keyboard[1][0]['callback_data'] = 'combat-bot_end-attack_'.$bot_id.'_2';
                $keyboard[2][0]['text'] = '🔪 Атака ніг';
                $keyboard[2][0]['callback_data'] = 'combat-bot_end-attack_'.$bot_id.'_3';
                $chat->editMessageText('⚔ <b>Combat</b> - атака

'.$player_type.''.$player_warrior['name'].' ⚔ '.$bot_warrior['name'].''.$bot_type, ['inline_keyboard' => $keyboard], update::$btn_id);
                die();
            } else {
                $chat->answerCallbackQuery('♨ Ви не можете атакувати цього воїна, бо він далеко від вас');
            }
        } else {
            if ($user->LocalStorageGet('bot-warrior_type_' . $bot_id) == 'infantry') $type = '🗡';
            if ($user->LocalStorageGet('bot-warrior_type_' . $bot_id) == 'archer') $type = '🎯';
            $chat->answerCallbackQuery('ℹ Інформація про воїна
'.$user->LocalStorageGet('bot-warrior_name_' . $bot_id).' '.$type.'
'.$user->LocalStorageGet('bot-warrior_health_' . $bot_id).' ❤ | '.$user->LocalStorageGet('bot-warrior_shield_' . $bot_id).' 🛡', true);
            die();
        }
    }
    if ($user->LocalStorageGet('action') == 'start' && $ex_callback[1] == 'add-warrior') {
        if ($user->LocalStorageGet('warriors') >= 3) { $chat->answerCallbackQuery('Максимум можна додати 3х воїнів'); die(); }
        $warriors = R::find('combatwarriors', 'user_id = ?', [$user->user['id']]);
        $i = 0;
        foreach ($warriors as $warrior) {
            if ($warrior['health'] > 0) {
                $found = false;
                for ($y = 1; $user->LocalStorageGet('warrior_'.$y); $y++) {
                    if ($user->LocalStorageGet('warrior_'.$y) == $warrior['id']) {
                        $found = true;
                        break;
                    }
                }
                if ($found) continue;
                if ($warrior['type'] == 'infantry') $type = '🗡';
                if ($warrior['type'] == 'archer') $type = '🎯';
                $keyboard[$i][0]['text'] = $type.' '.$warrior['name'];
                $keyboard[$i][0]['callback_data'] = 'combat-bot_start_'.$warrior['id'];
                $i++;
            }
        }
        if (!$keyboard[0][0]['text']) { $chat->answerCallbackQuery('Більше нема воїнів котрих можна додати'); die(); }
        $keyboard[$i][0]['text'] = '🔙 Назад 🔙';
        $keyboard[$i][0]['callback_data'] = 'combat-bot_start';
        $chat->editMessageText('⚔ <b>Combat</b>
Обери воїна', ['inline_keyboard' => $keyboard], update::$btn_id);
    }
    if ($ex_callback[1] == 'start-battle') {
        $chat->editMessageText('⚔ <b>Combat</b>
⏳ Чекаємо поки супротивник обере воїнів...', null, update::$btn_id);
        for ($i = 1; $user->LocalStorageGet('warrior_'.$i); $i++) {
            $location = [
                'me' => [[7, 7], [6, 6], [5, 5]],
                'enemy' => [[0, 0], [1, 1], [2, 2]],
            ];
            $warrior = R::load('combatwarriors', $user->LocalStorageGet('warrior_'.$i));
            $user->LocalStorageSet('warrior_name_'.$i, $warrior['name']);
            $user->LocalStorageSet('warrior_type_'.$i, $warrior['type']);
            $user->LocalStorageSet('warrior_health_'.$i, $warrior['health']);
            $user->LocalStorageSet('warrior_shield_'.$i, $warrior['shield']);
            $user->LocalStorageSet('warrior_custom_'.$i, $warrior['custom']);
            $user->LocalStorageSet('warrior_level_'.$i, $warrior['level']);
            $user->LocalStorageSet('warrior_location-x_'.$i, $location['me'][($i-1)][0]);
            $user->LocalStorageSet('warrior_location-y_'.$i, $location['me'][($i-1)][1]);
            //BOT
            $user->LocalStorageSet('bot-warrior_name_'.$i, 'БОТ-'.$i);
            $user->LocalStorageSet('bot-warrior_type_'.$i, $warrior['type']);
            $user->LocalStorageSet('bot-warrior_health_'.$i, 100);
            $user->LocalStorageSet('bot-warrior_shield_'.$i, $warrior['shield']);
            $user->LocalStorageSet('bot-warrior_custom_'.$i, $warrior['custom']);
            $user->LocalStorageSet('bot-warrior_level_'.$i, $warrior['level']);
            $user->LocalStorageSet('bot-warrior_location-x_'.$i, $location['enemy'][($i-1)][0]);
            $user->LocalStorageSet('bot-warrior_location-y_'.$i, $location['enemy'][($i-1)][1]);
            $user->LocalStorageSet('NextBotIndex', 0);
            $user->LocalStorageSet('aliveCount', countAliveWarriors());
        }
        $bet = floor($user->user['balance']*0.05);
        $user->addBal($bet*-1);
        $user->LocalStorageSet('bet', $bet);
        $keyboard[0][0]['text'] = '✅ Розпочинаємо!';
        $keyboard[0][0]['callback_data'] = 'combat-bot_update';
        $chat->editMessageText('⚔ <b>Combat</b>
Підтвердіть готовність', ['inline_keyboard' => $keyboard], update::$btn_id);
    }
    if ($ex_callback[1] == 'update') {
        update();
    }
    if ($ex_callback[1] == 'select') {
        if ($user->LocalStorageGet('selected') != $ex_callback[2]) {
            $user->LocalStorageSet('selected', $ex_callback[2]);
            if ($user->LocalStorageGet('warrior_type_' . $ex_callback[2]) == 'infantry') $type = '🗡';
            if ($user->LocalStorageGet('warrior_type_' . $ex_callback[2]) == 'archer') $type = '🎯';
            $selected = '== Ви обрали ==
<em>' . $type . ' ' . $user->LocalStorageGet('warrior_name_' . $ex_callback[2]) . '</em>
' . $user->LocalStorageGet('warrior_health_' . $ex_callback[2]) . '❤ | ' . $user->LocalStorageGet('warrior_shield_' . $ex_callback[2]) . '🛡';
        } else $user->LocalStorageSet('selected', null);
        $keyboard = getField();
        $chat->editMessageText('⚔ <b>Combat</b>
Доступно ходів: <b>'.$user->LocalStorageGet('aliveCount').'</b>
'.$selected, ['inline_keyboard' => $keyboard], update::$btn_id);
    }
    if ($ex_callback[1] == 'coords') {
        if (!$user->LocalStorageGet('selected')) {
            $chat->answerCallbackQuery('💢 Обери воїна котрим хочеш зробити хід');
            die();
        }
        $x = $ex_callback[2];
        $y = $ex_callback[3];
        if (check_canMove([$user->LocalStorageGet('warrior_location-x_'.$user->LocalStorageGet('selected')), $user->LocalStorageGet('warrior_location-y_'.$user->LocalStorageGet('selected'))], [$x, $y])) {
            if ($user->LocalStorageGet('aliveCount') > 0) {
                $user->LocalStorageSet('warrior_location-x_'.$user->LocalStorageGet('selected'), $x);
                $user->LocalStorageSet('warrior_location-y_'.$user->LocalStorageGet('selected'), $y);
                $user->LocalStorageSet('selected', null);
                $user->LocalStorageSet('aliveCount', ($user->LocalStorageGet('aliveCount')-1));
                $keyboard = getField();
                $chat->editMessageText('⚔ <b>Combat</b>
Доступно ходів: <b>'.$user->LocalStorageGet('aliveCount').'</b>', ['inline_keyboard' => $keyboard], update::$btn_id);
                $user->LocalStorageSet('NextBotIndex', 0);
                check_bot_move();
            } else {
                $chat->answerCallbackQuery('💢 Зараз не твій хід');
                die();
            }
        } else {
            $chat->answerCallbackQuery('💢 Ти не можеш сюди походити');
            die();
        }
    }
    if ($ex_callback[1] == 'protect') {
        $attacked = $ex_callback[2];
        $bot_id = $ex_callback[3];
        $attack_type = $ex_callback[4];
        end_attack($bot_id, $attacked, $attack_type);
        die();
    }
}
