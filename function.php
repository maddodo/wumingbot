<?php
function delmsg($chat_id, $msg_id, $sec = 60)
{
    global $telegram;
    sleep($sec);
    $telegram->deleteMessage($chat_id, $msg_id);
}

/**
 * @description: Markdown转义
 * @param {string} $str
 * @return {*}
 */
function mdescape($str)
{
    return str_replace(['_', '*', '[', ']', '(', ')', '~', '`', '>', '#', '+', '-', '=', '|', '{', '}', '.', '!'], ['\_', '\*', '\[', '\]', '\(', '\)', '\~', '\`', '\>', '\#', '\+', '\-', '\=', '\|', '\{', '\}', '\.', '\!'], $str);
}

function channel()
{
    global $redis;
    global $telegram;
    $keyname = 'channel' . $telegram->UserID();
    if ($redis->exists($keyname)) {
        return $redis->get($keyname);
    }
    $channel = $telegram->getChatMember('@wmsgkc', $telegram->UserID());
    if (!in_array($channel['result']['status'], ['creator', 'administrator', 'member'])) {
        $content = [
            'chat_id' => $telegram->ChatID(),
            'text' => "*请先点击下方按钮关注频道后查询！*",
            'parse_mode' => 'MarkdownV2',
            'reply_markup' => $telegram->buildInlineKeyBoard([
                array($telegram->buildInlineKeyBoardButton("👉点我关注频道👈", $url = "https://t.me/wmsgkc")),
            ]),
            'reply_to_message_id' => $telegram->getData()['message']['message_id'],
            'allow_sending_without_reply' => true,
        ];
        $sended = $telegram->sendMessage($content);
        delmsg($telegram->ChatID(), $sended[0]['result']['message_id'], 60);
        die();
    } else return $redis->set($keyname, $channel['result']['status']);
}

/**
 * @description: 模拟get请求
 * @param string $url 链接
 * @param array $headerArray 请求头
 * @return array 返回信息
 */
function geturl($url, $headerArray = array('charset="utf-8"'))
{

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headerArray);
    $output = curl_exec($ch);
    curl_close($ch);
    return json_decode($output, true);
}

/**
 * @description: 模拟post请求
 * @param string $url 链接
 * @param array $data 请求数据
 * @param array $headerArray 请求头
 * @return array 返回数组
 */
function posturl($url, $data, $headerArray = array('Content-type:application/json;charset="utf-8"', "Accept:application/json"))
{
    $data  = json_encode($data);

    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
    curl_setopt($curl, CURLOPT_POST, 1);
    curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
    curl_setopt($curl, CURLOPT_HTTPHEADER, $headerArray);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    $output = curl_exec($curl);
    curl_close($curl);
    return json_decode($output, true);
}


function startwith($str, $pattern)
{
    return (strpos($str, $pattern) === 0) ? true : false;
}

/**
 * @description: 混淆英文字符
 * @param {string} $word
 * @return {string} 神仙都看不懂的天数
 * Copyright (c) 2022 by zeyudada, All Rights Reserved. 
 */
