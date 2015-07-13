<?php
    // Author:      I.Kharlamov
    // Created:     08.04.2010
    // version:     1.0.0
    // Created:    10.09.2013
    // Modifyed by: I.Kharlamov

    // Log level
    error_reporting(E_ALL);

    // Script body
    if ($isWithoutService === false)
    {
        echo($vs_RespondContent);
    }
    else
    {
        echo($vs_RequestContent);
    }
?>

