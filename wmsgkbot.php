<?php
/*
 * @Author: zeyudada
 * @Date: 2022-04-20 19:12:17
 * @LastEditTime: 2022-05-22 12:32:07
 * @Description: 电报查询机器人
 * @Q Q: zeyunb@vip.qq.com(1776299529)
 * @E-mail: admin@zeyudada.cn
 * 
 * Copyright (c) 2022 by zeyudada, All Rights Reserved. 
 */
define('DEBUG', false);
define('BOT_NAME', 'wmsgkbot');
define('ADMIN_ID', 1393124548);

require_once 'Telegram.php';

$telegram = new Telegram('机器人密匙');
$data = $telegram->getData();



include_once('db.class.php');
$db_host = 'localhost';
$db_user = 'readonlyrk';
$db_pass = 'NfZ6KUUpuLkJjqZ';
$db_name = 'people'; //数据库名
$db_port = 6603; //数据库端口


$usertext = $telegram->Text();
$chat_id = $telegram->ChatID();
$user_id = $telegram->UserID();

$postfilter = "\\b(and|or)\\b.{1,6}?(=|>|<|\\bin\\b|\\blike\\b)|\\/\\*.+?\\*\\/|<\\s*script\\b|\\bEXEC\\b|UNION.+?SELECT|UPDATE.+?SET|INSERT\\s+INTO.+?VALUES|(SELECT|DELETE).+?FROM|(CREATE|ALTER|DROP|TRUNCATE)\\s+(TABLE|DATABASE)";
if (preg_match("/" . $postfilter . "/Uixs", $usertext) > 0) {
    $content = ['chat_id' => $chat_id, 'text' => '*警告*：请勿尝试提交不安全参数', 'parse_mode' => 'MarkdownV2'];
    $telegram->sendMessage($content);
    $telegram->finderror('不安全的参数在' . $chat_id);
    exit();
}

//连接 Mysql 数据库
$DB = new DB($db_host, $db_user, $db_pass, $db_name, $db_port);
//连接本地的 Redis 服务
$redis = new Redis();
$redis->connect('127.0.0.1', 6379);
if (!$redis->ping()) $telegram->finderror('redis连接失败！');

require_once(__DIR__ . '/function.php');

fastcgi_finish_request();

if ($telegram->getUpdateType() == 'inline_query') {
    $inline_query = $telegram->Inline_Query();
    $userdata = $DB->get_row("SELECT * FROM `tgbot`.`user` WHERE `userid` = '{$chat_id}'");
    if (empty($userdata)) {
        $telegram->answerInlineQuery([
            'inline_query_id' => $inline_query['id'],
            'results' => json_encode([[
                'type' => 'article',
                'id' => md5($inline_query['id']),
                'title' => '您还未注册机器人！请点击此处注册',
                'input_message_content' => [
                    'message_text' => "/start",
                    'parse_mode' => 'MarkdownV2',
                ],
                'url' => 'https://t.me/wmsgkbot'
            ]]),
            'cache_time' => 10
        ]);
    } else {
        $send = "*推荐社工人肉查询机器人：@wmsgkbot *\n免费查询QQ/微博/LOL绑定 三亿人口数据 二要素验证 手机状态查询等超多功能\n*输入激活码 `{$userdata['invite']}` 即可翻倍积分！*\n\n*";
        $send .= mdescape("加入链接 https://t.me/wmsgkbot?start={$userdata['invite']}") . " *";
        $telegram->answerInlineQuery([
            'inline_query_id' => $inline_query['id'],
            'results' => json_encode([[
                'type' => 'article',
                'id' => md5($inline_query['id'] . "1"),
                'title' => '👉点此发送邀请消息👈',
                'input_message_content' => [
                    'message_text' => $send,
                    'parse_mode' => 'MarkdownV2',
                ],
                'reply_markup' => [
                    'inline_keyboard' => [[
                        $telegram->buildInlineKeyBoardButton("👉免费猎魔社工机器人👈", "https://t.me/wmsgkbot?start={$userdata['invite']}")
                    ]]
                ],
                'description' => ''
            ]])
        ]);
    }
    exit();
}

