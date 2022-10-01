<?php

declare(strict_types=1);

require_once __DIR__.'/../libs/myFunctions.php';  // globale Funktionen

// Modul Prefix
if (!defined('MODUL_PREFIX'))
{
	define("MODUL_PREFIX", "UEM");
}

class UnifiEndpointMonitor extends IPSModule
{
	use myFunctions;

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

		$this->RegisterTimer("Endpoint Monitor", 0, MODUL_PREFIX."_EndpointMonitor(\$_IPS['TARGET']);");
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

		$this->RegisterVariableBoolean('Connected', $this->Translate('Connected'));

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
		$this->MaintainVariable("Accesspoint", $this->Translate("Accesspoint"), vtString, "", $vpos++, $this->ReadPropertyBoolean("DataPointConnection") == 1 && $this->ReadPropertyInteger("ConnectionType") == 0);
		$this->MaintainVariable("Channel", $this->Translate("Channel"), vtInteger, "", $vpos++, $this->ReadPropertyBoolean("DataPointConnection") == 1 && $this->ReadPropertyInteger("ConnectionType") == 0);
		$this->MaintainVariable("Radio", $this->Translate("Radio"), vtString, "", $vpos++, $this->ReadPropertyBoolean("DataPointConnection") == 1 && $this->ReadPropertyInteger("ConnectionType") == 0);
		$this->MaintainVariable("ESSID", $this->Translate("ESS ID"), vtString, "", $vpos++, $this->ReadPropertyBoolean("DataPointConnection") == 1 && $this->ReadPropertyInteger("ConnectionType") == 0);
		$this->MaintainVariable("RSSI", $this->Translate("RSSI"), vtInteger, "", $vpos++, $this->ReadPropertyBoolean("DataPointConnection") == 1 && $this->ReadPropertyInteger("ConnectionType") == 0);
		$this->MaintainVariable("Noise", $this->Translate("Noise"), vtInteger, "", $vpos++, $this->ReadPropertyBoolean("DataPointConnection") == 1 && $this->ReadPropertyInteger("ConnectionType") == 0);
		$this->MaintainVariable("SignalStrength", $this->Translate("Signal Strength"), vtInteger, "", $vpos++, $this->ReadPropertyBoolean("DataPointConnection") == 1 && $this->ReadPropertyInteger("ConnectionType") == 0);

		//Transfer Data
		$vpos = 300;
		$this->MaintainVariable("TXBytes", $this->Translate("TX Megabytes"), vtInteger, "", $vpos++, $this->ReadPropertyBoolean("DataPointTransfer") == 1 && $this->ReadPropertyInteger("ConnectionType") == 0);
		$this->MaintainVariable("RXBytes", $this->Translate("RX Megabytes"), vtInteger, "", $vpos++, $this->ReadPropertyBoolean("DataPointTransfer") == 1 && $this->ReadPropertyInteger("ConnectionType") == 0);
		$this->MaintainVariable("TXPackets", $this->Translate("TX Packets"), vtInteger, "", $vpos++, $this->ReadPropertyBoolean("DataPointTransfer") == 1 && $this->ReadPropertyInteger("ConnectionType") == 0);
		$this->MaintainVariable("RXPackets", $this->Translate("RX Packets"), vtInteger, "", $vpos++, $this->ReadPropertyBoolean("DataPointTransfer") == 1 && $this->ReadPropertyInteger("ConnectionType") == 0);


		$TimerMS = $this->ReadPropertyInteger("Timer") * 1000;
		$this->SetTimerInterval("Endpoint Monitor", $TimerMS);

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
		$ControllerType = $this->ReadPropertyInteger("ControllerType");
		$ServerAddress = $this->ReadPropertyString("ServerAddress");
		$ServerPort = $this->ReadPropertyInteger("ServerPort");
		$Username = $this->ReadPropertyString("UserName");
		$Password = $this->ReadPropertyString("Password");
		$Site = $this->ReadPropertyString("Site");
		$DeviceMac = strtolower($this->ReadPropertyString("DeviceMac"));

