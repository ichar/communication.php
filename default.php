<?php
    // Author:      I.Kharlamov
    // Created:     27.05.2013
    // version:     1.0.0
    // Created:    04.09.2014
    // Modifyed by: I.Kharlamov

    // Log level
    error_reporting(E_ALL);

    $model2 = array(
        'browser' => array('opera', 'safari', 'version', 'chrome', 'msie7', 'msie8'), //'firefox'
        'os' => array('mobile', 'old-windows', 'unknown'), //'xp'
        'flash-disable' => array('msie7', 'msie8')
    );

    $IsDefault = 0;

    $vs_CurrSessionID   = "";
    $vs_PrevSessionID   = "";
    $vs_CIS_UserID      = "";
    $vs_CIS_UserTypeID  = "";
    $vs_CIS_UserEmail   = "";
    $vs_CIS_UserLogin   = "";
    $vs_CIS_UserPswd    = "";
    $vs_CIS_UserHttp    = "";
    $vs_CIS_UserWeb     = "";
    $vs_CIS_CountryID   = "";
    $vs_CIS_RegionID    = "";
    $vs_CIS_CurrencyID  = "";
    $vs_CIS_PriceTypeID = "";
    $vs_CIS_SecurityID  = "";
    $vs_CIS_WizardID    = "";
?>
