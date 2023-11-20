//const axios = require('axios');
const mysql = require('mysql');
const express = require('express');
const TelegramBot = require('node-telegram-bot-api');
const token = 'BOT TOKEN';
const port = 7000;
const bot = new TelegramBot(token);
const chatId = process.argv[2];
const msgId = process.argv[3];
const app = express();
let move = 0;
let invert = false;
let active_card = '';
let antibonus = 0;
let antibonus_type = '';
let taked_user = null;
let color_select_user = null;
let color_select = false;
let sayed_uno_user = null;
let disable_start_timer = false;
let log = ['Починаємо гру!', '', 'Service'];
let start_time = 60;
let players = {};
let bank = 0;
let move_time = 30;
let move_timer;
let afk = [];
let bet = process.argv[4];
let emoji = ['👺', '😤', '😿', '👎', '🥰', '😋', '😹', '👍'];
let cards = ['🟦 0', '🟦 1', '🟦 2', '🟦 3', '🟦 4', '🟦 5', '🟦 6', '🟦 7', '🟦 8', '🟦 9', '🟦 1', '🟦 2', '🟦 3', '🟦 4',
             '🟦 5', '🟦 6', '🟦 7', '🟦 8', '🟦 9', '🟦 🚫', '🟦 🔁', '🟦 +2', '🟦 🚫', '🟦 🔁', '🟦 +2',
             '🟧 0', '🟧 1', '🟧 2', '🟧 3', '🟧 4', '🟧 5', '🟧 6', '🟧 7', '🟧 8', '🟧 9', '🟧 1', '🟧 2', '🟧 3', '🟧 4',
             '🟧 5', '🟧 6', '🟧 7', '🟧 8', '🟧 9', '🟧 🚫', '🟧 🔁', '🟧 +2', '🟧 🚫', '🟧 🔁', '🟧 +2',
             '🟩 0', '🟩 1', '🟩 2', '🟩 3', '🟩 4', '🟩 5', '🟩 6', '🟩 7', '🟩 8', '🟩 9', '🟩 1', '🟩 2', '🟩 3', '🟩 4',
             '🟩 5', '🟩 6', '🟩 7', '🟩 8', '🟩 9', '🟩 🚫', '🟩 🔁', '🟩 +2', '🟩 🚫', '🟩 🔁', '🟩 +2',
             '🟥 0', '🟥 1', '🟥 2', '🟥 3', '🟥 4', '🟥 5', '🟥 6', '🟥 7', '🟥 8', '🟥 9', '🟥 1', '🟥 2', '🟥 3', '🟥 4',
             '🟥 5', '🟥 6', '🟥 7', '🟥 8', '🟥 9', '🟥 🚫', '🟥 🔁', '🟥 +2', '🟥 🚫', '🟥 🔁', '🟥 +2',
             '⬛ +4', '⬛ +4', '⬛ +4', '⬛ +4', '⬛ 🇲🇺', '⬛ 🇲🇺', '⬛ 🇲🇺', '⬛ 🇲🇺',
            ];
let deck = cards;
shuffle(deck);
console.log('shuffled');
console.log(deck);
players.db = [];
players.gameinfo = [];
app.use(express.json());

