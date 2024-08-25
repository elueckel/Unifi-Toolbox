<?php

declare(strict_types=1);

require_once __DIR__.'/../libs/myFunctions.php';  // globale Funktionen
include_once __DIR__.'/../libs/timetest.php';

// Modul Prefix
if (!defined('MODUL_PREFIX'))
{
	define("MODUL_PREFIX", "UMEM");
}

class UnifiMultiEndpointMonitor extends IPSModule
{
	use myFunctions;
    use TestTime;

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

		$this->RegisterPropertyString("Devices", "");

		//$this->RegisterPropertyInteger("ConnectionType", 0);

		$this->RegisterPropertyBoolean("DataPointNetwork", 0);
		$this->RegisterPropertyBoolean("DataPointConnection", 0);
		$this->RegisterPropertyBoolean("DataPointTransfer", 0);

		$this->RegisterTimer("UniFi Multi Endpoint Monitor", 0, MODUL_PREFIX."_EndpointMonitor(\$_IPS['TARGET']);");
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
		$vpos = 10;

		//$this->RegisterVariableBoolean('Connected', $this->Translate('Connected'));

		$DevicesList = $this->ReadPropertyString("Devices");
		$DevicesJSON = json_decode($DevicesList, true);

		if (isset($DevicesJSON))
		{
			foreach ($DevicesJSON as $Device)
			{
				$DeviceName = $Device["varDeviceName"];
				$DeviceMac = $this->removeInvalidChars($Device["varDeviceMAC"], true);
				$ConnectionType = $Device["varDeviceConnectionType"];

				$vpos = 10;

				$this->RegisterVariableBoolean($DeviceMac."_Connected", $DeviceName.$this->Translate(' Connected'));

				//Network Data
				$this->MaintainVariable($DeviceMac."IPAddress", $DeviceName.$this->Translate(" IP Address"), vtString, "", $vpos++, $this->ReadPropertyBoolean("DataPointNetwork") == 1);
				$this->MaintainVariable($DeviceMac."Hostname", $DeviceName.$this->Translate(" Hostname"), vtString, "", $vpos++, $this->ReadPropertyBoolean("DataPointNetwork") == 1);

				//Connection Data General
				$vpos = 100;
				$this->MaintainVariable($DeviceMac."Satisfaction", $DeviceName.$this->Translate(" Satisfaction"), vtInteger, "", $vpos++, $this->ReadPropertyBoolean("DataPointConnection") == 1);
				$this->MaintainVariable($DeviceMac."LastSeen", $DeviceName.$this->Translate(" Last Seen"), vtString, "", $vpos++, $this->ReadPropertyBoolean("DataPointConnection") == 1);
				$this->MaintainVariable($DeviceMac."Uptime", $DeviceName.$this->Translate(" Uptime in hours"), vtInteger, "", $vpos++, $this->ReadPropertyBoolean("DataPointConnection") == 1);
				//Connection Data Wired
				$vpos = 300;
				//$this->MaintainVariable("SwitchPort", $this->Translate("Switch Port"), vtString, "", $vpos++, $this->ReadPropertyBoolean("DataPointConnection") == 1 AND $this->ReadPropertyInteger("ConnectionType") == 1);
				//$this->MaintainVariable("SwitchMAC", $this->Translate("Switch MAC"), vtString, "", $vpos++, $this->ReadPropertyBoolean("DataPointConnection") == 1 AND $this->ReadPropertyInteger("ConnectionType") == 1);
				//Connection Data Wireless
				$vpos = 500;
				$this->MaintainVariable($DeviceMac."Accesspoint", $DeviceName.$this->Translate(" Accesspoint"), vtString, "", $vpos++, $this->ReadPropertyBoolean("DataPointConnection") == 1 && $ConnectionType == 0);
				$this->MaintainVariable($DeviceMac."Channel", $DeviceName.$this->Translate(" Channel"), vtInteger, "", $vpos++, $this->ReadPropertyBoolean("DataPointConnection") == 1 && $ConnectionType == 0);
				$this->MaintainVariable($DeviceMac."Radio", $DeviceName.$this->Translate(" Radio"), vtString, "", $vpos++, $this->ReadPropertyBoolean("DataPointConnection") == 1 && $ConnectionType == 0);
				$this->MaintainVariable($DeviceMac."ESSID", $DeviceName.$this->Translate(" ESS ID"), vtString, "", $vpos++, $this->ReadPropertyBoolean("DataPointConnection") == 1 && $ConnectionType == 0);
				$this->MaintainVariable($DeviceMac."RSSI", $DeviceName.$this->Translate(" RSSI"), vtInteger, "", $vpos++, $this->ReadPropertyBoolean("DataPointConnection") == 1 && $ConnectionType == 0);
				$this->MaintainVariable($DeviceMac."Noise", $DeviceName.$this->Translate(" Noise"), vtInteger, "", $vpos++, $this->ReadPropertyBoolean("DataPointConnection") == 1 && $ConnectionType == 0);
				$this->MaintainVariable($DeviceMac."SignalStrength", $DeviceName.$this->Translate(" Signal Strength"), vtInteger, "", $vpos++, $this->ReadPropertyBoolean("DataPointConnection") == 1 && $ConnectionType == 0);

				//Transfer Data
				$vpos = 600;
				$this->MaintainVariable($DeviceMac."TXBytes", $DeviceName.$this->Translate(" TX Megabytes"), vtInteger, "", $vpos++, $this->ReadPropertyBoolean("DataPointTransfer") == 1 && $ConnectionType == 0);
				$this->MaintainVariable($DeviceMac."RXBytes", $DeviceName.$this->Translate(" RX Megabytes"), vtInteger, "", $vpos++, $this->ReadPropertyBoolean("DataPointTransfer") == 1 && $ConnectionType == 0);
				$this->MaintainVariable($DeviceMac."TXPackets", $DeviceName.$this->Translate(" TX Packets"), vtInteger, "", $vpos++, $this->ReadPropertyBoolean("DataPointTransfer") == 1 && $ConnectionType == 0);
				$this->MaintainVariable($DeviceMac."RXPackets", $DeviceName.$this->Translate(" RX Packets"), vtInteger, "", $vpos++, $this->ReadPropertyBoolean("DataPointTransfer") == 1 && $ConnectionType == 0);
			}
		}

