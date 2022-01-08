<?php

declare(strict_types=1);

require_once __DIR__.'/../libs/myFunctions.php';  // globale Funktionen

// Modul Prefix
if (!defined('MODUL_PREFIX'))
{
	define("MODUL_PREFIX", "UDM");
}

class UniFiDeviceMonitor extends IPSModule
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
		$this->RegisterPropertyInteger("DeviceType", 0);

		$this->RegisterPropertyBoolean("DataPointBasic", 1);
		$this->RegisterPropertyBoolean("DataPointHardware", 0);
		$this->RegisterPropertyBoolean("DataPointSpecific", 0);


		$this->RegisterTimer("Device Monitor", 0, MODUL_PREFIX."_DeviceMonitor(\$_IPS['TARGET']);");
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

		//Basic Data
		$vpos = 100;
		$this->MaintainVariable("DeviceModel", $this->Translate("Device Model"), vtString, "", $vpos++, $this->ReadPropertyBoolean("DataPointBasic") == 1);
		$this->MaintainVariable("SoftwareVersion", $this->Translate("Software Version"), vtString, "", $vpos++, $this->ReadPropertyBoolean("DataPointBasic") == 1);
		//$this->MaintainVariable("Satisfaction", $this->Translate("Satisfaction"), vtString, "", $vpos++, $this->ReadPropertyBoolean("DataPointBasic") == 1);
		$this->MaintainVariable("LastSeen", $this->Translate("Last Seen"), vtString, "", $vpos++, $this->ReadPropertyBoolean("DataPointBasic") == 1);
		$this->MaintainVariable("Uptime", $this->Translate("Uptime in hours"), vtInteger, "", $vpos++, $this->ReadPropertyBoolean("DataPointBasic") == 1);
		//$this->MaintainVariable("IPAddress", $this->Translate("IP Address"), vtString, "", $vpos++, $this->ReadPropertyBoolean("DataPointBasic") == 1);
		$this->MaintainVariable("Name", $this->Translate("Device Name"), vtString, "", $vpos++, $this->ReadPropertyBoolean("DataPointBasic") == 1);

		//Hardware Data
		$vpos = 200;
		$this->MaintainVariable("CPULoad", $this->Translate("CPU Load"), vtFloat, "", $vpos++, $this->ReadPropertyBoolean("DataPointHardware") == 1);
		$this->MaintainVariable("MemoryLoad", $this->Translate("Memory Load"), vtFloat, "", $vpos++, $this->ReadPropertyBoolean("DataPointHardware") == 1);


		//Device Specific Data Connection Data UDM/USG
		$vpos = 300;
		$this->MaintainVariable("WAN1IP", $this->Translate("WAN1 IP"), vtString, "", $vpos++, $this->ReadPropertyBoolean("DataPointSpecific") == 1 && $this->ReadPropertyInteger("DeviceType") == 0);
		$this->MaintainVariable("WAN1TXBytes", $this->Translate("WAN 1 TX Megabytes"), vtInteger, "", $vpos++, $this->ReadPropertyBoolean("DataPointSpecific") == 1 && $this->ReadPropertyInteger("DeviceType") == 0);
		$this->MaintainVariable("WAN1RXBytes", $this->Translate("WAN 1 RX Megabytes"), vtInteger, "", $vpos++, $this->ReadPropertyBoolean("DataPointSpecific") == 1 && $this->ReadPropertyInteger("DeviceType") == 0);
		$this->MaintainVariable("WAN1TXPackets", $this->Translate("WAN 1 TX Packets"), vtInteger, "", $vpos++, $this->ReadPropertyBoolean("DataPointSpecific") == 1 && $this->ReadPropertyInteger("DeviceType") == 0);
		$this->MaintainVariable("WAN1RXPackets", $this->Translate("WAN 1 RX Packets"), vtInteger, "", $vpos++, $this->ReadPropertyBoolean("DataPointSpecific") == 1 && $this->ReadPropertyInteger("DeviceType") == 0);
		$this->MaintainVariable("WAN1TXErrors", $this->Translate("WAN 1 TX Errors"), vtInteger, "", $vpos++, $this->ReadPropertyBoolean("DataPointSpecific") == 1 && $this->ReadPropertyInteger("DeviceType") == 0);
		$this->MaintainVariable("WAN1RXErrors", $this->Translate("WAN 1 RX Errors"), vtInteger, "", $vpos++, $this->ReadPropertyBoolean("DataPointSpecific") == 1 && $this->ReadPropertyInteger("DeviceType") == 0);

		$TimerMS = $this->ReadPropertyInteger("Timer") * 1000;
		$this->SetTimerInterval("Device Monitor", $TimerMS);

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
		$DeviceMac = strtolower($this->ReadPropertyString("DeviceMac"));

		//Change the Unifi API to be called here
		if ("" == $UnifiAPI)
		{
			$Site = $this->ReadPropertyString("Site");
			$UnifiAPI = "api/s/".$Site."/stat/device"."/".$DeviceMac;
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

	public function DeviceMonitor()
	{
		$Site = $this->ReadPropertyString("Site");
		$DeviceMac = strtolower($this->ReadPropertyString("DeviceMac"));

		$RawData = $this->AuthenticateAndGetData("api/s/".$Site."/stat/device"."/".$DeviceMac);

		// query JSON file for internet data
		if (false !== $RawData && $RawData !== "")
		{
			$JSONData = json_decode($RawData, true);

			if (isset($JSONData["data"][0]["model"]))
			{
				$DeviceModel = $JSONData["data"][0]["model"];
				$UnfiInternetDeviceArray = array("UDM", "UGW4", "UGW3", "UDMPRO");

				if (!in_array($DeviceModel, $UnfiInternetDeviceArray) && $this->ReadPropertyInteger("DeviceType") == 0)
				{
					$DeviceConfigError = true;
					$this->SendDebug($this->Translate("Device Monitor"), $this->Translate("Config error - device type set to UDM, USG etc. but is no such device."), 0);
				}
				else
				{
					$DeviceConfigError = false;
				}

				if ($this->ReadPropertyBoolean("DataPointBasic") == 1)
				{
					$DeviceModel = $JSONData["data"][0]["model"];
					SetValue($this->GetIDForIdent("DeviceModel"), $DeviceModel);
					$this->SendDebug($this->Translate("Device Monitor"), $this->Translate("Device Model ").$DeviceModel, 0);
					$SoftwareVersion = $JSONData["data"][0]["version"];
					SetValue($this->GetIDForIdent("SoftwareVersion"), $SoftwareVersion);
					$this->SendDebug($this->Translate("Device Monitor"), $this->Translate("Software Version ").$SoftwareVersion, 0);
					//$Satisfaction = $JSONData["data"][0]["satisfaction"];
					//SetValue($this->GetIDForIdent("Satisfaction"),$Satisfaction);
					//$this->SendDebug($this->Translate("Device Monitor"),$this->Translate("Device Satisfaction ").$Satisfaction,0);
					$SLastSeen = $JSONData["data"][0]["last_seen"];
					SetValue($this->GetIDForIdent("LastSeen"), gmdate("Y-m-d H:i:s", $SLastSeen));
					$this->SendDebug($this->Translate("Device Monitor"), $this->Translate("Connection Data Last Seen ").gmdate("Y-m-d H:i:s", $SLastSeen), 0);
					$Uptime = $JSONData["data"][0]["uptime"];
					SetValue($this->GetIDForIdent("Uptime"), round($Uptime / 3600, 0));
					$this->SendDebug($this->Translate("Device Monitor"), $this->Translate("Connection Data Uptime in hours ").round($Uptime / 3600, 0), 0);
					if (isset($JSONData["data"][0]["name"]))
					{
						$Name = $JSONData["data"][0]["name"];
						SetValue($this->GetIDForIdent("Name"), $Name);
						$this->SendDebug($this->Translate("Device Monitor"), $this->Translate("Devicename ").$Name, 0);
					}
				}
				if ($this->ReadPropertyBoolean("DataPointHardware") == 1)
				{
					$CPULoad = $JSONData["data"][0]["system-stats"]["cpu"];
					SetValue($this->GetIDForIdent("CPULoad"), $CPULoad);
					$this->SendDebug($this->Translate("Device Monitor"), $this->Translate("CPU Load ").$CPULoad, 0);
					$MemoryLoad = $JSONData["data"][0]["system-stats"]["mem"];
					SetValue($this->GetIDForIdent("MemoryLoad"), $MemoryLoad);
					$this->SendDebug($this->Translate("Device Monitor"), $this->Translate("Memory Load ").$MemoryLoad, 0);
				}
				if ($this->ReadPropertyBoolean("DataPointSpecific") == 1 && $this->ReadPropertyInteger("DeviceType") == 0 && $DeviceConfigError == false)
				{
					$WAN1IP = $JSONData["data"][0]["wan1"]["ip"];
					SetValue($this->GetIDForIdent("WAN1IP"), $WAN1IP);
					$this->SendDebug($this->Translate("Device Monitor"), $this->Translate("Connection Data WAN 1 IP ").$WAN1IP, 0);

					$WAN1TXBytes = $JSONData["data"][0]["wan1"]["tx_bytes"];
					SetValue($this->GetIDForIdent("WAN1TXBytes"), $WAN1TXBytes / 1000000);
					$this->SendDebug($this->Translate("Device Monitor"), $this->Translate("Transfer Data WAN 1 TX Bytes ").$WAN1TXBytes, 0);
					$WAN1RXBytes = $JSONData["data"][0]["wan1"]["rx_bytes"];
					SetValue($this->GetIDForIdent("WAN1RXBytes"), $WAN1RXBytes / 1000000);
					$this->SendDebug($this->Translate("Endpoint Monitor"), $this->Translate("Transfer Data WAN 1 RX Bytes ").$WAN1RXBytes, 0);
					$WAN1TXPackets = $JSONData["data"][0]["wan1"]["tx_packets"];
					SetValue($this->GetIDForIdent("WAN1TXPackets"), $WAN1TXPackets);
					$this->SendDebug($this->Translate("Device Monitor"), $this->Translate("Transfer Data WAN 1 TX Packets ").$WAN1TXPackets, 0);
					$WAN1RXPackets = $JSONData["data"][0]["wan1"]["tx_packets"];
					SetValue($this->GetIDForIdent("WAN1RXPackets"), $WAN1RXPackets);
					$this->SendDebug($this->Translate("Device Monitor"), $this->Translate("Transfer Data WAN 1 RXPackets ").$WAN1RXPackets, 0);
					$WAN1TXErrors = $JSONData["data"][0]["wan1"]["tx_errors"];
					SetValue($this->GetIDForIdent("WAN1TXErrors"), $WAN1TXErrors);
					$this->SendDebug($this->Translate("Device Monitor"), $this->Translate("Transfer Data WAN 1 TX Errors ").$WAN1TXErrors, 0);
					$WAN1RXErrors = $JSONData["data"][0]["wan1"]["rx_errors"];
					SetValue($this->GetIDForIdent("WAN1RXErrors"), $WAN1RXErrors);
					$this->SendDebug($this->Translate("Device Monitor"), $this->Translate("Transfer Data WAN 1 RX Errors ").$WAN1RXErrors, 0);
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