var sql = mysql.createConnection({
    host     : 'localhost',
    user     : 'USER',
    password : 'PAASSS',
    database : 'DB'
});
sql.connect();
function mt_rand(min, max) {
    return Math.floor(Math.random() * (max - min)) + min;
}
function getTxtCard(cards_txt) {
    let a = cards_txt % 10;
    let str;
    if(a==1) str="картку";
    if(a==2 || a==3 || a==4) str="картки";
    if(a==5 || a==6 || a==7 || a==8 || a==9 || a==0) str="карток";
    if (cards_txt==11 || cards_txt==12 || cards_txt==13 || cards_txt==14) str="карток";
    return str;
}
function shuffle(array) {
    let currentIndex = array.length,  randomIndex;
    // While there remain elements to shuffle.
    while (currentIndex != 0) {
        // Pick a remaining element.
        randomIndex = Math.floor(Math.random() * currentIndex);
        currentIndex--;
        // And swap it with the current element.
        [array[currentIndex], array[randomIndex]] = [
            array[randomIndex], array[currentIndex]];
    }
    return array;
}
function getRandomCard(antiBlack = false) {
    let selected_index = 0;
    let card = deck[selected_index];
    while (card.split(' ')[0] === '⬛' && antiBlack) {
        selected_index++;
        card = deck[selected_index];
    }
    console.log('Next deck card: '+deck[(selected_index+1)]+'; ALL DECK: ['+deck.length+']');
    deck.splice(selected_index, 1);
    if (deck.length <= 1) {
        deck = ['🟦 0', '🟦 1', '🟦 2', '🟦 3', '🟦 4', '🟦 5', '🟦 6', '🟦 7', '🟦 8', '🟦 9', '🟦 1', '🟦 2', '🟦 3', '🟦 4',
                '🟦 5', '🟦 6', '🟦 7', '🟦 8', '🟦 9', '🟦 🚫', '🟦 🔁', '🟦 +2', '🟦 🚫', '🟦 🔁', '🟦 +2',
                '🟧 0', '🟧 1', '🟧 2', '🟧 3', '🟧 4', '🟧 5', '🟧 6', '🟧 7', '🟧 8', '🟧 9', '🟧 1', '🟧 2', '🟧 3', '🟧 4',
                '🟧 5', '🟧 6', '🟧 7', '🟧 8', '🟧 9', '🟧 🚫', '🟧 🔁', '🟧 +2', '🟧 🚫', '🟧 🔁', '🟧 +2',
                '🟩 0', '🟩 1', '🟩 2', '🟩 3', '🟩 4', '🟩 5', '🟩 6', '🟩 7', '🟩 8', '🟩 9', '🟩 1', '🟩 2', '🟩 3', '🟩 4',
                '🟩 5', '🟩 6', '🟩 7', '🟩 8', '🟩 9', '🟩 🚫', '🟩 🔁', '🟩 +2', '🟩 🚫', '🟩 🔁', '🟩 +2',
                '🟥 0', '🟥 1', '🟥 2', '🟥 3', '🟥 4', '🟥 5', '🟥 6', '🟥 7', '🟥 8', '🟥 9', '🟥 1', '🟥 2', '🟥 3', '🟥 4',
                '🟥 5', '🟥 6', '🟥 7', '🟥 8', '🟥 9', '🟥 🚫', '🟥 🔁', '🟥 +2', '🟥 🚫', '🟥 🔁', '🟥 +2',
                '⬛ +4', '⬛ +4', '⬛ +4', '⬛ +4', '⬛ 🇲🇺', '⬛ 🇲🇺', '⬛ 🇲🇺', '⬛ 🇲🇺',
        ];
        shuffle(deck);
        console.log('NEW DECK:');
        console.log(deck);
        newAction('‼ Колода закінчилась, перетасування..');
    }
    return card;
}
function newAction(action) {
    log[0] = log[1];
    log[1] = log[2];
    log.splice(2, 1, action);
}
async function endGame(winner_number) {
    let options = {
        chat_id: chatId,
        message_id: msgId,
        parse_mode: 'html',
    }
    let players_text = '\n';
    players.db.forEach(el => {
        players_text += '👤 '+el.nick+'\n';
    });
    await bot.editMessageText('🎲 <b>[UNO] Гра завершена!</b> \n' +
        '== Гравці: ==' +
        players_text+
        '=============\n' +
        '<b>🔥 Переможець: </b>'+players.db[winner_number].nick+'\n' +
        '<em>Він отримує '+bank+'💰</em>', options);
    players.db.forEach(function callback(player, i, array_tmp) {
        console.log('CHAT_ID: '+player.tg_id+'; MSG_ID: '+players.gameinfo[i].message);
        let msg_id = players.gameinfo[i].message;
        let options_pm = {
            chat_id: player.tg_id,
            message_id: msg_id,
            parse_mode: 'html',
        }
        bot.editMessageText('🎲 <b>[UNO] Гра завершена!</b> \n' +
            '== Гравці: ==' +
            players_text+
            '=============\n' +
            '<b>🔥 Переможець: </b>'+players.db[winner_number].nick+'\n' +
            '<em>Він отримує '+bank+'💰</em>', options_pm);
    });
    process.exit(-1);
}
async function updateGame() {
    color_select = false;
    let players_game_text = '';
    let index = 0;
    let position = 0;
    let invert_txt = '';
    let uno_text = '';
    if (invert) {
        invert_txt = '⬆';
    } else {
        invert_txt = '⬇';
    }
    let log_text = '';
    log.forEach(action => {
        log_text += action+'\n';
    });
    await players.db.forEach(function callback(player, i, array) {
        if (players.gameinfo[i].cards.length === 0) {
            let winner = i;
            let options = {
                chat_id: chatId,
                message_id: msgId,
                parse_mode: 'html',
            }
            let players_text = '\n';
            players.db.forEach(el => {
                players_text += '👤 '+el.nick+'\n';
            });
            bot.editMessageText('🎲 <b>[UNO] Гра завершена!</b> \n' +
                '== Гравці: ==' +
                players_text+
                '=============\n' +
                '<b>🔥 Переможець: </b>'+players.db[winner].nick+'\n' +
                '<em>Він отримує '+bank+'💰</em>', options);
            players.db.forEach(async function callback(player, i, array_tmp) {
                console.log('CHAT_ID: '+player.tg_id+'; MSG_ID: '+players.gameinfo[i].message);
                let msg_id = players.gameinfo[i].message;
                let options_pm = {
                    parse_mode: 'html',
                }
                await bot.deleteMessage(player.tg_id, msg_id);
                let result = await bot.sendMessage(player.tg_id, '🎲 <b>[UNO] Гра завершена!</b> \n' +
                    '== Гравці: ==' +
                    players_text+
                    '=============\n' +
                    '<b>🔥 Переможець: </b>'+players.db[winner].nick+'\n' +
                    '<em>Він отримує '+bank+'💰</em>', options_pm);
            });
            sql.query("SELECT * FROM users WHERE id = ?", [players.db[winner].id], async function (error, results, fields) {
                let pl = results[0];
                let new_balance = Number(pl.balance)+Number(bank);
                console.log('NEW BALANCE FOR '+players.db[winner].id+': '+new_balance);
                sql.query("UPDATE users SET balance = ? WHERE id = " + players.db[winner].id, [new_balance], async function (error, results, fields) {
                    setTimeout(function () { process.exit(-1) }, 3000);
                });
            });
        }
    });
    players.db.forEach(function callback(player, i, array) {
        let buttons = [];
        let cardlog = '[CARDS '+player.nick+']: ';
        players.gameinfo[i].cards.forEach(function callback(card, card_number, all_arr) {
            cardlog += '['+card+'] ';
            if (buttons[index] === undefined) { buttons[index] = []; }
            buttons[index][position] = {text: card, callback_data: 'uno_card_'+card_number};
            position++;
            if (position === 4) {
                position = 0;
                index++;
            }
        });
        console.log(cardlog);
        if (position !== 0) { index++; }
        buttons[index] = [];
        if (sayed_uno_user === i) {
            sayed_uno_user = null;
            buttons[index][0] = {text: '👺 Штраф за "UNO!" (2)', callback_data: 'uno_fine'};
        } else if (taked_user === i) {
            buttons[index][0] = {text: '🚫 Пропустити хід', callback_data: 'uno_skip'};
        } else if (!antibonus) {
            buttons[index][0] = {text: '📚 Тягнути з колоди', callback_data: 'uno_deck_get'};
        } else {
            buttons[index][0] = {text: '📥 Взяти карти ('+antibonus+')', callback_data: 'uno_deck_antibonus_get'};
        }
        index++;
        if (player.id === 1) {
            buttons[index] = [];
            buttons[index][0] = {text: '💣 Адмін бомба', callback_data: 'uno_deck_getadmin'};
            index++;
        }
        buttons[index] = [];
        if (!color_select_user && !color_select) {
            buttons[index][0] = {text: '⚠ Вигукнути "UNO"', callback_data: 'uno_sayUno'};
            index++;
            buttons[index] = [];
            let numb = 0;
            if (player.uno_emoji) {
                for (let e = 0; e < 8; e++) {
                    if (e === 4) {
                        index++;
                        buttons[index] = [];
                        numb = 0;
                    }
                    buttons[index][numb] = {text: emoji[e], callback_data: 'uno_emoji_' + e};
                    numb++;
                }
            }
        }
        if (color_select_user === i) {
            color_select_user = null;
            color_select = true;
            buttons = [];
            buttons[0] = [];
            buttons[0][0] = {text: '🟥', callback_data: 'uno_black_red'};
            buttons[0][1] = {text: '🟧', callback_data: 'uno_black_yellow'};
            buttons[0][2] = {text: '🟦', callback_data: 'uno_black_blue'};
            buttons[0][3] = {text: '🟩', callback_data: 'uno_black_green'};
        }
        index = 0;
        position = 0;
        let opt = {
            chat_id: players.db[i].tg_id,
            message_id: players.gameinfo[i].message,
            parse_mode: 'html',
            reply_markup: JSON.stringify({
                inline_keyboard: buttons,
            })
        }
        players_game_text = '';
        players.db.forEach(function callback(player_tmp, i_tmp, array_tmp) {
            if (players.gameinfo[i_tmp].uno) {
                uno_text = ' (⚠ UNO)';
            } else {
                uno_text = '';
            }
            if (move === i_tmp) {
                players_game_text += '⏳ <b>'+player_tmp.nick+' ['+players.gameinfo[i_tmp].cards.length+']'+uno_text+' '+invert_txt+'</b>\n';
            } else {
                players_game_text += ''+player_tmp.nick+' ['+players.gameinfo[i_tmp].cards.length+']'+uno_text+'\n';
            }
        });
        bot.editMessageText('🎲 <b>UNO ('+bank+'💰)</b> \n' +
            '======\n' +
            '<em>'+log_text+'</em>' +
            '======\n' +
            players_game_text+
            '======\n' +
            '<b>Стіл: [ '+active_card+' ]</b>\n' +
            '<em>Очікування ходу - ' + move_time + ' сек.</em>', opt);
        //console.log('START CARDS '+players.db[i].nick+' [MSG: '+players.gameinfo[i].message+']: '+players.gameinfo[i].cards+'\n');
    });
    console.log('===================================');
    console.log('active_card: '+active_card);
    console.log('===================================');
}
function next_move() {
    if (invert) {
        move--;
    } else {
        move++;
    }
    if (move < 0) { move = (players.db.length-1); }
    if (move > (players.db.length-1)) move = 0;
    restartTimer(move);
    return true;
}
function contains(arr, elem) {
    for (var i = 0; i < arr.length; i++) {
        if (arr[i] === elem) {
            return true;
        }
    }
    return false;
}
function restartTimer(move, clear = true) {
    if (clear) {
        clearTimeout(move_timer);
    }
    move_timer = setTimeout(function () {
        while (antibonus) {
            players.gameinfo[move].cards.push(getRandomCard());
            antibonus--;
        }
        players.gameinfo[move].cards.push(getRandomCard());
        if (!contains(afk, move)) {
            afk.push(move);
        }
        console.log(afk);
        if (afk.length === players.gameinfo.length) {
            let options = {
                chat_id: chatId,
                message_id: msgId,
                parse_mode: 'html',
            }
            let players_text = '\n';
            players.db.forEach(el => {
                players_text += '👤 '+el.nick+'\n';
            });
            bot.editMessageText('🎲 <b>[UNO] Гру зупинено</b> \n' +
                '== Гравці: ==' +
                players_text+
                '=============\n' +
                '<b>📛 Усі гравці AFK, ставки не повернуті</b>\n', options);
            players.db.forEach(async function callback(player, i, array_tmp) {
                let msg_id = players.gameinfo[i].message;
                let options_pm = {
                    parse_mode: 'html',
                }
                await bot.deleteMessage(player.tg_id, msg_id);
                let result = await bot.sendMessage(player.tg_id, '🎲 <b>[UNO] Гру зупинено</b> \n' +
                    '== Гравці: ==' +
                    players_text+
                    '=============\n' +
                    '<b>📛 Усі гравці AFK, ставки не повернуті</b>\n', options_pm);
            });
            sql.query("SELECT * FROM settings WHERE id = ?", [1], async function (error, results, fields) {
                let bank_db = results[0];
                let new_bank_balance = Number(bank_db.value)+Number(bank);
                sql.query("UPDATE settings SET value = ? WHERE id = 1", [new_bank_balance], async function (error, results, fields) {
                    setTimeout(function () { process.exit(-1) }, 3000);
                });
            });
        }
        newAction('💢 '+players.db[move].nick+' AFK [взято картку]');
        next_move();
        updateGame();
    }, 30000);
}
async function startTimer(start_time) {
    start_time -= 5;
    let opt = {
        chat_id: chatId,
        message_id: msgId,
        parse_mode: 'html',
        reply_markup: JSON.stringify({
            inline_keyboard: [[
                {text: '🎮 Приєднатися до гри', url: 'https://t.me/Sumdu_bot?start=uno'}
            ],
            [
                {text: '📜 Правила гри', url: 'https://telegra.ph/Pravila-dlya-gri-UNO-10-19'}
            ]]
        })
    }
    let players_text = '\n';
    players.db.forEach(el => {
        players_text += el.nick+'\n';
    });
    if (start_time <= 0) {
        let opt = {
            chat_id: chatId,
            message_id: msgId,
            parse_mode: 'html',
        }
        if (players.db.length < 1) {
            await bot.editMessageText('⚠ <b>[UNO] Не вдалося почати гру!</b> \n' +
                '\n' +
                '<em>Недостатньо гравців</em>', {chat_id: chatId, message_id: msgId, parse_mode: 'html',});
            setTimeout(function () {
                process.exit(-1);
            }, 1000);
        }
        if (players.db.length < 2) {
            console.log('ID: '+players.db[0].id);
            sql.query("SELECT * FROM users WHERE id = ?", [players.db[0].id], async function (error, results, fields) {
                let pl = results[0];
                let new_balance = Number(pl.balance)+Number(bet);
                sql.query("UPDATE users SET balance = ? WHERE id = " + players.db[0].id, [new_balance], async function (error, results, fields) {
                    await bot.editMessageText('⚠ <b>[UNO] Не вдалося почати гру!</b> \n' +
                        '\n' +
                        '<em>Недостатньо гравців</em>', {chat_id: chatId, message_id: msgId, parse_mode: 'html',});
                    setTimeout(function () {
                        process.exit(-1);
                    }, 1000);
                });
            });
        } else {
            await bot.editMessageText('🧪 <b>[UNO] Гра почалася!</b> \n' +
                '<b>Гравці:</b>'+players_text+'' +
                '\n' +
                '♦ Ставка: <b>'+bet+'💰</b>\n' +
                '🎁 Нагорода за перемогу: <b>'+bank+'💰</b>\n', {chat_id: chatId, message_id: msgId, parse_mode: 'html',});
            //роздача карток
            players.db.forEach(function callback(player, i, array) {
                players.gameinfo[i].cards = [];
                for (let l = 0; l < 7; l++) {
                    players.gameinfo[i].cards[l] = getRandomCard();
                }
                console.log('START CARDS '+players.db[i].nick+' [MSG: '+players.gameinfo[i].message+']: '+players.gameinfo[i].cards+'\n');
            });
            active_card = getRandomCard(true);
            console.log('active_card: '+active_card);
            newAction('Почнемо гру!');
            newAction('Усім гравцям видано по 7 карток');
            newAction('Початкова карта: '+active_card);
            restartTimer(move, false);
            await updateGame();
            disable_start_timer = true;
        }
    }
    if (!disable_start_timer) {
        bot.editMessageText('⏳ <b>[UNO] Гра запущена!</b> \n' +
            '♦ Ставка: <b>'+bet+'💰</b>\n' +
            players_text + '\n' +
            '<em>Очікування гравців - ' + start_time + ' сек.</em>', opt);
        setTimeout(startTimer, 5000, start_time);
    }
}