function randeng($word)
{
    $from = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";
    $eng = [
        "𝖠𝖡𝖢𝖣𝖤𝖥𝖦𝖧𝖨𝖩𝖪𝖫𝖬𝖭𝖮𝖯𝖰𝖱𝖲𝖳𝖴𝖵𝖶𝖷𝖸𝖹𝖺𝖻𝖼𝖽𝖾𝖿𝗀𝗁𝗂𝗃𝗄𝗅𝗆𝗇𝗈𝗉𝗊𝗋𝗌𝗍𝗎𝗏𝗐𝗑𝗒𝗓𝟢𝟣𝟤𝟥𝟦𝟧𝟨𝟩𝟪𝟫",
        "𝐀𝐁𝐂𝐃𝐄𝐅𝐆𝐇𝐈𝐉𝐊𝐋𝐌𝐍𝐎𝐏𝐐𝐑𝐒𝐓𝐔𝐕𝐖𝐗𝐘𝐙𝐚𝐛𝐜𝐝𝐞𝐟𝐠𝐡𝐢𝐣𝐤𝐥𝐦𝐧𝐨𝐩𝐪𝐫𝐬𝐭𝐮𝐯𝐰𝐱𝐲𝐳𝟎𝟏𝟐𝟑𝟒𝟓𝟔𝟕𝟖𝟗",
        "𝗔𝗕𝗖𝗗𝗘𝗙𝗚𝗛𝗜𝗝𝗞𝗟𝗠𝗡𝗢𝗣𝗤𝗥𝗦𝗧𝗨𝗩𝗪𝗫𝗬𝗭𝗮𝗯𝗰𝗱𝗲𝗳𝗴𝗵𝗶𝗷𝗸𝗹𝗺𝗻𝗼𝗽𝗾𝗿𝘀𝘁𝘂𝘃𝘄𝘅𝘆𝘇𝟬𝟭𝟮𝟯𝟰𝟱𝟲𝟳𝟴𝟵",
        "𝐴𝐵𝐶𝐷𝐸𝐹𝐺𝐻𝐼𝐽𝐾𝐿𝑀𝑁𝑂𝑃𝑄𝑅𝑆𝑇𝑈𝑉𝑊𝑋𝑌𝑍𝑎𝑏𝑐𝑑𝑒𝑓𝑔ℎ𝑖𝑗𝑘𝑙𝑚𝑛𝑜𝑝𝑞𝑟𝑠𝑡𝑢𝑣𝑤𝑥𝑦𝑧𝟶𝟷𝟸𝟹𝟺𝟻𝟼𝟽𝟾𝟿",
        "𝔸𝔹ℂ𝔻𝔼𝔽𝔾ℍ𝕀𝕁𝕂𝕃𝕄ℕ𝕆ℙℚℝ𝕊𝕋𝕌𝕍𝕎𝕏𝕐ℤ𝕒𝕓𝕔𝕕𝕖𝕗𝕘𝕙𝕚𝕛𝕜𝕝𝕞𝕟𝕠𝕡𝕢𝕣𝕤𝕥𝕦𝕧𝕨𝕩𝕪𝕫𝟘𝟙𝟚𝟛𝟜𝟝𝟞𝟟𝟠𝟡"
    ];

    $text = '';
    for ($i = 0; $i < strlen($word); $i++) {
        $text .= mb_substr($eng[mt_rand(0, 4)], strpos($from, mb_substr($word, $i, 1, "utf-8")), 1, "utf-8");
    }
    return $text;
}

function randword($fix)
{
    return mb_substr($fix, mt_rand(0, mb_strlen($fix) - 1), 1);
}

function randmsg($invite)
{
    $space = randword("/|‖¦┊┋ 　");
    $word = randword("推嶊蓷");
    $word .= randword("荐鞯");
    $word .= "社";
    $word .= randword("工笁叿讧冮江仜㒰叿");
    $word .= randword("人亾亼");
    $word .= randword("肉禸㕯肏");
    $word .= randword("机僟");
    $word .= randword("器噐");
    $word .= randword("人亾亼");
    $word .= randword(":：;");
    $word .= randword("请請蜻埥");
    $word .= randword("手掱");
    $word .= randword("打咑");
    $word .= randword("@＠");
    $word .= randeng(randword("Ww") . randword("Mm") . randword("Ss") . randword("Gg") . randword("Kk") . randword("Bb") . randword("Oo") . randword("Tt"));
    $word .= randword(" 　") . "\n";
    $word .= randword("免兔凂浼悗俛挽");
    $word .= randword("费費曊鐨㵒镄");
    $word .= randword("查査楂碴喳馇嵖");
    $word .= randword("询咰詢洵恂㧦徇");
    $word .= randeng(randword("Qq") . randword("Qq"));
    $word .= $space;
    $word .= randword("微威溦嶶癓矀");
    $word .= "博" . $space;
    $word .= randeng(randword("Ll") . randword("Oo") . randword("Ll"));
    $word .= randword("绑梆垹綁");
    $word .= randword("定萣锭腚啶碇掟萣聢蝊");
    $word .= $space;
    $word .= randword("三③⑶⒊❸㈢");
    $word .= randword("亿肊eE");
    $word .= "猎";
    $word .= randword("魔嚤");
    $word .= randword(" 　") . "\n\n";
    $word .= randword("输瀭輸");
    $word .= randword("入叺");
    $word .= randword("激噭憿");
    $word .= randword("活萿佸");
    $word .= randword("码玛嗎瑪碼犸");
    $word .= "{$space} `{$invite}` {$space}";
    $word .= randword("积積");
    $word .= randword("分汾");
    $word .= randword("翻飜");
    $word .= randword("倍陪涪俻");
    // 蓷薦涻笁亾禸楂咰僟噐亾凂曊楂咰嶶博垹萣彡億亾囗薮琚②婹嫊験姃掱僟匨忲楂咰等趫哆糼能瀭叺噭萿犸旣妸飜俻積汾咖叺嗹帹
    // 推薦社工人肉查詢機器人免費查詢微博綁定三億人口數據二要素驗證手機狀態查詢等超多功能輸入激活碼即可翻倍積分加入鏈接
    return $word;
}

