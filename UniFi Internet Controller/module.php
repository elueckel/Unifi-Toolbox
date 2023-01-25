<?php

declare(strict_types=1);

require_once __DIR__.'/../libs/myFunctions.php';  // globale Funktionen
include_once __DIR__.'/../libs/timetest.php';

// Modul Prefix
if (!defined('MODUL_PREFIX'))
{
	define("MODUL_PREFIX", "UIC");
}

class UniFiDMInternetController extends IPSModule
{
	use myFunctions;
	use TestTime;

	public function Create()
	{
		//Never delete this line!
		parent::Create();

		//$this->RegisterPropertyInteger("ControllerType", 0);
		$this->RegisterPropertyString("ServerAddress", "192.168.1.1");
		$this->RegisterPropertyInteger("ServerPort", "443");
		$this->RegisterPropertyString("Site", "default");
		$this->RegisterPropertyString("UserName", "");
		$this->RegisterPropertyString("Password", "");
		$this->RegisterPropertyInteger("Timer", "0");

		$this->RegisterPropertyBoolean("WAN1IP", 0);
		$this->RegisterPropertyBoolean("WAN2IP", 0);

		$this->RegisterPropertyBoolean("version", 0);
		$this->RegisterPropertyBoolean("previous_version", 0);
		$this->RegisterPropertyBoolean("update_available", 0);
		$this->RegisterPropertyBoolean("update_downloaded", 0);
		$this->RegisterPropertyBoolean("uptime", 0);
		$this->RegisterPropertyBoolean("ubnt_device_type", 0);
		$this->RegisterPropertyBoolean("udm_version", 0);

		$this->RegisterPropertyBoolean("gw_version", 0);
		$this->RegisterPropertyBoolean("isp_name", 0);
		$this->RegisterPropertyBoolean("isp_organization", 0);
		$this->RegisterPropertyBoolean("wan_ip", 0);
		$this->RegisterPropertyBoolean("WAN1availability", 0);
		$this->RegisterPropertyBoolean("WAN1latency_average", 0);
		$this->RegisterPropertyBoolean("WAN1time_period", 0);
		$this->RegisterPropertyBoolean("WAN2availability", 0);
		$this->RegisterPropertyBoolean("WAN2latency_average", 0);
		$this->RegisterPropertyBoolean("WAN2time_period", 0);
		$this->RegisterPropertyBoolean("xput_up", 0);
		$this->RegisterPropertyBoolean("xput_down", 0);
		$this->RegisterPropertyBoolean("speedtest_lastrun", 0);

		$this->RegisterTimer("Collect Connection Data", 0, MODUL_PREFIX."_GetInternetData(\$_IPS['TARGET']);");

		$this->createVarProfile(MODUL_PREFIX.".TimeS", vtInteger, $this->Translate(" seconds"), 0, 0, 1, 2, "Clock");
		$this->createVarProfile(MODUL_PREFIX.".TimeMS", vtInteger, $this->Translate(" milliseconds"), 0, 0, 1, 2, "Clock");
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

		$vpos = 100;
		// subsystem wan
		$this->MaintainVariable("wan_ip", $this->Translate("WAN IP active"), vtString, "", $vpos++, $this->ReadPropertyBoolean("wan_ip"));
		$this->MaintainVariable("WAN1IP", $this->Translate("WAN1 External IP Address"), vtString, "", $vpos++, $this->ReadPropertyBoolean("WAN1IP"));
		$this->MaintainVariable("WAN1availability", $this->Translate("WAN1 availability"), vtInteger, "~Intensity.100", $vpos++, $this->ReadPropertyBoolean("WAN1availability"));
		$this->MaintainVariable("WAN1latency_average", $this->Translate("WAN1 latency-average"), vtInteger, MODUL_PREFIX.".TimeMS", $vpos++, $this->ReadPropertyBoolean("WAN1latency_average"));
		$this->MaintainVariable("WAN1time_period", $this->Translate("WAN1 time-period"), vtInteger, MODUL_PREFIX.".TimeS", $vpos++, $this->ReadPropertyBoolean("WAN1time_period"));
		$this->MaintainVariable("WAN2IP", $this->Translate("WAN2 External IP Address"), vtString, "", $vpos++, $this->ReadPropertyBoolean("WAN2IP"));
		$this->MaintainVariable("WAN2availability", $this->Translate("WAN2 availability"), vtInteger, "~Intensity.100", $vpos++, $this->ReadPropertyBoolean("WAN2availability"));
		$this->MaintainVariable("WAN2latency_average", $this->Translate("WAN2 latency-average"), vtInteger, MODUL_PREFIX.".TimeMS", $vpos++, $this->ReadPropertyBoolean("WAN2latency_average"));
		$this->MaintainVariable("WAN2time_period", $this->Translate("WAN2 time-period"), vtInteger, MODUL_PREFIX.".TimeS", $vpos++, $this->ReadPropertyBoolean("WAN2time_period"));
		$this->MaintainVariable("isp_name", $this->Translate("ISP Name"), vtString, "", $vpos++, $this->ReadPropertyBoolean("isp_name"));
		$this->MaintainVariable("isp_organization", $this->Translate("ISP Organization"), vtString, "", $vpos++, $this->ReadPropertyBoolean("isp_organization"));
		$this->MaintainVariable("version", $this->Translate("Unifi Network Version"), vtString, "", $vpos++, $this->ReadPropertyBoolean("version"));
		$this->MaintainVariable("previous_version", $this->Translate("Unifi Network Vorgängerversion"), vtString, "", $vpos++, $this->ReadPropertyBoolean("previous_version"));
		$this->MaintainVariable("update_available", $this->Translate("Update available"), vtBoolean, "", $vpos++, $this->ReadPropertyBoolean("update_available"));
		$this->MaintainVariable("update_downloaded", $this->Translate("Update downloaded"), vtBoolean, "", $vpos++, $this->ReadPropertyBoolean("update_downloaded"));
		$this->MaintainVariable("uptime", $this->Translate("Uptime"), vtInteger, "~UnixTimestamp", $vpos++, $this->ReadPropertyBoolean("uptime"));

		// subsystem www
		$this->MaintainVariable("xput_up", $this->Translate("Speed Upload"), vtFloat, "", $vpos++, $this->ReadPropertyBoolean("xput_up"));
		$this->MaintainVariable("xput_down", $this->Translate("Speed Download"), vtFloat, "", $vpos++, $this->ReadPropertyBoolean("xput_down"));
		$this->MaintainVariable("speedtest_lastrun", $this->Translate("Speed Lastrun"), vtInteger, "~UnixTimestamp", $vpos++, $this->ReadPropertyBoolean("speedtest_lastrun"));

		$this->MaintainVariable("ubnt_device_type", $this->Translate("UBNT Device Type"), vtString, "", $vpos++, $this->ReadPropertyBoolean("ubnt_device_type"));
		$this->MaintainVariable("udm_version", $this->Translate("UDM Version"), vtString, "", $vpos++, $this->ReadPropertyBoolean("udm_version"));
		$this->MaintainVariable("gw_version", $this->Translate("UDM UnifiOS Version"), vtString, "", $vpos++, $this->ReadPropertyBoolean("gw_version"));

		$TimerMS = $this->ReadPropertyInteger("Timer") * 1000;
		$this->SetTimerInterval("Collect Connection Data", $TimerMS);

		if (0 == $TimerMS)
		{
			// instance inactive
			$this->SetStatus(104);
		}
		else
		{
			// instance active
			$this->SetStatus(102);
		}
	}


