<?php
    // Author:      I.Kharlamov
    // Created:     23.01.2013
    // version:     1.0.0
    // Created:    04.09.2014
    // Modifyed by: I.Kharlamov

    require_once($include."communication/constants.php");
    require_once($include."communication/exceptions.php");
    require_once($include."communication/regions.php");

    function isCISItemValid($x)
    {
        global $n_a;
        return (!$x || $x == $n_a) ? false : true;
    }

    function startswith($haystack, $needle)
    {
        return $needle === "" || strpos($haystack, $needle) === 0;
    }

    function endswith($haystack, $needle)
    {
        return $needle === "" || substr($haystack, -strlen($needle)) === $needle;
    }

    function getXMLItemValue($dom, $id)
    {
        global $n_a;
        $x = $dom->getElementsByTagName($id);

        if ($x->length == 0) return "";

        $value = $x->item(0);
        $x = $value->textContent;

        return (!$x || $x == $n_a) ? "" : $x;
    }

    function setXMLItemValue($dom, $id, $value)
    {
        $x = $dom->getElementsByTagName($id);

        if ($x->length > 0)
        {
            $item = $x->item(0);
            $old = $item->firstChild;

            if ($old)
            {
                $new = new DOMText($value);
                $item->replaceChild($new, $old);
            }
        }
    }

    function setXMLNodeItemValue($dom, $node, $id, $value)
    {
        $x = $dom->getElementsByTagName($node);

        if ($x->length == 0)
        {
            $items = $dom->createElement($node);
            $dom->appendChild($items);
        }
        else
        {
            $items = $x->item(0);
        }

        $item = $dom->createElement($id, $value ? $value : "");
        $items->appendChild($item);
    }

    function updateXMLItemTextValue($dom, $id, $value=null)
    {
        global $IsDebug;
        if ($IsDebug) trace("updateXMLItemTextValue:[".$id."], value:[".$value."]");
        
        $item = null;
        $v = $value ? $value : "";
        $old = false;

        try
        {
            $item = $dom->getElementsByTagName($id)->item(0);
            if ($item) $old = $item->firstChild;
        }
        catch (Exception $err) {}

        if ($item)
        {
            $new = new DOMText($v);

            if ($old)
                $item->replaceChild($new, $old);
            else
                $item->appendChild($new);
            return;
        }

        if (!getXMLItemValue($dom, "system"))
        {
            if ($IsDebug) trace("updateXMLItemTextValue: XML is not valid.");
            return;
        }

        try
        {
            $item = $dom->createElement($id, $v);
            $dom->appendChild($item);
        }
        catch (Exception $err) {}
    }

    function getXMLItemAttrValue($dom, $tag, $attr)
    {
        $item = $dom->getElementsByTagName($tag)->item(0);

        if ($item)
        {
            return ($attr && $item->hasAttribute($attr)) ? $item->getAttribute($attr) : "";
        }

        return "";
    }

    function getXMLItemAttrValueById($dom, $tag, $id, $attr)
    {
        $item = $dom->getElementsByTagName($tag)->item(0);

        if ($item && $item->getAttribute('id') == $id)
        {
            return ($attr && $item->hasAttribute($attr)) ? $item->getAttribute($attr) : "";
        }

        return "";
    }

    function setXMLItemAttrValue($dom, $id, $attr, $value)
    {
    }

    function cleanTags($xml, $tags, $value=null)
    {
        $out = $xml;
        foreach ($tags as $tag)
        {
            $out = preg_replace("/<".$tag.">(.*?)<\/".$tag.">/", "<$tag>".($value ? $value : "")."</$tag>", $out);
        }
        return $out;
    }

    function getCurrency($c)
    {
        return ($c == 001 ? 'у.е.' : 
               ($c == 203 ? 'CZK'  : 
               ($c == 643 ? 'RUR'  : 
               ($c == 398 ? 'KZT'  : 
               ($c == 840 ? 'USD'  : 
               ($c == 978 ? 'EUR'  : 
               ($c == 980 ? 'UAH'  : 
               '')))))));
    }

    function getRegionMail($id, $comma)
    {
        global $region_mail;

        if (isset($region_mail[$id]) && $region_mail[$id][2])
            return ($comma ? ', ' : '').$region_mail[$id][1];
        else
            return '';
    }

    function getCISLineValue($s)
    {
        return ereg_replace("/\!/", "", $s);
    }

    function getValidEmails($s)
    {
        global $n_a;
        $output = "";
        $a = array();
        $n = 0;

        foreach (preg_split("/,\s|,|;|\s/", $s) as $x)
        {
            $x = trim($x);
            if (!$x) continue;
            $mail = strtolower($x);
            if ($mail && $mail != $n_a && !in_array($mail, $a))
            {
                if ($output) $output .= ", ";
                $output .= $x;
                $a[] = $mail;
                ++$n;
            }
        }

        return ($n > 0) ? $output : "";
    }

    function getKeyword($key, $value)
    {
        global $keywords;
        if (strlen($key) > 2)
            $lang = substr(strtoupper($key), 0, 2);
        else
            $lang = strtoupper($key);
        if ($value) {
            if (isset($keywords[$value])) {
                $keyword = $keywords[$value];
                if (isset($keyword[$lang])) return $keyword[$lang];
                else if (isset($keyword['EN'])) return $keyword['EN'];
                return $keyword['RU'];
            }
        }
        return $value;
    }

    function getSalesFromAddress($key)
    {
        global $sales_from_address, $sales_from_default;
        if ($key && array_key_exists($key, $sales_from_address))
            return $sales_from_address[$key];
        return $sales_from_default;
    }

    function validatePriceValue($value)
    {
        $x = preg_split("/\.|,/", $value);
        if (count($x) ==  2) {
            return sprintf("%s.%s", $x[0], substr($x[1]."00", 0, 2));
        }
        return $value;
    }

    function getRemoteHost()
    {
        return 
            isset($_SESSION['RemoteHost']) && $_SESSION['RemoteHost'] ? $_SESSION['RemoteHost'] : (
                isset($_SERVER['REMOTE_HOST']) && $_SERVER['REMOTE_HOST'] ? $_SERVER['REMOTE_HOST'] : ''
            );
    }

    function getHttpReferer()
    {
        return 
            isset($_SESSION['HttpReferer']) && $_SESSION['HttpReferer'] ? $_SESSION['HttpReferer'] : (
                isset($_SERVER['HTTP_REFERER']) && $_SERVER['HTTP_REFERER'] ? $_SERVER['HTTP_REFERER'] : ''
            );
    }

    function getUserAgent()
    {
        return 
            isset($_SESSION['UserAgent']) && $_SESSION['UserAgent'] ? $_SESSION['UserAgent'] : (
                isset($_SERVER['HTTP_USER_AGENT']) && $_SERVER['HTTP_USER_AGENT'] ? $_SERVER['HTTP_USER_AGENT'] : ''
            );
    }

    function getOS($mode=0, $agent=null)
    {
        if (!$agent)
            $agent = getUserAgent();
        if (!$agent)
            return 'unknown';

        $oses = array (
            'Android' => '(Android)',
            'iPhone' => '(iPhone)',
            'iOS' => '(iOS)',
            '3.11' => 'Win16',
            '95' => '(Windows 95)|(Win95)|(Windows_95)',
            '98' => '(Windows 98)|(Win98)',
            '2000' => '(Windows NT 5.0)|(Windows 2000)',
            'XP' => '(Windows NT 5.1)|(Windows XP)',
            '2003' => '(Windows NT 5.2)',
            'Vista' => '(Windows NT 6.0)|(Windows Vista)',
            '7' => '(Windows NT 6.1)|(Windows 7)',
            'NT 4.0' => '(Windows NT 4.0)|(WinNT4.0)|(WinNT)|(Windows NT)',
            'ME' => 'Windows ME',
            'Open BSD'=>'OpenBSD',
            'Sun OS'=>'SunOS',
            'Linux'=>'(Linux)|(X11)',
            'Safari' => '(Safari)',
            'Macintosh'=>'(Mac_PowerPC)|(Macintosh)',
            'QNX'=>'QNX',
            'BeOS'=>'BeOS',
            'OS/2'=>'OS\/2',
            'Search Bot'=>'(nuhk)|(Googlebot)|(Yammybot)|(Openbot)|(Slurp\/cat)|(msnbot)|(ia_archiver)'
        );

        $old_windows = array('3.11', '95', '98', '2000', 'xp');
        $windows = array('3.11', '95', '98', '2000', 'xp', '2003', 'vista', '7', 'nt 4.0', 'me');
        $mobile = array('android', 'iphone', 'ios');
  
        foreach($oses as $os=>$pattern){
            if (preg_match("/".$pattern."/i", $agent)) {
                $x = strtolower($os);
                if ($mode)
                {
                    if (in_array($x, $old_windows))
                        return 'old-windows';
                    if (in_array($x, $windows))
                        return 'windows';
                    if (in_array($x, $mobile))
                        return 'mobile';
                }
                return $x;
            }
        }
        return 'unknown';
    }
    
    function getBrowser($mode=0, $agent=null)
    {
        if (!$agent)
            $agent = getUserAgent();
        if (!$agent)
            return 'unknown';

        $info = array();
        $browser = $version = '';
        preg_match("/(MSIE|Opera|OPR|Firefox|Version)(?:\/| )([0-9.]+)/", $agent, $info);
        if (!$info)
            preg_match("/(Chrome|Version)(?:\/| )([0-9.]+)/", $agent, $info);
        try {
            if ($info) list(,$browser, $version) = $info;
        }
        catch (Exception $err) {
            return 'unknown';
        }
        $browser = strtolower($browser);
        if ($mode == 1)
        {
            if ($browser == 'opr')
                return 'opera';
            if ($browser == 'opera' && $version == '9.80')
                return 'opera '.substr($agent,-5);
            if ($browser == 'version')
                return 'safari';
            if ($browser == "msie") {
                $x = preg_split("/\./", $version);
                return 'msie'.$x[0];
            }
            if (!$browser && strpos($agent, 'Gecko') !== false)
                return 'Browser based on Gecko';
        }
        else if ($mode == 2)
            return $browser.":".$version;
        else if ($mode == 3)
            return $agent;
        return $browser ? strtolower($browser) : 'unknown';
    }
    
    function sendSOAPErrorMessage($errorName, $exception=null)
    {
        global $lastOperationErrorCode, $IsErrorMail, $error_mail_to, $service_error_mail_to, $constructor_error_mail_to;
        global $vs_httpHostName, $vs_UserAgentIP, $vs_CurrSessionID, $vs_WizardID, $vs_CIS_RegionID, $vs_WebServiceURL, $vs_WebConstructorServiceURL, $vs_CIS_UserID;
        global $service, $webConstructorServiceClient, $vs_RequestContent, $vs_RespondContent, $vs_lastErrorDescr;
        global $ERRORS_DESCRIPTION, $debug, $EOL;

        $s = "";
        $s .= "Host: ".$vs_httpHostName.$EOL;
        $x = getRemoteHost();
        if ($x)
            $s .= "RemoteHost: [".$x."]".$EOL;
        $x = getUserAgent();
        if ($x)
            $s .= "Agent: ".$x."["."OS:".getOS(0, $x).", Browser:".getBrowser(0, $x)."]".$EOL;
        $x = getHttpReferer();
        if ($x)
            $s .= "HttpReferer: [".$x."]".$EOL;
        $s .= "WebService: [".$service."]".$EOL;
        $s .= "User IP: ".$vs_UserAgentIP.$EOL;
        $s .= "Region ID: ".$vs_CIS_RegionID.$EOL; //."[".$_SESSION["regionID"]."]"
        $s .= "User ID: ".$vs_CIS_UserID.$EOL;
        $s .= "Session ID: ".$vs_CurrSessionID.$EOL;
        $s .= "Wizard ID: ".$vs_WizardID.$EOL;

        $s .= $EOL;
        if (!$vs_lastErrorDescr 
            && $lastOperationErrorCode 
            && isset($ERRORS_DESCRIPTION[$lastOperationErrorCode])
            )
        {
            $x = preg_split("/\=/", $ERRORS_DESCRIPTION[$lastOperationErrorCode]);
            $vs_lastErrorDescr = trim(count($x) > 1 ? $x[1] : $x[0]);
        }
        $s .= $vs_lastErrorDescr ? "[".$lastOperationErrorCode."] ".$vs_lastErrorDescr.".".$EOL.$EOL : "";

        if ($exception) {
            $s .= "Error (File: ".$exception->getFile().", line ".$exception->getLine()."): ".$exception->getMessage();
            $s .= $EOL;
        }

        if ($s || $errorName) {
            $e = fopen($debug."1C-errors.txt", 'a');
            $c = sprintf("%s %3s {%s}: %s\n", date('Y-m-d H:i:s'), $lastOperationErrorCode, $errorName, $s);
            fwrite($e, $c);
            fclose($e);
        }

        if ($vs_RespondContent) {
            $s .= "-----------------".$EOL;
            $s .= "RESPONSE:".$EOL;
            $s .= preg_replace('/\t/', '  ', $vs_RespondContent);
            $s .= $EOL;
        }
        //else
        if ($vs_RequestContent) {
            $s .= "-----------------".$EOL;
            $s .= "REQUEST:".$EOL;
            $s .= preg_replace('/\t/', '  ', $vs_RequestContent);
            $s .= $EOL;
        }

        $header = "Content-type: text/plain; charset=\"utf-8\"";
        if ($IsErrorMail) {
    	    $ne = array(-208, -100);
    	    if (in_array($lastOperationErrorCode, $ne)) return;
            $se = array(-9, -10, -12, -13, -22, -23, -24, -25, -30, -31, -32, -39, -99, -100, -131, -132, -199);
            $mail_to = getValidEmails( 
                ($webConstructorServiceClient ? $constructor_error_mail_to : $error_mail_to).",".
                (in_array($lastOperationErrorCode, $se) ? $service_error_mail_to : '')
            );
            if (!$mail_to) return;
            mail($mail_to, $errorName." (".$lastOperationErrorCode.")", $s, $header);
            trace("SOAPErrorMessage[".$lastOperationErrorCode."] mailed to:[".$mail_to."]");
        }
    }

    function trace($s)
    {
        global $debug, $service, $vs_CIS_UserID, $EOL;
        try
        {
            $t = fopen($debug."1C-trace.txt", 'a');
            if ($t) {
                fwrite($t, sprintf("-> %s %s %s\n", date('Y-m-d H:i:s'), $vs_CIS_UserID, $s));
	        fclose($t);
	    }
        }
        catch (Exception $err) {}
    }

    function logger($s)
    {
        global $debug, $vs_CIS_RegionID, $vs_CIS_UserID, $vs_OperationValue, $vs_WizardID, $lastOperationErrorCode, $vs_CIS_UserWeb, $service, $EOL;
        try
        {
            $t = fopen($debug."1C-log.txt", 'a');
            fwrite($t, sprintf("-> %s %-9s %-9s %-25s %3s %4s [%s] %-30s %s\n", date('Y-m-d H:i:s'), 
                $vs_CIS_RegionID, $vs_CIS_UserID, $vs_WizardID, $vs_OperationValue, $lastOperationErrorCode, $s, $vs_CIS_UserWeb, $service));
            fclose($t);
        }
        catch (Exception $err) {}
    }

    function isRegionValid($value)
    {
        if ($value == $_SESSION["regionID"])
            return true;
        $x = preg_replace('/([0]+)(\w+)/', '$2', $value);
        return $x == $_SESSION["regionID"] ? true : false;
    }

    function isWizardActive($id)
    {
        $signs = array('1', '+', 'on');
        $active = false;

        $fi = fopen('m2.cfg', 'r');
        if ($fi && $id) {
            while (($line = fgets($fi)) !== false) {
                $x = preg_split("/\:/", $line);
                if ($x[0] && $id == trim($x[0])) {
                    if (count($x) > 1)
                        $active = in_array(trim($x[1]), $signs) ? true : false;
                    break;
                }
            }
            fclose($fi);
        }
        return $active;
    }

    function setSessionItem($key, $value)
    {
	   if (isset($_SESSION) && $key && $value) $_SESSION[$key] = $value;
    }

    function setSession($id=null)
    {
        global $vs_CurrSessionID, $IsDebug;

        if (isset($id) && $vs_CurrSessionID != $id) {
            session_id($id);
            $vs_CurrSessionID = $id;

            if ($IsDebug) trace("Set session: id=[".$id."]");
        }
        if (!isset($_SESSION)) {
            session_start();
            $vs_CurrSessionID = session_id();

            if ($IsDebug) trace("New session: id=[".$vs_CurrSessionID."]");
        }

        getSession();
    }

    function getSessionItem($key)
    {
        $value = '';

        $session_keys = array(
            'sessionID'  => 'wms_SessID__var',
            'userID'     => 'wms_UsrID__var',
            'userTypeID' => 'wms_UsrType__var',
            'userLogin'  => 'wms_LgnLogin__var',
            'userPswd'   => 'wms_PswdID__var',
            'userName'   => 'wms_UsrName__var'
        );

        if ($key && array_key_exists($key, $session_keys))
        {
            $skey = $session_keys[$key];
            $value = isset($_SESSION[$key]) && $_SESSION[$key] ? $_SESSION[$key] : (isset($_SESSION[$skey]) ? $_SESSION[$skey] : '');
        }
        else
            $value = isset($_SESSION[$key]) ? $_SESSION[$key] : '';

        return $value;
    }

    function getSession()
    {
        global $vs_PrevSessionID, 
               $vs_CurrSessionID, 
               $vs_ConnectionType, 
               $vs_WebServiceURL, 
               $vs_CIS_UserID, 
               $vs_CIS_UserTypeID, 
               $vs_CIS_UserLogin, 
               $vs_CIS_UserPswd, 
               $vs_CIS_UserHttp, 
               $vs_CIS_UserWeb, 
               $vs_CIS_CountryID, 
               $vs_CIS_RegionID, 
               $vs_CIS_CurrencyID, 
               $vs_CIS_PriceTypeID, 
               $vs_CIS_SecurityID,
               $IsDefault,
               $services,
               $sid;

        if (!isset($_SESSION))
            return;

        $vs_CurrSessionID = session_id();
        $vs_PrevSessionID = isset($_SESSION["sessionID"]) ? $_SESSION["sessionID"] : $vs_CurrSessionID; // XXX

        if ($IsDefault)
            return;

        $vs_CIS_UserID = getSessionItem('userID');
        $vs_CIS_UserTypeID = getSessionItem('userTypeID');
        $vs_CIS_UserLogin = getSessionItem('userLogin');
        $vs_CIS_UserPswd = getSessionItem('userPswd');

        if (!($vs_CIS_UserID && $vs_CIS_UserTypeID && $vs_CIS_UserLogin && $vs_CIS_UserPswd))
            $vs_ConnectionType = RetailPrice;
        else 
            $vs_ConnectionType = DealerPrice;

        $vs_CIS_UserHttp = getSessionItem('httpHost');
        $vs_CIS_UserWeb = getSessionItem('webResource');
        $vs_CIS_CountryID = getSessionItem('countryID');
        $vs_CIS_RegionID = getSessionItem('regionID');

        $url = getSessionItem('WebServiceURL');
        if ($url && $vs_WebServiceURL != $url) {
            array_splice($services, 0, 0, preg_replace('/ws\d\d/', $sid, $url));
        }

        $vs_CIS_CurrencyID = getSessionItem('currencyID');
        $vs_CIS_PriceTypeID = getSessionItem('priceTypeID');
        $vs_CIS_SecurityID = getSessionItem('securityID');
        $vs_ConnectionType = getSessionItem('ConnectionType');
    }

    function printSessionValues($s)
    {
        global $vs_mySessionValues,
               $vs_CurrSessionID,
               $vs_ConnectionType,
               $vs_CIS_UserID,
               $vs_CIS_UserTypeID,
               $vs_CIS_UserLogin,
               $vs_CIS_UserPswd,
               $vs_CIS_UserHttp,
               $vs_CIS_UserWeb,
               $vs_CIS_CountryID,
               $vs_CIS_RegionID,
               $vs_CIS_CurrencyID,
               $vs_CIS_PriceTypeID,
               $vs_CIS_SecurityID;

        $vs_mySessionValues = $vs_mySessionValues.$s.RETURNCHAR;
        $vs_mySessionValues = $vs_mySessionValues.TABCHAR."Session ID is ".$vs_CurrSessionID.RETURNCHAR;
        $vs_mySessionValues = $vs_mySessionValues.TABCHAR."Connection type is ".$vs_ConnectionType.RETURNCHAR;
        $vs_mySessionValues = $vs_mySessionValues.TABCHAR."User ID is ".$vs_CIS_UserID.RETURNCHAR;
        $vs_mySessionValues = $vs_mySessionValues.TABCHAR."User type is ".$vs_CIS_UserTypeID.RETURNCHAR;
        $vs_mySessionValues = $vs_mySessionValues.TABCHAR."Http host is ".$vs_CIS_UserHttp.RETURNCHAR;
        $vs_mySessionValues = $vs_mySessionValues.TABCHAR."Web resurce is ".$vs_CIS_UserWeb.RETURNCHAR;
        $vs_mySessionValues = $vs_mySessionValues.TABCHAR."Country ID is ".$vs_CIS_CountryID.RETURNCHAR;
        $vs_mySessionValues = $vs_mySessionValues.TABCHAR."Region ID is ".$vs_CIS_RegionID.RETURNCHAR;
        $vs_mySessionValues = $vs_mySessionValues.TABCHAR."Currency ID is ".$vs_CIS_CurrencyID.RETURNCHAR;
        $vs_mySessionValues = $vs_mySessionValues.TABCHAR."Price type is ".$vs_CIS_PriceTypeID.RETURNCHAR;
        $vs_mySessionValues = $vs_mySessionValues.TABCHAR."Security ID is ".$vs_CIS_SecurityID.RETURNCHAR;
    }
?>

