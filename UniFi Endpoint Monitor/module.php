<?php

declare(strict_types=1);

if (!defined('vtBoolean')) {
    define('vtBoolean', 0);
    define('vtInteger', 1);
    define('vtFloat', 2);
    define('vtString', 3);
    define('vtArray', 8);
    define('vtObject', 9);
}
class UnifiEndpointMonitor extends IPSModule
{
    public function Create()
    {
        //Never delete this line!
        parent::Create();

        $this->RegisterPropertyInteger("ControllerType", 0);
        $this->RegisterPropertyString("ServerAddress", "192.168.1.1");
        $this->RegisterPropertyInteger("ServerPort", "443");
        $this->RegisterPropertyString("Site", "default");
        $this->RegisterPropertyString("UserName", "");
        $this->RegisterPropertyString("Password", "");
        $this->RegisterPropertyInteger("Timer", "0");
            
        $this->RegisterPropertyString("DeviceMac", "");
        $this->RegisterPropertyInteger("ConnectionType", 0);

        $this->RegisterPropertyBoolean("DataPointNetwork", 0);
        $this->RegisterPropertyBoolean("DataPointConnection", 0);
        $this->RegisterPropertyBoolean("DataPointTransfer", 0);

        $this->RegisterTimer("Endpoint Monitor", 0, "EM_EndpointMonitor(\$_IPS['TARGET']);");
    }

    public function Destroy()
    {
        //Never delete this line!
        parent::Destroy();
    }

    public function ApplyChanges()
    {
        //Never delete this line!
        parent::ApplyChanges();
                        
        //Network Data
        $vpos = 100;
        $this->MaintainVariable("IPAddress", $this->Translate("IP Address"), vtString, "", $vpos++, $this->ReadPropertyBoolean("DataPointNetwork") == 1);
        $this->MaintainVariable("Hostname", $this->Translate("Hostname"), vtString, "", $vpos++, $this->ReadPropertyBoolean("DataPointNetwork") == 1);
        //$this->MaintainVariable("Name", $this->Translate("Name"), vtString, "", $vpos++, $this->ReadPropertyBoolean("DataPointNetwork") == 1);

        //Connection Data General
        $vpos = 200;
        $this->MaintainVariable("Satisfaction", $this->Translate("Satisfaction"), vtInteger, "", $vpos++, $this->ReadPropertyBoolean("DataPointConnection") == 1);
        $this->MaintainVariable("LastSeen", $this->Translate("Last Seen"), vtString, "", $vpos++, $this->ReadPropertyBoolean("DataPointConnection") == 1);
        $this->MaintainVariable("Uptime", $this->Translate("Uptime in hours"), vtInteger, "", $vpos++, $this->ReadPropertyBoolean("DataPointConnection") == 1);
        //Connection Data Wired
        $vpos = 230;
        //$this->MaintainVariable("SwitchPort", $this->Translate("Switch Port"), vtString, "", $vpos++, $this->ReadPropertyBoolean("DataPointConnection") == 1 AND $this->ReadPropertyInteger("ConnectionType") == 1);
        //$this->MaintainVariable("SwitchMAC", $this->Translate("Switch MAC"), vtString, "", $vpos++, $this->ReadPropertyBoolean("DataPointConnection") == 1 AND $this->ReadPropertyInteger("ConnectionType") == 1);
        //Connection Data Wireless
        $vpos = 250;
        $this->MaintainVariable("Accesspoint", $this->Translate("Accesspoint"), vtString, "", $vpos++, $this->ReadPropertyBoolean("DataPointConnection") == 1 and $this->ReadPropertyInteger("ConnectionType") == 0);
        $this->MaintainVariable("Channel", $this->Translate("Channel"), vtInteger, "", $vpos++, $this->ReadPropertyBoolean("DataPointConnection") == 1 and $this->ReadPropertyInteger("ConnectionType") == 0);
        $this->MaintainVariable("Radio", $this->Translate("Radio"), vtString, "", $vpos++, $this->ReadPropertyBoolean("DataPointConnection") == 1 and $this->ReadPropertyInteger("ConnectionType") == 0);
        $this->MaintainVariable("ESSID", $this->Translate("ESS ID"), vtString, "", $vpos++, $this->ReadPropertyBoolean("DataPointConnection") == 1 and $this->ReadPropertyInteger("ConnectionType") == 0);
        $this->MaintainVariable("RSSI", $this->Translate("RSSI"), vtInteger, "", $vpos++, $this->ReadPropertyBoolean("DataPointConnection") == 1 and $this->ReadPropertyInteger("ConnectionType") == 0);
        $this->MaintainVariable("Noise", $this->Translate("Noise"), vtInteger, "", $vpos++, $this->ReadPropertyBoolean("DataPointConnection") == 1 and $this->ReadPropertyInteger("ConnectionType") == 0);
        $this->MaintainVariable("SignalStrength", $this->Translate("Signal Strength"), vtInteger, "", $vpos++, $this->ReadPropertyBoolean("DataPointConnection") == 1 and $this->ReadPropertyInteger("ConnectionType") == 0);

        //Transfer Data
        $vpos = 300;
        $this->MaintainVariable("TXBytes", $this->Translate("TX Megabytes"), vtInteger, "", $vpos++, $this->ReadPropertyBoolean("DataPointTransfer") == 1 and $this->ReadPropertyInteger("ConnectionType") == 0);
        $this->MaintainVariable("RXBytes", $this->Translate("RX Megabytes"), vtInteger, "", $vpos++, $this->ReadPropertyBoolean("DataPointTransfer") == 1 and $this->ReadPropertyInteger("ConnectionType") == 0);
        $this->MaintainVariable("TXPackets", $this->Translate("TX Packets"), vtInteger, "", $vpos++, $this->ReadPropertyBoolean("DataPointTransfer") == 1 and $this->ReadPropertyInteger("ConnectionType") == 0);
        $this->MaintainVariable("RXPackets", $this->Translate("RX Packets"), vtInteger, "", $vpos++, $this->ReadPropertyBoolean("DataPointTransfer") == 1 and $this->ReadPropertyInteger("ConnectionType") == 0);


        $TimerMS = $this->ReadPropertyInteger("Timer") * 1000;
        $this->SetTimerInterval("Endpoint Monitor", $TimerMS);

        if (0 == $TimerMS) {
            // instance inactive
            $this->SetStatus(104);
        } else {
            // instance active
            $this->SetStatus(102);
        }
    }