		//Change the Unifi API to be called here
		if ("" == $UnifiAPI)
		{
			$Site = $this->ReadPropertyString("Site");
			$UnifiAPI = "api/s/".$Site."/stat/sta"."/".$DeviceMac;
		}

		//Generic Section providing for Authenthication against a DreamMachine or Classic CloudKey
		$Cookie = $this->getCookie($Username, $Password, $ServerAddress, $ServerPort, $ControllerType);

		// Section below will collect and return the RawData
		if (!isset($Cookie) || false == $Cookie)
		{
			return false;
		}
		else
		{
			$RawData = $this->getRawData($Cookie, $ServerAddress, $ServerPort, $UnifiAPI, $ControllerType);
			return $RawData;
		}
	}

	public function EndpointMonitor()
	{
		$ControllerType = $this->ReadPropertyInteger("ControllerType");
		$ServerAddress = $this->ReadPropertyString("ServerAddress");
		$ServerPort = $this->ReadPropertyInteger("ServerPort");
		$Username = $this->ReadPropertyString("UserName");
		$Password = $this->ReadPropertyString("Password");
		$Site = $this->ReadPropertyString("Site");
		$DeviceMac = strtolower($this->ReadPropertyString("DeviceMac"));

		$UnifiAPI = "api/s/".$Site."/stat/sta"."/".$DeviceMac;

		//Generic Section providing for Authenthication against a DreamMachine or Classic CloudKey
		$Cookie = $this->getCookie($Username, $Password, $ServerAddress, $ServerPort, $ControllerType);

		// Section below will collect and return the RawData
		if (!isset($Cookie) || false == $Cookie)
		{
			return false;
		}
		else
		{
			$RawData = $this->getRawData($Cookie, $ServerAddress, $ServerPort, $UnifiAPI, $ControllerType);
		}

		// query JSON file for internet data
		if (false !== $RawData)
		{
			if ($RawData !== "")
			{
				$JSONData = json_decode($RawData, true);
				$DeviceAvailable = $JSONData["meta"]["rc"];

				if ($DeviceAvailable == "ok")
				{
					$Connected = GetValue($this->GetIDForIdent("Connected"));

					if ($Connected == false)
					{
						//after a device gets reconnected, wait until the controller has rebuilt the data set
						$this->SendDebug($this->Translate("Endpoint Monitor"), $this->Translate("Device was disconnected and is now connected again. Module waits for Controller to collect data."), 0);
						IPS_Sleep(10000);
						SetValue($this->GetIDForIdent("Connected"), true);
						$RawData = $this->getRawData($Cookie, $ServerAddress, $ServerPort, $UnifiAPI, $ControllerType);
						$JSONData = json_decode($RawData, true);
					}

					$ConnectionMethod = $JSONData["data"][0]["is_wired"];

					if ($ConnectionMethod == true && $this->ReadPropertyInteger("ConnectionType") == 0)
					{
						$ConnectionConfigError = true;
						$this->SendDebug($this->Translate("Endpoint Monitor"), $this->Translate("Config error - device monitored is a wired device. Please select wired in the module configuration."), 0);
					}
					else
					{
						$ConnectionConfigError = false;
					}

					if ($this->ReadPropertyBoolean("DataPointNetwork") == 1)
					{
						$IPAddress = $JSONData["data"][0]["ip"];
						SetValue($this->GetIDForIdent("IPAddress"), $IPAddress);
						$this->SendDebug($this->Translate("Endpoint Monitor"), $this->Translate("Network Data IP ").$IPAddress, 0);

						if ("" != $JSONData["data"][0]["hostname"])
						{
							$Hostname = $JSONData["data"][0]["hostname"];
						}
						elseif ("" != $JSONData["data"][0]["name"])
						{
							$Hostname = $JSONData["data"][0]["name"];
						}
						else
						{
							$Hostname = "";
						}
						SetValue($this->GetIDForIdent("Hostname"), $Hostname);
						$this->SendDebug($this->Translate("Endpoint Monitor"), $this->Translate("Network Data Hostname ").$Hostname, 0);
						//$Name = $JSONData["data"][0]["name"];
						//SetValue($this->GetIDForIdent("Name"),$Name);
						//$this->SendDebug($this->Translate("Endpoint Monitor"),$this->Translate("Network Data Name ").$Name,0);
					}
					if ($this->ReadPropertyBoolean("DataPointConnection") == 1)
					{
						if (isset($JSONData["data"][0]["satisfaction"])) {
							$Satisfaction = isset($JSONData["data"][0]["satisfaction"]);
							SetValue($this->GetIDForIdent("Satisfaction"), $Satisfaction);
						}
						$this->SendDebug($this->Translate("Endpoint Monitor"), $this->Translate("Connection Data Satisfaction ").$Satisfaction, 0);
						$SLastSeen = $JSONData["data"][0]["last_seen"];
						SetValue($this->GetIDForIdent("LastSeen"), gmdate("Y-m-d H:i:s", $SLastSeen));
						$this->SendDebug($this->Translate("Endpoint Monitor"), $this->Translate("Connection Data Last Seen ").gmdate("Y-m-d H:i:s", $SLastSeen), 0);
						$Uptime = $JSONData["data"][0]["uptime"];
						SetValue($this->GetIDForIdent("Uptime"), round($Uptime / 3600, 0));
						$this->SendDebug($this->Translate("Endpoint Monitor"), $this->Translate("Connection Data Uptime in hours ").round($Uptime / 3600, 0), 0);
					}
					if ($this->ReadPropertyBoolean("DataPointConnection") == 1 && $this->ReadPropertyInteger("ConnectionType") == 0 && $ConnectionConfigError == false)
					{
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
					if ($this->ReadPropertyBoolean("DataPointTransfer") == 1 && $this->ReadPropertyInteger("ConnectionType") == 0 && $ConnectionConfigError == false)
					{
						$TXBytes = $JSONData["data"][0]["tx_bytes"];
						SetValue($this->GetIDForIdent("TXBytes"), $TXBytes / 1000000);
						$this->SendDebug($this->Translate("Endpoint Monitor"), $this->Translate("Transfer Data TXBytes ").$TXBytes / 1000000, 0);
						$RXBytes = $JSONData["data"][0]["rx_bytes"];
						SetValue($this->GetIDForIdent("RXBytes"), $RXBytes / 1000000);
						$this->SendDebug($this->Translate("Endpoint Monitor"), $this->Translate("Transfer Data RXBytes ").$RXBytes / 1000000, 0);
						$TXPackets = $JSONData["data"][0]["tx_packets"];
						SetValue($this->GetIDForIdent("TXPackets"), $TXPackets);
						$this->SendDebug($this->Translate("Endpoint Monitor"), $this->Translate("Transfer Data TXPackets ").$TXPackets, 0);
						$RXPackets = $JSONData["data"][0]["rx_packets"];
						SetValue($this->GetIDForIdent("RXPackets"), $RXPackets);
						$this->SendDebug($this->Translate("Endpoint Monitor"), $this->Translate("Transfer Data RXPackets ").$RXPackets, 0);
					}
				}
				elseif ($DeviceAvailable == "error")
				{
					$this->SendDebug($this->Translate("Endpoint Monitor"), $this->Translate("Device to be monitored is not available / Disconnected"), 0);
					SetValue($this->GetIDForIdent("Connected"), false);
				}
			}
			else
			{
				$this->SendDebug($this->Translate("Endpoint Monitor"), $this->Translate("There does not seem to be any configuration - no data is available from the UniFi"), 0);
			}
		}
	}

	// public function, which is checking the site-name
	public function checkSiteName()
	{
		$ControllerType = $this->ReadPropertyInteger("ControllerType");
		$ServerAddress = $this->ReadPropertyString("ServerAddress");
		$ServerPort = $this->ReadPropertyInteger("ServerPort");
		$Username = $this->ReadPropertyString("UserName");
		$Password = $this->ReadPropertyString("Password");
		$Site = $this->ReadPropertyString("Site");

		return $this->getSiteName($Site, $Username, $Password, $ServerAddress, $ServerPort, $ControllerType);
	}
}
