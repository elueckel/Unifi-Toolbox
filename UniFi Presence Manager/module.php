<?php

declare(strict_types=1);

require_once __DIR__.'/../libs/myFunctions.php';  // globale Funktionen

// Modul Prefix
if (!defined('MODUL_PREFIX'))
{
	define("MODUL_PREFIX", "UPM");
}

class UniFiPresenceManager extends IPSModule
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
		$this->RegisterPropertyBoolean("GeneralPresenceUpdatedVariable", "0");

		$this->RegisterPropertyString("Devices", "");

		$this->RegisterTimer("Check Presence", 0, MODUL_PREFIX."_CheckPresence(\$_IPS['TARGET']);");
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

		//Create Devices mentioned in configuration
		$DevicesList = $this->ReadPropertyString("Devices");
		$DevicesJSON = json_decode($DevicesList, true);
		//var_dump($DevicesJSON);

		if (isset($DevicesJSON))
		{
			foreach ($DevicesJSON as $Device)
			{
				$DeviceName = $Device["varDeviceName"];
				$DeviceMac = $this->removeInvalidChars($Device["varDeviceMAC"], true);
				$this->MaintainVariable($DeviceMac, $DeviceName, vtBoolean, "~Presence", $vpos++, isset($DevicesJSON));
			}
		}

		$this->MaintainVariable("GeneralPresenceUpdatedVariable", $this->Translate("Presence Updated"), vtBoolean, "~Switch", 10, $this->ReadPropertyBoolean("GeneralPresenceUpdatedVariable") == 1);

		$TimerMS = $this->ReadPropertyInteger("Timer") * 1000;
		$this->SetTimerInterval("Check Presence", $TimerMS);

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
			$RawData = $this->getRawData($Cookie, $ServerAddress, $ServerPort, $UnifiAPI/*, $ControllerType ->do not use here!!!*/);
			return $RawData;
		}
	}

	public function CheckPresence()
	{
		$Site = $this->ReadPropertyString("Site");

		$RawData = $this->AuthenticateAndGetData("api/s/".$Site."/stat/sta");

		// query JSON file for internet data
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

					//Itterate through all device and check if one of them matches the list in the config form.
					foreach ($ActiveDevices["data"] as $Index => $Device)
					{
						$DeviceMacClean = $this->removeInvalidChars($Device["mac"], true);

						$OldPresenceValue = GetValue($this->GetIDForIdent($DeviceMac));
						if ($DeviceMac == $DeviceMacClean)
						{
							if ($OldPresenceValue == 0)
							{ //check if new value is different and only than trigger a replacement
								SetValue($this->GetIDForIdent($DeviceMac), 1);
								if ($this->ReadPropertyBoolean("GeneralPresenceUpdatedVariable") == 1)
								{
									SetValue($this->GetIDForIdent("GeneralPresenceUpdatedVariable"), 1);
								}
								$this->SendDebug($this->Translate("Presence Manager"), $this->Translate("Device ACTIVE with MAC: ".$DeviceMac), 0);
							}
							break;
						}
						else
						{
							if ($Index === array_key_last($ActiveDevices["data"]))
							{
								if ($OldPresenceValue == 1)
								{
									SetValue($this->GetIDForIdent($DeviceMac), 0);
									if ($this->ReadPropertyBoolean("GeneralPresenceUpdatedVariable") == 1)
									{
										SetValue($this->GetIDForIdent("GeneralPresenceUpdatedVariable"), 1);
									}
									$this->SendDebug($this->Translate("Presence Manager"), $this->Translate("Device NOT active with MAC: ".$DeviceMac), 0);
								}
							}
						}
					}
				}
			}
			else
			{
				$this->SendDebug($this->Translate("Presence Manager"), $this->Translate("There does not seem to be any configuration - no data is available from the UniFi"), 0);
			}
		}
	}
}
