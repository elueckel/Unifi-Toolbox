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
	class UniFiDeviceBlocker extends IPSModule
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
			//$this->RegisterPropertyInteger("Timer", "0");

			$this->RegisterPropertyString("Devices", "");

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
					$DeviceNameClean = str_replace(array("-",":"," "), "", $DeviceName);
					$DeviceMacAdress = $Device["varDeviceMAC"];
					$DeviceMacClean = str_replace(array(":"," "), "", $DeviceMacAdress);

					if (@IPS_GetObjectIDByIdent($DeviceMacClean, $this->InstanceID) == false) {

						$DeviceMacCleanID = IPS_CreateVariable(0);
						IPS_SetName($DeviceMacCleanID, $DeviceName);
						IPS_SetIdent($DeviceMacCleanID, $DeviceMacClean);
						IPS_SetVariableCustomProfile($DeviceMacCleanID, "~Switch");
						IPS_SetParent($DeviceMacCleanID, $this->InstanceID);
						 
						SetValue($DeviceMacCleanID,true);
						IPS_Sleep(1000);
						$this->EnableAction($DeviceMacClean);
						$this->RegisterMessage($DeviceMacCleanID, VM_UPDATE);

					}

					foreach ($DevicesJSON as $Device) {
						$DeviceMacAdress = $Device["varDeviceMAC"];
						$DeviceMacClean = str_replace(array(":"," "), "", $DeviceMacAdress);
						$VarID = @IPS_GetObjectIDByIdent($DeviceMacClean, $this->InstanceID);
						$this->RegisterMessage($VarID, VM_UPDATE);
					}
				}

					/*
					
					$this->MaintainVariable($DeviceNameClean, $DeviceName, vtBoolean, "~Switch", $vpos++, isset($DevicesJSON));
					$DeviceNameCleanID = @IPS_GetObjectIDByIdent($DeviceNameClean, $this->InstanceID);
					SetValue($DeviceNameCleanID,true); // make a device will not a disconnected when the module is initialized

					$this->EnableAction($DeviceNameClean);
					
					//$DeviceNameCleanID = @IPS_GetObjectIDByIdent($DeviceNameClean, $this->InstanceID);
					if (IPS_GetObject($DeviceNameCleanID)['ObjectType'] == 2) {
							$this->RegisterMessage($DeviceNameCleanID, VM_UPDATE);
					}
					*/			
			}				

		}

		public function MessageSink($TimeStamp, $SenderID, $Message, $Data) {
		
			$this->SendDebug($this->Translate("Sender"),$SenderID, 0);
			$this->SetBuffer("SenderID",$SenderID);
			$this->AuthenticateAndProcessRequest();

		}


		public function AuthenticateAndProcessRequest() {
			
			$ControllerType = $this->ReadPropertyInteger("ControllerType");
			$ServerAdress = $this->ReadPropertyString("ServerAdress");
			$ServerPort = $this->ReadPropertyInteger("ServerPort");
			$Username = $this->ReadPropertyString("UserName");
			$Password = $this->ReadPropertyString("Password");
			$Site = $this->ReadPropertyString("Site");

			////////////////////////////////////////
			//Change the Unifi API to be called here
			$UnifiAPI = "api/s/".$Site."/cmd/sta";
			////////////////////////////////////////

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
			preg_match_all('|(?i)Set-Cookie: (.*);|U', substr($data, 0, $header_size), $results);
			if (isset($results[1])) {
				$Cookie = implode(';', $results[1]);
				if (!empty($body)) {
					if ($code == 200) { 
						$this->SendDebug($this->Translate("Authentication"),$this->Translate('Login Successful'),0); 
						$this->SendDebug($this->Translate("Authentication"),$this->Translate('Cookie Provided is: ').$Cookie,0);
					}
					else if ($code == 400) {
						$this->SendDebug($this->Translate("Authentication"),$this->Translate('400 Bad Request - The server cannot or will not process the request due to an apparent client error.'),0);
					}
					else if ($code == 401 || $code == 403) {
						$this->SendDebug($this->Translate("Authentication"),$this->Translate('401 Unauthorized / 403 Forbidden - The request contained valid data and was understood by the server, but the server is refusing action. Missing user permission?'),0);
					}
				}
			}

			$SenderID = $this->GetBuffer("SenderID");
			if ($SenderID != "") {
				$SenderObjectData = IPS_GetObject($SenderID);
				$SenderName = ($SenderObjectData["ObjectName"]);
				$SenderObjectIdent = ($SenderObjectData["ObjectIdent"]);
				$SenderStatus = GetValue($SenderID);

				//Get MAC Adress from Config form
				$DevicesList = $this->ReadPropertyString("Devices");
				$DevicesJSON = json_decode($DevicesList,true);
				
				if (isset($DevicesJSON)) {
					$DeviceMacAdress = "";

					foreach ($DevicesJSON as $Device) {
						$DeviceMacClean = str_replace(array(":"," "), "", $Device["varDeviceMAC"]);
						if ($SenderObjectIdent == $DeviceMacClean) {
							$DeviceMacAdress = $Device["varDeviceMAC"];
							$this->SendDebug($this->Translate("Device Blocker"),$this->Translate("Device to be managed: ").$Device["varDeviceName"],0);
							break;
						}
					}
				}

				if (!isset($DeviceMacAdress) || "" == $DeviceMacAdress) {
					$this->SendDebug($this->Translate("Device Blocker"),$this->Translate("The switched variable did not have an entry in the module configuration - execution stopped"),0);
					exit;
				}

				
				$this->SendDebug($this->Translate("Device Blocker"),$Cookie,0);

				//////////////////////////////////////////
				//Change the Unifi API to be called here
				$UnifiAPI = "api/s/".$Site."/cmd/stamgr";
				//////////////////////////////////////////
				
				//create XSRF Token

				if (($Cookie) && strpos($Cookie, 'TOKEN') !== false) {
					$cookie_bits = explode('=', $Cookie);
					if (empty($cookie_bits) || !array_key_exists(1, $cookie_bits)) {
						return;
					}

					$jwt_components = explode('.', $cookie_bits[1]);
					if (empty($jwt_components) || !array_key_exists(1, $jwt_components)) {
						return;
					}

					$X_CSRF_Token = 'x-csrf-token: ' . json_decode(base64_decode($jwt_components[1]))->csrfToken;
				}

				if (isset($Cookie)) {

					$this->SendDebug($this->Translate("Device Blocker"),$this->Translate("Module is authenticated and will try to manage device"),0);

					if ($SenderStatus == 1) {
						$Command = "unblock-sta";
						$this->SendDebug($this->Translate("Device Blocker"),$this->Translate("Module will try to unblock device ").$SenderName.$this->Translate(" with MAC adress ").$DeviceMacAdress,0);
					} 
					else if ($SenderStatus == 0) {
						$Command = "block-sta";
						$this->SendDebug($this->Translate("Device Blocker"),$this->Translate("Module will try to block device ").$SenderName.$this->Translate(" with MAC adress ").$DeviceMacAdress,0);
					}

					//$CommandToController = json_encode(array($Command => $DeviceMacAdress));
					
					$CommandToController = json_encode(array(
						"cmd" => $Command,
						"mac" => $DeviceMacAdress

					), JSON_UNESCAPED_SLASHES);
					//var_dump($CommandToController);
					

					$ch = curl_init();
					if ($ControllerType == 0) {
						$MiddlePartURL = "/proxy/network/";
					}
					elseif ($ControllerType == 1) {
						$MiddlePartURL = "/";
					}	
					curl_setopt($ch, CURLOPT_POST, true);
					curl_setopt($ch, CURLOPT_URL, "https://".$ServerAdress.":".$ServerPort.$MiddlePartURL.$UnifiAPI);
					curl_setopt($ch, CURLOPT_HTTPHEADER, array('Cookie:'.$Cookie,$X_CSRF_Token,'Content-Type:application/json', 'Expect:'/*,'data='.$CommandToController*/));
					curl_setopt($ch, CURLOPT_POSTFIELDS, $CommandToController);
					curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE); 
					curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);	
					curl_setopt($ch, CURLOPT_SSLVERSION, 'CURL_SSLVERSION_TLSv1');
					curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 
					$RawData = curl_exec($ch);
					$HTTP_Code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
					$this->SendDebug($this->Translate("Device Blocker"),$this->Translate("Feedback from UniFi Controller: ").$RawData." / HTTP Message ".$HTTP_Code ,0);
					
					$ControllerFeedbackComplete = json_decode($RawData,true);
					$ControllerFeedbackOK =  $ControllerFeedbackComplete["meta"]["rc"];
					$this->SendDebug($this->Translate("Device Blocker"),$this->Translate("Was operation executed: ").$ControllerFeedbackOK ,0);
					curl_close($ch);
					if ($ControllerFeedbackOK == "ok") {
						//WFC_SendPopup(12345, "Test", "Eine nette <br> Meldung"); 
					}
					else if ($ControllerFeedbackOK == "error") {
						//WFC_SendPopup(12345, "Test", "Eine nette <br> Meldung"); 
					}
										
				}

			}

			

		}
		
		public function RequestAction($Ident, $Value) {
		
			$this->SetValue($Ident, $Value);
		
		}

	}
