<?php
    // Author:      I.Kharlamov
    // Created:     31.03.2010
    // version:     1.0.0
    // Created:    01.10.2013

    // Log level
    error_reporting(E_ALL);

    $IsDebug      = 0;
    $IsErrorMail  = 0;
    $IsOrderMail  = 0;
    $IsMailCc     = 0;
    $IsMailTrace  = 0;
    $IsStatistics = 0;
    $IsLog        = 0;

    $sid = "ws05";

    $debug = ""; // "C:\\";
    $include = "";
    $error_mail_to = "";
    $emergency_mail_to = "" ;
    $service_error_mail_to = "";
    $constructor_error_mail_to = "";
    $sales_from_default = "";

    $IsOrderMailToEmergency = 1;

    require_once($include."communication/lib.php");

    $EOL = "\n";
    $n_a = "n/a";

    $vs_Tmp_var = "";
    $vs_httpHostName = "";

    mb_internal_encoding("UTF-8");
    mb_regex_encoding("UTF-8");

    $webServiceClient = null;
    $webConstructorServiceClient = null;
    $exeptionObject = null;
    $statisticsDB = null;

    $isRestoreItems = false;
    $isCheckSecurityID = isset($_SESSION) ? true : false;
    $isHTTPS = (isset($_SERVER['HTTPS']) && mb_strlen($_SERVER['HTTPS']) > 0 ? true : false);

    $domRequestXML = new DOMDocument();
    $domResponseXML = new DOMDocument();

    $domRequestXML->preserveWhiteSpace = false;
    $domRequestXML->formatOutput = true;

    $lastOperationErrorCode = 0;

    $vs_HelperVersion = "";
    $vs_OperationValue = "";
    $vs_RequestContent = "";
    $vs_RespondContent = "";
    $vs_ConstructorRespondContent = "";
    $vs_lastErrorDescr = "";
    $vs_mySessionValues = "";
    $vs_myTestValues = "";
    $vs_OrderEmail = "";
    $vs_OrderInfo = "";

    $vs_CurrSessionID = "";
    $vs_PrevSessionID = "";

    $vs_documentNumber = "";
    $vs_documentDate = "";
    $vs_PositionNumber = "";
    $vs_WizardID = "";
    $vs_WizardName = "";
    $vs_RequestedRef = "";
    $vs_RequestedPage = "";
    $vs_ConnectionType = "";

    $vs_CIS_UserID = "";
    $vs_CIS_UserTypeID = "";
    $vs_CIS_UserLogin = "";
    $vs_CIS_UserPswd = "";
    $vs_CIS_UserHttp = "";
    $vs_CIS_UserWeb = "";
    $vs_CIS_CountryID = "";
    $vs_CIS_RegionID = "";
    $vs_CIS_CurrencyID = "";
    $vs_CIS_PriceTypeID = "";
    $vs_CIS_SecurityID = "";

    $vn_StartTime = time();
    $vn_ProcessingTime = time() - $vn_StartTime;

    // CONSTANS
    define("TABCHAR","\t");
    define("RETURNCHAR","\r");

    define("xmlFileSizeLimit", 307200);          // MaxDataSize In bytes

    define("scriptStartDate", date("Y-m-d"));    // Date
    define("scriptStartTime", date("H:i:s"));    // Time

    if (array_key_exists("HTTP_HOST", $_SERVER) && mb_strlen($vs_httpHostName) <= 0 )
    {
        $vs_httpHostName = trim($_SERVER["HTTP_HOST"]);
    }

    // =================
    //  Global Services
    // =================

    // Constructor System service URI
    // ------------------------------
    $vs_WebConstructorServiceURL = "http://localhost/Service2.asmx?wsdl";

    // DB1C System service URI
    // -----------------------
    $vs_WebServiceURL = "http://localhost/ws/".$sid."?wsdl";
    
    switch ($vs_httpHostName)
    {
        default:
            $vs_WebServiceURL = "http://localhost/ws/".$sid."?wsdl";
    }

    $services = array();
    array_push($services, $vs_WebServiceURL);

    $service = "";

    // Languge definition
    // ------------------
    define("LANGUAGE_RUS", "RU");
    define("LANGUAGE_ENG", "EN");
    define("LANGUAGE_FR",  "FR");
    define("LANGUAGE_DE",  "DE");
    define("LANGUAGE_CN",  "CN");

    // Miscellaneous information
    // -------------------------
    $vs_UserAgentIP = "";
    $vs_httpHostName = "";
    $vs_httpRequestURL = "";
    $vs_UserAgentInfo = "";
    $vs_httpRefererInfo = "";

    if (array_key_exists("HTTP_X_FORWARDED_FOR", $_SERVER))
    {
        $vs_UserAgentIP = trim($_SERVER["HTTP_X_FORWARDED_FOR"]);
    }

    if (array_key_exists("HTTP_CLIENT_IP", $_SERVER) && mb_strlen($vs_UserAgentIP)<=0)
    {
        $vs_UserAgentIP = $_SERVER["HTTP_CLIENT_IP"];
    }

    if (array_key_exists("REMOTE_ADDR", $_SERVER) && mb_strlen($vs_UserAgentIP)<=0)
    {
        $vs_UserAgentIP = trim($_SERVER["REMOTE_ADDR"]);
    }

    if (mb_strlen($vs_UserAgentIP)<=0)
    {
        $vs_UserAgentIP = NotAvailable;
    }

    if (array_key_exists("HTTP_HOST", $_SERVER))
    {
        $vs_httpHostName = trim($_SERVER["HTTP_HOST"]);
    }

    if (array_key_exists("REQUEST_URI", $_SERVER))
    {
        $vs_httpRequestURL = preg_replace("/^A-Za-z0-9_\//", "", trim($_SERVER["REQUEST_URI"]));
    }

    if (array_key_exists("HTTP_USER_AGENT", $_SERVER))
    {
        $vs_UserAgentInfo = $_SERVER["HTTP_USER_AGENT"];
    }

    if (array_key_exists("HTTP_REFERER", $_SERVER))
    {
        $vs_httpRefererInfo = trim($_SERVER["HTTP_REFERER"]);
    }
?>

