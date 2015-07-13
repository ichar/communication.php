<?php
    // Author:      I.Kharlamov
    // Created:     09.12.10
    // version:     1.0.0
    // Created:    24.10.2013
    // Modifyed by: I.Kharlamov

    require_once($include."communication/lib.php");

    if ($IsDebug) trace("session end");

    setSessionItem("sessionID", $vs_CurrSessionID);
    setSessionItem("httpHost", $vs_CIS_UserHttp);
    setSessionItem("webResource", $vs_CIS_UserWeb);
    setSessionItem("countryID", $vs_CIS_CountryID);
    setSessionItem("regionID", $vs_CIS_RegionID);
    setSessionItem("currencyID", $vs_CIS_CurrencyID);
    setSessionItem("priceTypeID", $vs_CIS_PriceTypeID);
    setSessionItem("securityID", $vs_CIS_SecurityID);
    setSessionItem("ConnectionType", $vs_ConnectionType);

    if ($service && $vs_WebServiceURL != $service && $vs_WebConstructorServiceURL != $service)
        setSessionItem("WebServiceURL", $service);

    if ($IsDebug) trace(".");
?>