    public function AuthenticateAndGetData(string $UnifiAPI = "")
    {
        $ControllerType = $this->ReadPropertyInteger("ControllerType");
        $ServerAddress = $this->ReadPropertyString("ServerAddress");
        $ServerPort = $this->ReadPropertyInteger("ServerPort");
        $Username = $this->ReadPropertyString("UserName");
        $Password = $this->ReadPropertyString("Password");
        $Site = $this->ReadPropertyString("Site");

        $DeviceMac = strtolower($this->ReadPropertyString("DeviceMac"));

        //Change the Unifi API to be called here
        if ("" == $UnifiAPI) {
            $Site = $this->ReadPropertyString("Site");
            $UnifiAPI = "api/s/".$Site."/stat/sta";
        }

        //Generic Section providing for Authenthication against a DreamMachine or Classic CloudKey
        $ch = curl_init();

        if (!isset($ControllerType) || $ControllerType == 0) {
            $SuffixURL = "/api/auth/login";
            curl_setopt($ch, CURLOPT_POSTFIELDS, "username=".$Username."&password=".$Password);
        } elseif ($ControllerType == 1) {
            $SuffixURL = "/api/login";
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['username' => $Username, 'password' => $Password]));
        }
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_URL, "https://".$ServerAddress.":".$ServerPort.$SuffixURL);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $data = curl_exec($ch);

        if (false === $data) {
            $this->SendDebug($this->Translate("Authentication"), $this->Translate('Error: Not reachable / No response!'), 0);

            // IP or Port not reachable / no response
            $this->SetStatus(200);

            return false;
        }

        $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $body        = trim(substr($data, $header_size));
        $code        = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        $this->SendDebug($this->Translate("Authentication"), $this->Translate('Return-Code Provided is: ').$code, 0);
        //$this->SendDebug($this->Translate("Debug"), $data,0);

        preg_match_all('|(?i)Set-Cookie: (.*);|U', substr($data, 0, $header_size), $results);
        if (isset($results[1])) {
            $Cookie = implode(';', $results[1]);
            if (!empty($body)) {
                if (200 == $code) {
                    $this->SendDebug($this->Translate("Authentication"), $this->Translate('Login Successful'), 0);
                    $this->SendDebug($this->Translate("Authentication"), $this->Translate('Cookie Provided is: ').$Cookie, 0);
                } elseif (400 == $code) {
                    $this->SendDebug($this->Translate("Authentication"), $this->Translate('400 Bad Request - The server cannot or will not process the request due to an apparent client error.'), 0);
                    echo $this->Translate('400 Bad Request - The server cannot or will not process the request due to an apparent client error.');
                    return false;
                } elseif (401 == $code || 403 == $code) {
                    $this->SendDebug($this->Translate("Authentication"), $this->Translate('401 Unauthorized / 403 Forbidden - The request contained valid data and was understood by the server, but the server is refusing action. Missing user permission?'), 0);
                    echo $this->Translate('401 Unauthorized / 403 Forbidden - The request contained valid data and was understood by the server, but the server is refusing action. Missing user permission?');
                    return false;
                }
            }
        }

        // Section below will collect and store it into a buffer
            
        if (isset($Cookie)) {
            $ch = curl_init();
            if (!isset($ControllerType) || $ControllerType == 0) {
                $MiddlePartURL = "/proxy/network/";
            } elseif ($ControllerType == 1) {
                $MiddlePartURL = "/";
            }
            curl_setopt($ch, CURLOPT_URL, "https://".$ServerAddress.":".$ServerPort.$MiddlePartURL.$UnifiAPI."/".$DeviceMac);
            curl_setopt($ch, CURLOPT_HTTPGET, true);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array("cookie: ".$Cookie));
            curl_setopt($ch, CURLOPT_SSLVERSION, 'CURL_SSLVERSION_TLSv1');

            $RawData = curl_exec($ch);
            curl_close($ch);
            //$JSON = json_decode($RawData,true);
            //$this->SetBuffer("RawData",$RawData);
            
            if (isset($RawData) && 400 == $RawData) {
                $this->SendDebug($this->Translate("UniFi API Call"), $this->Translate('400 Bad Request - The server cannot or will not process the request due to an apparent client error.'), 0);
                $this->SetStatus(201); // login seems to be not successful
                return false;
            } elseif (isset($RawData) && (401 == $RawData || 403 == $RawData || $RawData == "Unauthorized")) {
                $this->SendDebug($this->Translate("UniFi API Call"), $this->Translate('401 Unauthorized / 403 Forbidden - The request contained valid data and was understood by the server, but the server is refusing action. Missing user permission?'), 0);
                $this->SetStatus(201); // login seems to be not successful
                return false;
            } elseif (isset($RawData)) {
                $this->SendDebug($this->Translate("UniFi API Call"), $this->Translate("Successfully Called"), 0);
                $this->SendDebug($this->Translate("UniFi API Call"), $this->Translate("Data Provided: ").$RawData, 0);
                $this->SetBuffer("RawData", $RawData);
            } else {
                $this->SendDebug($this->Translate("UniFi API Call"), $this->Translate("API could not be called - check the login data. Do you see a Cookie?"), 0);
                $this->SetStatus(201); // login seems to be not successful
                return false;
            }
        }

        return true;
    }

    public function EndpointMonitor()
    {
        $Site = $this->ReadPropertyString("Site");

        if ($this->AuthenticateAndGetData("api/s/".$Site."/stat/sta")) {
            $RawData = $this->GetBuffer("RawData");
            
            if ($RawData !== "") {
                $JSONData = json_decode($RawData, true);
                $ConnectionMethod = $JSONData["data"][0]["is_wired"];

                if ($ConnectionMethod == true and $this->ReadPropertyInteger("ConnectionType") == 0) {
                    $ConnectionConfigError = true;
                    $this->SendDebug($this->Translate("Endpoint Monitor"), $this->Translate("Config error - device monitored is a wired device. Please select wired in the module configuration."), 0);
                } else {
                    $ConnectionConfigError = false;
                }

                if ($this->ReadPropertyBoolean("DataPointNetwork") == 1) {
                    $IPAddress = $JSONData["data"][0]["ip"];
                    SetValue($this->GetIDForIdent("IPAddress"), $IPAddress);
                    $this->SendDebug($this->Translate("Endpoint Monitor"), $this->Translate("Network Data IP ").$IPAddress, 0);
                    $Hostname = $JSONData["data"][0]["hostname"];
                    SetValue($this->GetIDForIdent("Hostname"), $Hostname);
                    $this->SendDebug($this->Translate("Endpoint Monitor"), $this->Translate("Network Data Hostname ").$Hostname, 0);
                    //$Name = $JSONData["data"][0]["name"];
                    //SetValue($this->GetIDForIdent("Name"),$Name);
                    //$this->SendDebug($this->Translate("Endpoint Monitor"),$this->Translate("Network Data Name ").$Name,0);
                }
                if ($this->ReadPropertyBoolean("DataPointConnection") == 1) {
                    $Satisfaction = $JSONData["data"][0]["satisfaction"];
                    SetValue($this->GetIDForIdent("Satisfaction"), $Satisfaction);
                    $this->SendDebug($this->Translate("Endpoint Monitor"), $this->Translate("Connection Data Satisfaction ").$Satisfaction, 0);
                    $SLastSeen = $JSONData["data"][0]["last_seen"];
                    SetValue($this->GetIDForIdent("LastSeen"), gmdate("Y-m-d H:i:s", $SLastSeen));
                    $this->SendDebug($this->Translate("Endpoint Monitor"), $this->Translate("Connection Data Last Seen ").gmdate("Y-m-d H:i:s", $SLastSeen), 0);
                    $Uptime = $JSONData["data"][0]["uptime"];
                    SetValue($this->GetIDForIdent("Uptime"), Round($Uptime/3600, 0));
                    $this->SendDebug($this->Translate("Endpoint Monitor"), $this->Translate("Connection Data Uptime in hours ").Round($Uptime/3600, 0), 0);
                }
                if ($this->ReadPropertyBoolean("DataPointConnection") == 1 and $this->ReadPropertyInteger("ConnectionType") == 0 and $ConnectionConfigError == false) {
                    $Accesspoint = $JSONData["data"][0]["ap_mac"];
                    SetValue($this->GetIDForIdent("Accesspoint"), $Accesspoint);
                    $this->SendDebug($this->Translate("Endpoint Monitor"), $this->Translate("Connection Data Accesspoint ").$Accesspoint, 0);
                    $Channel = $JSONData["data"][0]["channel"];
                    SetValue($this->GetIDForIdent("Channel"), $Channel);
                    $this->SendDebug($this->Translate("Endpoint Monitor"), $this->Translate("Connection Data Channel ").$Channel, 0);
                    $Radio = $JSONData["data"][0]["radio"];
                    SetValue($this->GetIDForIdent("Radio"), $Radio);
                    $this->SendDebug($this->Translate("Endpoint Monitor"), $this->Translate("Connection Data Radio ").$Radio, 0);
                    $ESSID = $JSONData["data"][0]["essid"];
                    SetValue($this->GetIDForIdent("ESSID"), $ESSID);
                    $this->SendDebug($this->Translate("Endpoint Monitor"), $this->Translate("Connection Data ESSID ").$ESSID, 0);
                    $RSSI = $JSONData["data"][0]["rssi"];
                    SetValue($this->GetIDForIdent("RSSI"), $RSSI);
                    $this->SendDebug($this->Translate("Endpoint Monitor"), $this->Translate("Connection Data RSSI ").$RSSI, 0);
                    $Noise = $JSONData["data"][0]["noise"];
                    SetValue($this->GetIDForIdent("Noise"), $Noise);
                    $this->SendDebug($this->Translate("Endpoint Monitor"), $this->Translate("Connection Data Noise ").$Noise, 0);
                    $SignalStrength = $JSONData["data"][0]["signal"];
                    SetValue($this->GetIDForIdent("SignalStrength"), $SignalStrength);
                    $this->SendDebug($this->Translate("Endpoint Monitor"), $this->Translate("Connection Data SignalStrength ").$SignalStrength, 0);
                }
                if ($this->ReadPropertyBoolean("DataPointTransfer") == 1 and $this->ReadPropertyInteger("ConnectionType") == 0 and $ConnectionConfigError == false) {
                    $TXBytes = $JSONData["data"][0]["tx_bytes"];
                    SetValue($this->GetIDForIdent("TXBytes"), $TXBytes/1000000);
                    $this->SendDebug($this->Translate("Endpoint Monitor"), $this->Translate("Transfer Data TXBytes ").$TXBytes/1000000, 0);
                    $RXBytes = $JSONData["data"][0]["rx_bytes"];
                    SetValue($this->GetIDForIdent("RXBytes"), $RXBytes/1000000);
                    $this->SendDebug($this->Translate("Endpoint Monitor"), $this->Translate("Transfer Data RXBytes ").$RXBytes/1000000, 0);
                    $TXPackets = $JSONData["data"][0]["tx_packets"];
                    SetValue($this->GetIDForIdent("TXPackets"), $TXPackets);
                    $this->SendDebug($this->Translate("Endpoint Monitor"), $this->Translate("Transfer Data TXPackets ").$TXPackets, 0);
                    $RXPackets = $JSONData["data"][0]["rx_packets"];
                    SetValue($this->GetIDForIdent("RXPackets"), $RXPackets);
                    $this->SendDebug($this->Translate("Endpoint Monitor"), $this->Translate("Transfer Data RXPackets ").$RXPackets, 0);
                }
            } else {
                $this->SendDebug($this->Translate("Endpoint Monitor"), $this->Translate("There does not seem to be any configuration - no data is available from the UniFi"), 0);
            }
        }
    }
}
