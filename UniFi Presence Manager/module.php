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
	class UniFiPresenceManager extends IPSModule
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
			$this->RegisterPropertyBoolean("GeneralPresenceUpdatedVariable","0");

			$this->RegisterPropertyString("Devices", "");

			$this->RegisterTimer("Check Presence",0,"PM_CheckPresence(\$_IPS['TARGET']);");

		}

		public function Destroy() {
			//Never delete this line!
			parent::Destroy();
		}

		public function ApplyChanges() {
			//Never delete this line!
			parent::ApplyChanges();

			$vpos = 100;

			//Create Devices mentioned in configuration
			$DevicesList = $this->ReadPropertyString("Devices");
			$DevicesJSON = json_decode($DevicesList,true);
			//var_dump($DevicesJSON);

			if (isset($DevicesJSON)) {
				foreach ($DevicesJSON as $Device) {
					$DeviceName = $Device["varDeviceName"];
					$DeviceMac = str_replace(array("-",":"), "", $Device["varDeviceMAC"]);
					$this->MaintainVariable($DeviceMac, $DeviceName, vtBoolean, "~Presence", $vpos++, isset($DevicesJSON));	
				}
			}

			$this->MaintainVariable("GeneralPresenceUpdatedVariable", $this->Translate("Presence Updated"), vtBoolean, "~Switch", 10, $this->ReadPropertyBoolean("GeneralPresenceUpdatedVariable") == 1);

			$TimerMS = $this->ReadPropertyInteger("Timer") * 1000;
			$this->SetTimerInterval("Check Presence",$TimerMS);

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

			////////////////////////////////////////
			//Change the Unifi API to be called here
			$UnifiAPI = "api/s/".$Site."/stat/sta";
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
			preg_match_all('|Set-Cookie: (.*);|U', substr($data, 0, $header_size), $results);
			if (isset($results[1])) {
				$Cookie = implode(';', $results[1]);
				if (!empty($body)) {
					if (($code == 200) && ($code != 400)) { 
						$this->SendDebug($this->Translate("Authentication"),$this->Translate('Login Successful'),0); 
						$this->SendDebug($this->Translate("Authentication"),$this->Translate('Cookie Provided is: ').$Cookie,0);
					}
					if ($code == 400) {
							$this->SendDebug($this->Translate("Authentication"),$this->Translate('Login Failure - We have received an HTTP response status: 400. Probably a controller login failure'),0);
			
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
				curl_setopt($ch, CURLOPT_URL, "https://".$ServerAdress.":".$ServerPort.$MiddlePartURL.$UnifiAPI);
				curl_setopt($ch, CURLOPT_HTTPGET, TRUE);
				curl_setopt($ch , CURLOPT_RETURNTRANSFER, true);
				curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE); 
				curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
				curl_setopt($ch, CURLOPT_HTTPHEADER, array("cookie: ".$Cookie));
				curl_setopt($ch, CURLOPT_SSLVERSION, 'CURL_SSLVERSION_TLSv1'); 	    

				$RawData = curl_exec($ch);
				curl_close($ch);
				//$JSON = json_decode($RawData,true);
				//$this->SetBuffer("$RawData",$RawData);
				
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

		public function CheckPresence() {

			$this->AuthenticateAndGetData();
			$RawData = $this->GetBuffer("RawData");
			
			if ($RawData !== "") {
				$JSONData = json_decode($RawData,true);
				$ActiveDevices = $JSONData;

				//Load devices from config form 
				$DevicesList = $this->ReadPropertyString("Devices");
				$DevicesJSON = json_decode($DevicesList,true);

				foreach ($DevicesJSON as $Device) {
					//Build a clean array out of the devices mentioned in the config form with : or - 
					$DeviceMac = str_replace(array("-",":"), "", $Device["varDeviceMAC"]);

					//Itterate through all device and check if one of them matches the list in the config form.
					foreach ($ActiveDevices["data"] as $Index=>$Device) {
						$DeviceMacClean = str_replace(array("-",":"), "", $Device["mac"]);

						$OldPresenceValue = GetValue($this->GetIDForIdent($DeviceMac));
						if ($DeviceMac == $DeviceMacClean) {	
							if ($OldPresenceValue == 0) { //check if new value is different and only than trigger a replacement
								SetValue($this->GetIDForIdent($DeviceMac),1);
								if ($this->ReadPropertyBoolean("GeneralPresenceUpdatedVariable") == 1) {
									SetValue($this->GetIDForIdent("GeneralPresenceUpdatedVariable"),1);
								}
								$this->SendDebug($this->Translate("Presence Manager"),$this->Translate("Device ACTIVE with MAC: ".$DeviceMac),0); 
							}
							break;
						}
						else {
							if ($Index === array_key_last($ActiveDevices["data"])) {
								if ($OldPresenceValue == 1) {
									SetValue($this->GetIDForIdent($DeviceMac),0);
									if ($this->ReadPropertyBoolean("GeneralPresenceUpdatedVariable") == 1) {
										SetValue($this->GetIDForIdent("GeneralPresenceUpdatedVariable"),1);
									}
									$this->SendDebug($this->Translate("Presence Manager"),$this->Translate("Device NOT active with MAC: ".$DeviceMac),0); 
								}
							}

						}

					}
				}
			}
			else {
				$this->SendDebug($this->Translate("Presence Manager"),$this->Translate("There does not seem to be any configuration - no data is available from the UniFi"),0); 
			}			

		}

	}