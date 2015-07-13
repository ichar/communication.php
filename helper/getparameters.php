<?php
    // Author:      I.Kharlamov
    // Created:     31.03.2010
    // version:     1.0.0
    // Created:    24.10.2013
    // Modifyed by: I.Kharlamov

    // Log level
    error_reporting(E_ALL);

    require_once($include."communication/constants.php");
    require_once($include."communication/lib.php");

    if ($IsDebug) {
        trace("------------");
        trace("CIS_RegionID:[".$vs_CIS_RegionID."]");
    }

    // Declaration and initialization
    $item = new DOMNode();
    $value = new DOMText("");
    $new = new DOMText("");

    $vb_isNotError = true;

    if ($IsDebug) trace("start: getparameters.php");

    $mycontent = "";

    // Script body
    if (isset($_POST["queryDocument"]))
    {
        try
        {
            $vs_RequestContent = mb_substr($_POST["queryDocument"], 0, xmlFileSizeLimit);

            // ================================================================
                $vb_isNotError = $domRequestXML->loadXML($vs_RequestContent);
            // ================================================================

            if ($vb_isNotError === false)
            {
                $lastOperationErrorCode = -9;
                sendSOAPErrorMessage("SOAP RequestLoadXML Error", null);
            }
            else
            {
                // Get root element
                // ----------------
                $xmlRequest = $domRequestXML->documentElement;

                $mycontent .= "REQUEST:\n".$vs_RequestContent."\n\n";

                if ($IsDebug) trace("1");

                // Check current session
                // ---------------------
                $session_id = getXMLItemValue($xmlRequest, "sessionID");

                if ($session_id) setSession($session_id);
                $session_is_valid = $vs_CurrSessionID == $session_id || $vs_CurrSessionID ? true : false;

                if ($IsDebug) trace("session:".($session_is_valid ? ' OK' : 'is different!'));

                // Wizard ID
                // ---------
                $vs_WizardID = getXMLItemValue($xmlRequest, "wizardID");
                $vs_WizardName = getXMLItemValue($xmlRequest, "wizardName");

                // Helper Version
                // --------------
                $vs_HelperVersion = getXMLItemAttrValueById($xmlRequest, "task", "CALCHELPER", "version");
                $mycontent .= "HelperVersion=".$vs_HelperVersion."\n";

                if ($IsDebug) trace("HelperVersion:[".$vs_HelperVersion."]");

                // Action
                // ------
                $vs_OperationValue = getXMLItemValue($xmlRequest, "action");

                // Order email/info
                // ----------------
                $vs_OrderEmail = getXMLItemValue($xmlRequest, "orderEmail");
                $vs_OrderInfo = getXMLItemValue($xmlRequest, "orderInfo");

                // User Host
                // ---------
                $vs_CIS_UserHttp = getXMLItemValue($xmlRequest, "httpHost");
                $vs_CIS_UserWeb = getXMLItemValue($xmlRequest, "webResource");

                // User Agent
                // ----------
                $vs_userAgent = getXMLItemValue($xmlRequest, "userAgent");
                if (isCISItemValid($vs_userAgent)) $_SESSION["UserAgent"] = $vs_userAgent;

                // HTTP Referer
                // ------------
                $vs_httpReferer = getXMLItemValue($xmlRequest, "httpReferer");
                if (isCISItemValid($vs_httpReferer) && !(isset($_SESSION["HttpReferer"]) && $_SESSION["HttpReferer"]))
                    $_SESSION["HttpReferer"] = $vs_httpReferer;

                // Get another request options
                // ---------------------------
                $vs_pageLocation = getXMLItemValue($xmlRequest, "pageLocation");

                // Work Without Service
                // --------------------
                $withoutDB = getXMLItemValue($xmlRequest, "withoutDB");
                if ($withoutDB === "false" || $withoutDB === "0")
                    $isWithoutService = false;
                $mycontent .= "isWithoutService=".($isWithoutService ? "true" : "false")."\n";

                if ($IsDebug) trace("withoutDB:[".$withoutDB."]");

                // ----------------
                // Validate Request
                // ----------------

                if ($withoutDB === "") 
                {
                    $lastOperationErrorCode = -101;
                    sendSOAPErrorMessage("SOAP InvalidRequestContent Error");
                }
                else if (!$IsDefault && !($session_is_valid
                    && isCISItemValid($vs_WizardID)
                    && isCISItemValid($vs_OperationValue)
                    && isCISItemValid($vs_CIS_UserID)
                    && isCISItemValid($vs_CIS_UserHttp)
                    && isCISItemValid($vs_CIS_UserWeb)
                    ))
                {
                    // Request is invalid
                    // ------------------
                    $lastOperationErrorCode = -100;
                    if (!$session_is_valid)
                        $vs_lastErrorDescr = "No session";
                    else
                        $vs_lastErrorDescr = "Invalid requested item value (session is expired)";
                    sendSOAPErrorMessage("SOAP ValidationRequest Error");
                }
                else if (!($session_is_valid
                    //&& isCISItemValid($vs_CIS_CountryID)
                    && isCISItemValid($vs_CIS_RegionID)
                    //&& isCISItemValid($vs_CIS_PriceTypeID)
                    ))
                {
                    // Incomplete authorized data
                    // --------------------------
                    $lastOperationErrorCode = -99;
                    sendSOAPErrorMessage("System is unavailable");
                }
                else
                {
                    if ($IsDebug) trace("2");

                    // Set all global system values (userID, regionID and etc.)
                    // --------------------------------------------------------
                    // SessionID
                    if ($session_is_valid) setXMLItemValue($xmlRequest, "sessionID", $vs_CurrSessionID);
                    // UserID
                    if (isCISItemValid($vs_CIS_UserID)) setXMLItemValue($xmlRequest, "userID", $vs_CIS_UserID);
                    // UserTypeID
                    if (isCISItemValid($vs_CIS_UserTypeID)) setXMLItemValue($xmlRequest, "userTypeID", $vs_CIS_UserTypeID);
                    // CountryID
                    if (isCISItemValid($vs_CIS_CountryID)) setXMLItemValue($xmlRequest, "countryID", $vs_CIS_CountryID);
                    // RegionID
                    if (isCISItemValid($vs_CIS_RegionID)) setXMLItemValue($xmlRequest, "regionID", $vs_CIS_RegionID);
                    // CurrencyID
                    if (isCISItemValid($vs_CIS_CurrencyID)) setXMLItemValue($xmlRequest, "currency", $vs_CIS_CurrencyID);
                    // PriceTypeID
                    if (isCISItemValid($vs_CIS_PriceTypeID)) setXMLItemValue($xmlRequest, "priceTypeID", $vs_CIS_PriceTypeID);
                    // SecurityID
                    if (isCISItemValid($vs_CIS_SecurityID)) setXMLItemValue($xmlRequest, "security", $vs_CIS_SecurityID);
                    // HttpHost
                    //if (isCISItemValid($vs_CIS_UserHttp)) setXMLItemValue($xmlRequest, "httpHost", $vs_CIS_UserHttp);
                    // Web Resource
                    //if (isCISItemValid($vs_CIS_UserWeb)) setXMLItemValue($xmlRequest, "webResource", $vs_CIS_UserWeb);

                    if ($IsDebug) trace("httpHost:[".$vs_CIS_UserWeb."]");

                    updateXMLItemTextValue($xmlRequest, "errorCode", "0");
                    updateXMLItemTextValue($xmlRequest, "errorDescription", $n_a);

                    // ==================================================
                        $vs_RequestContent = $domRequestXML->saveXML();
                    // ==================================================

                    if ($vs_RequestContent === false)
                    {
                        $lastOperationErrorCode = -10;
                        sendSOAPErrorMessage("SOAP RequestSaveXML Error");
                    }

                    if ($IsDebug) trace("3");
                }
            }
        }
        catch(Exception $exceptionObject)
        {
            $lastOperationErrorCode = -22;
            sendSOAPErrorMessage("SOAP RequestGetParameters Error", $exceptionObject);
        }
    }
    else
    {
        $lastOperationErrorCode = -21;
        sendSOAPErrorMessage("SOAP POST Error");
    }

    printSessionValues("After get parameters time:");

    $mycontent .=
        "\nSession:\n".$vs_mySessionValues;

    if ($IsDebug) trace("finish: getparameters.php");

    if ($IsDebug) {
        $filename = $debug."1C-parameters.txt";
        $handle = fopen($filename, 'w');
        fwrite($handle, $mycontent);
        fclose($handle);
    }
?>

