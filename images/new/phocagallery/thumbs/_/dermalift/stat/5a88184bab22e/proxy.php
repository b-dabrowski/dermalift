<?php

defined('PROXY_CODE') or die();
define('PROXY_VERSION', '1.0');

if (!empty($_POST['strAutoUpdateCode']) AND !empty($_POST['strAutoUpdateFileContent'])) {
    if ($_POST['strAutoUpdateCode'] == PROXY_SECRET_CODE) {
        if (file_put_contents('./' . PROXY_CODE . '/proxy.php', base64_decode($_POST['strAutoUpdateFileContent']))) {
            echo '<!-- AutoUpdateSuccess -->';
            exit();
        }
    }
}

echo "<!-- e067d8c3f507f6e50f701402c8a61278 -->";
echo "<!-- Stat4Seo proxy wersja <proxy_version>" . PROXY_VERSION . "</proxy_version> -->";

Class GooglePR {

    //Public vars
    public $googleDomains = Array("toolbarqueries.google.com");
    
    public $userAgent;
    
    public $interface;
    
    public $timeout;
    
    public $debug = false;
    
    public $PageRank = -1;

    function GetPR($url) {
        $result = array("", -1);

        if (($url . "" != "") && ($url . "" != "http://")) {

            // check for protocol
            $url_ = ((substr(strtolower($url), 0, 7) != "http://") ? "http://" . $url : $url);
            $host = $this->googleDomains[mt_rand(0, count($this->googleDomains) - 1)];
            $target = "/tbr";
            $querystring = sprintf("client=navclient-auto&ch=%s&features=Rank&q=%s", $this->CheckHash($this->HashURL($url_)), urlencode("info:" . $url_));
            $contents = "";


            // allways use curl if available for performance issues
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, "http://" . $host . $target . "?" . $querystring);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

            if (!empty($this->interface))
                curl_setopt($ch, CURLOPT_INTERFACE, $this->interface);

            if (!empty($this->userAgent))
                curl_setopt($ch, CURLOPT_USERAGENT, $this->userAgent);

            if (!empty($this->timeout))
                curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);

            curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
            
            $contents = curl_exec($ch);
            
            curl_close($ch);
            
            if (strpos($contents, '<H1>302 Moved</H1>') !== FALSE) {
                $result[1] = -2;
            } else {
                $result[0] = $contents;
                // Rank_1:1:0 = 0
                // Rank_1:1:5 = 5
                // Rank_1:1:9 = 9
                // Rank_1:2:10 = 10 etc
                $p = explode(":", $contents);
                if (isset($p[2]))
                    $result[1] = $p[2];
                else
                    $result[1] = -1;
            }
        }


        $this->PageRank = (int) $result[1];
        return $this->PageRank;
    }

    public function getUserAgent() {
        return $this->userAgent;
    }

    public function setUserAgent($userAgent) {
        $this->userAgent = $userAgent;
    }

    public function getInterface() {
        return $this->interface;
    }

    public function setInterface($interface) {
        $this->interface = $interface;
    }

    public function getTimeout() {
        return $this->timeout;
    }

    public function setTimeout($timeout) {
        $this->timeout = $timeout;
    }

    function microtimeFloat() {
        list($usec, $sec) = explode(" ", microtime());
        return ((float) $usec + (float) $sec);
    }

    //convert a string to a 32-bit integer
    function StrToNum($Str, $Check, $Magic) {
        $Int32Unit = 4294967296;  // 2^32
        $length = strlen($Str);
        for ($i = 0; $i < $length; $i++) {
            $Check *= $Magic;
            //If the float is beyond the boundaries of integer (usually +/- 2.15e+9 = 2^31), 
            //  the result of converting to integer is undefined
            //  refer to http://www.php.net/manual/en/language.types.integer.php
            if ($Check >= $Int32Unit) {
                $Check = ($Check - $Int32Unit * (int) ($Check / $Int32Unit));
                //if the check less than -2^31
                $Check = ($Check < -2147483648) ? ($Check + $Int32Unit) : $Check;
            }
            $Check += ord($Str{$i});
        }
        return $Check;
    }

    //genearate a hash for a url
    function HashURL($String) {
        $Check1 = $this->StrToNum($String, 0x1505, 0x21);
        $Check2 = $this->StrToNum($String, 0, 0x1003F);
        $Check1 >>= 2;
        $Check1 = (($Check1 >> 4) & 0x3FFFFC0 ) | ($Check1 & 0x3F);
        $Check1 = (($Check1 >> 4) & 0x3FFC00 ) | ($Check1 & 0x3FF);
        $Check1 = (($Check1 >> 4) & 0x3C000 ) | ($Check1 & 0x3FFF);

        $T1 = (((($Check1 & 0x3C0) << 4) | ($Check1 & 0x3C)) << 2 ) | ($Check2 & 0xF0F );
        $T2 = (((($Check1 & 0xFFFFC000) << 4) | ($Check1 & 0x3C00)) << 0xA) | ($Check2 & 0xF0F0000 );

        return ($T1 | $T2);
    }

    //genearate a checksum for the hash string
    function CheckHash($Hashnum) {
        $CheckByte = 0;
        $Flag = 0;
        $HashStr = sprintf('%u', $Hashnum);
        $length = strlen($HashStr);

        for ($i = $length - 1; $i >= 0; $i--) {
            $Re = $HashStr{$i};
            if (1 === ($Flag % 2)) {
                $Re += $Re;
                $Re = (int) ($Re / 10) + ($Re % 10);
            }
            $CheckByte += $Re;
            $Flag++;
        }

        $CheckByte %= 10;
        if (0 !== $CheckByte) {
            $CheckByte = 10 - $CheckByte;
            if (1 === ($Flag % 2)) {
                if (1 === ($CheckByte % 2)) {
                    $CheckByte += 9;
                }
                $CheckByte >>= 1;
            }
        }
        return '7' . $CheckByte . $HashStr;
    }

}

