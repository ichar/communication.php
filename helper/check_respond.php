<?php
    // Author:      I.Kharlamov
    // Created:     07.04.2010
    // version:     1.0.0
    // Created:    29.04.2015
    // Modifyed by: I.Kharlamov

    // Log level
    error_reporting(E_ALL);

    require_once($include."communication/constants.php");
    require_once($include."communication/lib.php");

    if ($IsDebug) trace("start: check_respond.php");

    date_default_timezone_set('Europe/Moscow');

    // Declaration and initialization
    $myStrTmp = "";
    $xmlResponse = "";
    $IsNotError = false;
    $IsSave = false;
    $error_code = "";
    $error_description = "";
    $total = "";
    $currency = "";
    $currency_name = "";
    $mail_to_user = "";
    $send_charset = "utf-8"; // "cp1251"
    $wizard = "";
    $error = "";
    $cr = "<br>\n";

    if ($IsDebug)
    {
        $filename = $debug."1C-response.txt";
        $handle = fopen($filename, 'w');

        $mycontent = "isWithoutService=".($isWithoutService ? '1' : '0').
            "\n\n"."RESPONSE:\n".$vs_RespondContent.
            "\n\n"."CONSTRUCTOR RESPONSE:\n".$vs_ConstructorRespondContent."\n\n"
            ;
        fwrite($handle, $mycontent);
    }

    if ($IsDebug) trace("1");

    if (!$lastOperationErrorCode)
    {
        try
        {
            $vs_RespondContent = mb_substr($vs_RespondContent, 0, xmlFileSizeLimit);
            
            // =============================================================
                $IsNotError = $domResponseXML->loadXML($vs_RespondContent);
            // =============================================================

            if ($IsNotError === false)
            {
                $lastOperationErrorCode = -24;
                sendSOAPErrorMessage("SOAP WebService ResponseLoadXML Error", null);

                if ($IsDebug) fwrite($handle, "-> load WebService XML error:".$lastOperationErrorCode."\n");
            }
            else
            {
                // Get root element
                // ----------------
                $xmlResponse = $domResponseXML->documentElement;

                if ($IsDebug) fwrite($handle, "-> start WebService XML parsing\n");

                // Error of service
                // ----------------
                $error_code         = getXMLItemValue($xmlResponse, "errorCode");
                $error_description  = getXMLItemValue($xmlResponse, "errorDescription");

                // Get responsed parameters
                // ------------------------
                $vs_CIS_WizardID    = getXMLItemValue($xmlResponse, "wizardID");
                $vs_CIS_UserID      = getXMLItemValue($xmlResponse, "userID");
                $vs_CIS_UserName    = getXMLItemValue($xmlResponse, "userName");
                $vs_CIS_UserTypeID  = getXMLItemValue($xmlResponse, "userTypeID");
                $vs_CIS_UserEmail   = getXMLItemValue($xmlResponse, "userEmail");
                $vs_CIS_CountryID   = getXMLItemValue($xmlResponse, "countryID");
                $vs_CIS_RegionID    = getXMLItemValue($xmlResponse, "regionID");
                $vs_CIS_PriceTypeID = getXMLItemValue($xmlResponse, "priceTypeID");
                $vs_CIS_SecurityID  = getXMLItemValue($xmlResponse, "security");
                $vs_documentNumber  = getXMLItemValue($xmlResponse, "documentNumber");
                $vs_documentDate    = getXMLItemValue($xmlResponse, "documentDate");
                $vs_PositionNumber  = getXMLItemValue($xmlResponse, "lineNumber");
                $vs_CIS_UserHttp    = getXMLItemValue($xmlResponse, "httpHost");
                $vs_CIS_UserWeb     = getXMLItemValue($xmlResponse, "webResource");
                
                $_m = getSessionItem('_m');
                $vs_pageLocation    = getXMLItemValue($xmlResponse,  $_m == 2 ? "pageLocation" : "httpReferer");

                $language = ''; //getXMLItemAttrValue($xmlResponse, "lanquage", "id");
                if (!$language) $language = $vs_CIS_CountryID;

                $wizard = $vs_CIS_WizardID ? $vs_CIS_WizardID : $vs_WizardID;

                if ($IsDebug)
                {
                    trace("Action:[".$vs_OperationValue."]");
                    trace("Wizard:[".$wizard."]");
                    trace("Region:[".$vs_CIS_RegionID."]");
                    trace("Language:[".$language."]");
                    trace("Document:[".$vs_documentNumber."/".$vs_documentDate."]");
                }

                if ($IsDebug) fwrite($handle, "-> finish WebService XML parsing\n");

                // Get & Check calculated total & currency
                // ---------------------------------------
                // Total
                $total = validatePriceValue(getXMLItemValue($xmlResponse, "total"));

                // Currency
                $currency = getXMLItemValue($xmlResponse, "currency");
                $currency_name = getCurrency($currency);

                // Error
                $error = getXMLItemAttrValue($xmlResponse, "error", "code");

                if ($IsDebug)
                {
                    fwrite($handle, "-> Total=".$total.", Currency=".$currency."(".$currency_name.")\n");
                    trace("Total=[".$total."], Currency=[".$currency."]");
                    trace("Error:[".$error."]");
                }

                if ($total)
                {
                    updateXMLItemTextValue($xmlResponse, "total", $total);
                    $IsSave = true;
                }

                // Restore any cleaned items before
                // --------------------------------
                if ($isRestoreItems)
                {
                    if ($vs_OrderInfo)
                    {
                        updateXMLItemTextValue($xmlResponse, "orderInfo", $vs_OrderInfo);
                        $IsSave = true;
                    }

                    if ($vs_WizardName)
                    {
                        updateXMLItemTextValue($xmlResponse, "wizardName", $vs_WizardName);
                        $IsSave = true;
                    }
                }

                if ($IsDebug) fwrite($handle, "-> WebService OK\n");
            }
        }
        catch (Exception $exceptionObject)
        {
            $lastOperationErrorCode = -23;
            sendSOAPErrorMessage("SOAP WebService ResponseLoadXML CatchError", $exceptionObject);
        }

        // -----------------------
        // Check and Detail errors
        // -----------------------

        if ($IsDebug) fwrite($handle, "-> lastOperationErrorCode=".$lastOperationErrorCode."\n");

        if (!$total && !$lastOperationErrorCode)
        {
            if ($IsDebug) fwrite($handle, "\n-> Check errors\n");

            if (isCISItemValid($error_code) && $error_code != "0" && isCISItemValid($error_description))
    	    {
                $lastOperationErrorCode = -20;
                $vs_lastErrorDescr = $error_code.": ".$error_description;
            }
            else if (!isCISItemValid($vs_CIS_UserID))
            {
                $lastOperationErrorCode = -30;
                $vs_lastErrorDescr = "Unknown or empty UserID (unauthorized)";
            }
            else if (!isCISItemValid($vs_CIS_UserHttp))
            {
                $lastOperationErrorCode = -31;
                $vs_lastErrorDescr = "Unknown or empty HostName";
            }
            else if (!isCISItemValid($vs_CIS_UserWeb))
            {
                $lastOperationErrorCode = -32;
                $vs_lastErrorDescr = "Unknown or empty WebResource description";
            }
            else if ($isCheckSecurityID && !$vs_CIS_SecurityID)
            {
                $lastOperationErrorCode = -33;
                $vs_lastErrorDescr = "Unknown or empty SecurityID";
            }
            else if (!$vs_CurrSessionID)
            {
                $lastOperationErrorCode = -38;
                $vs_lastErrorDescr = "No session";
            }
            else if ($webConstructorServiceClient || ($vs_OperationValue != "203" 
                    && $vs_CIS_UserName 
                    && isRegionValid($vs_CIS_RegionID)
                ))
            {
                $lastOperationErrorCode = -39;
                $vs_lastErrorDescr = "Total=0 (Calculation is not performed)";
            }

            if ($error == "-3")
            {
                $lastOperationErrorCode = 0;
            }
            else if (!(isCISItemValid($vs_CIS_CountryID) || isCISItemValid($vs_CIS_RegionID) || isCISItemValid($vs_CIS_PriceTypeID)))
            {
                $lastOperationErrorCode = -199;
                sendSOAPErrorMessage("System is unavailable");
            }
            else if ($lastOperationErrorCode)
            {
                sendSOAPErrorMessage("SOAP WebService Calculation Error");
            }
        }
    }

    $vs_mySessionValues = $vs_mySessionValues."After request time:".RETURNCHAR;
    $vs_mySessionValues = $vs_mySessionValues.TABCHAR."Session ID is ".$vs_CurrSessionID.RETURNCHAR;
    $vs_mySessionValues = $vs_mySessionValues.TABCHAR."Connection type is ".$vs_ConnectionType.RETURNCHAR;
    $vs_mySessionValues = $vs_mySessionValues.TABCHAR."User ID is ".$vs_CIS_UserID.RETURNCHAR;
    $vs_mySessionValues = $vs_mySessionValues.TABCHAR."User type is ".$vs_CIS_UserTypeID.RETURNCHAR;
    $vs_mySessionValues = $vs_mySessionValues.TABCHAR."Http host is ".$vs_CIS_UserHttp.RETURNCHAR;
    $vs_mySessionValues = $vs_mySessionValues.TABCHAR."Web resource is ".$vs_CIS_UserWeb.RETURNCHAR;
    $vs_mySessionValues = $vs_mySessionValues.TABCHAR."Country ID is ".$vs_CIS_CountryID.RETURNCHAR;
    $vs_mySessionValues = $vs_mySessionValues.TABCHAR."Region ID is ".$vs_CIS_RegionID.RETURNCHAR;
    $vs_mySessionValues = $vs_mySessionValues.TABCHAR."Currency ID is ".$vs_CIS_CurrencyID.RETURNCHAR;
    $vs_mySessionValues = $vs_mySessionValues.TABCHAR."Price type is ".$vs_CIS_PriceTypeID.RETURNCHAR;
    $vs_mySessionValues = $vs_mySessionValues.TABCHAR."Security ID is ".$vs_CIS_SecurityID.RETURNCHAR;
    $vs_mySessionValues = $vs_mySessionValues.TABCHAR."Test value is ".$vs_myTestValues.RETURNCHAR.RETURNCHAR;

    // --------------------
    // Security extra check
    // --------------------

    if (!$lastOperationErrorCode)
    {
        if ($isCheckSecurityID && $vs_CIS_SecurityID != $n_a)
        {
            $vs_TemporaryStr = "http";

            if ($isHTTPS)
                $vs_TemporaryStr = "https";

            $vs_TemporaryStr = $vs_TemporaryStr."://".$vs_CIS_UserWeb."/".$vs_CIS_SecurityID.".dhs";
            $as_TemporaryArr = get_headers($vs_TemporaryStr);

            if (strpos('HTTP', $as_TemporaryArr[0]) !== false &&
                strpos('200', $as_TemporaryArr[0]) !== false &&
                strpos('OK', $as_TemporaryArr[0]) !== false)
            {
                $lastOperationErrorCode = -27;
                sendSOAPErrorMessage("SOAP Security Error", null);
            }
        }
    }

    if ($IsDebug) trace("2");

    // -------------------------
    // HTTP Referer & Model Type
    // -------------------------

    $referer = getHttpReferer();

    $mtype = ''
	.(strpos($referer, '//dealer.') !== false ? 'D' : '')
	.(strpos($vs_pageLocation, 'mobile') !== false ? 'M' : '')
	.(getOS(1) == 'mobile' ? 'I' : '')
        .(substr($wizard, 0, 1) == 'I' ? 'C' : '');

    // ------------------------------
    // Send client order notification
    // ------------------------------

    if (!$lastOperationErrorCode && $IsOrderMail)
    {
        if ($vs_OperationValue == "207" && $vs_documentNumber && $vs_documentNumber != $n_a)
        {
            $subject = " Client Order";

            $body = 
                "<html><head>".
                "<style type=\"text/css\">".
                "<!-- ".
                "body, td{font-family:tahoma;font-size:11px;}".
                "p.caption{text-decoration:underline;font-weight:bold;margin:3px 0 0 0;padding:0;}".
                "div{margin-left:5px;}".
                ".number{border:1px solid #a00;padding:10px;font-size:12px;width:300px;text-align:center;margin:0;font-weight:bold;color:#a00;background-color:#ffffdd;}". //#f8e6e8 fefec0
                ".main{margin-left:2px;}".
                ".cis{font-weight:bold;width:250px;}".
                ".c1{width:200px;padding:3px 10px 0 0;vertical-align:top;}".
                ".c2{width:300px;padding:2px 10px 2px 10px;border:1px solid #ccc;font-weight:bold;margin-top:1px;}".
                " -->".
                "</style>".
                "</head><body>".
                $cr;

            $body .= 
                "<p class=\"caption\">".getKeyword($language, 'order successfully created')."</p>".$cr.
                "<div class=\"number\">".
                $vs_documentNumber." от ".$vs_documentDate.
                "</div>".$cr.
                "<div class=\"main\">".
                "User Agent: ".getUserAgent().$cr.
                "User ID: ".$vs_CIS_UserID.$cr.
                "User Name: ".$vs_CIS_UserName.$cr.
                "User Email: ".$vs_CIS_UserEmail.$cr.
                "User IP: ".$vs_UserAgentIP.$cr.
                "Host Name: ".$vs_httpHostName.$cr.
                "Wizard ID: ".$wizard.$cr.
                "Region ID:".$vs_CIS_RegionID.$cr.
                "HttpReferer: ".$referer.$cr.
                "PageLocation: ".$vs_pageLocation."</div>".$cr;

            $lines = preg_split("/\|\|/", $vs_OrderInfo);
            if (count($lines) > 1) {
                $body .= "<b>".getKeyword($language, 'order items')."</b><br>".$cr;
                foreach ($lines as $line) {
                    $x = preg_split("/::/", $line);
                    if (count($x) == 2) {
                        $v = getCISLineValue($x[1]);
                        $body .= "<div style=\"display:inline;\"><span class=\"c1\">".getCISLineValue($x[0])."</span><span class=\"c2\">".($v?$v:'&nbsp;')."</span></div>".$cr;
                    }
                }
                $body .= $cr;
            } else if ($IsMailTrace) {
                $body .= "TRACE:".count($lines)." [".$vs_OrderInfo."]".$cr;
            }

            if ($total)
                $body .= "<b>".getKeyword($language, 'total')." ".$total." ".$currency_name."</b>".$cr;

            $body .=
                $cr.getKeyword($language, 'feedback').$cr;

            $body .=
                $cr."-------------------------------------------------------------------".$cr;

            $body .= "</body></html>";

            $mail_from = getSalesFromAddress($_SERVER['SERVER_NAME']);
            $headers  = "From: $mail_from\n";
            $headers .= "Content-type: text/html; charset=$send_charset\n";
            $headers .= "Mime-Version: 1.0";

            // VAGichko@.ru, AVSergeev@.ru, RVShkulev@.ru, nvyush@yandex.ru
            $mail_to_list = array(
                getValidEmails($vs_CIS_UserEmail.getRegionMail($vs_CIS_RegionID, true)),
                getValidEmails($IsOrderMailToEmergency ? $emergency_mail_to : ''),
                getValidEmails("ichar@g2.ru")
            );

            foreach($mail_to_list as $i=>$mail_to) {
                if ($mail_to) {
                    mail($mail_to, $subject, $body, $headers);
                    if ($IsDebug)
                        trace("mail_to:[".$mail_to."]");
                }
                if ($i == 0) {
                    $subject .= " (copy)";
                    $mail_to_user = $mail_to;
                }
                if ($i == 1) {
                    $subject .= " = ".$wizard." ".$mtype;
                }
                if ($IsMailCc && $i == 0) {
                    $mail_cc = getValidEmails($vs_OrderEmail);
                    if ($mail_cc) {
                        $headers .= "Cc: $mail_cc\n";
                        if ($IsDebug)
                            trace("mail_cc:[".$mail_cc."]");
                    }
                }
            }
        }
    }

    // ----------------------------------
    // Update response with checked items
    // ----------------------------------

    if ($IsNotError && $xmlResponse)
    {
        updateXMLItemTextValue($xmlResponse, "userEmail", $mail_to_user);

        // ==================================================
            $vs_RespondContent = $domResponseXML->saveXML();
        // ==================================================

        if ($IsDebug) fwrite($handle, "-> save WebService XML to string\n");

        if ($vs_RespondContent === false)
        {
            $lastOperationErrorCode = -25;
            sendSOAPErrorMessage("SOAP WebService ResponseSaveXML Error", null);
        }
    }

    if ($IsDebug) trace("3");

    // ----------------
    // Write statistics
    // ----------------

    $vn_ProcessingTime = time() - $vn_StartTime;

    if ($IsDebug) trace("4");

    // ----------
    // Log errors
    // ----------

    if ($lastOperationErrorCode)
    {
        // Reset response(!)
        $xmlResponse = $domRequestXML->documentElement;

        updateXMLItemTextValue($xmlResponse, "errorCode", " ".$lastOperationErrorCode." ");
        updateXMLItemTextValue($xmlResponse, "errorDescription", " ".$vs_lastErrorDescr."[".$lastOperationErrorCode."] ");

        // ==================================================
            $vs_RespondContent = $domRequestXML->saveXML();
        // ==================================================
    }

    if ($IsLog)	logger(sprintf("%12s %3s %-4s", ($total ? $total : "0"), $currency_name, $mtype));

    if ($IsDebug)
    {
        trace("ErrorCode:[".$lastOperationErrorCode."]");
        trace("finish: check_respond.php");
    }

    if ($IsDebug)
    {
        fwrite($handle, "\n"."Final RespondContent:\n".$vs_RespondContent."\n");
        fwrite($handle, "\n"."OrderInfo:\n".$vs_OrderInfo."\n");

        $mycontent = "\nIsNotError=".($IsNotError ? 'true' : 'false').", lastOperationErrorCode=".$lastOperationErrorCode."\n\n"
            ."WebServiceURL:\n".$service."\n"
            ."WebConstructorServiceURL:\n".$vs_WebConstructorServiceURL."\n"
            ."RegionID:[".$vs_CIS_RegionID."]\n"
            ;
        fwrite($handle, $mycontent);
        fclose($handle);
    }
?>

