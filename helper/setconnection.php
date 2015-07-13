<?php
    // Author:      I.Kharlamov
    // Created:     07.04.2010
    // version:     1.0.0
    // Created:    25.09.2013
    // Modifyed by: I.Kharlamov

    // Log level
    error_reporting(E_ALL);

    require_once($include."communication/constants.php");
    require_once($include."communication/lib.php");

    $service = "";

    // Script body
    if (!$lastOperationErrorCode)
    {
        if ($IsDebug) trace("start: setconnection.php");

        $vb_doConnect = true;

        switch($vs_OperationValue)
        {
            case "203":    // initialization
                break;

            case "204":    // calculate
                break;

            case "205":    // save
                break;

            case "206":    // cancel
                break;

            case "207":    // order
                break;

            default:
                $lastOperationErrorCode = -1 * ($vs_OperationValue + 0);
                sendSOAPErrorMessage("SOAP Invalid Operation Code Error", null);
        }

        if (!$lastOperationErrorCode)
        {
            if ($vs_WebConstructorServiceURL && $vs_HelperVersion == "3")
            {
                if ($IsDebug) trace("connection: webConstructorServiceClient");

                $service = $vs_WebConstructorServiceURL;
                $isCheckSecurityID = false;

                if ($IsDebug) trace("service:[".$service."]");

                try
                {
                    // =========================================================
                        $webConstructorServiceClient = new SoapClient($service,
                            array(
                            )
                        );
                    // =========================================================

                    $webServiceClient = $webConstructorServiceClient;
                }
                catch (SoapFault $exceptionObject)
                {
                    $lastOperationErrorCode = -13;
                    sendSOAPErrorMessage("SOAP WebConstructorService SetConnection Error", $exceptionObject);
                }

                if ($IsDebug && !$lastOperationErrorCode)
                    trace("OK");
            }
            else if (count($services) > 0)
            {
                if ($IsDebug) trace("connection: webServiceClient");

                $isCheckSecurityID = false;

                foreach ($services as $service)
                {
                    $lastOperationErrorCode = 0;

                    if ($IsDebug) trace("service:[".$service."]");

                    try
                    {
                    // =======================================================
                        $webServiceClient = new SoapClient($service,
                            array(
                            )
                        );
                        break;
                    // =======================================================
                    }
                    catch (SoapFault $exceptionObject)
                    {
                        $lastOperationErrorCode = -12;
                        sendSOAPErrorMessage("SOAP WebService SetConnection Error", $exceptionObject);
                    }
                }
                
                if ($IsDebug && !$lastOperationErrorCode)
                    trace("OK");
            }
            else
            {
                $lastOperationErrorCode = -14;
                sendSOAPErrorMessage("SOAP WEB Service URL Error", null);
            }
        }

        if ($IsDebug) trace("finish: setconnection.php");
    }
?>

