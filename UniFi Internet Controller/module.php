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

class UniFiDMInternetController extends IPSModule {

	public function Create() {
		//Never delete this line!
		parent::Create();

		//$this->RegisterPropertyInteger("ControllerType", 0);
		$this->RegisterPropertyString("ServerAddress","192.168.1.1");
		$this->RegisterPropertyInteger("ServerPort", "443");
		$this->RegisterPropertyString("Site","default");
		$this->RegisterPropertyString("UserName","");
		$this->RegisterPropertyString("Password","");
		$this->RegisterPropertyInteger("Timer", "0");

		$this->RegisterPropertyBoolean("WAN1IP",0);
		$this->RegisterPropertyBoolean("WAN2IP",0);

		$this->RegisterPropertyBoolean("version",0);
		$this->RegisterPropertyBoolean("previous_version",0);
		$this->RegisterPropertyBoolean("update_available",0);
		$this->RegisterPropertyBoolean("update_downloaded",0);
		$this->RegisterPropertyBoolean("uptime",0);
		$this->RegisterPropertyBoolean("ubnt_device_type",0);
		$this->RegisterPropertyBoolean("udm_version",0);

		$this->RegisterPropertyBoolean("gw_version",0);
		$this->RegisterPropertyBoolean("isp_name",0);
		$this->RegisterPropertyBoolean("isp_organization",0);
		$this->RegisterPropertyBoolean("wan_ip",0);
		$this->RegisterPropertyBoolean("WAN1availability",0);
		$this->RegisterPropertyBoolean("WAN1latency_average",0);
		$this->RegisterPropertyBoolean("WAN1time_period",0);
		$this->RegisterPropertyBoolean("WAN2availability",0);
		$this->RegisterPropertyBoolean("WAN2latency_average",0);
		$this->RegisterPropertyBoolean("WAN2time_period",0);
		
		$this->RegisterTimer("Collect Connection Data",0,"IC_GetInternetData(\$_IPS['TARGET']);");

		if (IPS_VariableProfileExists("IC.TimeS") == false){
			IPS_CreateVariableProfile("IC.TimeS", vtInteger);
			IPS_SetVariableProfileValues("IC.TimeS", 0, 0, 1);
			IPS_SetVariableProfileDigits("IC.TimeS", 2);
			IPS_SetVariableProfileText("IC.TimeS", "", $this->Translate(" seconds"));
			IPS_SetVariableProfileIcon("IC.TimeS",  "Clock");
		}

		if (IPS_VariableProfileExists("IC.TimeMS") == false){
			IPS_CreateVariableProfile("IC.TimeMS", vtInteger);
			IPS_SetVariableProfileValues("IC.TimeMS", 0, 0, 1);
			IPS_SetVariableProfileDigits("IC.TimeMS", 2);
			IPS_SetVariableProfileText("IC.TimeMS", "", $this->Translate(" milliseconds"));
			IPS_SetVariableProfileIcon("IC.TimeMS",  "Clock");
		}


	}

	public function Destroy() {
		//Never delete this line!
		parent::Destroy();
	}

