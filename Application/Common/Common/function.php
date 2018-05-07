<?php

//页面跳转
function jump($message, $url,$num=3) {
    header("Content-type: text/html; charset=utf-8");
    echo "<script>alert('" . $message . "');location.href='" . $url . "';</script>";
    exit();
}

function jump_success($message,$url,$jptime=1){
    header("Content-type: text/html; charset=utf-8");
    echo "<script src=\"".C('WEB_URL')."Public/admin/js/jquery-1.10.2.min.js\" type=\"text/javascript\"></script>";
    echo "<script src=\"".C('WEB_URL')."Public/layer/layer.js\" type=\"text/javascript\"></script>";
    echo "<meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0\" />";
    echo "<script>layer.msg('".$message."',{icon:6,time:'".($jptime*1000)."'}, function (){window.location.href= '".$url."';})</script>";
    exit();
}

//时间函数

function strtotime_2($date) {
    $datetime = new DateTime($date);
    return $datetime->format('U'); //Unix 时间戳
}

//判断是否微信
function is_weixin() {
    if (strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') !== false) {
        return true;
    }
    return false;
}

/**
 * __mkdirs
 *
 * 循环建立目录的辅助函数
 *
 * @param dir    目录路径
 * @param mode    文件权限
 */
function __mkdirs($dir, $mode = 0777) {
    if (!is_dir($dir)) {
        __mkdirs(dirname($dir), $mode);
        @mkdir($dir, $mode);
        return true;
    }
    return true;
}

/*
 * 精确时间间隔函数
 * $time 发布时间 如 1356973323
 * $str 输出格式 如 Y-m-d H:i:s
 * 半年的秒数为15552000，1年为31104000，此处用半年的时间
 */

function from_time($time, $str) {
    isset($str) ? $str : $str = 'm-d';
    $way = time() - $time;
    $r = '';
    if ($way < 60) {
        $r = '刚刚';
    } elseif ($way >= 60 && $way < 3600) {
        $r = floor($way / 60) . '分钟前';
    } elseif ($way >= 3600 && $way < 86400) {
        $r = floor($way / 3600) . '小时前';
    } elseif ($way >= 86400 && $way < 2592000) {
        $r = floor($way / 86400) . '天前';
    } elseif ($way >= 2592000 && $way < 15552000) {
        $r = floor($way / 2592000) . '个月前';
    } else {
        $r = date("$str", $time);
    }
    return $r;
}

//自定义输出
function dd($data) {
    header("Content-type:text/html;charset=utf-8");
    echo "<pre>";
    print_r($data);
    die();
}

// 过滤掉emoji表情
/*
  $type=1 替换为图片
  $type=2 替换为空
 */
function filterEmoji($str, $type = 1) {
    $str = preg_replace_callback(
            '/./u', function (array $match) {
        if ($type == 1) {
            //图片存在替换正常，否则替换为空
            $images = "/emoji/" . strtoupper(json_decode(str_replace('\u', '', json_encode($match[0])))) . ".png";
            if (file_exists(THINK_PATH . "../Public" . $images)) {
                return strlen($match[0]) >= 4 ? "<img src='" . C('WEB_URL') . "/Public" . $images . "' style='padding:0px 1px;'>" : $match[0];
            } else {
                return strlen($match[0]) >= 4 ? "" : $match[0];
            }
        } else {
            return strlen($match[0]) >= 4 ? "" : $match[0];
        }
    }, $str);
    return $str;
}

/**
 * 字符截取 支持UTF8/GBK
 * @param $string
 * @param $length
 * @param $dot
 */
function str_cut($string, $length, $dot = '...', $charset = "utf-8") {
    $strlen = strlen($string);
    if ($strlen <= $length)
        return $string;
    $string = str_replace(array(' ', '&nbsp;', '&amp;', '&quot;', '&#039;', '&ldquo;', '&rdquo;', '&mdash;', '&lt;', '&gt;', '&middot;', '&hellip;'), array('∵', ' ', '&', '"', "'", '“', '”', '—', '<', '>', '·', '…'), $string);
    $strcut = '';
    if (strtolower($charset) == 'utf-8') {
        $length = intval($length - strlen($dot) - $length / 3);
        $n = $tn = $noc = 0;
        while ($n < strlen($string)) {
            $t = ord($string[$n]);
            if ($t == 9 || $t == 10 || (32 <= $t && $t <= 126)) {
                $tn = 1;
                $n++;
                $noc++;
            } elseif (194 <= $t && $t <= 223) {
                $tn = 2;
                $n += 2;
                $noc += 2;
            } elseif (224 <= $t && $t <= 239) {
                $tn = 3;
                $n += 3;
                $noc += 2;
            } elseif (240 <= $t && $t <= 247) {
                $tn = 4;
                $n += 4;
                $noc += 2;
            } elseif (248 <= $t && $t <= 251) {
                $tn = 5;
                $n += 5;
                $noc += 2;
            } elseif ($t == 252 || $t == 253) {
                $tn = 6;
                $n += 6;
                $noc += 2;
            } else {
                $n++;
            }
            if ($noc >= $length) {
                break;
            }
        }
        if ($noc > $length) {
            $n -= $tn;
        }
        $strcut = substr($string, 0, $n);
        $strcut = str_replace(array('∵', '&', '"', "'", '“', '”', '—', '<', '>', '·', '…'), array(' ', '&amp;', '&quot;', '&#039;', '&ldquo;', '&rdquo;', '&mdash;', '&lt;', '&gt;', '&middot;', '&hellip;'), $strcut);
    } else {
        $dotlen = strlen($dot);
        $maxi = $length - $dotlen - 1;
        $current_str = '';
        $search_arr = array('&', ' ', '"', "'", '“', '”', '—', '<', '>', '·', '…', '∵');
        $replace_arr = array('&amp;', '&nbsp;', '&quot;', '&#039;', '&ldquo;', '&rdquo;', '&mdash;', '&lt;', '&gt;', '&middot;', '&hellip;', ' ');
        $search_flip = array_flip($search_arr);
        for ($i = 0; $i < $maxi; $i++) {
            $current_str = ord($string[$i]) > 127 ? $string[$i] . $string[++$i] : $string[$i];
            if (in_array($current_str, $search_arr)) {
                $key = $search_flip[$current_str];
                $current_str = str_replace($search_arr[$key], $replace_arr[$key], $current_str);
            }
            $strcut .= $current_str;
        }
    }
    return $strcut . $dot;
}

