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
	class UniFiDeviceMonitor extends IPSModule
	{
		public function Create() {
			//Never delete this line!
			parent::Create();

			$this->RegisterPropertyInteger("ControllerType", 0);
			$this->RegisterPropertyString("ServerAdress","192.168.1.1");
			$this->RegisterPropertyInteger("ServerPort", "443");
			$this->RegisterPropertyString("Site","default");
			$this->RegisterPropertyString("UserName","");
			$this->RegisterPropertyString("Password","");
			$this->RegisterPropertyInteger("Timer", "0");
			
			$this->RegisterPropertyString("DeviceMac", "");
			$this->RegisterPropertyInteger("DeviceType", 0);

			$this->RegisterPropertyBoolean("DataPointBasic", 1);
			$this->RegisterPropertyBoolean("DataPointHardware", 0);
			$this->RegisterPropertyBoolean("DataPointSpecific", 0);
			

			$this->RegisterTimer("Device Monitor",0,"DM_DeviceMonitor(\$_IPS['TARGET']);");

		}

		public function Destroy() {
			//Never delete this line!
			parent::Destroy();
		}

		public function ApplyChanges() {
			//Never delete this line!
			parent::ApplyChanges();
						
			//Basic Data
			$vpos = 100;
			$this->MaintainVariable("DeviceModel", $this->Translate("Device Model"), vtString, "", $vpos++, $this->ReadPropertyBoolean("DataPointBasic") == 1);
			$this->MaintainVariable("SoftwareVersion", $this->Translate("Software Version"), vtString, "", $vpos++, $this->ReadPropertyBoolean("DataPointBasic") == 1);
			//$this->MaintainVariable("Satisfaction", $this->Translate("Satisfaction"), vtString, "", $vpos++, $this->ReadPropertyBoolean("DataPointBasic") == 1);
			$this->MaintainVariable("LastSeen", $this->Translate("Last Seen"), vtString, "", $vpos++, $this->ReadPropertyBoolean("DataPointBasic") == 1);
			$this->MaintainVariable("Uptime", $this->Translate("Uptime in hours"), vtString, "", $vpos++, $this->ReadPropertyBoolean("DataPointBasic") == 1);
			//$this->MaintainVariable("IPAddress", $this->Translate("IP Address"), vtString, "", $vpos++, $this->ReadPropertyBoolean("DataPointBasic") == 1);
			$this->MaintainVariable("Name", $this->Translate("Device Name"), vtString, "", $vpos++, $this->ReadPropertyBoolean("DataPointBasic") == 1);

			//Hardware Data
			$vpos = 200;
			$this->MaintainVariable("CPULoad", $this->Translate("CPU Load"), vtString, "", $vpos++, $this->ReadPropertyBoolean("DataPointHardware") == 1);
			$this->MaintainVariable("MemoryLoad", $this->Translate("Memory Load"), vtString, "", $vpos++, $this->ReadPropertyBoolean("DataPointHardware") == 1);
			
			
			//Device Specific Data Connection Data UDM/USG
			$vpos = 300;
			$this->MaintainVariable("WAN1IP", $this->Translate("WAN1 IP"), vtString, "", $vpos++, $this->ReadPropertyBoolean("DataPointSpecific") == 1 AND $this->ReadPropertyInteger("DeviceType") == 0);
			$this->MaintainVariable("WAN1TXBytes", $this->Translate("WAN 1 TX Megabytes"), vtInteger, "", $vpos++, $this->ReadPropertyBoolean("DataPointSpecific") == 1 AND $this->ReadPropertyInteger("DeviceType") == 0);
			$this->MaintainVariable("WAN1RXBytes", $this->Translate("WAN 1 RX Megabytes"), vtInteger, "", $vpos++, $this->ReadPropertyBoolean("DataPointSpecific") == 1 AND $this->ReadPropertyInteger("DeviceType") == 0);
			$this->MaintainVariable("WAN1TXPackets", $this->Translate("WAN 1 TX Packets"), vtInteger, "", $vpos++, $this->ReadPropertyBoolean("DataPointSpecific") == 1 AND $this->ReadPropertyInteger("DeviceType") == 0);
			$this->MaintainVariable("WAN1RXPackets", $this->Translate("WAN 1 RX Packets"), vtInteger, "", $vpos++, $this->ReadPropertyBoolean("DataPointSpecific") == 1 AND $this->ReadPropertyInteger("DeviceType") == 0);
			$this->MaintainVariable("WAN1TXErrors", $this->Translate("WAN 1 TX Errors"), vtInteger, "", $vpos++, $this->ReadPropertyBoolean("DataPointSpecific") == 1 AND $this->ReadPropertyInteger("DeviceType") == 0);
			$this->MaintainVariable("WAN1RXErrors", $this->Translate("WAN 1 RX Errors"), vtInteger, "", $vpos++, $this->ReadPropertyBoolean("DataPointSpecific") == 1 AND $this->ReadPropertyInteger("DeviceType") == 0);

			$TimerMS = $this->ReadPropertyInteger("Timer") * 1000;
			$this->SetTimerInterval("Device Monitor",$TimerMS);

			if (0 == $TimerMS) {
				// instance inactive
				$this->SetStatus(104);
			}
			else {
				// instance active
				$this->SetStatus(102);
			}

		}


		public function AuthenticateAndGetData() {
			
			$ControllerType = $this->ReadPropertyInteger("ControllerType");
			$ServerAdress = $this->ReadPropertyString("ServerAdress");
			$ServerPort = $this->ReadPropertyInteger("ServerPort");
			$Username = $this->ReadPropertyString("UserName");
			$Password = $this->ReadPropertyString("Password");
			$Site = $this->ReadPropertyString("Site");

			$DeviceMac = strtolower($this->ReadPropertyString("DeviceMac"));

			////////////////////////////////////////
			//Change the Unifi API to be called here
			$UnifiAPI = "api/s/".$Site."/stat/device";
			////////////////////////////////////////

			//Generic Section providing for Authenthication against a Dream Maschine or Classic CloudKey

			$ch = curl_init();

			if ($ControllerType == 0) {
				$SuffixURL = "/api/auth/login";
				curl_setopt($ch, CURLOPT_POSTFIELDS, "username=".$Username."&password=".$Password);
			}
			elseif ($ControllerType == 1) {
				$SuffixURL = "/api/login";
				curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['username' => $Username, 'password' => $Password]));
			}				
			curl_setopt($ch, CURLOPT_POST, TRUE);
			curl_setopt($ch, CURLOPT_URL, "https://".$ServerAdress.":".$ServerPort.$SuffixURL);
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
			curl_setopt($ch, CURLOPT_HEADER, true);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);  
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 
			$data = curl_exec($ch);

			$header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
			$body        = trim(substr($data, $header_size));
			$code        = curl_getinfo($ch, CURLINFO_HTTP_CODE);

			$this->SendDebug($this->Translate("Authentication"),$this->Translate('Cookie Provided is: ').$code,0);
			preg_match_all('|(?i)Set-Cookie: (.*);|U', substr($data, 0, $header_size), $results);
			if (isset($results[1])) {
				$Cookie = implode(';', $results[1]);
				if (!empty($body)) {
					if ($code == 200) { 
						$this->SendDebug($this->Translate("Authentication"),$this->Translate('Login Successful'),0); 
						$this->SendDebug($this->Translate("Authentication"),$this->Translate('Cookie Provided is: ').$Cookie,0);
					}
					else if ($code == 400) {
						$this->SendDebug($this->Translate("Authentication"),$this->Translate('400 Bad Request - The server cannot or will not process the request due to an apparent client error.'),0);
					}
					else if ($code == 401 || $code == 403) {
						$this->SendDebug($this->Translate("Authentication"),$this->Translate('401 Unauthorized / 403 Forbidden - The request contained valid data and was understood by the server, but the server is refusing action. Missing user permission?'),0);
					}
				}
			}

			// Section below will collect and store it into a buffer
			
			if (isset($Cookie)) {

				$ch = curl_init();
				if ($ControllerType == 0) {
					$MiddlePartURL = "/proxy/network/";
				}
				elseif ($ControllerType == 1) {
					$MiddlePartURL = "/";
				}	
				curl_setopt($ch, CURLOPT_URL, "https://".$ServerAdress.":".$ServerPort.$MiddlePartURL.$UnifiAPI."/".$DeviceMac);
				curl_setopt($ch, CURLOPT_HTTPGET, TRUE);
				curl_setopt($ch , CURLOPT_RETURNTRANSFER, true);
				curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE); 
				curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
				curl_setopt($ch, CURLOPT_HTTPHEADER, array("cookie: ".$Cookie));
				curl_setopt($ch, CURLOPT_SSLVERSION, 'CURL_SSLVERSION_TLSv1'); 	    

				$RawData = curl_exec($ch);
				curl_close($ch);
				
				if (isset($RawData) AND $RawData != "Unauthorized") {
					$this->SendDebug($this->Translate("UniFi API Call"),$this->Translate("Successfully Called"),0); 
					$this->SendDebug($this->Translate("UniFi API Call"),$this->Translate("Data Provided: ").$RawData,0);
					$this->SetBuffer("RawData",$RawData);
				}
				else {
					$this->SendDebug($this->Translate("UniFi API Call"),$this->Translate("API could not be called - check the login data. Do you see a Cookie?"),0); 
				}
			}

		}

		public function DeviceMonitor() {

			$this->AuthenticateAndGetData();
			$RawData = $this->GetBuffer("RawData");
			
			if ($RawData !== "") {
				$JSONData = json_decode($RawData,true);
				//var_dump($JSONData);
				$DeviceModel = $JSONData["data"][0]["model"];
				$UnfiInternetDeviceArray = array("UDM","UGW4","UGW3","UDMPRO");
				
				if (!in_array($DeviceModel,$UnfiInternetDeviceArray) AND $this->ReadPropertyInteger("DeviceType") == 0) {
					$DeviceConfigError = true;
					$this->SendDebug($this->Translate("Device Monitor"),$this->Translate("Config error - device type set to UDM, USG etc. but is no such device."),0); 
				}
				else {
					$DeviceConfigError = false;
				}

				if ($this->ReadPropertyBoolean("DataPointBasic") == 1) {
					$DeviceModel = $JSONData["data"][0]["model"];
					SetValue($this->GetIDForIdent("DeviceModel"),$DeviceModel);
					$this->SendDebug($this->Translate("Device Monitor"),$this->Translate("Device Model ").$DeviceModel,0); 
					$SoftwareVersion = $JSONData["data"][0]["version"];
					SetValue($this->GetIDForIdent("SoftwareVersion"),$SoftwareVersion);
					$this->SendDebug($this->Translate("Device Monitor"),$this->Translate("Software Version ").$SoftwareVersion,0);
					//$Satisfaction = $JSONData["data"][0]["satisfaction"];
					//SetValue($this->GetIDForIdent("Satisfaction"),$Satisfaction);
					//$this->SendDebug($this->Translate("Device Monitor"),$this->Translate("Device Satisfaction ").$Satisfaction,0); 
					$SLastSeen = $JSONData["data"][0]["last_seen"];
					SetValue($this->GetIDForIdent("LastSeen"),gmdate("Y-m-d H:i:s", $SLastSeen));
					$this->SendDebug($this->Translate("Device Monitor"),$this->Translate("Connection Data Last Seen ").gmdate("Y-m-d H:i:s", $SLastSeen),0); 
					$Uptime = $JSONData["data"][0]["uptime"];
					SetValue($this->GetIDForIdent("Uptime"),Round($Uptime/3600,0));
					$this->SendDebug($this->Translate("Device Monitor"),$this->Translate("Connection Data Uptime in hours ").Round($Uptime/3600,0),0);  
					$Name = $JSONData["data"][0]["name"];
					SetValue($this->GetIDForIdent("Name"),$Name);
					$this->SendDebug($this->Translate("Device Monitor"),$this->Translate("Devicename ").$Name,0); 
				} 
				if ($this->ReadPropertyBoolean("DataPointHardware") == 1) {
					$CPULoad = $JSONData["data"][0]["system-stats"]["cpu"];
					SetValue($this->GetIDForIdent("CPULoad"),$CPULoad);
					$this->SendDebug($this->Translate("Device Monitor"),$this->Translate("CPU Load ").$CPULoad,0);
					$MemoryLoad = $JSONData["data"][0]["system-stats"]["mem"];
					SetValue($this->GetIDForIdent("MemoryLoad"),$MemoryLoad);
					$this->SendDebug($this->Translate("Device Monitor"),$this->Translate("Memory Load ").$MemoryLoad,0);
					
				}
				if ($this->ReadPropertyBoolean("DataPointSpecific") == 1 AND $this->ReadPropertyInteger("DeviceType") == 0 AND $DeviceConfigError == false) {
					$WAN1IP = $JSONData["data"][0]["wan1"]["ip"];
					SetValue($this->GetIDForIdent("WAN1IP"),$WAN1IP);
					$this->SendDebug($this->Translate("Device Monitor"),$this->Translate("Connection Data WAN 1 IP ").$WAN1IP,0);

					$WAN1TXBytes = $JSONData["data"][0]["wan1"]["tx_bytes"];
					SetValue($this->GetIDForIdent("WAN1TXBytes"),$WAN1TXBytes/1000000);
					$this->SendDebug($this->Translate("Device Monitor"),$this->Translate("Transfer Data WAN 1 TX Bytes ").$WAN1TXBytes,0);
					$WAN1RXBytes = $JSONData["data"][0]["wan1"]["rx_bytes"];
					SetValue($this->GetIDForIdent("WAN1RXBytes"),$WAN1RXBytes/1000000);
					$this->SendDebug($this->Translate("Endpoint Monitor"),$this->Translate("Transfer Data WAN 1 RX Bytes ").$WAN1RXBytes,0); 
					$WAN1TXPackets = $JSONData["data"][0]["wan1"]["tx_packets"];
					SetValue($this->GetIDForIdent("WAN1TXPackets"),$WAN1TXPackets);
					$this->SendDebug($this->Translate("Device Monitor"),$this->Translate("Transfer Data WAN 1 TX Packets ").$WAN1TXPackets,0); 
					$WAN1RXPackets = $JSONData["data"][0]["wan1"]["tx_packets"];
					SetValue($this->GetIDForIdent("WAN1RXPackets"),$WAN1RXPackets);
					$this->SendDebug($this->Translate("Device Monitor"),$this->Translate("Transfer Data WAN 1 RXPackets ").$WAN1RXPackets,0); 
					$WAN1TXErrors = $JSONData["data"][0]["wan1"]["tx_errors"];
					SetValue($this->GetIDForIdent("WAN1TXErrors"),$WAN1TXErrors);
					$this->SendDebug($this->Translate("Device Monitor"),$this->Translate("Transfer Data WAN 1 TX Errors ").$WAN1TXErrors,0); 
					$WAN1RXErrors = $JSONData["data"][0]["wan1"]["rx_errors"];
					SetValue($this->GetIDForIdent("WAN1RXErrors"),$WAN1RXErrors);
					$this->SendDebug($this->Translate("Device Monitor"),$this->Translate("Transfer Data WAN 1 RX Errors ").$WAN1RXErrors,0); 
				}
			}
			else {
				$this->SendDebug($this->Translate("Endpoint Monitor"),$this->Translate("There does not seem to be any configuration - no data is available from the UniFi"),0); 
			}			

		}

	}
