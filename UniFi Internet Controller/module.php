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

			$this->RegisterPropertyBoolean("WAN1IP",0);
			$this->RegisterPropertyBoolean("WAN2IP",0);

			$this->RegisterPropertyBoolean("version",0);
			$this->RegisterPropertyBoolean("update_available",0);
			$this->RegisterPropertyBoolean("update_downloaded",0);
			$this->RegisterPropertyBoolean("uptime",0);
			$this->RegisterPropertyBoolean("ubnt_device_type",0);
			$this->RegisterPropertyBoolean("udm_version",0);

			$this->RegisterPropertyBoolean("isp_name",0);
			$this->RegisterPropertyBoolean("isp_organization",0);
			$this->RegisterPropertyBoolean("WAN1availability",0);
			$this->RegisterPropertyBoolean("WAN1latency_average",0);
			$this->RegisterPropertyBoolean("WAN1time_period",0);
			$this->RegisterPropertyBoolean("WAN2availability",0);
			$this->RegisterPropertyBoolean("WAN2latency_average",0);
			$this->RegisterPropertyBoolean("WAN2time_period",0);
			
			$this->RegisterTimer("Collect Connection Data",0,"IC_GetInternetData(\$_IPS['TARGET']);");

			if (IPS_VariableProfileExists("IC.TimeS") == false){
				IPS_CreateVariableProfile("IC.TimeS", 1);
				IPS_SetVariableProfileValues("IC.TimeS", 0, 0, 1);
				IPS_SetVariableProfileDigits("IC.TimeS", 2);
				IPS_SetVariableProfileText("IC.TimeS", "", $this->Translate(" seconds"));
				IPS_SetVariableProfileIcon("IC.TimeS",  "Clock");
			}

			if (IPS_VariableProfileExists("IC.TimeMS") == false){
				IPS_CreateVariableProfile("IC.TimeMS", 1);
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
			$this->MaintainVariable("WAN1IP", $this->Translate("WAN1 External IP Address"), vtString, "", $vpos++,  $this->ReadPropertyBoolean("WAN1IP"));

			$this->MaintainVariable("WAN1availability", $this->Translate("WAN1 availability"), vtInteger, "~Intensity.100", $vpos++,  $this->ReadPropertyBoolean("WAN1availability") && 0 == $this->ReadPropertyInteger("ControllerType"));
			$this->MaintainVariable("WAN1latency_average", $this->Translate("WAN1 latency-average"), vtInteger, "IC.TimeMS", $vpos++,  $this->ReadPropertyBoolean("WAN1latency_average") && 0 == $this->ReadPropertyInteger("ControllerType"));
			$this->MaintainVariable("WAN1time_period", $this->Translate("WAN1 time-period"), vtInteger, "IC.TimeS", $vpos++,  $this->ReadPropertyBoolean("WAN1time_period") && 0 == $this->ReadPropertyInteger("ControllerType"));

			$this->MaintainVariable("WAN2IP", $this->Translate("WAN2 External IP Address"), vtString, "", $vpos++,  $this->ReadPropertyBoolean("WAN2IP"));

			$this->MaintainVariable("WAN2availability", $this->Translate("WAN2 availability"), vtInteger, "~Intensity.100", $vpos++,  $this->ReadPropertyBoolean("WAN2availability") && 0 == $this->ReadPropertyInteger("ControllerType"));
			$this->MaintainVariable("WAN2latency_average", $this->Translate("WAN2 latency-average"), vtInteger, "IC.TimeMS", $vpos++,  $this->ReadPropertyBoolean("WAN2latency_average") && 0 == $this->ReadPropertyInteger("ControllerType"));
			$this->MaintainVariable("WAN2time_period", $this->Translate("WAN2 time-period"), vtInteger, "IC.TimeS", $vpos++,  $this->ReadPropertyBoolean("WAN2time_period") && 0 == $this->ReadPropertyInteger("ControllerType"));

			$this->MaintainVariable("isp_name", $this->Translate("ISP Name"), vtString, "", $vpos++,  $this->ReadPropertyBoolean("isp_name") && 0 == $this->ReadPropertyInteger("ControllerType"));
			$this->MaintainVariable("isp_organization", $this->Translate("ISP Organization"), vtString, "", $vpos++,  $this->ReadPropertyBoolean("isp_organization") && 0 == $this->ReadPropertyInteger("ControllerType"));
			$this->MaintainVariable("version", $this->Translate("Unifi Network Version"), vtString, "", $vpos++,  $this->ReadPropertyBoolean("version"));
			$this->MaintainVariable("update_available", $this->Translate("Update available"), vtBoolean, "", $vpos++,  $this->ReadPropertyBoolean("update_available"));
			$this->MaintainVariable("update_downloaded", $this->Translate("Update downloaded"), vtBoolean, "", $vpos++,  $this->ReadPropertyBoolean("update_downloaded"));
			$this->MaintainVariable("uptime", $this->Translate("Uptime"), vtInteger, "~UnixTimestamp", $vpos++,  $this->ReadPropertyBoolean("uptime"));

			$this->MaintainVariable("ubnt_device_type", $this->Translate("UBNT Device Type"), vtString, "", $vpos++,  $this->ReadPropertyBoolean("ubnt_device_type") && 0 == $this->ReadPropertyInteger("ControllerType"));
			$this->MaintainVariable("udm_version", $this->Translate("UDM Version"), vtString, "", $vpos++,  $this->ReadPropertyBoolean("udm_version") && 0 == $this->ReadPropertyInteger("ControllerType"));

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


		public function AuthenticateAndGetData($UnifiAPI = "") {
			
			$ControllerType = $this->ReadPropertyInteger("ControllerType");
			$ServerAdress = $this->ReadPropertyString("ServerAdress");
			$ServerPort = $this->ReadPropertyInteger("ServerPort");
			$Username = $this->ReadPropertyString("UserName");
			$Password = $this->ReadPropertyString("Password");
			$Site = $this->ReadPropertyString("Site");

			////////////////////////////////////////
			//Change the Unifi API to be called here
            if ("" == $UnifiAPI) {
                $UnifiAPI = "api/s/".$Site."/stat/sysinfo";
            }
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

					// login seems to be not successful
					$this->SetStatus(201);
				}
			}

		}

		public function GetInternetData() {

			if($this->ReadPropertyBoolean("WAN1IP")
				|| $this->ReadPropertyBoolean("WAN2IP")
				|| $this->ReadPropertyBoolean("version")
				|| $this->ReadPropertyBoolean("update_available")
				|| $this->ReadPropertyBoolean("update_downloaded")
				|| $this->ReadPropertyBoolean("uptime")
				|| $this->ReadPropertyBoolean("ubnt_device_type")
				|| $this->ReadPropertyBoolean("udm_version")
			)
			{
				// query JSON file for internet data
				$this->AuthenticateAndGetData();
				$RawData = $this->GetBuffer("RawData");
				$JSONData = json_decode($RawData, true);


				// get IP addresses
				$variableArray = array(
							array('ident' => "WAN1IP",	'localeName' => "WAN1 External IP Address", 'index' => 0),
							array('ident' => "WAN2IP",	'localeName' => "WAN2 External IP Address", 'index' => 1),
						);

				foreach ($variableArray as $variable) {
					if ($this->ReadPropertyBoolean($variable['ident'])) {
//					$this->SendDebug("GetInternetData", print_r($JSONData), 0);

						if (isset($JSONData['data'][0]["ip_addrs"][$variable['index']])) {
							$value = $JSONData['data'][0]["ip_addrs"][$variable['index']];
							if (isset($value)) {
								if ($value !== GetValue($this->GetIDForIdent($variable['ident']))) {
									$this->SendDebug($this->Translate($variable['localeName']), $this->Translate("updated to ").$value, 0);
									SetValue($this->GetIDForIdent($variable['ident']), $value);
								} else {
									$this->SendDebug($this->Translate($variable['localeName']), $this->Translate("no update received"), 0);
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
							array('ident' => "update_available",	'localeName' => "Update available"),
							array('ident' => "update_downloaded",	'localeName' => "Update downloaded"),
							array('ident' => "uptime",	'localeName' => "Uptime", 'valueCorrection' => "\$value = (time() - (time() % 60)) - (\$value - (\$value % 60));"),	// value correction to avoid an update for every cycle
						);

				if($this->ReadPropertyInteger("ControllerType") == 0)
				{
					$variableArray[] = array('ident' => "ubnt_device_type",	'localeName' => "UBNT Device Type");
					$variableArray[] = array('ident' => "udm_version",	'localeName' => "UDM Version");
				}

				foreach ($variableArray as $variable) {
					if ($this->ReadPropertyBoolean($variable['ident'])) {
						if (isset($JSONData['data'][0][$variable['ident']])) {
							$value = $JSONData['data'][0][$variable['ident']];
							if (isset($value)) {
								if (isset($variable['valueCorrection'])) {
									eval($variable['valueCorrection']);
								}

								if ($value !== GetValue($this->GetIDForIdent($variable['ident']))) {
									$this->SendDebug($this->Translate($variable['localeName']), $this->Translate("updated to ").$value, 0);
									SetValue($this->GetIDForIdent($variable['ident']), $value);
								} else {
									$this->SendDebug($this->Translate($variable['localeName']), $this->Translate("no update received"), 0);
								}
							}
						} else {
							$this->SendDebug($this->Translate($variable['localeName']), $this->Translate("No data"), 0);
						}
					}
				}
			}


			// special properties of Unifi DreamMachine
			if($this->ReadPropertyInteger("ControllerType") == 0 
				&& ($this->ReadPropertyBoolean("WAN1availability")
					|| $this->ReadPropertyBoolean("WAN1latency_average")
					|| $this->ReadPropertyBoolean("WAN1time_period")
					|| $this->ReadPropertyBoolean("WAN2availability")
					|| $this->ReadPropertyBoolean("WAN2latency_average")
					|| $this->ReadPropertyBoolean("WAN2time_period")
					|| $this->ReadPropertyBoolean("isp_name")
					|| $this->ReadPropertyBoolean("isp_organization")
				)
			)
			{
                // query JSON file for internet data
                $Site = $this->ReadPropertyString("Site");
                $this->AuthenticateAndGetData("api/stat/sites");
                $RawData = $this->GetBuffer("RawData");
                $JSONData = json_decode($RawData, true);
//				$this->SendDebug("GetInternetData2", print_r($JSONData), 0);
            
                // get everything else
                $healthArray = $JSONData['data'][0]['health'];

                $variableArray = array(
                array('ident' => "WAN1availability", 'json' => "return (isset(\$health['uptime_stats']['WAN']['availability']) ? \$health['uptime_stats']['WAN']['availability'] : null);", 'localeName' => "WAN1 availablity"),
                array('ident' => "WAN1latency_average", 'json' => "return (isset(\$health['uptime_stats']['WAN']['latency_average']) ? \$health['uptime_stats']['WAN']['latency_average'] : null);", 'localeName' => "WAN1 latency_average"),
                array('ident' => "WAN1time_period", 'json' => "return (isset(\$health['uptime_stats']['WAN']['time_period']) ? \$health['uptime_stats']['WAN']['time_period'] : null);", 'localeName' => "WAN1 time_period"),
                array('ident' => "WAN2availability", 'json' => "return (isset(\$health['uptime_stats']['WAN2']['availability']) ? \$health['uptime_stats']['WAN2']['availability'] : null);", 'localeName' => "WAN2 availablity"),
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
        
                                        if ($value !== GetValue($this->GetIDForIdent($variable['ident']))) {
                                            $this->SendDebug($this->Translate($variable['localeName']), $this->Translate("updated to ").$value, 0);
                                            SetValue($this->GetIDForIdent($variable['ident']), $value);
                                        } else {
                                            $this->SendDebug($this->Translate($variable['localeName']), $this->Translate("no update received"), 0);
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
