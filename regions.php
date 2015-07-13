<?php
    // Author:      I.Kharlamov
    // Created:     06.06.2013
    // version:     1.0.0
    // Created:    19.11.2014
    // Modifyed by: I.Kharlamov

    // Public 1C web-services
    // ----------------------
    $WEB_SERVICES = array(
        '000000000' => "http://localhost/ws/ws01?wsdl",
    );

    $TEST_WEB_SERVICES = array(
        '000000000' => "http://localhost/ws/ws01?wsdl"
    );

    function getWebService($id) {
        global $TEST_WEB_SERVICES, $WEB_SERVICES;
        return ($id && isset($services[$id]) && $services[$id] ? $services[$id] : $services['000000000']);
    }

    // Order mail notifications
    // ------------------------
    $region_mail = array(
        '000000008' => array("", '', true)
    );

    $sales_from_address = array(
    );

    $keywords = array(
    );
?>