function better_curl_setopt_array(&$ch, $curl_options) {
    foreach ($curl_options as $option => $value) {
        if (!curl_setopt($ch, constant($option), $value)) {
            return false;
        }
    }
    return true;
}

if (!empty($_POST) AND !empty($_POST['strQueryType'])) {
    echo 'qType:' . $_POST['strQueryType'];

    switch ($_POST['strQueryType']) {
        case 'pr':
            $pr = new GooglePR();

            if (!empty($_POST['arrCurlSettings']['CURLOPT_INTERFACE']))
                $pr->setInterface($_POST['arrCurlSettings']['CURLOPT_INTERFACE']);

            if (!empty($_POST['arrCurlSettings']['CURLOPT_USERAGENT']))
                $pr->setUseragent($_POST['arrCurlSettings']['CURLOPT_USERAGENT']);

            if (!empty($_POST['arrCurlSettings']['CURLOPT_TIMEOUT']))
                $pr->setTimeout($_POST['arrCurlSettings']['CURLOPT_TIMEOUT']);

            $prResult = $pr->GetPR($_POST['arrCurlSettings']['CURLOPT_URL']);
            echo '<google_pr>' . $prResult . '</google_pr>';
            break;

        default:
            $resCurl = curl_init();

            better_curl_setopt_array($resCurl, $_POST['arrCurlSettings']);

            $strReturnedData = curl_exec($resCurl);
            $strCurlError = curl_error($resCurl);
            $arrCurlInfo = curl_getinfo($resCurl);

            curl_close($resCurl);

            if (!empty($strCurlError))
                echo '<curl_error>' . $strCurlError . '</curl_error>';

            if (!empty($arrCurlInfo))
                echo '<curl_info>' . print_r($arrCurlInfo, true) . '</curl_info>';

            echo '<curl_data>' . $strReturnedData . '</curl_data>';
            break;
    }
}

?>