if ($telegram->messageFromGroup()) {
    $groupdata = $DB->get_row("SELECT * FROM `tgbot`.`group` WHERE `groupid` = '{$chat_id}'");
    if (empty($groupdata)) {
        $groupdata = array(
            'groupid' => $chat_id,
            'title' => addslashes($data['message']['chat']['title']),
            'type' => $data['message']['chat']['type'],
            'count' => $telegram->getChatMemberCount($chat_id)['result'],
            'name' => $data['message']['chat']['username'],
            'creattime' => date('Y-m-d H:i:s'),
        );
        $DB->insert_array('tgbot`.`group', $groupdata);
    } else {
        $DB->query("UPDATE `tgbot`.`group` SET `title` = '" . addslashes($data['message']['chat']['title']) . "', `count` = '" . $telegram->getChatMemberCount($chat_id)['result'] . "', `name` = '" . $data['message']['chat']['username'] . "' WHERE `groupid` = '{$chat_id}'");
    }
    $userdata = $DB->get_row("SELECT * FROM `tgbot`.`user` WHERE `userid` = '{$user_id}'");
    if (empty($userdata)) {
        $userdata = array(
            'userid' => $data['message']['from']['id'],
            'name' => addslashes($data['message']['from']['username']),
            'first' => addslashes($data['message']['from']['first_name']),
            'last' => addslashes($data['message']['from']['last_name']),
            'balance' => 50,
            'inviter' => 'none',
            'invitecount' => 0,
            'checkin' => '1999-01-01',
            'invite' => setKami(),
            'discount' => 50
        );
        $DB->insert_array('tgbot`.`user', $userdata);
    } else {
        $DB->query("UPDATE `tgbot`.`user` SET `name` = '" . addslashes($data['message']['from']['username']) . "', `first` = '" . addslashes($data['message']['from']['first_name']) . "', `last` = '" . addslashes($data['message']['from']['last_name']) . "' WHERE `userid` = '{$user_id}'");
    }
} else {
    $userdata = $DB->get_row("SELECT * FROM `tgbot`.`user` WHERE `userid` = '{$user_id}'");
    if (empty($userdata)) {
        $userdata = array(
            'userid' => $user_id,
            'name' => addslashes($telegram->Username()),
            'first' => addslashes($telegram->FirstName()),
            'last' => addslashes($telegram->LastName()),
            'balance' => 50,
            'inviter' => 'none',
            'invitecount' => 0,
            'checkin' => '1999-01-01',
            'invite' => setKami(),
            'discount' => 50
        );
        $DB->insert_array('tgbot`.`user', $userdata);
    } else {
    }
}

$keyboard = $telegram->buildInlineKeyBoard([
    array($telegram->buildInlineKeyBoardButton("社工讨论群", $url = "https://t.me/wmsgk"), $telegram->buildInlineKeyBoardButton("免费API接口", $url = "https://wmsgk.com")),
]);

$callback_query = $telegram->Callback_Query();
if (!empty($callback_query)) {
    $message_id = $callback_query['message']['message_id'];
    $user_id = $callback_query['from']['id'];
    $chat_id = $telegram->Callback_ChatID();

    $callback_data = $telegram->Callback_Data();
    $arr = explode(' ', $callback_data);
    if (DEBUG) file_put_contents('callback.json', json_encode($callback_query));

    switch ($arr[0]) {
        case 'randmsg':
            # 随机化推广词
            $keyboard = $telegram->buildInlineKeyBoard([
                array(
                    $telegram->buildInlineKeyBoardButton("推广词随机化", null, "randmsg"),
                    $telegram->buildInlineKeyBoardButton("分享给好友", null, null, "推荐社工人肉查询机器人：@wmsgkbot \n\n输入激活码 {$userdata['invite']} 即可免费使用\n\n点击链接 https://t.me/wmsgkbot?start={$userdata['invite']}")
                ),
            ]);
            $reply = randmsg($userdata['invite']);
            $content = [
                'chat_id' => $chat_id, 'message_id' => $message_id, 'callback_query_id' => $telegram->Callback_ID(), 'text' => $reply,
                'parse_mode' => 'MarkdownV2',
                'reply_markup' => $keyboard
            ];
            $telegram->editMessageText($content);
            break;

        default:
            # code...
            break;
    }
}


start:
if($userdata['status'] > 0){
    $content = [
        'chat_id' => $chat_id,
        'text' => "*检测到被封禁用户*\n\n看我一🔪捅死你🐎",
        'parse_mode' => 'MarkdownV2',
        'reply_markup' => $keyboard
    ];
    $telegram->sendMessage($content);
    $telegram->banChatMember([
        'chat_id' => $chat_id,
        'user_id' => $userdata['userid'],
        'revoke_messages' => true
    ]);
    exit('ok');
}

/* 用户在查询大批量数据时的限制 */
$limit = $userdata['balance'] + ($userdata['invitecount'] * 10);