	public function ApplyChanges() {
		//Never delete this line!
		parent::ApplyChanges();

		$vpos = 100;
		$this->MaintainVariable("wan_ip", $this->Translate("WAN IP active"), vtString, "", $vpos++,  $this->ReadPropertyBoolean("wan_ip"));
		$this->MaintainVariable("WAN1IP", $this->Translate("WAN1 External IP Address"), vtString, "", $vpos++,  $this->ReadPropertyBoolean("WAN1IP"));
		$this->MaintainVariable("WAN1availability", $this->Translate("WAN1 availability"), vtInteger, "~Intensity.100", $vpos++,  $this->ReadPropertyBoolean("WAN1availability"));
		$this->MaintainVariable("WAN1latency_average", $this->Translate("WAN1 latency-average"), vtInteger, "IC.TimeMS", $vpos++,  $this->ReadPropertyBoolean("WAN1latency_average"));
		$this->MaintainVariable("WAN1time_period", $this->Translate("WAN1 time-period"), vtInteger, "IC.TimeS", $vpos++,  $this->ReadPropertyBoolean("WAN1time_period"));
		$this->MaintainVariable("WAN2IP", $this->Translate("WAN2 External IP Address"), vtString, "", $vpos++,  $this->ReadPropertyBoolean("WAN2IP"));
		$this->MaintainVariable("WAN2availability", $this->Translate("WAN2 availability"), vtInteger, "~Intensity.100", $vpos++,  $this->ReadPropertyBoolean("WAN2availability"));
		$this->MaintainVariable("WAN2latency_average", $this->Translate("WAN2 latency-average"), vtInteger, "IC.TimeMS", $vpos++,  $this->ReadPropertyBoolean("WAN2latency_average"));
		$this->MaintainVariable("WAN2time_period", $this->Translate("WAN2 time-period"), vtInteger, "IC.TimeS", $vpos++,  $this->ReadPropertyBoolean("WAN2time_period"));
		$this->MaintainVariable("isp_name", $this->Translate("ISP Name"), vtString, "", $vpos++,  $this->ReadPropertyBoolean("isp_name"));
		$this->MaintainVariable("isp_organization", $this->Translate("ISP Organization"), vtString, "", $vpos++,  $this->ReadPropertyBoolean("isp_organization"));
		$this->MaintainVariable("version", $this->Translate("Unifi Network Version"), vtString, "", $vpos++,  $this->ReadPropertyBoolean("version"));
		$this->MaintainVariable("previous_version", $this->Translate("Unifi Network Vorgängerversion"), vtString, "", $vpos++,  $this->ReadPropertyBoolean("previous_version"));
		$this->MaintainVariable("update_available", $this->Translate("Update available"), vtBoolean, "", $vpos++,  $this->ReadPropertyBoolean("update_available"));
		$this->MaintainVariable("update_downloaded", $this->Translate("Update downloaded"), vtBoolean, "", $vpos++,  $this->ReadPropertyBoolean("update_downloaded"));
		$this->MaintainVariable("uptime", $this->Translate("Uptime"), vtInteger, "~UnixTimestamp", $vpos++,  $this->ReadPropertyBoolean("uptime"));

		$this->MaintainVariable("ubnt_device_type", $this->Translate("UBNT Device Type"), vtString, "", $vpos++,  $this->ReadPropertyBoolean("ubnt_device_type"));
		$this->MaintainVariable("udm_version", $this->Translate("UDM Version"), vtString, "", $vpos++,  $this->ReadPropertyBoolean("udm_version"));
		$this->MaintainVariable("gw_version", $this->Translate("UDM UnifiOS Version"), vtString, "", $vpos++,  $this->ReadPropertyBoolean("gw_version"));

		$TimerMS = $this->ReadPropertyInteger("Timer") * 1000;
		$this->SetTimerInterval("Collect Connection Data",$TimerMS);

		if (0 == $TimerMS) {
			// instance inactive
			$this->SetStatus(104);
		}
		else {
			// instance active
			$this->SetStatus(102);
		}

	}


