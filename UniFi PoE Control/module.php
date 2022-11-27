<?php

declare(strict_types=1);

require_once __DIR__.'/../libs/myFunctions.php';  // globale Funktionen

// Modul Prefix
if (!defined('MODUL_PREFIX'))
{
	define("MODUL_PREFIX", "UPC");
}

class UniFiPoEControl extends IPSModule
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

		$this->RegisterPropertyString("Devices", "");
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
				$DeviceNameClean = $this->removeInvalidChars($DeviceName);
				$DeviceMacAddress = $Device["varDeviceMAC"];
				$DeviceMacClean = $this->removeInvalidChars($DeviceMacAddress, true);
				$NoOfPorts = $Device["varNoOfPorts"];

				for ($i=1; $i <= $NoOfPorts; $i++) { 
					if (@IPS_GetObjectIDByIdent($DeviceMacClean, $this->InstanceID) == false)
					{
						$this->RegisterVariableBoolean($DeviceMacClean . $i, $DeviceName . " Port: " . $i, "~Switch");
						$DeviceMacCleanID = @IPS_GetObjectIDByIdent($DeviceMacClean .$i, $this->InstanceID);
	
						SetValue($DeviceMacCleanID, false);
						IPS_Sleep(1000);
						$this->EnableAction($DeviceMacClean . $i);
						$this->RegisterMessage($DeviceMacCleanID, VM_UPDATE);
					}
					foreach ($DevicesJSON as $Device)
					{
						$DeviceMacAddress = $Device["varDeviceMAC"];
						$DeviceMacClean = $this->removeInvalidChars($DeviceMacAddress, true);
						$VarID = @IPS_GetObjectIDByIdent($DeviceMacClean . $i, $this->InstanceID);
						$this->RegisterMessage($VarID, VM_UPDATE);
					}
				}

			}
		}
	}

	public function MessageSink($TimeStamp, $SenderID, $Message, $Data)
	{
		$this->SendDebug($this->Translate("Sender"), $SenderID, 0);
		$this->SetBuffer("SenderID", $SenderID);

		$Site = $this->ReadPropertyString("Site");
		$this->AuthenticateAndProcessRequest("api/s/".$Site."/cmd/devmgr");
	}


	public function AuthenticateAndProcessRequest(string $UnifiAPI = "")
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
			$UnifiAPI = "api/s/".$Site."/cmd/sta";
		}

		//Generic Section providing for Authenthication against a DreamMachine or Classic CloudKey
		$Cookie = $this->getCookie($Username, $Password, $ServerAddress, $ServerPort, $ControllerType);

		// get SenderID
		$SenderID = $this->GetBuffer("SenderID");
		if ($SenderID != "")
		{
			$SenderObjectData = IPS_GetObject($SenderID);
			$SenderName = ($SenderObjectData["ObjectName"]);
			$SenderObjectIdent = ($SenderObjectData["ObjectIdent"]);
			$SenderStatus = GetValue($SenderID);
			$portIndex = -1;

			//Get MAC Address from Config form
			$DevicesList = $this->ReadPropertyString("Devices");
			$DevicesJSON = json_decode($DevicesList, true);

			if (isset($DevicesJSON))
			{
				$DeviceMacAddress = "";


				foreach ($DevicesJSON as $Device)
				{
					$NoOfPorts= $Device["varNoOfPorts"];
					for ($i=1; $i <= $NoOfPorts; $i++) { 
						$DeviceMacClean = $this->removeInvalidChars($Device["varDeviceMAC"], true);
						if ($SenderObjectIdent == $DeviceMacClean .$i)
						{
							$DeviceMacAddress = $Device["varDeviceMAC"];
							$this->SendDebug($this->Translate("PoE Control"), $this->Translate("Device to be managed: ").$Device["varDeviceName"]."Port index: ".$i, 0);
							$portIndex = $i;
							break;
						}
					}

				}
			}

			if (!isset($DeviceMacAddress) || "" == $DeviceMacAddress)
			{
				$this->SendDebug($this->Translate("PoE Control"), $this->Translate("The switched variable did not have an entry in the module configuration - execution stopped"), 0);
				return false;
			}


			$this->SendDebug($this->Translate("PoE Control"), $Cookie, 0);

			//////////////////////////////////////////
			//Change the Unifi API to be called here
			$Site = $this->ReadPropertyString("Site");
			$UnifiAPI = "api/s/".$Site."/cmd/devmgr";
			//////////////////////////////////////////

			//create XSRF Token
			$X_CSRF_Token = $this->createXsrfToken($Cookie);

			if (isset($Cookie))
			{
				$this->SendDebug($this->Translate("PoE Control"), $this->Translate("Module is authenticated and will try to manage device"), 0);

				if ($SenderStatus == 1)
				{
					$Command = "power-cycle";
					$this->SendDebug($this->Translate("PoE Control"), $this->Translate("PoE Switchport will be cycled ").$SenderName.$this->Translate(" with MAC address ").$DeviceMacAddress, 0);
				}
				elseif ($SenderStatus == 0)
				{
					$this->SendDebug($this->Translate("PoE Control"), $this->Translate("Nothing to do for ").$SenderName, 0);
					return;
				}

				//$CommandToController = json_encode(array($Command => $DeviceMacAddress));
	
				$CommandToController = json_encode(array(
					"cmd" => $Command,
					"mac" => $DeviceMacAddress,
					'port_idx' => intval($portIndex),

				), JSON_UNESCAPED_SLASHES);
				//var_dump($CommandToController);


				$ch = curl_init();
				if ($ControllerType == 0)
				{
					$MiddlePartURL = "/proxy/network/";
				}
				elseif ($ControllerType == 1)
				{
					$MiddlePartURL = "/";
				}
				curl_setopt($ch, CURLOPT_POST, true);
				curl_setopt($ch, CURLOPT_URL, "https://".$ServerAddress.":".$ServerPort.$MiddlePartURL.$UnifiAPI);
				curl_setopt($ch, CURLOPT_HTTPHEADER, array('Cookie:'.$Cookie, $X_CSRF_Token, 'Content-Type:application/json', 'Expect:'/*,'data='.$CommandToController*/));
				curl_setopt($ch, CURLOPT_POSTFIELDS, $CommandToController);
				curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
				curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
				curl_setopt($ch, CURLOPT_SSLVERSION, 'CURL_SSLVERSION_TLSv1');
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

				$RawData = curl_exec($ch);
				$HTTP_Code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
				$this->SendDebug($this->Translate("PoE Control"), $this->Translate("Feedback from UniFi Controller: ").$RawData." / HTTP Message ".$HTTP_Code, 0);

				$ControllerFeedbackComplete = json_decode($RawData, true);
				$ControllerFeedbackOK = $ControllerFeedbackComplete["meta"]["rc"];
				$this->SendDebug($this->Translate("PoE Control"), $this->Translate("Was operation executed: ").$ControllerFeedbackOK, 0);
				curl_close($ch);
				if ($ControllerFeedbackOK == "ok")
				{
					// reset variable
					SetValue($SenderID, false);
					//WFC_SendPopup(12345, "Test", "Eine nette <br> Meldung");
				}
				elseif ($ControllerFeedbackOK == "error")
				{
					//WFC_SendPopup(12345, "Test", "Eine nette <br> Meldung");
				}
			}
		}

		return true;
	}

	// required for changing variable values in GUI
	public function RequestAction($Ident, $Value) {

		$this->SetValue($Ident, $Value);

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