app.post(`/bot${token}`, (req, res) => {
    bot.processUpdate(req.body);
    res.sendStatus(200);
});
// Start Express Server
app.listen(port, () => {
    console.log(`Express server is listening on ${port}`);
    let opt = {
        chat_id: chatId,
        message_id: msgId,
        parse_mode: 'html',
        reply_markup: JSON.stringify({
            inline_keyboard: [[
                {text: '🎮 Приєднатися до гри', url: 'https://t.me/Sumdu_bot?start=uno'}
            ],
            [
                {text: '📜 Правила гри', url: 'https://telegra.ph/Pravila-dlya-gri-UNO-10-19'}
            ]]
        })
    }
    bot.editMessageText('⏳ <b>[UNO] Гра запущена!</b> \n' +
        '\n' +
        '♦ Ставка: <b>'+bet+'💰</b>\n' +
        '\n' +
        '<em>Очікування гравців - '+start_time+' сек.</em>', opt);
    setTimeout(startTimer, 5000, start_time);
});

// Just to ping!
bot.on('message', async msg => {
    let die = false;
    let cmd = msg.text.split(' ');
    if (cmd[0] === '/start' && cmd[1] === 'uno') {
        players.db.forEach(function callback(player, i, array) {
            if (players.db[i].tg_id === msg.chat.id) {
                die = true;
            }
        });
        if (!die) {
            sql.query("SELECT * FROM users WHERE tg_id = ?", [msg.from.id], async function (error, results, fields) {
                if (error) throw error;
                let player = results[0];
                if (Number(player.balance) >= Number(bet)) {
                    sql.query("UPDATE users SET balance = ? WHERE id = "+player.id, [(player.balance-bet)], async function (error, results, fields) {
                        bank += Number(bet);
                        if (error) throw error;
                        players.db.push(player);
                        let result = await bot.sendMessage(msg.chat.id, '⏳ Ви приєдналися до гри в uno. Очікуйте поки почнеться гра. \n\n Додаткова інформація в чаті звідки запустили гру');
                        players.gameinfo.push({message: result.message_id, cards: [], uno: false});
                    });
                } else {
                    bot.sendMessage(msg.chat.id, '💢 У тебе недостатньо коштів щоб оплатити ставку у цій грі\n\nНеобхідно: '+bet+'💰\nБаланс: '+player.balance+'💰');
                }
            });
        } else {
            await bot.sendMessage(msg.chat.id, '💢 Ви вже приєднані до гри в uno.');
        }
    }
});
bot.on('callback_query', function onCallbackQuery(callbackQuery) {
    const action = callbackQuery.data;
    const msg = callbackQuery.message;
    let wait = false;
    let ex_callback = action.split('_');
    if (ex_callback[1] === 'card') {
        players.db.forEach(function callback(pl, i, array) {
            if (pl.tg_id === msg.chat.id) {
                let selected_card = players.gameinfo[i].cards[ex_callback[2]];
                let selected_card_info = selected_card.split(' ');
                let selected_color = selected_card_info[0];
                let selected_number = selected_card_info[1];
                let active_card_info = active_card.split(' ');
                let active_color = active_card_info[0];
                let active_number = active_card_info[1];
                let cards_tmp; //буфер видачі
                let cards_push; //буфер
                let all_players;
                if (i === move) {
                    if (antibonus !== 0 && antibonus_type && selected_number !== antibonus_type) {
                        bot.answerCallbackQuery(callbackQuery.id, {text: '♨ [Правила гри] Ви повинні викинути '+antibonus_type+' щоб відбитися або взяти карти', show_alert: true});
                    } else {
                        if (active_number === selected_number || active_color === selected_color || selected_number === '+4' || selected_card === '⬛ 🇲🇺') {
                            players.gameinfo[i].cards.splice(ex_callback[2], 1);
                            active_card = selected_card;
                            if (selected_number === '🔁') {
                                if (invert) {
                                    invert = false;
                                } else {
                                    invert = true;
                                }
                            } else if (selected_number === '🚫') {
                                next_move();
                            } else if (selected_number === '+2') {
                                antibonus += 2;
                                antibonus_type = selected_number;
                            } else if (selected_number === '+4') {
                                antibonus += 4;
                                antibonus_type = selected_number;
                                color_select_user = i;
                                wait = true;
                                newAction(players.db[i].nick + ' викинув [' + selected_card + '] та обирає колір...');
                            } else if (selected_color === '⬛') {
                                color_select_user = i;
                                wait = true;
                                newAction(players.db[i].nick + ' викинув [' + selected_card + '] та обирає колір...');
                            } else if (selected_number === '0' && players.gameinfo[i].cards.length > 0) {
                                all_players = players.gameinfo.length; //кількість гравців
                                if (invert) {
                                    cards_tmp = players.gameinfo[(all_players-1)].cards; //записуємо карти останнього гравця в буфер
                                    players.gameinfo[(all_players-1)].cards = players.gameinfo[0].cards; //видаємо останньому гравцю картки першого
                                    players.gameinfo[0].uno = false;
                                    if (players.gameinfo[(all_players-1)].cards.length === 1) {
                                        players.gameinfo[(all_players-1)].uno = true;
                                    }
                                    for (let l = (all_players-2); l >= 0; l--) {
                                        cards_push = cards_tmp; //копіюємо буфер у буфер видачі
                                        cards_tmp = players.gameinfo[l].cards; //записуємо карти гравця в буфер
                                        players.gameinfo[l].cards = cards_push; //видаємо гравцю карти із буферу видачі
                                        players.gameinfo[l].uno = false;
                                        if (players.gameinfo[l].cards.length === 1) {
                                            players.gameinfo[l].uno = true;
                                        }
                                    }
                                } else {
                                    cards_tmp = players.gameinfo[0].cards; //записуємо карти першого гравця в буфер
                                    players.gameinfo[0].cards = players.gameinfo[(all_players-1)].cards; //видаємо першому гравцю картки останнього
                                    players.gameinfo[(all_players-1)].uno = false;
                                    if (players.gameinfo[0].cards.length === 1) {
                                        players.gameinfo[0].uno = true;
                                    }
                                    for (let l = 1; l < all_players; l++) {
                                        cards_push = cards_tmp; //копіюємо буфер у буфер видачі
                                        cards_tmp = players.gameinfo[l].cards; //записуємо карти гравця в буфер
                                        players.gameinfo[l].cards = cards_push; //видаємо гравцю карти із буферу видачі
                                        players.gameinfo[l].uno = false;
                                        if (players.gameinfo[l].cards.length === 1) {
                                            players.gameinfo[l].uno = true;
                                        }
                                    }
                                }
                                newAction(players.db[i].nick + ' викинув [' + selected_card + ']');
                                newAction('⚠ Усі гравці обмінялися картками ⚠');
                            }
                            if (!wait) {
                                next_move();
                                newAction(players.db[i].nick + ' викинув [' + selected_card + ']');
                            }
                            taked_user = null;
                            updateGame();
                        } else {
                            bot.answerCallbackQuery(callbackQuery.id, {
                                text: '♨ [Правила гри] Цю карту наразі неможна викинути',
                                show_alert: true
                            });
                        }
                    }
                } else {
                    if (active_number === selected_number && active_color === selected_color) {
                        move = i;
                        players.gameinfo[i].cards.splice(ex_callback[2], 1);
                        active_card = selected_card;
                        if (selected_number === '🔁') {
                            if (invert) {
                                invert = false;
                            } else {
                                invert = true;
                            }
                        } else if (selected_number === '🚫') {
                            next_move();
                        } else if (selected_number === '+2') {
                            antibonus += 2;
                            antibonus_type = selected_number;
                        } else if (selected_number === '+4') {
                            antibonus += 4;
                            antibonus_type = selected_number;
                            color_select_user = i;
                            wait = true;
                            newAction(players.db[i].nick + ' підкинув [' + selected_card + '] та обирає колір...');
                        } else if (selected_card === '⬛ 🇲🇺') {
                            color_select_user = i;
                            wait = true;
                            newAction(players.db[i].nick + ' підкинув [' + selected_card + '] та обирає колір...');
                        }
                        if (!wait) {
                            next_move();
                            newAction(players.db[i].nick+' підкинув ['+selected_card+']');
                        }
                        updateGame();
                    } else {
                        bot.answerCallbackQuery(callbackQuery.id, {text: '♨ [Правила гри] Зараз не твій хід.\nНе в свій хід можна підкинути тільки таку ж карту', show_alert: true});
                    }
                }
            }
        });
    } else if (ex_callback[1] === 'deck') {
        if (ex_callback[2] === 'get') {
            players.db.forEach(function callback(pl, i, array) {
                if (pl.tg_id === msg.chat.id) {
                    if (i === move) {
                        let card = getRandomCard();
                        newAction(pl.nick+' взяв з колоди картку');
                        taked_user = i;
                        players.gameinfo[i].cards.push(card);
                        players.gameinfo[i].uno = false;
                        updateGame();
                    } else {
                        bot.answerCallbackQuery(callbackQuery.id, {text: '♨ [Правила гри] Зараз не твій хід', show_alert: true});
                    }
                }
            });
        } else if (ex_callback[2] === 'getadmin') {
            players.db.forEach(function callback(pl, i, array) {
                if (pl.tg_id === msg.chat.id) {
                    if (i === move) {
                        let card = '⬛ +4';
                        newAction(pl.nick+' взяв з колоди картку');
                        taked_user = i;
                        players.gameinfo[i].cards.push(card);
                        players.gameinfo[i].uno = false;
                        updateGame();
                    } else {
                        bot.answerCallbackQuery(callbackQuery.id, {text: '♨ [Правила гри] Зараз не твій хід', show_alert: true});
                    }
                }
            });
        } else if (ex_callback[2] === 'antibonus') {
            players.db.forEach(function callback(pl, i, array) {
                if (pl.tg_id === msg.chat.id) {
                    if (i === move) {
                        if (antibonus) {
                            for (let k = 0; k < antibonus; k++) {
                                players.gameinfo[i].cards.push(getRandomCard());
                            }
                            newAction(pl.nick+' взяв з колоди '+antibonus+' '+getTxtCard(antibonus));
                            players.gameinfo[i].uno = false;
                            antibonus = 0;
                            next_move();
                            updateGame();
                        } else {
                            bot.answerCallbackQuery(callbackQuery.id, {text: '♨ [CORE] Виникла помилка', show_alert: true});
                        }
                    } else {
                        bot.answerCallbackQuery(callbackQuery.id, {text: '♨ [Правила гри] Зараз не твій хід', show_alert: true});
                    }
                }
            });
        }
    } else if (ex_callback[1] === 'skip') {
        players.db.forEach(function callback(pl, i, array) {
            if (pl.tg_id === msg.chat.id) {
                taked_user = null;
                next_move();
                newAction(pl.nick+' пропускає хід');
                updateGame();
            }
        });
    } else if (ex_callback[1] === 'sayUno' && !color_select_user && !color_select) {
        let triggered = false;
        players.db.forEach(function callback(pl, i, array) {
            if (pl.tg_id === msg.chat.id) {
                players.db.forEach(function callback(pl_tmp, i_tmp, array_tmp) {
                    if (players.gameinfo[i_tmp].cards.length === 1 && !players.gameinfo[i_tmp].uno && i_tmp !== i) {
                        players.gameinfo[i_tmp].cards.push(getRandomCard());
                        players.gameinfo[i_tmp].cards.push(getRandomCard());
                        players.gameinfo[i_tmp].uno = false;
                        newAction(players.db[i_tmp].nick+' бере 2 картки з колоди');
                        triggered = true;
                        newAction(players.db[i].nick+' вигукнув "UNO!"');
                        updateGame();
                    }
                });
                if (players.gameinfo[i].cards.length === 2 && !triggered && move === i) {
                    sayed_uno_user = i;
                    triggered = true;
                    players.gameinfo[i].uno = true;
                    newAction(players.db[i].nick+' вигукнув "UNO!"');
                    updateGame();
                }
                if (players.gameinfo[i].cards.length === 1) {
                    triggered = true;
                    players.gameinfo[i].uno = true;
                    newAction(players.db[i].nick+' вигукнув "UNO!"');
                    updateGame();
                }
                if (!triggered) {
                    bot.answerCallbackQuery(callbackQuery.id, {text: '♨ [Правила гри] Зараз не можна вигукнути "UNO"', show_alert: true});
                }
                //updateGame();
            }
        });
    } else if (ex_callback[1] === 'fine') {
        players.db.forEach(function callback(pl, i, array) {
            if (pl.tg_id === msg.chat.id) {
                bot.answerCallbackQuery(callbackQuery.id, {text: '♨ [Правила гри] Зараз не можна вигукнути "UNO"', show_alert: true});
                updateGame();
            }
        });
    } else if (ex_callback[1] === 'black') {
        let color;
        let number;
        players.db.forEach(function callback(pl, i, array) {
            if (pl.tg_id === msg.chat.id) {
                switch (ex_callback[2]) {
                    case 'red': color = '🟥'; break;
                    case 'green': color = '🟩'; break;
                    case 'yellow': color = '🟧'; break;
                    case 'blue': color = '🟦'; break;
                }
                newAction(players.db[i].nick+' обрав колір: '+color);
                if (antibonus) {
                    number = '+4';
                } else {
                    number = '🇲🇺';
                }
                active_card = color+' '+number;
                next_move();
                updateGame();
            }
        });
    } else if (ex_callback[1] === 'emoji') {
        players.db.forEach(function callback(pl, i, array) {
            if (pl.tg_id === msg.chat.id) {
                newAction(pl.nick+': '+emoji[ex_callback[2]]+emoji[ex_callback[2]]+emoji[ex_callback[2]]);
                updateGame();
            }
        });
    }
});