	public function AuthenticateAndGetData(string $UnifiAPI = "") {
		
		//$ControllerType = $this->ReadPropertyInteger("ControllerType");
		$ServerAddress = $this->ReadPropertyString("ServerAddress");
		$ServerPort = $this->ReadPropertyInteger("ServerPort");
		$Username = $this->ReadPropertyString("UserName");
		$Password = $this->ReadPropertyString("Password");

		//Change the Unifi API to be called here
		if ("" == $UnifiAPI) {
			$Site = $this->ReadPropertyString("Site");
			$UnifiAPI = "api/s/".$Site."/stat/sysinfo";
		}

		//Generic Section providing for Authenthication against a DreamMachine or Classic CloudKey
		$ch = curl_init();

		if(!isset($ControllerType) || $ControllerType == 0) {
			$SuffixURL = "/api/auth/login";
			curl_setopt($ch, CURLOPT_POSTFIELDS, "username=".$Username."&password=".$Password);
		}
		elseif ($ControllerType == 1) {
			$SuffixURL = "/api/login";
			curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['username' => $Username, 'password' => $Password]));
		}				
		curl_setopt($ch, CURLOPT_POST, TRUE);
		curl_setopt($ch, CURLOPT_URL, "https://".$ServerAddress.":".$ServerPort.$SuffixURL);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
		curl_setopt($ch, CURLOPT_HEADER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);  
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 
		$data = curl_exec($ch);

		if(false === $data)
		{
			$this->SendDebug($this->Translate("Authentication"), $this->Translate('Error: Not reachable / No response!'),0);

			// IP or Port not reachable / no response
			$this->SetStatus(200);

			return false;
		}

		$header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
		$body        = trim(substr($data, $header_size));
		$code        = curl_getinfo($ch, CURLINFO_HTTP_CODE);

		$this->SendDebug($this->Translate("Authentication"),$this->Translate('Return-Code Provided is: ').$code,0);
		//$this->SendDebug($this->Translate("Debug"), $data,0);

		preg_match_all('|(?i)Set-Cookie: (.*);|U', substr($data, 0, $header_size), $results);
		if (isset($results[1])) {
			$Cookie = implode(';', $results[1]);
			if (!empty($body)) {
				if (200 == $code) { 
					$this->SendDebug($this->Translate("Authentication"),$this->Translate('Login Successful'),0); 
					$this->SendDebug($this->Translate("Authentication"),$this->Translate('Cookie Provided is: ').$Cookie,0);
				}
				else if (400 == $code) {
					$this->SendDebug($this->Translate("Authentication"),$this->Translate('400 Bad Request - The server cannot or will not process the request due to an apparent client error.'),0);
					echo $this->Translate('400 Bad Request - The server cannot or will not process the request due to an apparent client error.');
					return false;
				}
				else if (401 == $code || 403 == $code) {
					$this->SendDebug($this->Translate("Authentication"),$this->Translate('401 Unauthorized / 403 Forbidden - The request contained valid data and was understood by the server, but the server is refusing action. Missing user permission?'),0);
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
			}
			elseif ($ControllerType == 1) {
				$MiddlePartURL = "/";
			}	
			curl_setopt($ch, CURLOPT_URL, "https://".$ServerAddress.":".$ServerPort.$MiddlePartURL.$UnifiAPI);
			curl_setopt($ch, CURLOPT_HTTPGET, TRUE);
			curl_setopt($ch , CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE); 
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
			curl_setopt($ch, CURLOPT_HTTPHEADER, array("cookie: ".$Cookie));
			curl_setopt($ch, CURLOPT_SSLVERSION, 'CURL_SSLVERSION_TLSv1'); 	    

			//$this->SendDebug("Debug: ", "https://".$ServerAddress.":".$ServerPort.$MiddlePartURL.$UnifiAPI, 0);

			$RawData = curl_exec($ch);
			curl_close($ch);
			//$JSON = json_decode($RawData,true);
			//$this->SetBuffer("RawData",$RawData);
			
			if (isset($RawData) && 400 == $RawData) {
				$this->SendDebug($this->Translate("UniFi API Call"),$this->Translate('400 Bad Request - The server cannot or will not process the request due to an apparent client error.'),0);
				$this->SetStatus(201); // login seems to be not successful
				return false;
			}
			else if (isset($RawData) && (401 == $RawData || 403 == $RawData || $RawData == "Unauthorized")) {
				$this->SendDebug($this->Translate("UniFi API Call"),$this->Translate('401 Unauthorized / 403 Forbidden - The request contained valid data and was understood by the server, but the server is refusing action. Missing user permission?'),0);
				$this->SetStatus(201); // login seems to be not successful
				return false;
			}
			else if (isset($RawData)) {
				$this->SendDebug($this->Translate("UniFi API Call"),$this->Translate("Successfully Called"),0); 
				$this->SendDebug($this->Translate("UniFi API Call"),$this->Translate("Data Provided: ").$RawData,0);
				$this->SetBuffer("RawData",$RawData);
			}
			else {
				$this->SendDebug($this->Translate("UniFi API Call"),$this->Translate("API could not be called - check the login data. Do you see a Cookie?"),0); 
				$this->SetStatus(201); // login seems to be not successful
				return false;
			}
		}

		return true;
	}

	public function GetInternetData() {
		$Site = $this->ReadPropertyString("Site");

		if($this->ReadPropertyBoolean("WAN1IP")
			|| $this->ReadPropertyBoolean("WAN2IP")
			|| $this->ReadPropertyBoolean("version")
			|| $this->ReadPropertyBoolean("previous_version")
			|| $this->ReadPropertyBoolean("update_available")
			|| $this->ReadPropertyBoolean("update_downloaded")
			|| $this->ReadPropertyBoolean("uptime")
			|| $this->ReadPropertyBoolean("ubnt_device_type")
			|| $this->ReadPropertyBoolean("udm_version")
		  ) {

			// query JSON file for internet data
			if ($this->AuthenticateAndGetData("api/s/".$Site."/stat/sysinfo")) {
				$RawData = $this->GetBuffer("RawData");
				$JSONData = json_decode($RawData, true);


				// get IP addresses
				$variableArray = array(
							array('ident' => "WAN1IP",	'localeName' => "WAN1 External IP Address", 'index' => 0),
							array('ident' => "WAN2IP",	'localeName' => "WAN2 External IP Address", 'index' => 1),
						);

				foreach ($variableArray as $variable) {
					if ($this->ReadPropertyBoolean($variable['ident'])) {
						if (isset($JSONData['data'][0]["ip_addrs"][$variable['index']])) {
							$value = $JSONData['data'][0]["ip_addrs"][$variable['index']];
							if (isset($value)) {
								if ($value != GetValue($this->GetIDForIdent($variable['ident']))) {
									$this->SendDebug($this->Translate($variable['localeName']), $this->Translate("updated to ").$value, 0);
									SetValue($this->GetIDForIdent($variable['ident']), $value);
								} else {
									$this->SendDebug($this->Translate($variable['localeName']), $this->Translate("no update received")." (".$value.")", 0);
								}
							}
						} else {
							$this->SendDebug($this->Translate($variable['localeName']), $this->Translate("No data"), 0);
						}
					}
				}


				// get everything else (besides IP addresses)
				$variableArray = array(
					array('ident' => "version",	'localeName' => "Unifi Network Version"),
					array('ident' => "previous_version",	'localeName' => "Unifi Network Vorgängerversion"),
					array('ident' => "update_available",	'localeName' => "Update available"),
					array('ident' => "update_downloaded",	'localeName' => "Update downloaded"),
					array('ident' => "uptime",	'localeName' => "Uptime", 'valueCorrection' => "\$value = (time() - (time() % 60)) - (\$value - (\$value % 60));"),	// value correction to avoid an update for every cycle
				);

				$variableArray[] = array('ident' => "ubnt_device_type",	'localeName' => "UBNT Device Type");
				$variableArray[] = array('ident' => "udm_version",	'localeName' => "UDM Version");

				foreach ($variableArray as $variable) {
					if ($this->ReadPropertyBoolean($variable['ident'])) {
						if (isset($JSONData['data'][0][$variable['ident']])) {
							$value = $JSONData['data'][0][$variable['ident']];
							if (isset($value)) {
								if (isset($variable['valueCorrection'])) {
									eval($variable['valueCorrection']);
								}

								if ($value != GetValue($this->GetIDForIdent($variable['ident']))) {
									$this->SendDebug($this->Translate($variable['localeName']), $this->Translate("updated to ").$value, 0);
									SetValue($this->GetIDForIdent($variable['ident']), $value);
								} else {
									$this->SendDebug($this->Translate($variable['localeName']), $this->Translate("no update received")." (".$value.")", 0);
								}
							}
						} else {
							$this->SendDebug($this->Translate($variable['localeName']), $this->Translate("No data"), 0);
						}
					}
				}
            }
		}

		if($this->ReadPropertyBoolean("gw_version")
			|| $this->ReadPropertyBoolean("wan_ip")
			|| $this->ReadPropertyBoolean("WAN1availability")
			|| $this->ReadPropertyBoolean("WAN1latency_average")
			|| $this->ReadPropertyBoolean("WAN1time_period")
			|| $this->ReadPropertyBoolean("WAN2availability")
			|| $this->ReadPropertyBoolean("WAN2latency_average")
			|| $this->ReadPropertyBoolean("WAN2time_period")
			|| $this->ReadPropertyBoolean("isp_name")
			|| $this->ReadPropertyBoolean("isp_organization")
		) {
            if ($this->AuthenticateAndGetData("api/stat/sites")) {
				$RawData = $this->GetBuffer("RawData");
				$JSONData = json_decode($RawData, true);
				// $this->SendDebug("JSONData", $JSONData, 0);
				$healthArray = $JSONData['data'][0]['health'];

				$variableArray = array(
					array('ident' => "gw_version", 'json' => "return (isset(\$health['gw_version']) ? \$health['gw_version'] : null);", 'localeName' => "UDM UnifiOS Version"),
					array('ident' => "wan_ip", 'json' => "return (isset(\$health['wan_ip']) ? \$health['wan_ip'] : null);", 'localeName' => "WAN IP active"),
					array('ident' => "WAN1availability", 'json' => "return (isset(\$health['uptime_stats']['WAN']['availability']) ? round(\$health['uptime_stats']['WAN']['availability']) : null);", 'localeName' => "WAN1 availablity"),
					array('ident' => "WAN1latency_average", 'json' => "return (isset(\$health['uptime_stats']['WAN']['latency_average']) ? \$health['uptime_stats']['WAN']['latency_average'] : null);", 'localeName' => "WAN1 latency_average"),
					array('ident' => "WAN1time_period", 'json' => "return (isset(\$health['uptime_stats']['WAN']['time_period']) ? \$health['uptime_stats']['WAN']['time_period'] : null);", 'localeName' => "WAN1 time_period"),
					array('ident' => "WAN2availability", 'json' => "return (isset(\$health['uptime_stats']['WAN2']['availability']) ? round(\$health['uptime_stats']['WAN2']['availability']) : null);", 'localeName' => "WAN2 availablity"),
					array('ident' => "WAN2latency_average", 'json' => "return (isset(\$health['uptime_stats']['WAN2']['latency_average']) ? \$health['uptime_stats']['WAN2']['latency_average'] : null);", 'localeName' => "WAN2 latency_average",),
					array('ident' => "WAN2time_period", 'json' => "return (isset(\$health['uptime_stats']['WAN2']['time_period']) ? \$health['uptime_stats']['WAN2']['time_period'] : null);", 'localeName' => "WAN2 time_period"),
					array('ident' => "isp_name", 'json' => "return (isset(\$health['isp_name']) ? \$health['isp_name'] : null);", 'localeName' => "ISP Name"),
					array('ident' => "isp_organization", 'json' => "return (isset(\$health['isp_organization']) ? \$health['isp_organization'] : null);", 'localeName' => "ISP Organization"),
				);

				foreach ($healthArray as $health) {
					if (isset($health['subsystem']) && 'wan' == $health['subsystem']) {
						foreach ($variableArray as $variable) {
							if ($this->ReadPropertyBoolean($variable['ident'])) {
								if (null !== eval($variable['json'])) {
									$value = eval($variable['json']);
									if (isset($value)) {
										if (isset($variable['valueCorrection'])) {
											eval($variable['valueCorrection']);
										}
		
										if ($value != GetValue($this->GetIDForIdent($variable['ident']))) {
											$this->SendDebug($this->Translate($variable['localeName']), $this->Translate("updated to ").$value, 0);
											SetValue($this->GetIDForIdent($variable['ident']), $value);
										} else {
											$this->SendDebug($this->Translate($variable['localeName']), $this->Translate("no update received")." (".$value.")", 0);
										}
									}
								} else {
									$this->SendDebug($this->Translate($variable['localeName']), $this->Translate("No data"), 0);
								}
							}
						}
					}
				}
			}
		}
	}
}
