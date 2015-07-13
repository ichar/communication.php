<?php
    // Author:      I.Kharlamov
    // Created:     10.09.2013
    // version:     1.0.0
    // Created:    24.10.2013
    // Modifyed by: I.Kharlamov

    // Errors Code & Descriptions
    // --------------------------
    $ERRORS_DESCRIPTION = array(
          '-9' => "SOAP RequestLoadXML Error (getparameters.php) = Can't load XML from incoming request",
         '-10' => "SOAP RequestSaveXML Error (getparameters.php) = Can't update requested XML",
         '-12' => "SOAP WebService SetConnection Error (setconnection.php) = No SOAP connection (Exception)",
         '-13' => "SOAP WebConstructorService SetConnection Error (setconnection.php) = No SOAP connection (Exception)",
         '-21' => "SOAP POST Error (getparameters.php) = \$_POST[\"queryDocument\"] is not set",
         '-22' => "SOAP RequestGetParameters Error (getparameters.php) = Can't read parameters from request (Exception)",
         '-23' => "SOAP WebService ResponseLoadXML CatchError (check_respond.php) = Can't read response attributes (Exception)",
         '-24' => "SOAP WebService ResponseLoadXML Error (check_respond.php) = Can't load XML from response",
         '-25' => "SOAP WebService ResponseSaveXML Error (check_respond.php) = Can't update response XML",
         '-27' => "SOAP Security Error (check_respond.php) = Not valid security header (*.dhs)",
         '-30' => "Unknown or empty UserID [userID] (check_respond.php) = Field is empty",
         '-31' => "Unknown or empty HostName [httpHost] (check_respond.php) = Field is empty",
         '-32' => "Unknown or empty WebResource description [webResource] (check_respond.php) = Field is empty",
         '-33' => "Unknown or empty SecurityID [security] (check_respond.php) = Field is empty",
         '-39' => "SOAP WebService Calculation Error (Total=0) (check_respond.php) = No total value (undefined reason)",
         '-99' => "System is unavailable (getparameters.php) = Incomplete authorized data (undefined reason)",
        '-100' => "SOAP ValidationRequest Error (getparameters.php) = Invalid requested item value (session is expired)",
        '-130' => "SOAP WEB Service URL Error (setconnection.php) = Empty web-service URL or helper version",
        '-131' => "SOAP WebService Execute Error (request.php) = REQUEST WEB-Service error (Exception)",
        '-132' => "SOAP WebConstructorService Execute Error (request.php) = REQUEST WEB-Service error (Exception)",
        '-199' => "System is unavailable (check_respond.php) = No valid response values (undefined reason)"
    );
?>
