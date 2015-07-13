<?php
    // Author:      I.Kharlamov
    // Created:     27.05.2013
    // version:     1.0.0
    // Created:    10.09.2013
    // Modifyed by: I.Kharlamov

    require_once($include."communication/lib.php");

    setSession();
    
    $vs_ConnectionType = RetailPrice;

    if (!$vs_CurrSessionID)
    {
        $vs_Tmp_var = session_cache_limiter("must-revalidate");
        $vs_CurrSessionID = session_id();
    }

    printSessionValues("Session start time:");
?>