	public function AuthenticateAndGetData(string $UnifiAPI = "")
	{

		//$ControllerType = $this->ReadPropertyInteger("ControllerType");
		$ServerAddress = $this->ReadPropertyString("ServerAddress");
		$ServerPort = $this->ReadPropertyInteger("ServerPort");
		$Username = $this->ReadPropertyString("UserName");
		$Password = $this->ReadPropertyString("Password");

		//Change the Unifi API to be called here
		if ("" == $UnifiAPI)
		{
			$Site = $this->ReadPropertyString("Site");
			$UnifiAPI = "api/s/".$Site."/stat/sysinfo";
		}

		//Generic Section providing for Authenthication against a DreamMachine or Classic CloudKey
		$Cookie = $this->getCookie($Username, $Password, $ServerAddress, $ServerPort);

		// Section below will collect and return the RawData
		if (!isset($Cookie) || false == $Cookie)
		{
			return false;
		}
		else
		{
			$RawData = $this->getRawData($Cookie, $ServerAddress, $ServerPort, $UnifiAPI/*, $ControllerType*/);
			return $RawData;
		}
	}

	public function GetInternetData()
	{
		//$ControllerType = $this->ReadPropertyInteger("ControllerType");
		$ServerAddress = $this->ReadPropertyString("ServerAddress");
		$ServerPort = $this->ReadPropertyInteger("ServerPort");
		$Username = $this->ReadPropertyString("UserName");
		$Password = $this->ReadPropertyString("Password");
		$Site = $this->ReadPropertyString("Site");

		//Generic Section providing for Authenthication against a DreamMachine or Classic CloudKey
		$Cookie = $this->getCookie($Username, $Password, $ServerAddress, $ServerPort);

		if (isset($Cookie) && false !== $Cookie
			&& (
				$this->ReadPropertyBoolean("WAN1IP")
				|| $this->ReadPropertyBoolean("WAN2IP")
				|| $this->ReadPropertyBoolean("version")
				|| $this->ReadPropertyBoolean("previous_version")
				|| $this->ReadPropertyBoolean("update_available")
				|| $this->ReadPropertyBoolean("update_downloaded")
				|| $this->ReadPropertyBoolean("uptime")
				|| $this->ReadPropertyBoolean("ubnt_device_type")
				|| $this->ReadPropertyBoolean("udm_version")
			)
		) {
			// Section below will collect and return the RawData
			$UnifiAPI = "api/s/".$Site."/stat/sysinfo";
			$RawData = $this->getRawData($Cookie, $ServerAddress, $ServerPort, $UnifiAPI/*, $ControllerType*/);

			// query JSON file for internet data
			if (false !== $RawData)
			{
				$JSONData = json_decode($RawData, true);

				// get IP addresses
				$variableArray = array(
					array('ident' => "WAN1IP",	'localeName' => "WAN1 External IP Address", 'index' => 0),
					array('ident' => "WAN2IP",	'localeName' => "WAN2 External IP Address", 'index' => 1),
				);

				foreach ($variableArray as $variable)
				{
					if ($this->ReadPropertyBoolean($variable['ident']))
					{
						if (isset($JSONData['data'][0]["ip_addrs"][$variable['index']]))
						{
							$value = $JSONData['data'][0]["ip_addrs"][$variable['index']];
							if (isset($value))
							{
								if ($value != GetValue($this->GetIDForIdent($variable['ident'])))
								{
									$this->SendDebug($this->Translate($variable['localeName']), $this->Translate("updated to ").$value, 0);
									SetValue($this->GetIDForIdent($variable['ident']), $value);
								}
								else
								{
									$this->SendDebug($this->Translate($variable['localeName']), $this->Translate("no update received")." (".$value.")", 0);
								}
							}
						}
						else
						{
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

				foreach ($variableArray as $variable)
				{
					if ($this->ReadPropertyBoolean($variable['ident']))
					{
						if (isset($JSONData['data'][0][$variable['ident']]))
						{
							$value = $JSONData['data'][0][$variable['ident']];
							if (isset($value))
							{
								if (isset($variable['valueCorrection']))
								{
									eval($variable['valueCorrection']);
								}

								if ($value != GetValue($this->GetIDForIdent($variable['ident'])))
								{
									$this->SendDebug($this->Translate($variable['localeName']), $this->Translate("updated to ").$value, 0);
									SetValue($this->GetIDForIdent($variable['ident']), $value);
								}
								else
								{
									$this->SendDebug($this->Translate($variable['localeName']), $this->Translate("no update received")." (".$value.")", 0);
								}
							}
						}
						else
						{
							$this->SendDebug($this->Translate($variable['localeName']), $this->Translate("No data"), 0);
						}
					}
				}
			}
		}

		if (isset($Cookie) && false !== $Cookie
			&& (
				$this->ReadPropertyBoolean("gw_version")
				|| $this->ReadPropertyBoolean("wan_ip")
				|| $this->ReadPropertyBoolean("WAN1availability")
				|| $this->ReadPropertyBoolean("WAN1latency_average")
				|| $this->ReadPropertyBoolean("WAN1time_period")
				|| $this->ReadPropertyBoolean("WAN2availability")
				|| $this->ReadPropertyBoolean("WAN2latency_average")
				|| $this->ReadPropertyBoolean("WAN2time_period")
				|| $this->ReadPropertyBoolean("isp_name")
				|| $this->ReadPropertyBoolean("isp_organization")
			)
		) {
			// Section below will collect and return the RawData
			$UnifiAPI = "api/stat/sites";
			$RawData = $this->getRawData($Cookie, $ServerAddress, $ServerPort, $UnifiAPI/*, $ControllerType*/);

			// query JSON file for internet data
			if ($RawData)
			{
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
					array('ident' => "WAN2latency_average", 'json' => "return (isset(\$health['uptime_stats']['WAN2']['latency_average']) ? \$health['uptime_stats']['WAN2']['latency_average'] : null);", 'localeName' => "WAN2 latency_average"),
					array('ident' => "WAN2time_period", 'json' => "return (isset(\$health['uptime_stats']['WAN2']['time_period']) ? \$health['uptime_stats']['WAN2']['time_period'] : null);", 'localeName' => "WAN2 time_period"),
					array('ident' => "isp_name", 'json' => "return (isset(\$health['isp_name']) ? \$health['isp_name'] : null);", 'localeName' => "ISP Name"),
					array('ident' => "isp_organization", 'json' => "return (isset(\$health['isp_organization']) ? \$health['isp_organization'] : null);", 'localeName' => "ISP Organization"),
					array('ident' => "xput_up", 'json' => "return (isset(\$health['xput_up']) ? \$health['xput_up'] : null);", 'localeName' => "Speed Upload"),
					array('ident' => "xput_down", 'json' => "return (isset(\$health['xput_down']) ? \$health['xput_down'] : null);", 'localeName' => "Speed Download"),
					array('ident' => "speedtest_lastrun", 'json' => "return (isset(\$health['speedtest_lastrun']) ? \$health['speedtest_lastrun'] : null);", 'localeName' => "Speed Lastrun"),
				);

				foreach ($healthArray as $health)
				{
					if (isset($health['subsystem']) && ('wan' == $health['subsystem'] || 'www' == $health['subsystem']))
					{
						foreach ($variableArray as $variable)
						{
							if ($this->ReadPropertyBoolean($variable['ident']))
							{
								if (null !== eval($variable['json']))
								{
									$value = eval($variable['json']);
									if (isset($value))
									{
										if (isset($variable['valueCorrection']))
										{
											eval($variable['valueCorrection']);
										}

										if ($value != GetValue($this->GetIDForIdent($variable['ident'])))
										{
											$this->SendDebug($this->Translate($variable['localeName']), $this->Translate("updated to ").$value, 0);
											SetValue($this->GetIDForIdent($variable['ident']), $value);
										}
										else
										{
											$this->SendDebug($this->Translate($variable['localeName']), $this->Translate("no update received")." (".$value.")", 0);
										}
									}
								}
								else
								{
									$this->SendDebug($this->Translate($variable['localeName']), $this->Translate("No data"), 0);
								}
							}
						}
					}
				}
			}
		}
	}

	// public function, which is checking the site-name
	public function CheckSiteName()
	{
		//$ControllerType = $this->ReadPropertyInteger("ControllerType");
		$ServerAddress = $this->ReadPropertyString("ServerAddress");
		$ServerPort = $this->ReadPropertyInteger("ServerPort");
		$Username = $this->ReadPropertyString("UserName");
		$Password = $this->ReadPropertyString("Password");
		$Site = $this->ReadPropertyString("Site");

		return $this->getSiteName($Site, $Username, $Password, $ServerAddress, $ServerPort/*, $ControllerType*/);
	}

	// read PortForwarding rules (user defined rules only!)
	public function GetPortForwardRules(bool $printOutput = false)
	{
		//$ControllerType = $this->ReadPropertyInteger("ControllerType");
		$ServerAddress = $this->ReadPropertyString("ServerAddress");
		$ServerPort = $this->ReadPropertyInteger("ServerPort");
		$Username = $this->ReadPropertyString("UserName");
		$Password = $this->ReadPropertyString("Password");
		$Site = $this->ReadPropertyString("Site");

		$UnifiAPI = "api/s/".$Site."/rest/portforward";

		$Cookie = $this->getCookie($Username, $Password, $ServerAddress, $ServerPort/*, $ControllerType*/);
		if (isset($Cookie) && false !== $Cookie)
		{
			$RawData = $this->getRawData($Cookie, $ServerAddress, $ServerPort, $UnifiAPI/*, $ControllerType*/);
			if (defined('DEBUG') && DEBUG)
			{
				echo "\nRawData: ".$RawData."\n";
			}

			// query JSON file for internet data
			if (false !== $RawData)
			{
				if ($RawData !== "")
				{
					$JSONData = json_decode($RawData, true);

					if (is_array($JSONData) && isset($JSONData['data']))
					{
						$jsonArray = $JSONData['data'];

						if ($printOutput)
						{
							print_r($jsonArray);
						}

						return $jsonArray;
					}
				}
				else
				{
					$this->SendDebug("GetPortForwardRules()", $this->Translate("There does not seem to be any configuration - no data is available from the UniFi"), 0);
				}
			}
			else
			{
				// debug output already done in getRawData()
			}
		}

		return false;
	}

	// activate PortForwarding rule (user defined rules only!)
	public function ActivatePortForwardRule(string $ruleId)
	{
		$ControllerType = 0;
		$ServerAddress = $this->ReadPropertyString("ServerAddress");
		$ServerPort = $this->ReadPropertyInteger("ServerPort");
		$Username = $this->ReadPropertyString("UserName");
		$Password = $this->ReadPropertyString("Password");
		$Site = $this->ReadPropertyString("Site");

		$portrulesArray = UIC_GetPortForwardRules($this->InstanceID, false);

		// check, if $ruleId is a valid ID
		$validRule = false;
		foreach($portrulesArray AS $rule)
		{
			if($rule['_id'] == $ruleId)
			{
				$validRule = true;
				break;
			}
		}

		if ($validRule)
		{
			$UnifiAPI = "api/s/".$Site."/rest/portforward/".$ruleId;

			$Cookie = $this->getCookie($Username, $Password, $ServerAddress, $ServerPort/*, $ControllerType*/);
			if (isset($Cookie) && false !== $Cookie)
			{
				//create XSRF Token
				$X_CSRF_Token = $this->createXsrfToken($Cookie);

				if (isset($Cookie))
				{
					$this->SendDebug($this->Translate("ActivatePortForwardRule()"), $this->Translate("Module is authenticated and will try to manage device"), 0);

					// [field] => enabled, [pattern] => true|false
					$CommandToController = json_encode(array(
						"enabled" => true,
					), JSON_UNESCAPED_SLASHES);
					//var_dump($CommandToController);

					if ($ControllerType == 0)
					{
						$MiddlePartURL = "/proxy/network/";
					}
					elseif ($ControllerType == 1)
					{
						$MiddlePartURL = "/";
					}

echo "\nhttps://".$ServerAddress.":".$ServerPort.$MiddlePartURL.$UnifiAPI."\n";

					$ch = curl_init();
					curl_setopt($ch, CURLOPT_POST, true);
					curl_setopt($ch, CURLOPT_URL, "https://".$ServerAddress.":".$ServerPort.$MiddlePartURL.$UnifiAPI);
					curl_setopt($ch, CURLOPT_HTTPHEADER, array('Cookie:'.$Cookie, $X_CSRF_Token));
					curl_setopt($ch, CURLOPT_POSTFIELDS, $CommandToController);
					curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
					curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
					curl_setopt($ch, CURLOPT_SSLVERSION, 'CURL_SSLVERSION_TLSv1');
					curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
					$RawData = curl_exec($ch);
					$HTTP_Code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
					$this->SendDebug($this->Translate("ActivatePortForwardRule()"), $this->Translate("Feedback from UniFi Controller: ").$RawData." / HTTP Message ".$HTTP_Code, 0);

					$ControllerFeedbackComplete = json_decode($RawData, true);
					$ControllerFeedbackOK = $ControllerFeedbackComplete["meta"]["rc"];
					$this->SendDebug($this->Translate("ActivatePortForwardRule()"), $this->Translate("Was operation executed: ").$ControllerFeedbackOK, 0);
					curl_close($ch);
				}

				if (defined('DEBUG') && DEBUG)
				{
					echo "\nRawData: ".$RawData."\n";
				}

				// query JSON file for internet data
				if (false !== $RawData)
				{
					if ($RawData !== "")
					{
						$JSONData = json_decode($RawData, true);

						if (is_array($JSONData) && isset($JSONData['data']))
						{
							$jsonArray = $JSONData['data'];
							
							print_r($jsonArray);

							return true;
						}
					}
					else
					{
						$this->SendDebug("ActivatePortForwardRule()", $this->Translate("There does not seem to be any configuration - no data is available from the UniFi"), 0);
					}
				}
				else
				{
					// debug output already done in getRawData()
				}
			}
		}
		else
		{
			$this->SendDebug("ActivatePortForwardRule()", $this->Translate("The ruleID '").$ruleId.$this->Translate("' is no valid rule ID!"), 0);
		}

		return false;
	}
}
