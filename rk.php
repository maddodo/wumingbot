<?php
if (empty($type)) die('not here');
$echo = '';
$truenum = 0;
$totalnum = 0;
switch ($type) {
    case 'name':
        $rs = $DB->getAll("SELECT distinct * FROM `people`.`new` WHERE `name` = '{$arg1}' LIMIT $limit");
        $totalnum += $DB->count("SELECT distinct count(*) FROM `people`.`new` WHERE `name` = '{$arg1}' LIMIT 2000");
        $truenum += count($rs);
        $echo .= arr2txt($rs);
        $rs = $DB->getAll("SELECT distinct * FROM `people`.`car` WHERE `name` = '{$arg1}' LIMIT $limit");
        $totalnum += $DB->count("SELECT distinct count(*) FROM `people`.`car` WHERE `name` = '{$arg1}' LIMIT 2000");
        $truenum += count($rs);
        $echo .= arr2txt($rs);
        $rs = $DB->getAll("SELECT distinct * FROM `people`.`kf` WHERE `name` = '{$arg1}' LIMIT $limit");
        $totalnum += $DB->count("SELECT distinct count(*) FROM `people`.`kf` WHERE `name` = '{$arg1}' LIMIT 2000");
        $truenum += count($rs);
        $echo .= arr2txt($rs);
        $rs = $DB->getAll("SELECT distinct * FROM `people`.`jingdong` WHERE `name` = '{$arg1}' LIMIT $limit");
        $totalnum += $DB->count("SELECT distinct count(*) FROM `people`.`jingdong` WHERE `name` = '{$arg1}' LIMIT 2000");
        $truenum += count($rs);
        $echo .= arr2txt($rs);
        $rs = $DB->getAll("SELECT distinct * FROM `people`.`fixman` WHERE `name` = '{$arg1}' LIMIT $limit");
        $totalnum += $DB->count("SELECT distinct count(*) FROM `people`.`fixman` WHERE `name` = '{$arg1}' LIMIT 2000");
        $truenum += count($rs);
        $echo .= arr2txt($rs);
        $rs = $DB->getAll("SELECT distinct * FROM `people`.`fixman2` WHERE `name` = '{$arg1}' LIMIT $limit");
        $totalnum += $DB->count("SELECT distinct count(*) FROM `people`.`fixman2` WHERE `name` = '{$arg1}' LIMIT 2000");
        $truenum += count($rs);
        $echo .= arr2txt($rs);
        $rs = $DB->getAll("SELECT distinct * FROM `people`.`jxydvip` WHERE `name` = '{$arg1}' LIMIT $limit");
        $totalnum += $DB->count("SELECT distinct count(*) FROM `people`.`jxydvip` WHERE `name` = '{$arg1}' LIMIT 2000");
        $truenum += count($rs);
        $echo .= arr2txt($rs);
        $rs = $DB->getAll("SELECT distinct * FROM `people`.`shunfeng` WHERE `name` = '{$arg1}' LIMIT $limit");
        $totalnum += $DB->count("SELECT distinct count(*) FROM `people`.`shunfeng` WHERE `name` = '{$arg1}' LIMIT 2000");
        $truenum += count($rs);
        $echo .= arr2txt($rs);
        $rs = $DB->getAll("SELECT distinct * FROM `people`.`vancl` WHERE `name` = '{$arg1}' LIMIT $limit");
        $totalnum += $DB->count("SELECT distinct count(*) FROM `people`.`vancl` WHERE `name` = '{$arg1}' LIMIT 2000");
        $truenum += count($rs);
        $echo .= arr2txt($rs);
        $rs = $DB->getAll("SELECT distinct * FROM `people`.`police` WHERE `name` = '{$arg1}' LIMIT $limit");
        $totalnum += $DB->count("SELECT distinct count(*) FROM `people`.`police` WHERE `name` = '{$arg1}' LIMIT 2000");
        $truenum += count($rs);
        $echo .= arr2txt($rs);
        break;

    case 'mobile':
        $rs = $DB->getAll("SELECT distinct * FROM `people`.`new` WHERE `mobile` = '{$arg1}' LIMIT $limit");
        $totalnum += $DB->count("SELECT distinct count(*) FROM `people`.`new` WHERE `name` = '{$arg1}' LIMIT 2000");
        $truenum += count($rs);
        $echo .= arr2txt($rs);
        $rs = $DB->getAll("SELECT distinct * FROM `people`.`car` WHERE `mobile` = '{$arg1}' LIMIT $limit");
        $totalnum += $DB->count("SELECT distinct count(*) FROM `people`.`car` WHERE `name` = '{$arg1}' LIMIT 2000");
        $truenum += count($rs);
        $echo .= arr2txt($rs);
        $rs = $DB->getAll("SELECT distinct * FROM `people`.`kf` WHERE `mobile` = '{$arg1}' LIMIT $limit");
        $totalnum += $DB->count("SELECT distinct count(*) FROM `people`.`kf` WHERE `name` = '{$arg1}' LIMIT 2000");
        $truenum += count($rs);
        $echo .= arr2txt($rs);
        $rs = $DB->getAll("SELECT distinct * FROM `people`.`jingdong` WHERE `mobile` = '{$arg1}' LIMIT $limit");
        $totalnum += $DB->count("SELECT distinct count(*) FROM `people`.`jingdong` WHERE `name` = '{$arg1}' LIMIT 2000");
        $truenum += count($rs);
        $echo .= arr2txt($rs);
        $rs = $DB->getAll("SELECT distinct * FROM `people`.`fixman` WHERE `mobile` = '{$arg1}' LIMIT $limit");
        $totalnum += $DB->count("SELECT distinct count(*) FROM `people`.`fixman` WHERE `name` = '{$arg1}' LIMIT 2000");
        $truenum += count($rs);
        $echo .= arr2txt($rs);
        $rs = $DB->getAll("SELECT distinct * FROM `people`.`fixman2` WHERE `mobile` = '{$arg1}' LIMIT $limit");
        $totalnum += $DB->count("SELECT distinct count(*) FROM `people`.`fixman2` WHERE `name` = '{$arg1}' LIMIT 2000");
        $truenum += count($rs);
        $echo .= arr2txt($rs);
        $rs = $DB->getAll("SELECT distinct * FROM `people`.`jxydvip` WHERE `mobile` = '{$arg1}' LIMIT $limit");
        $totalnum += $DB->count("SELECT distinct count(*) FROM `people`.`jxydvip` WHERE `name` = '{$arg1}' LIMIT 2000");
        $truenum += count($rs);
        $echo .= arr2txt($rs);
        $rs = $DB->getAll("SELECT distinct * FROM `people`.`shunfeng` WHERE `mobile` = '{$arg1}' LIMIT $limit");
        $totalnum += $DB->count("SELECT distinct count(*) FROM `people`.`shunfeng` WHERE `name` = '{$arg1}' LIMIT 2000");
        $truenum += count($rs);
        $echo .= arr2txt($rs);
        $rs = $DB->getAll("SELECT distinct * FROM `people`.`vancl` WHERE `mobile` = '{$arg1}' LIMIT $limit");
        $totalnum += $DB->count("SELECT distinct count(*) FROM `people`.`vancl` WHERE `name` = '{$arg1}' LIMIT 2000");
        $truenum += count($rs);
        $echo .= arr2txt($rs);
        $rs = $DB->getAll("SELECT distinct * FROM `people`.`police` WHERE `mobile` = '{$arg1}' LIMIT $limit");
        $totalnum += $DB->count("SELECT distinct count(*) FROM `people`.`police` WHERE `name` = '{$arg1}' LIMIT 2000");
        $truenum += count($rs);
        $echo .= arr2txt($rs);
        break;

    case 'mail':
        $rs = $DB->getAll("SELECT distinct * FROM `people`.`new` WHERE `mail` = '{$arg1}' LIMIT $limit");
        $totalnum += $DB->count("SELECT distinct count(*) FROM `people`.`new` WHERE `name` = '{$arg1}' LIMIT 2000");
        $truenum += count($rs);
        $echo .= arr2txt($rs);
        $rs = $DB->getAll("SELECT distinct * FROM `people`.`jingdong` WHERE `mail` = '{$arg1}' LIMIT $limit");
        $totalnum += $DB->count("SELECT distinct count(*) FROM `people`.`jingdong` WHERE `name` = '{$arg1}' LIMIT 2000");
        $truenum += count($rs);
        $echo .= arr2txt($rs);
        $rs = $DB->getAll("SELECT distinct * FROM `people`.`kf` WHERE `mail` = '{$arg1}' LIMIT $limit");
        $totalnum += $DB->count("SELECT distinct count(*) FROM `people`.`kf` WHERE `name` = '{$arg1}' LIMIT 2000");
        $truenum += count($rs);
        $echo .= arr2txt($rs);
        break;

    default:
        # code...
        break;
}
if (empty($echo)) $send .= "猎魔没有数据";
else $send .= $echo;