/**
 * 图片过滤替换
 */
function wap_img($url) {
    return '<img src="' . $url . '" style="width:100%;">';
}

/**
 * 内容中图片替换
 */
function content_strip($content) {
    $content = preg_replace('/<img[^>]*src=[\'"]?([^>\'"\s]*)[\'"]?[^>]*>/ie', "wap_img('$1')", $content);
    return $content;
}

/**
 * 判断email格式是否正确
 * @param $email
 */
function is_email($email) {
    return strlen($email) > 6 && preg_match("/^[\w\-\.]+@[\w\-\.]+(\.\w+)+$/", $email);
}

/**
 * 判断手机号码格式是否正确
 * @param $email
 */
function is_phone($phone) {
    return strlen($phone) == 11 && preg_match("/^1[0-9]{10}$/", $phone);
}

/**
 * 产生随机字符串
 *
 * @param    int        $length  输出长度 
 * @param    string     $chars   可选的 ，默认为 0123456789
 * @return   string     字符串
 */
function random($length, $chars = '0123456789') {
    $hash = '';
    $max = strlen($chars) - 1;
    for ($i = 0; $i < $length; $i++) {
        $hash .= $chars[mt_rand(0, $max)];
    }
    return $hash;
}

//书写运行中错误日志
function my_error_handler($errno, $errstr, $errfile, $errline) {
    if ($errno == 8)
        return '';
    $errfile = str_replace(THINK_PATH . "../Public/cache/", '', $errfile);
    error_log(date('m-d H:i:s', SYS_TIME) . ' | ' . $errno . ' | ' . str_pad($errstr, 30) . ' | ' . $errfile . ' | ' . $errline . "\r\n", 3, 'errorlog/error_' . date('YW', SYS_TIME) . '.php');
}

//书写日志到run.log
function write_log($message = '', $filename = "") {
    __mkdirs(THINK_PATH . '../Public/cache/runlog/');
    if ($filename) {
        $statlogfile = THINK_PATH . '../Public/cache/runlog/' . $filename . '.log'; //不存在自动生成
    } else {
        $statlogfile = THINK_PATH . '../Public/cache/runlog/' . date("YW", SYS_TIME) . '.log'; //不存在自动生成
    }
    if ($fp = @fopen($statlogfile, 'a')) {
        @flock($fp, 2);
        fwrite($fp, $message . "[" . date("Y-m-d H:i:s", SYS_TIME) . "]\r\n");
        fclose($fp);
    }
}

//获取当前URL
function geturl() {
    return 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
}

/** 
* create_uuid
*
* uuid生成字符串辅助函数 
*
*/
function create_uuid($prefix = "arpz~"){    //可以指定前缀
    return $prefix.md5(uniqid(mt_rand(),true)).mt_rand(1000,9999);
}
/**
 * 只保留字符串首尾字符，隐藏中间用*代替（两个字符时只显示第一个）
 * @param string $user_name 姓名
 * @return string 格式化后的姓名
 */
function substr_cut($user_name){
    $strlen     = mb_strlen($user_name, 'utf-8');
    $firstStr     = mb_substr($user_name, 0, 1, 'utf-8');
    $lastStr     = mb_substr($user_name, -1, 1, 'utf-8');
    return $strlen == 2 ? $firstStr . str_repeat('*', mb_strlen($user_name, 'utf-8') - 1) : $firstStr . str_repeat("*", $strlen - 2) . $lastStr;
} 

function sub_cut($user_name){
    $strlen     = mb_strlen($user_name, 'utf-8');
    $firstStr     = mb_substr($user_name, 0, 3, 'utf-8');
    $lastStr     = mb_substr($user_name, -1, 1, 'utf-8');
    return $strlen == 2 ? $firstStr . str_repeat('*', mb_strlen($user_name, 'utf-8') - 1) : $firstStr . str_repeat("*", $strlen - 2) . $lastStr;
} 


function timediff($begin_time, $end_time) {
    if ($begin_time < $end_time) {
        $starttime = $begin_time;
        $endtime = $end_time;
    } else {
        $starttime = $end_time;
        $endtime = $begin_time;
    }
    //计算天数
    $timediff = $endtime - $starttime;
    $days = intval($timediff / 86400);
    //计算小时数
    $remain = $timediff % 86400;
    $hours = intval($remain / 3600);
    //计算分钟数
    $remain = $remain % 3600;
    $mins = intval($remain / 60);
    //计算秒数
    $secs = $remain % 60;
    $days = str_pad($days, 2, "0", STR_PAD_LEFT);
    $hours = str_pad($hours, 2, "0", STR_PAD_LEFT);
    $mins = str_pad($mins, 2, "0", STR_PAD_LEFT);
    $secs = str_pad($secs, 2, "0", STR_PAD_LEFT);
    $res = array("day" => $days, "hour" => $hours, "min" => $mins, "sec" => $secs);
    return $res;
}