$arr = explode(' ', $usertext);
$cmd = explode('@', $arr[0]);
if (!empty($cmd[1]) && $cmd[1] != BOT_NAME) exit();
$text = $cmd[0];
switch ($text) {
    case '/cha':
        # 匹配查询的是啥
        if (empty($arr[1])) {
            $content = [
                'chat_id' => $chat_id,
                'text' => "*通过一种联系方式进行查询*\n\n语法：`/cha 关键词`\n还可以配合二要素验证：`/2fa 姓名 身份证号`\n\n目前可查：QQ/微博/LOL绑定 手机号查户籍/QQ/LOL/微博 查同名户籍 查邮箱户籍 查老密码",
                'parse_mode' => 'MarkdownV2',
                'reply_markup' => $keyboard
            ];
            $telegram->sendMessage($content);
            exit('ok');
        }
        $arg1 = $DB->escape($arr[1]);
        channel();
        include('cha.php');
        break;

    case '/16e':
        # 十六亿
        if (empty($arr[1])) {
            $telegram->sendMessage([
                'chat_id' => $chat_id,
                'text' => "*查询十六亿QQ绑定和老密码*\n\n语法：`/16e 关键词`\n示例：`/16e 286738260`",
                'parse_mode' => 'MarkdownV2'
            ]);
            exit();
        } elseif (mb_strlen($arr[1]) < 5) {
            $telegram->sendMessage([
                'chat_id' => $chat_id,
                'text' => "*关键词格式错误！*\n\n关键词至少五位数\n正确示例：`/16e 286738260`",
                'parse_mode' => 'MarkdownV2'
            ]);
            exit();
        }
        channel();
        $arg1 = $DB->escape($arr[1]);
        $totalnum = $DB->count("SELECT count(*) FROM `bind`.`16eqq` WHERE `username` = '{$arg1}' OR `mobile` = '{$arg1}' LIMIT 2000");
        $qb = $DB->getAll("SELECT DISTINCT * FROM `bind`.`16eqq` WHERE `username` = '{$arg1}' OR `mobile` = '{$arg1}' LIMIT {$limit}");
        $truenum = count($qb);
        if(empty($qb)) $send = '您的关键词在十六亿里没有数据';
        else $send = "查询结果：\n" . arr2txt($qb);

        
        if (strlen("总共{$totalnum}个结果，您的账号查询到了{$truenum}个结果\n" . $send) > 4096) {
            $telegram->sendChatAction($chat_id, 'upload_document');
            $savedir = __DIR__ . '/16e_tmp/' . date('Y-m-d') . '/';
            $filename = $arg1 . '_' . $truenum . '_' . $user_id . '.txt';
            if (!file_exists($savedir)) {
                mkdir($savedir, 0744, true);
            }
            file_put_contents($savedir . $filename, $send);
            $file = curl_file_create($savedir . $filename, 'text/plain');
            $telegram->sendDocument([
                'chat_id' => $chat_id, 'document' => $file, 'reply_markup' => $keyboard,
                'caption' => "总共{$totalnum}个结果，您的账号查询到了{$truenum}个结果\n[想查询更多结果吗？](https://t.me/wmsgkc/315)",
                'parse_mode' => 'MarkdownV2',
                'reply_to_message_id' => $data['message']['message_id'],
                'allow_sending_without_reply' => true
            ]);
        } else $telegram->sendMessage([
            'chat_id' => $chat_id, 'text' => "总共{$totalnum}个结果，您的账号查询到了{$truenum}个结果\n" . $send, 'reply_markup' => $keyboard,
            'reply_to_message_id' => $data['message']['message_id'],
            'allow_sending_without_reply' => true
        ]);
        break;

    case '/ping':
        # Ping check
        if (empty($arr[1])) {
            $starttime = microtime(true);
            $telegram->sendChatAction($chat_id, 'typing');
            $send = mdescape('服务器与电报DC1的延迟：' . number_format((microtime(true) - $starttime) * 1000, 4) . '毫秒');
            $telegram->sendMessage([
                'chat_id' => $chat_id,
                'text' => "*Pong\! {$send}*\n\n机器人还可以检测其他服务器的连通性哦！\n示例：`/ping google.com`",
                'parse_mode' => 'MarkdownV2'
            ]);
            exit();
        }
        if (!filter_var($arr[1], FILTER_VALIDATE_DOMAIN, FILTER_FLAG_HOSTNAME) && !filter_var($arr[1], FILTER_VALIDATE_IP, FILTER_FLAG_NO_RES_RANGE)) {
            $content = [
                'chat_id' => $chat_id,
                'text' => "*请勿尝试提交不安全内容*\n正确示例：`/ping google.com`",
                'parse_mode' => 'MarkdownV2',
                'reply_to_message_id' => $data['message']['message_id'],
                'allow_sending_without_reply' => true
            ];
            $telegram->sendMessage($content);
            exit();
        }
        $telegram->sendChatAction($chat_id, 'typing');
        $shell = "ping -c 4 -i 0.3 -W 10 " . $arr[1];
        exec($shell, $result, $status);
        if ($status) {
            $send = "抱歉，系统错误，请联系管理员";
        } else {
            $send = implode("\n", $result);
        }
        $content = [
            'chat_id' => $chat_id,
            'text' => $send,
            'reply_to_message_id' => $data['message']['message_id'],
            'allow_sending_without_reply' => true
        ];
        $telegram->sendMessage($content);
        break;

    case '/2fa':
        # 二要素验证
        if (empty($arr[1]) || empty($arr[2])) {
            $telegram->sendMessage([
                'chat_id' => $chat_id,
                'text' => "*验证姓名和身份证号码是否匹配*\n\n这是联网数据库，数据是最新的\n语法：`/2fa 姓名 身份证号`\n示例：`/2fa 马承钊 370681198704206877`",
                'parse_mode' => 'MarkdownV2'
            ]);
            exit();
        } else {
            require_once __DIR__ . '/IdentityCard.php'; //引入身份证验证类
            if (!IdentityCard::isValid($arr[2])) {
                $telegram->sendMessage([
                    'chat_id' => $chat_id,
                    'text' => "*身份证号码校验错误！说明此身份证不合法*\n\n正确示例：`/2fa 马承钊 370681198704206877`",
                    'parse_mode' => 'MarkdownV2'
                ]);
                exit();
            }
        }
        $cachecard = $redis->lRange($arr[2], 0, -1);
        if (!empty($cachecard)) {
            $send = "姓名：`{$arr[1]}`\n身份证：`{$arr[2]}`\n结果：*" . ($cachecard[0] != 'false' ? '匹配' : '不匹配') . "*\n";
            $send .= "详细信息：\n地址：" . mdescape($cachecard[1]) . "\n性别：{$cachecard[2]}\n生日：" . mdescape($cachecard[3]);
        } else {
            channel();
            $telegram->sendChatAction($chat_id, 'typing');

            $curl = curl_init();
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt($curl, CURLOPT_URL, "https://zidv2.market.alicloudapi.com/idcheck/Post");
            curl_setopt($curl, CURLOPT_HTTPHEADER, array(
                "Authorization:APPCODE 4b59326b6bbf4873a88b2f78f5756c44",
                "Content-Type:application/x-www-form-urlencoded; charset=UTF-8"
            ));
            curl_setopt($curl, CURLOPT_FAILONERROR, false);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_HEADER, false);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($curl, CURLOPT_POSTFIELDS, "cardNo={$arr[2]}&realName=" . urlencode($arr[1]));
            $json = json_decode(curl_exec($curl), true);
            curl_close($curl);
            //$json = json_decode(file_get_contents('https://wmsgk.vip/api/checkidcard?apiKey=859e471162f51b9b8401ca2623ccf645&name=' . urlencode($arr[1]) . '&card=' . $arr[2]), true);
            if ($json['error_code'] == 0) {
                //存储数据到列表中
                $redis->rpush($arr[2], $arr[1]);
                $redis->rpush($arr[2], $json['result']['IdCardInfor']['area']);
                $redis->rpush($arr[2], $json['result']['IdCardInfor']['sex']);
                $redis->rpush($arr[2], $json['result']['IdCardInfor']['birthday']);
                $send = "姓名：`{$arr[1]}`\n身份证：`{$arr[2]}`\n结果：*" . ($json['result']['isok'] ? '匹配' : '不匹配') . "*\n";
                if ($json['result']['isok']) $send .= "详细信息：\n地址：" . mdescape($json['result']['IdCardInfor']['area']) . "\n性别：{$json['result']['IdCardInfor']['sex']}\n生日：" . mdescape($json['result']['IdCardInfor']['birthday']);
            } elseif ($json['error_code'] == 206501) {
                //存储数据到列表中
                $redis->rpush($arr[2], "kuwu");
                $redis->rpush($arr[2], $json['result']['IdCardInfor']['area']);
                $redis->rpush($arr[2], $json['result']['IdCardInfor']['sex']);
                $redis->rpush($arr[2], $json['result']['IdCardInfor']['birthday']);
                $send = mdescape("查无此人。。。\n详见：https://t.me/wmsgkc/303");
            } else {
                $send = '接口返回异常，请联系管理员！';
            }
        }

        $content = [
            'chat_id' => $chat_id,
            'text' => $send,
            'reply_to_message_id' => $data['message']['message_id'],
            'allow_sending_without_reply' => true,
            'parse_mode' => 'MarkdownV2'
        ];
        $telegram->sendMessage($content);
        break;

    case '/sj':
        # 查询手机空号
        if (empty($arr[1])) {
            $telegram->sendMessage([
                'chat_id' => $chat_id,
                'text' => "*查询手机号在运营商的状态*\n\n这是联网数据库，数据是最新的，当查询过一次后就会永久缓存\n示例：`/sj 18645089813`",
                'parse_mode' => 'MarkdownV2'
            ]);
            exit();
        } elseif (strlen($arr[1]) != 11 || !is_numeric($arr[1])) {
            $telegram->sendMessage([
                'chat_id' => $chat_id,
                'text' => "*手机号格式错误！*\n\n正确示例：`/sj 18645089813`",
                'parse_mode' => 'MarkdownV2'
            ]);
            exit();
        }
        channel();
        $telegram->sendChatAction($chat_id, 'typing');

        $profile = $DB->get_row("SELECT * FROM `word`.`phonenumber` WHERE `phone` = '{$arr[1]}'");
        if (empty($profile)) {
            $headers = array();
            array_push($headers, "Authorization:APPCODE 4b59326b6bbf4873a88b2f78f5756c44");
            $url = "https://mobileempty.shumaidata.com/mobileempty?mobile=" . $arr[1];

            $curl = curl_init();
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "GET");
            curl_setopt($curl, CURLOPT_URL, $url);
            curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_HEADER, false);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
            $json = json_decode(curl_exec($curl), true);
            curl_close($curl);

            if ($json['code'] == 200) {
                $DB->query("INSERT INTO `word`.`phonenumber`(`phone`, `area`, `channel`, `status`) VALUES ('{$arr[1]}', '{$json['data']['area']}', '{$json['data']['channel']}', {$json['data']['status']})");
                /* 0:空号；1:实号；2:停机；3:库无；4:沉默号；5:风险号 */
                $status_list = [
                    0 => '空号',
                    1 => '活跃号',
                    2 => '停机',
                    3 => '无数据',
                    4 => '沉默号',
                    5 => '风险号'
                ];
                $status = $status_list[$json['data']['status']];
                $send = '手机：`' . $arr[1] . "`\n归属地：`" . $json['data']['area'] . "`\n运营商：`" . $json['data']['channel'] . "`\n结果：*{$status}*";
            } elseif ($json['code'] == 400) {
                $send = '提交参数格式错误！';
            } else {
                $send = $json['msg'] . "\n接口状态错误，请联系管理员！";
            }
        } else {
            $status_list = [
                0 => '空号',
                1 => '活跃号',
                2 => '停机',
                3 => '无数据',
                4 => '沉默号',
                5 => '风险号'
            ];
            $status = $status_list[$profile['status']];
            $send = '手机：`' . $arr[1] . "`\n归属地：`" . $profile['area'] . "`\n运营商：`" . $profile['channel'] . "`\n结果：*{$status}*";
        }

        $content = [
            'chat_id' => $chat_id,
            'text' => $send,
            'reply_to_message_id' => $data['message']['message_id'],
            'allow_sending_without_reply' => true,
            'parse_mode' => 'MarkdownV2'
        ];
        $telegram->sendMessage($content);
        break;

    case '/laolai':
        # 老赖
        if (empty($arr[1])) {
            $telegram->sendMessage([
                'chat_id' => $chat_id,
                'text' => "*查询全国失信被执行人名单*\n\n这是联网数据库，数据是最新的\n语法：`/laolai 姓名 身份证或组织机构代码 省份`\n示例：`/laolai 张进友`",
                'parse_mode' => 'MarkdownV2'
            ]);
            exit();
        }
        channel();
        $telegram->sendChatAction($chat_id, 'typing');

        $cardNum = empty($arr[2]) ? '' : $arr[2];
        $areaName = empty($arr[3]) ? '' : urlencode($arr[3]);
        $url = 'https://sp0.baidu.com/8aQDcjqpAAV3otqbppnN2DJv/api.php?resource_id=6899&query=%E5%A4%B1%E4%BF%A1%E8%A2%AB%E6%89%A7%E8%A1%8C%E4%BA%BA%E5%90%8D%E5%8D%95&cardNum=' . $cardnum . '&iname=' . urlencode($arr[1]) . '&areaName=' . $areaName . '&pn=0&rn=99&from_mid=1&ie=utf-8&oe=utf-8&format=json';

        $json = geturl($url, ['Referer: https://www.baidu.com/s?ie=utf-8&f=8&rsv_bp=1&rsv_idx=1&tn=baidu&wd=%E5%A4%B1%E4%BF%A1%E6%9F%A5%E8%AF%A2&fenlei=256&rsv_pq=ce3d21fe0003ee29&rsv_t=8c477L9UYERtd2IlaTvw5hWuFqccUKcf3To9WkcHSWQ4PJ%2B9G5BJwJblQak&rqlang=cn&rsv_enter=1&rsv_dl=tb&rsv_sug3=13&rsv_sug1=9&rsv_sug7=100&rsv_sug2=0&rsv_btype=i&inputT=3049&rsv_sug4=3051']);
        $chengyuans = count($json['data'][0]['disp_data']); //成员数
        if ($chengyuans == 0) {
            $send = '未找到相关失信被执行人';
        } else {
            $send = "*共{$chengyuans}个结果*：  \n";
            foreach ($json['data'][0]['disp_data'] as $shixin) {
                $send .= "姓名：" . $shixin['iname'] . $shixin['sexy'] . "  \n";
                $send .= "证件号：" . mdescape($shixin['cardNum']) . "  \n";
                $send .= "执行法院：" . $shixin['courtName'] . "  \n";
                $send .= "省份：" . $shixin['areaName'] . "  \n";
                $send .= "案号：" . mdescape($shixin['caseCode']) . "  \n";
                $send .= "判决：" . mdescape($shixin['duty']) . "  \n";
                $send .= "履行情况：" . $shixin['performance'] . "  \n";
                $send .= "行为具体情形：" . $shixin['disruptTypeName'] . "  \n";
                $send .= "发布时间：" . $shixin['publishDate'] . "  \n  \n";
            }
        }


        if (strlen($send) > 4096) {
            $telegram->sendChatAction($chat_id, 'upload_document');
            $savedir = __DIR__ . '/laolai_tmp/' . date('Y-m-d') . '/';
            $filename = mdescape($arr[1]) . '_' . $user_id . '.md';
            if (!file_exists($savedir)) {
                mkdir($savedir, 0744, true);
            }
            file_put_contents($savedir . $filename, $send);
            $file = curl_file_create($savedir . $filename, 'text/x-markdown');
            $sended = $telegram->sendDocument([
                'chat_id' => $chat_id, 'document' => $file, 'reply_markup' => $keyboard,
                'reply_to_message_id' => $data['message']['message_id'],
                'allow_sending_without_reply' => true
            ]);
        } else {
            $content = [
                'chat_id' => $chat_id,
                'text' => $send,
                'reply_to_message_id' => $data['message']['message_id'],
                'allow_sending_without_reply' => true,
                'parse_mode' => 'MarkdownV2'
            ];
            $sended = $telegram->sendMessage($content);
        }
        delmsg($chat_id, $sended['result']['message_id'], 60);
        break;

    case '/order':
        # 快递数据
        if (empty($arr[1])) {
            $content = [
                'chat_id' => $chat_id,
                'text' => "*通过一种联系方式进行查询*\n\n语法：`/order 关键词`\n\n目前有1300W\+数据，还在陆续增加中。。。",
                'parse_mode' => 'MarkdownV2',
                'reply_markup' => $keyboard
            ];
            $telegram->sendMessage($content);
            exit('ok');
        }
        $arg1 = $DB->escape($arr[1]);
        channel();
        include('order.php');
        break;

    case '/kd':
        # 实时快递
        if (empty($arr[1])) {
            $telegram->sendMessage([
                'chat_id' => $chat_id,
                'text' => "*通过快递单号查询物流状态*\n\n这是联网数据库，数据是最新的\n语法：`/kd 快递单号 公司简写可选`\n示例：`/kd 780098068058`\n_【顺丰和丰网请输入单号 : 收件人或寄件人手机号后四位。例如：123456789:1234】_",
                'parse_mode' => 'MarkdownV2'
            ]);
            exit();
        }
        channel();
        $telegram->sendChatAction($chat_id, 'typing');

        $headers = array();
        array_push($headers, "Authorization:APPCODE 4b59326b6bbf4873a88b2f78f5756c44");

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "GET");
        curl_setopt($curl, CURLOPT_URL, "https://wuliu.market.alicloudapi.com/kdi?no={$arr[1]}&type={$arr[2]}");
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        $json = json_decode(curl_exec($curl), true);
        curl_close($curl);

        if ($json['code'] == 0) {
            # 正常查询
            $deliverystatus = [
                0 => '快递收件(揽件)',
                1 => '在途中',
                2 => '正在派件',
                3 => '已签收',
                4 => '派送失败（无法联系到收件人或客户要求择日派送，地址不详或手机号不清）',
                5 => '疑难件（收件人拒绝签收，地址有误或不能送达派送区域，收费等原因无法正常派送）',
                6 => '退件签收'
            ];
            $send = "查询结果：*" . $deliverystatus[$json['result']['deliverystatus']] . "*\n快递单号：`{$json['result']['number']}`\n快递公司：`" . $json['result']['expName'] . "`\n*物流详情*：\n";
            foreach ($json['result']['list'] as $value) {
                $send .= "时间：`" . mdescape($value['time']) . "`\n信息：" . mdescape($value['status']) . "\n";
            }
            $send .= "是否签收：" . ($json['result']['issign'] == 1 ? '是' : '否') . "\n";
            if (!empty($json['result']['courier'])) $send .= "快递员：{$json['result']['courier']}\n";
            if (!empty($json['result']['courierPhone'])) $send .= "快递员电话：`{$json['result']['courierPhone']}`\n";
            $send .= "最后更新：`" . mdescape($json['result']['updateTime']) . "`\n";
            $send .= "快递耗时：{$json['result']['takeTime']}";
        } else {
            $send = $json['msg'];
        }

        $content = [
            'chat_id' => $chat_id,
            'text' => $send,
            'reply_to_message_id' => $data['message']['message_id'],
            'allow_sending_without_reply' => true,
            'parse_mode' => 'MarkdownV2'
        ];
        $telegram->sendMessage($content, true);
        break;

    case '/checkin':
        # 签到领积分
        if (strtotime($userdata['checkin']) < strtotime(date('Y-m-d'))) {
            $random = rand(1, 10);
            $DB->query("UPDATE `tgbot`.`user` SET `checkin` = CURRENT_TIMESTAMP,`balance` = balance + $random WHERE `userid` = '{$user_id}'");

            $telegram->sendMessage([
                'chat_id' => $chat_id,
                'text' => "签到成功！获得 $random 积分",
                'reply_to_message_id' => $data['message']['message_id'],
                'allow_sending_without_reply' => true
            ]);
        } else {
            $telegram->sendMessage([
                'chat_id' => $chat_id,
                'text' => "您今天已经签到过了~",
                'reply_to_message_id' => $data['message']['message_id'],
                'allow_sending_without_reply' => true
            ]);
        }
        break;

    default:
        # 没匹配的操作
        switch ($data['message']['chat']['type']) {
            case 'supergroup':
                # 超级群
                switch ($text) {
                    case '/start':
                        $content = [
                            'chat_id' => $chat_id,
                            'text' => "[*无名社工库群组*](https://t.me/wmsgk)\n\n猎魔：`/cha 关键词`\nPING：`/ping 域名/IP`\n二要素验证：`/2fa 姓名 身份证号`\n手机状态查询：`/sj 手机号`\n实时快递：`/kd 快递单号 公司简写可选`\n老赖查询：`/laolai 姓名 身份证或组织机构代码 省份`\n\n目前可查：QQ/微博/LOL绑定 手机号查户籍/QQ/LOL/微博 查同名户籍 查邮箱户籍 查老密码",
                            'parse_mode' => 'MarkdownV2',
                            'reply_markup' => $keyboard,
                            'reply_to_message_id' => $data['message']['message_id'],
                            'allow_sending_without_reply' => true
                        ];
                        $telegram->sendMessage($content);
                        break;

                    default:
                        # code...
                        break;
                }
                break;

            case 'group':
                # 普通群
                switch ($text) {
                    case '/start':
                        $content = [
                            'chat_id' => $chat_id,
                            'text' => "[*无名社工库群组*](https://t.me/wmsgk)\n\n猎魔：`/cha 关键词`\nPING：`/ping 域名/IP`\n二要素验证：`/2fa 姓名 身份证号`\n手机状态查询：`/sj 手机号`\n实时快递：`/kd 快递单号 公司简写可选`\n老赖查询：`/laolai 姓名 身份证或组织机构代码 省份`\n\n目前可查：QQ/微博/LOL绑定 手机号查户籍/QQ/LOL/微博 查同名户籍 查邮箱户籍 查老密码",
                            'parse_mode' => 'MarkdownV2',
                            'reply_markup' => $keyboard,
                            'reply_to_message_id' => $data['message']['message_id'],
                            'allow_sending_without_reply' => true
                        ];
                        $telegram->sendMessage($content);
                        break;

                    default:
                        # code...
                        break;
                }
                break;

            case 'private':
                # 私聊
                switch ($text) {
                    case '/start':
                        $content = [
                            'chat_id' => $chat_id,
                            'text' => "[*无名社工库群组*](https://t.me/wmsgk)\n\n猎魔：`/cha 关键词`\nPING：`/ping 域名/IP`\n二要素验证：`/2fa 姓名 身份证号`\n手机状态查询：`/sj 手机号`\n实时快递：`/kd 快递单号 公司简写可选`\n老赖查询：`/laolai 姓名 身份证或组织机构代码 省份`\n\n目前可查：QQ/微博/LOL绑定 手机号查户籍/QQ/LOL/微博 查同名户籍 查邮箱户籍 查老密码",
                            'parse_mode' => 'MarkdownV2',
                            'reply_markup' => $keyboard,
                            'reply_to_message_id' => $data['message']['message_id'],
                            'allow_sending_without_reply' => true
                        ];
                        $telegram->sendMessage($content);
                        if (!empty($arr[1])) {
                            $invitecode = $DB->escape($arr[1]);
                            $inviterdata = $DB->get_row("SELECT * FROM `tgbot`.`user` WHERE `invite` = '{$invitecode}'");
                            if (!empty($inviterdata) && $userdata['inviter'] == 'none' && $inviterdata['userid'] != $user_id) {
                                $DB->query("UPDATE `tgbot`.`user` SET `balance` = balance + 50, `inviter` = {$inviterdata['userid']} WHERE `userid` = '{$user_id}'");
                                $DB->query("UPDATE `tgbot`.`user` SET `invitecount` = invitecount + 1 WHERE `userid` = '{$inviterdata['userid']}'");
                                $telegram->sendMessage([
                                    'chat_id' => $chat_id,
                                    'text' => "使用邀请码成功！您的积分+50！"
                                ]);
                                $telegram->sendMessage([
                                    'chat_id' => $inviterdata['userid'],
                                    'text' => "有人使用了你的邀请码！\n被邀请人：" . mdescape($userdata['first']) . ' ' . mdescape($userdata['last']) . "\n发送 /info 查看你的信息",
                                    'parse_mode' => 'MarkdownV2',
                                ]);
                                if (DEBUG && $inviterdata['userid'] != ADMIN_ID) $telegram->sendMessage([
                                    'chat_id' => ADMIN_ID,
                                    'text' => "有新人使用了邀请码！\n被邀请人：\n`" . mdescape(json_encode($userdata)) . "`\n邀请人：\n`" . mdescape(json_encode($inviterdata)) . "`",
                                    'parse_mode' => 'MarkdownV2',
                                    'reply_markup' => $telegram->buildInlineKeyBoard([[
                                        $telegram->buildInlineKeyBoardButton("查看被邀请人", "tg://user?id={$userdata['userid']}"),
                                        $telegram->buildInlineKeyBoardButton("查看邀请人", "tg://user?id={$inviterdata['userid']}"),
                                    ]])
                                ]);
                            }
                        }
                        break;
                    case '/info':
                        # 账号信息
                        //$invitecount = $DB->count("SELECT count(*) FROM `tgbot`.`user` WHERE `inviter` = '{$user_id}'");
                        $limit = $userdata['balance'] + ($userdata['invitecount'] * 10);
                        $send = "👏 你好！ " . mdescape($telegram->FirstName()) . ' ' . mdescape($telegram->LastName()) . "\n\n";
                        $send .= "UID: `{$user_id}`\n邀请人数：{$userdata['invitecount']} 人\n积分：{$userdata['balance']}\n每个泄露源限制 {$limit} 条\n\n邀请码：`" . $userdata['invite'] . "`";
                        $telegram->sendMessage([
                            'chat_id' => $chat_id,
                            'text' => $send,
                            'parse_mode' => 'MarkdownV2',
                            'reply_to_message_id' => $data['message']['message_id'],
                            'allow_sending_without_reply' => true
                        ]);
                        break;

                    case '/share':
                        # 获取邀请链接
                        $send = "*推荐社工人肉查询机器人：@wmsgkbot *\n免费查询QQ/微博/LOL绑定 三亿人口数据 二要素验证 手机状态查询等超多功能\n*输入激活码 `{$userdata['invite']}` 即可翻倍积分！*\n\n*";
                        $send .= mdescape("加入链接 https://t.me/wmsgkbot?start={$userdata['invite']}") . " *";
                        $keyboard = $telegram->buildInlineKeyBoard([
                            array(
                                $telegram->buildInlineKeyBoardButton("推广词随机化", null, "randmsg"),
                                $telegram->buildInlineKeyBoardButton("分享给好友", null, null, "推荐社工人肉查询机器人：@wmsgkbot \n\n输入激活码 {$userdata['invite']} 即可免费使用\n\n点击链接 https://t.me/wmsgkbot?start={$userdata['invite']}")
                            ),
                        ]);
                        $telegram->sendMessage([
                            'chat_id' => $chat_id,
                            'text' => $send,
                            'parse_mode' => 'MarkdownV2',
                            'reply_markup' => $keyboard,
                            'reply_to_message_id' => $data['message']['message_id'],
                            'allow_sending_without_reply' => true
                        ]);
                        break;

                    case '/markdown':
                        # 复读机
                        if($user_id != ADMIN_ID) exit();
                        $telegram->sendMessage([
                            'chat_id' => $chat_id,
                            'text' => $usertext,
                            'parse_mode' => 'MarkdownV2',
                            'reply_to_message_id' => $data['message']['message_id'],
                            'allow_sending_without_reply' => true
                        ]);
                        break;

                    case '/execute':
                        # 代替一个用户执行命令
                        if($user_id != ADMIN_ID) exit();
                        if (empty($arr[1])) {
                            $telegram->sendMessage([
                                'chat_id' => $chat_id,
                                'text' => "*👮管理员指令：代替一个用户执行命令*\n\n这将把用户资料全部替换成他的并重新运行脚本\n语法：`/execute [用户ID] [可选聊天ID] 【指令】`\n示例：`/execute 1495535705 5079888130 【/info】`",
                                'parse_mode' => 'MarkdownV2'
                            ]);
                            exit();
                        }
                        $usertext = cut('【', '】', $usertext);
                        $user_id = $arr[1];
                        if(is_numeric($arr[2])) $chat_id = $arr[2];
                        goto start;
                        break;

                    default:
                        # 私聊默认查询关键词
                        $arg1 = $DB->escape($usertext);
                        if ($userdata['inviter'] == 'none') {
                            $inviterdata = $DB->get_row("SELECT * FROM `tgbot`.`user` WHERE `invite` = '{$arg1}'");
                            if (!empty($inviterdata) && $inviterdata['userid'] != $user_id) {
                                $DB->query("UPDATE `tgbot`.`user` SET `balance` = balance + 50, `inviter` = {$inviterdata['userid']} WHERE `userid` = '{$user_id}'");
                                $DB->query("UPDATE `tgbot`.`user` SET `invitecount` = invitecount + 1 WHERE `userid` = '{$inviterdata['userid']}'");
                                $telegram->sendMessage([
                                    'chat_id' => $chat_id,
                                    'text' => "使用邀请码成功！您的积分+50！"
                                ]);
                                $telegram->sendMessage([
                                    'chat_id' => $inviterdata['userid'],
                                    'text' => "有人使用了你的邀请码！\n被邀请人：" . mdescape($userdata['first']) . ' ' . mdescape($userdata['last']) . "\n发送 /info 查看你的信息",
                                    'parse_mode' => 'MarkdownV2',
                                ]);
                                if (DEBUG && $inviterdata['userid'] != ADMIN_ID) $telegram->sendMessage([
                                    'chat_id' => ADMIN_ID,
                                    'text' => "有新人使用了邀请码！\n被邀请人：\n`" . mdescape(json_encode($userdata)) . "`\n邀请人：\n`" . mdescape(json_encode($inviterdata)) . "`",
                                    'parse_mode' => 'MarkdownV2',
                                    'reply_markup' => $telegram->buildInlineKeyBoard([[
                                        $telegram->buildInlineKeyBoardButton("查看被邀请人", "tg://user?id={$userdata['userid']}"),
                                        $telegram->buildInlineKeyBoardButton("查看邀请人", "tg://user?id={$inviterdata['userid']}"),
                                    ]])
                                ]);
                            }
                        } else {
                            $telegram->sendChatAction($chat_id, 'typing');
                        }
                        include('cha.php');
                        break;
                }
                break;

            case 'channel':
                # 频道
                break;
        }
        break;
}
