<?php

declare(strict_types=1);

if (!defined('vtBoolean'))
{
	define('vtBoolean', 0);
	define('vtInteger', 1);
	define('vtFloat', 2);
	define('vtString', 3);
	define('vtArray', 8);
	define('vtObject', 9);
}

trait myFunctions
{
	private function getCookie($Username, $Password, $ServerAddress, $ServerPort, $ControllerType = 0)
	{
		//Generic Section providing for Authenthication against a DreamMachine or Classic CloudKey
		$ch = curl_init();
		if (defined('DEBUG') && DEBUG)
		{
			echo "\nCURLOPT_POSTFIELDS: ".CURLOPT_POSTFIELDS;
		}

		if ($ControllerType == 0)
		{
			$SuffixURL = "/api/auth/login";
			curl_setopt($ch, CURLOPT_POSTFIELDS, "username=".$Username."&password=".$Password);
		}
		elseif ($ControllerType == 1)
		{
			$SuffixURL = "/api/login";
			curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(array('username' => $Username, 'password' => $Password)));
		}
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_URL, "https://".$ServerAddress.":".$ServerPort.$SuffixURL);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
		curl_setopt($ch, CURLOPT_HEADER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$data = curl_exec($ch);
		if (defined('DEBUG') && DEBUG)
		{
			echo "\ngetCookie() URL: "."https://".$ServerAddress.":".$ServerPort.$SuffixURL;
			echo "\ngetCookie() data: ".$data;
		}

		if (false === $data)
		{
			$this->SendDebug($this->Translate("Authentication"), $this->Translate('Error 404: Not reachable / No response!'), 0);

			// IP or Port not reachable / no response
			$this->SetStatus(200);

			return false;
		}

		$header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
		if (defined('DEBUG') && DEBUG)
		{
			echo "\ngetCookie() CURLINFO_HEADER_SIZE: ".$header_size;
		}
		$body = trim(substr($data, $header_size));
		$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		if (defined('DEBUG') && DEBUG)
		{
			echo "\ngetCookie() CURLINFO_HTTP_CODE: ".$code;
		}

		$this->SendDebug($this->Translate("Authentication"), $this->Translate('Return-Code Provided is: ').$code, 0);

		preg_match_all('|(?i)Set-Cookie: (.*);|U', substr($data, 0, $header_size), $results);
		if (isset($results[1]))
		{
			$Cookie = implode(';', $results[1]);
			if (!empty($body))
			{
				if (200 == $code)
				{
					$this->SendDebug($this->Translate("Authentication"), $this->Translate('Login Successful'), 0);
					$this->SendDebug($this->Translate("Authentication"), $this->Translate('Cookie Provided is: ').$Cookie, 0);
					$this->SetStatus(102); // login successful
				}
				elseif (400 == $code)
				{
					$this->SendDebug($this->Translate("Authentication"), $this->Translate('400 Bad Request - The server cannot or will not process the request due to an apparent client error.'), 0);
					echo $this->Translate('400 Bad Request - The server cannot or will not process the request due to an apparent client error.');
					$this->SetStatus(201); // login seems to be not successful
					return false;
				}
				elseif (401 == $code || 403 == $code)
				{
					$this->SendDebug($this->Translate("Authentication"), $this->Translate('401 Unauthorized / 403 Forbidden - The request contained valid data and was understood by the server, but the server is refusing action. Missing user permission?'), 0);
					echo $this->Translate('401 Unauthorized / 403 Forbidden - The request contained valid data and was understood by the server, but the server is refusing action. Missing user permission?');
					$this->SetStatus(201); // login seems to be not successful
					return false;
				}
			}
		}
		else
		{
			$this->SendDebug($this->Translate("Authentication"), $this->Translate('No cookie found'), 0);
			echo $this->Translate('No cookie found');
			$this->SetStatus(201); // login seems to be not successful
			return false;
		}
		if (defined('DEBUG') && DEBUG)
		{
			echo "\ngetCookie()=".$Cookie;
		}
		return $Cookie;
	}

	private function getRawData($Cookie, $ServerAddress, $ServerPort, $UnifiAPI, $ControllerType = 0)
	{
		$ch = curl_init();
		if ($ControllerType == 0)
		{
			$MiddlePartURL = "/proxy/network/";
		}
		elseif ($ControllerType == 1)
		{
			$MiddlePartURL = "/";
		}
		curl_setopt($ch, CURLOPT_URL, "https://".$ServerAddress.":".$ServerPort.$MiddlePartURL.$UnifiAPI);
		curl_setopt($ch, CURLOPT_HTTPGET, true);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array("cookie: ".$Cookie));
		curl_setopt($ch, CURLOPT_SSLVERSION, 'CURL_SSLVERSION_TLSv1');

		if (defined('DEBUG') && DEBUG)
		{
			echo "\ngetRawData() URL: "."https://".$ServerAddress.":".$ServerPort.$MiddlePartURL.$UnifiAPI;
		}

		$RawData = curl_exec($ch);
		curl_close($ch);
		if (defined('DEBUG') && DEBUG)
		{
			echo "\ngetRawData() RawData: ".$RawData;
		}

		if (isset($RawData) && 400 == $RawData)
		{
			$this->SendDebug($this->Translate("UniFi API Call"), $this->Translate('400 Bad Request - The server cannot or will not process the request due to an apparent client error.'), 0);
			$this->SetStatus(201); // login seems to be not successful
			return false;
		}
		elseif (isset($RawData) && (401 == $RawData || 403 == $RawData || $RawData == "Unauthorized"))
		{
			$this->SendDebug($this->Translate("UniFi API Call"), $this->Translate('401 Unauthorized / 403 Forbidden - The request contained valid data and was understood by the server, but the server is refusing action. Missing user permission?'), 0);
			$this->SetStatus(201); // login seems to be not successful
			return false;
		}
		elseif (isset($RawData) && 404 == $RawData)
		{
			$this->SendDebug($this->Translate("UniFi API Call"), $this->Translate('404 Not Found - Please check your IP, Port and ControllerType'), 0);
			$this->SetStatus(200); // IP, Port or ControllerType wrong
			return false;
		}
		elseif (isset($RawData) && false !== $RawData && false !== strpos($RawData, 'api.err.NoSiteContext'))
		{
			$this->SendDebug($this->Translate("UniFi API Call"), $this->Translate('NoSiteContext - It seems, that the configured Site is wrong.'), 0);
			$this->SetStatus(202); // NoSiteContext --> Site falsch
			return $RawData;
		}
		elseif (isset($RawData) && false !== $RawData && false === strpos($RawData, '"rc":"error"'))
		{
			$this->SendDebug($this->Translate("UniFi API Call"), $this->Translate("Successfully Called"), 0);
			$this->SendDebug($this->Translate("UniFi API Call"), $this->Translate("Data Provided: ").$RawData, 0);
			$this->SetStatus(102); // login successful
			return $RawData;
		}
		else
		{
			$this->SendDebug($this->Translate("UniFi API Call"), $this->Translate("API could not be called - check the login data. Do you see a Cookie?"), 0);
			$this->SetStatus(201); // login seems to be not successful
			return false;
		}
	}

	private function createXsrfToken($Cookie)
	{
		//create XSRF Token
		if (($Cookie) && strpos($Cookie, 'TOKEN') !== false)
		{
			$cookie_bits = explode('=', $Cookie);
			if (empty($cookie_bits) || !array_key_exists(1, $cookie_bits))
			{
				return "";
			}

			$jwt_components = explode('.', $cookie_bits[1]);
			if (empty($jwt_components) || !array_key_exists(1, $jwt_components))
			{
				return "";
			}

			$X_CSRF_Token = 'x-csrf-token: '.json_decode(base64_decode($jwt_components[1]))->csrfToken;

			return $X_CSRF_Token;
		}
		else
		{
			return "";
		}
	}

	private function getSiteName($Site, $Username, $Password, $ServerAddress, $ServerPort, $ControllerType = 0)
	{
		$UnifiAPI = "api/self/sites";

		$Cookie = $this->getCookie($Username, $Password, $ServerAddress, $ServerPort, $ControllerType);
		if (isset($Cookie) && false !== $Cookie)
		{
			$RawData = $this->getRawData($Cookie, $ServerAddress, $ServerPort, $UnifiAPI, $ControllerType);
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
						$sitesArray = $JSONData['data'];
						$sitesFound = array();

						foreach ($sitesArray as $siteValue)
						{
							if ($Site == $siteValue['name'])
							{
								$this->SendDebug("checkSiteName()", $this->Translate("Site '").$Site.$this->Translate("' found. --> Configuration is correct!"), 0);
								echo $this->Translate("Site '").$Site.$this->Translate("' found. --> Configuration is correct!");
								return true;
							}
							else
							{
								$sitesFound[] = $siteValue['name'];
							}
						}

						$this->SendDebug("checkSiteName()", $this->Translate("Error: Site '").$Site.$this->Translate("' not found! --> available site-names: ").implode(", ", $sitesFound), 0);
						echo $this->Translate("Error: Site '").$Site.$this->Translate("' not found! --> available site-names: ").implode(", ", $sitesFound);
					}
				}
				else
				{
					$this->SendDebug("checkSiteName()", $this->Translate("There does not seem to be any configuration - no data is available from the UniFi"), 0);
				}
			}
			else
			{
				// debug output already done in getRawData()
			}
		}

		return false;
	}

	private function createVarProfile($ProfilName, $ProfileType, $Suffix = '', $MinValue = 0, $MaxValue = 0, $StepSize = 0, $Digits = 0, $Icon = 0, $Associations = '')
	{
		if (!IPS_VariableProfileExists($ProfilName))
		{
			IPS_CreateVariableProfile($ProfilName, $ProfileType);
			IPS_SetVariableProfileText($ProfilName, '', $Suffix);

			if (in_array($ProfileType, array(vtInteger, vtFloat)))
			{
				IPS_SetVariableProfileValues($ProfilName, $MinValue, $MaxValue, $StepSize);
				IPS_SetVariableProfileDigits($ProfilName, $Digits);
			}

			IPS_SetVariableProfileIcon($ProfilName, $Icon);

			if ($Associations != '')
			{
				foreach ($Associations as $a)
				{
					$w = isset($a['Wert']) ? $a['Wert'] : '';
					$n = isset($a['Name']) ? $a['Name'] : '';
					$i = isset($a['Icon']) ? $a['Icon'] : '';
					$f = isset($a['Farbe']) ? $a['Farbe'] : -1;
					IPS_SetVariableProfileAssociation($ProfilName, $w, $n, $i, $f);
				}
			}

			$this->SendDebug("Variable-Profile", "Profile ".$ProfilName." created", 0);
		}
	}

	private function removeInvalidChars($input, $toLower = false)
	{
		if ($toLower)
		{
			$input = strtolower($input);
		}

		return preg_replace('/[^a-z0-9]/i', '', $input);
	}
}