		$TimerMS = $this->ReadPropertyInteger("Timer") * 1000;
		$this->SetTimerInterval("UniFi Multi Endpoint Monitor", $TimerMS);

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
			//$UnifiAPI = "api/s/".$Site."/stat/sta"."/".$DeviceMac;
			$UnifiAPI = "api/s/".$Site."/stat/sta";
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

		//$UnifiAPI = "api/s/".$Site."/stat/sta"."/".$DeviceMac;
		$UnifiAPI = "api/s/".$Site."/stat/sta";

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


		if (false !== $RawData)
		{
			if ($RawData !== "")
			{
				$JSONData = json_decode($RawData, true);
				$ActiveDevices = $JSONData;

				//Load devices from config form
				$DevicesList = $this->ReadPropertyString("Devices");
				$DevicesJSON = json_decode($DevicesList, true);

				foreach ($DevicesJSON as $Device)
				{
					//Build a clean array out of the devices mentioned in the config form with : or -
					$DeviceMac = $this->removeInvalidChars($Device["varDeviceMAC"], true);
					$DeviceName = $Device["varDeviceName"];
					$ConnectionType = $Device["varDeviceConnectionType"];
					$Connected = false;

					//Itterate through all device and check if one of them matches the list in the config form.
					foreach ($ActiveDevices["data"] as $Index => $DeviceFromController)
					{
						$DeviceFromControllerClean = $this->removeInvalidChars($DeviceFromController["mac"], true);

						if ($DeviceMac == $DeviceFromControllerClean)
						{
							$this->SendDebug($this->Translate("Endpoint Monitor"), $this->Translate("Device with Name was found: ").$DeviceName, 0);
							$Connected = true;				
							$ConnectionMethod = $DeviceFromController["is_wired"];
							$this->SendDebug($this->Translate("Endpoint Monitor"), $this->Translate("Setze Wert"), 0);
						$this->SetValue($this->GetIDForIdent($DeviceMac."_Connected"), $Connected);

							if ($ConnectionMethod == true && $ConnectionType == 0)
							{
								$ConnectionConfigError = true;
								//$this->SendDebug($this->Translate("Endpoint Monitor"), $this->Translate("Config error - device monitored is a wired device. Please select wired in the module configuration."), 0);
							}
							else
							{
								$ConnectionConfigError = false;
							}

							if ($this->ReadPropertyBoolean("DataPointNetwork") == 1)
							{	
								if (isset($DeviceFromController["ip"])) {
									$IPAddress = $DeviceFromController["ip"];
									$this->SetValue($this->GetIDForIdent($DeviceMac."IPAddress"), $IPAddress);
									$this->SendDebug($this->Translate("Endpoint Monitor"), $this->Translate("Network Data IP ").$IPAddress, 0);
								}
								if ("" != $DeviceFromController["hostname"])
								{
									$Hostname = $DeviceFromController["hostname"];
								}
								else
								{
									$Hostname = "";
								}
								$this->SetValue($this->GetIDForIdent($DeviceMac."Hostname"), $Hostname);
								$this->SendDebug($this->Translate("Endpoint Monitor"), $this->Translate("Network Data Hostname ").$Hostname, 0);
								$Connected = true;	
							}

							if ($this->ReadPropertyBoolean("DataPointConnection") == 1)
							{
								if (isset($DeviceFromController["satisfaction"])) {
									$Satisfaction = $DeviceFromController["satisfaction"];
									//$Satisfaction = isset($DeviceMac["satisfaction"]);
									$this->SetValue($this->GetIDForIdent($DeviceMac."Satisfaction"), $Satisfaction);
									$this->SendDebug($this->Translate("Endpoint Monitor"), $this->Translate("Connection Data Satisfaction ").$Satisfaction, 0);
								}
								//$this->SendDebug($this->Translate("Endpoint Monitor"), $this->Translate("Connection Data Satisfaction ").$Satisfaction, 0);
								$SLastSeen = $DeviceFromController["last_seen"];
								$SLastSeen = $SLastSeen + date("Z");
								$this->SetValue($this->GetIDForIdent($DeviceMac."LastSeen"), gmdate("Y-m-d H:i:s", $SLastSeen));
								$this->SendDebug($this->Translate("Endpoint Monitor"), $this->Translate("Connection Data Last Seen ").gmdate("Y-m-d H:i:s", $SLastSeen), 0);
								$Uptime = $DeviceFromController["uptime"];
								$this->SetValue($this->GetIDForIdent($DeviceMac."Uptime"), round($Uptime / 3600, 0));
								$this->SendDebug($this->Translate("Endpoint Monitor"), $this->Translate("Connection Data Uptime in hours ").round($Uptime / 3600, 0), 0);
							}

							if ($this->ReadPropertyBoolean("DataPointConnection") == 1 && $ConnectionType == 0 && $ConnectionConfigError == false);
							{
								$Accesspoint = $DeviceFromController["ap_mac"];
								$this->SetValue($this->GetIDForIdent($DeviceMac."Accesspoint"), $Accesspoint);
								$this->SendDebug($this->Translate("Endpoint Monitor"), $this->Translate("Connection Data Accesspoint ").$Accesspoint, 0);
								$Channel = $DeviceFromController["channel"];
								$this->SetValue($this->GetIDForIdent($DeviceMac."Channel"), $Channel);
								$this->SendDebug($this->Translate("Endpoint Monitor"), $this->Translate("Connection Data Channel ").$Channel, 0);
								$Radio = $DeviceFromController["radio"];
								$this->SetValue($this->GetIDForIdent($DeviceMac."Radio"), $Radio);
								$this->SendDebug($this->Translate("Endpoint Monitor"), $this->Translate("Connection Data Radio ").$Radio, 0);
								$ESSID = $DeviceFromController["essid"];
								$this->SetValue($this->GetIDForIdent($DeviceMac."ESSID"), $ESSID);
								$this->SendDebug($this->Translate("Endpoint Monitor"), $this->Translate("Connection Data ESSID ").$ESSID, 0);
								$RSSI = $DeviceFromController["rssi"];
								$this->SetValue($this->GetIDForIdent($DeviceMac."RSSI"), $RSSI);
								$this->SendDebug($this->Translate("Endpoint Monitor"), $this->Translate("Connection Data RSSI ").$RSSI, 0);
								$Noise = $DeviceFromController["noise"];
								$this->SetValue($this->GetIDForIdent($DeviceMac."Noise"), $Noise);
								$this->SendDebug($this->Translate("Endpoint Monitor"), $this->Translate("Connection Data Noise ").$Noise, 0);
								$SignalStrength = $DeviceFromController["signal"];
								$this->SetValue($this->GetIDForIdent($DeviceMac."SignalStrength"), $SignalStrength);
								$this->SendDebug($this->Translate("Endpoint Monitor"), $this->Translate("Connection Data SignalStrength ").$SignalStrength, 0);
							}
							if ($this->ReadPropertyBoolean("DataPointTransfer") == 1 && $ConnectionType == 0 && $ConnectionConfigError == false)
							{
								$TXBytes = $DeviceFromController["tx_bytes"];
								$this->SetValue($this->GetIDForIdent($DeviceMac."TXBytes"), $TXBytes / 1000000);
								$this->SendDebug($this->Translate("Endpoint Monitor"), $this->Translate("Transfer Data TXBytes ").$TXBytes / 1000000, 0);
								$RXBytes = $DeviceFromController["rx_bytes"];
								$this->SetValue($this->GetIDForIdent($DeviceMac."RXBytes"), $RXBytes / 1000000);
								$this->SendDebug($this->Translate("Endpoint Monitor"), $this->Translate("Transfer Data RXBytes ").$RXBytes / 1000000, 0);
								$TXPackets = $DeviceFromController["tx_packets"];
								$this->SetValue($this->GetIDForIdent($DeviceMac."TXPackets"), $TXPackets);
								$this->SendDebug($this->Translate("Endpoint Monitor"), $this->Translate("Transfer Data TXPackets ").$TXPackets, 0);
								$RXPackets = $DeviceFromController["rx_packets"];
								$this->SetValue($this->GetIDForIdent($DeviceMac."RXPackets"), $RXPackets);
								$this->SendDebug($this->Translate("Endpoint Monitor"), $this->Translate("Transfer Data RXPackets ").$RXPackets, 0);
							}

						}
						else
						{
							//$this->SendDebug($this->Translate("Endpoint Monitor"), $this->Translate("NOT found - Device: ").$DeviceName, 0);
						}
						$this->SendDebug($this->Translate("Endpoint Monitor"), $this->Translate("Setze Wert").$Connected, 0);
						$SetValue($this->GetIDForIdent($DeviceMac."_Connected"), $Connected);
					}
				}
			}
			else
			{
				$this->SendDebug($this->Translate("Endpoint Monitor"), $this->Translate("There does not seem to be any configuration - no data is available from the UniFi"), 0);
			}
		}
	}	

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
