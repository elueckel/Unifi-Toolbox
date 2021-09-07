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
	class UniFiInternetController extends IPSModule
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

			$this->RegisterPropertyBoolean("ConnectionData01",0);
			$this->RegisterPropertyBoolean("ConnectionData02",0);

			$this->RegisterTimer("Collect Connection Data",0,"IC_GetInternetData(\$_IPS['TARGET']);");

		}

		public function Destroy() {
			//Never delete this line!
			parent::Destroy();
		}

		public function ApplyChanges() {
			//Never delete this line!
			parent::ApplyChanges();

			$vpos = 100;
			$this->MaintainVariable("WAN1IP", $this->Translate("WAN 1 External IP Address"), vtString, "", $vpos++,  $this->ReadPropertyBoolean("ConnectionData01") == "1");
			$this->MaintainVariable("WAN2IP", $this->Translate("WAN 2 External IP Address"), vtString, "", $vpos++,  $this->ReadPropertyBoolean("ConnectionData02") == "1");

			$TimerMS = $this->ReadPropertyInteger("Timer") * 1000;
			$this->SetTimerInterval("Collect Connection Data",$TimerMS);

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
			$UnifiAPI = "api/s/".$Site."/stat/sysinfo";
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
					if (($code >= 200) && ($code < 400)) { 
						$this->SendDebug($this->Translate("Authentication"),$this->Translate('Login Successful'),0); 
						$this->SendDebug($this->Translate("Authentication"),$this->Translate('Cookie Provided is: ').$Cookie,0);
					}
					//if ($code === 400 OR $code ) {
					//		$this->SendDebug($this->Translate("Authentication"),$this->Translate('Login Failure - We have received an HTTP response status: 400. Probably a controller login failure'),0);
					//}
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
				$JSON = json_decode($RawData,true);
				$this->SetBuffer("$RawData",$RawData);
				
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

		public function GetInternetData() {

			//Query JSON File for Internetdata

			if ($this->ReadPropertyBoolean("ConnectionData01") == "1") {

				$this->AuthenticateAndGetData();
				$RawData = $this->GetBuffer("RawData");
				$JSONData = json_decode($RawData,true);

				if (isset($JSONData["data"][0]["ip_addrs"][0])) {
					$WAN1IP = $JSONData["data"][0]["ip_addrs"][0];
					if (isset($WAN1IP)) {
						if ($WAN1IP !== GetValue($this->GetIDForIdent("WAN1IP"))) {
							$this->SendDebug($this->Translate("Internet Data WAN 01"),$this->Translate("IP Adress 1 has been updated to ").$WAN1IP,0); 
							SetValue($this->GetIDForIdent("WAN1IP"),$WAN1IP);
						}
						else {
							$this->SendDebug($this->Translate("Internet Data WAN 01"),$this->Translate("IP Adress 1 has not been updated since it is still the same"),0); 
						}
					}
				}
				else {
					$this->SendDebug($this->Translate("Internet Data WAN 01"),$this->Translate("No data for WAN Port 1"),0); 
				}
			}

			if ($this->ReadPropertyBoolean("ConnectionData02") == "1") {

				$this->AuthenticateAndGetData();
				$RawData = $this->GetBuffer("RawData");
				$JSONData = json_decode($RawData,true);

				if (isset($JSONData["data"][0]["ip_addrs"][1])) {
					$WAN2IP = $JSONData["data"][0]["ip_addrs"][1];
					if (isset($WAN2IP)) {
						if ($WAN2IP !== GetValue($this->GetIDForIdent("WAN2IP"))) {
							$this->SendDebug($this->Translate("Internet Data WAN 02"),$this->Translate("IP Adress 2 has been updated to ").$WAN2IP,0); 
							SetValue($this->GetIDForIdent("WAN2IP"),$WAN2IP);
						}
						else {
							$this->SendDebug($this->Translate("Internet Data WAN 02"),$this->Translate("IP Adress 2 has not been updated since it is still the same"),0); 
						}
					}
				}
				else {
					$this->SendDebug($this->Translate("Internet Data WAN 02"),$this->Translate("No data for WAN Port 2"),0); 
				}

			}
			/*
			else {
				$this->SendDebug($this->Translate("Module"),$this->Translate("No data query has been actived - please select one"),0); 
			}
			*/

		}

	}