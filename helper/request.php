<?php
    // Author:      I.Kharlamov
    // Created:     07.04.2010
    // version:     1.0.0
    // Created:    06.09.2013
    // Modifyed by: I.Kharlamov

    // Log level
    error_reporting(E_ALL);

    require_once($include."communication/constants.php");
    require_once($include."communication/lib.php");

    if ($IsDebug) trace("start: request.php");

    // Declaration and initialization
    $request = "";

    // Script body
    if (!$lastOperationErrorCode)
    {
        if ($webConstructorServiceClient)
        {
            if ($IsDebug) trace("execute: webConstructorServiceClient");

            $request = cleanTags($vs_RequestContent, array("wizardName", "orderInfo"));
            $isRestoreItems = false;

            try
            {
                // ==========================================================
                    $soapRetValue = $webConstructorServiceClient->Execute82(
                        array(
                        ));
                // ==========================================================

                foreach ($soapRetValue as $key => $val)
                {
                    if (gettype($val) == "string" && $val != "")
                        $vs_ConstructorRespondContent = $vs_ConstructorRespondContent.$val;
                }

                $vs_RespondContent = $vs_ConstructorRespondContent;
            }
            catch (SoapFault $exceptionObject)
            {
                $lastOperationErrorCode = -132;
                sendSOAPErrorMessage("SOAP WebConstructorService Execute Error", $exceptionObject);
            }

            if ($IsDebug) trace($lastOperationErrorCode ? "Error" : "OK");
        }
        else if ($webServiceClient)
        {
            if ($IsDebug) trace("execute: webServiceClient");

            $request = $vs_RequestContent;
        
            try
            {
                // ==========================================================
                if ($sid == "ws05")
                    $soapRetValue = $webServiceClient->Execute82(
                        array(
                        ));
                // ==========================================================

                foreach ($soapRetValue as $key => $val)
                {
                    if (gettype($val) == "string" && $val != "")
                        $vs_RespondContent = $vs_RespondContent.$val;
                }
            }
            catch (SoapFault $exceptionObject)
            {
                $lastOperationErrorCode = -131;
                sendSOAPErrorMessage("SOAP WebService Execute Error", $exceptionObject);
            }

            if ($IsDebug) trace($lastOperationErrorCode ? "Error" : "OK");
        }
    }

    if ($IsDebug)
    {
        $filename = $debug."1C-request.txt";
        $mycontent = "REQUEST:\n".$request;
        $handle = fopen($filename, 'w');
        fwrite($handle, $mycontent);
        fclose($handle);
    }

    if ($IsDebug) trace("finish: request.php");
?>