function arr2txt($arr)
{
    $known = [
        'name' => '姓名',
        'mobile' => '手机',
        'mail' => '邮箱',
        'address' => '地址',
        'provinces' => '省',
        'city' => '市',
        'zip' => '邮编',
        'place' => '地址',
        'sfz' => '身份证',
        'mail' => '邮箱',
        'extra' => '其他',
        'extra2' => '其他',
        'jifen' => '积分',
        'pwd' => '密码',
        'user' => '用户名',
        'date' => '日期',
        'car' => '车牌号',
        'motor' => '引擎',
        'color' => '颜色',
        'carname' => '车名',
        'source' => '来源',
        'tel' => '座机',
        'price' => '价格',
        'qq' => 'QQ',
        'uid' => 'UID',
        'server' => '大区',
        'username' => '用户名'
    ];
    if (empty($arr)) return;
    $echo = '';
    foreach ($arr as $key => $value) {
        if (is_array($value) && !empty($value)) {
            foreach ($value as $k => $v) {
                if (!empty($v) && $v != '\N') $echo .= "{$known[$k]}: {$v} ";
            }
            $echo .= "\n";
        } elseif(!empty($value)) $echo .= "{$known[$key]}: {$value} ";
    }
    return $echo . "\n";
}


/**
 * php截取指定两个字符之间字符串，默认字符集为utf-8 
 * @param string $begin  开始字符串
 * @param string $end    结束字符串
 * @param string $str    需要截取的字符串
 * @return string
 */
function cut($begin, $end, $str)
{
    $b = mb_strpos($str, $begin) + mb_strlen($begin);
    return mb_substr($str, $b, mb_strpos($str, $end) - $b);
}

/**
 * @description: 自定义错误处理
 * @param int $errno 错误的级别
 * @param string $errstr 错误的信息
 * @param string $errfile 错误的文件名
 * @param int $errline 错误发生的行号
 */
function customError($errno, $errstr, $errfile, $errline)
{
    global $telegram;
    $e = new \Exception();
    $backtrace = '============[ERROR_Trace]===========';
    $backtrace .= "\n";
    $backtrace .= $e->getTraceAsString();
    try {
        $dir_name = 'logs';
        if (!is_dir($dir_name)) {
            mkdir($dir_name);
        }
        $fileName = $dir_name . '/' . dirname(__FILE__) . '-' . date('Y-m-d') . '.txt';
        $myFile = fopen($fileName, 'a+');
        $date = '============[Date]============';
        $date .= "\n";
        $date .= '[ ' . date('Y-m-d H:i:s  e') . ' ] ';
        fwrite($myFile, $date . $backtrace . "\n\n");
        fclose($myFile);
    } catch (\Exception $e) {
        $telegram->finderror($e->getMessage());
    }

    $telegram->finderror("*发生错误:* [$errno] $errstr,错误在行 $errline 文件 $errfile");
}
set_error_handler("customError", E_ERROR);

// 卡密生成器
function  setKami($codeLen = 8)
{
    $str = "abcdefghijkmnpqrstuvwxyz0123456789ABCDEFGHIGKLMNPQRSTUVWXYZ"; //设置被随机采集的字符串
    $rand = "";
    for ($i = 0; $i < $codeLen - 1; $i++) {
        $rand .= $str[mt_rand(0, strlen($str) - 1)];  //如：随机数为30  则：$str[30]
    }
    return $rand;
}
