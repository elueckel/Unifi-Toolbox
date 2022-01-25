<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

include_once __DIR__.'/UnifiControllerStub/UnifiControllerStub.php';

include_once __DIR__.'/stubs/ConstantStubs.php';
include_once __DIR__.'/stubs/GlobalStubs.php';
include_once __DIR__.'/stubs/KernelStubs.php';
include_once __DIR__.'/stubs/MessageStubs.php';
include_once __DIR__.'/stubs/ModuleStubs.php';


class UnifiInternetControllerTest extends TestCase
{
	private $moduleInstanceID = "{D1D2F76B-AAA2-A2F8-82CD-0A482654FA49}";
	/*
	"id": "{D1D2F76B-AAA2-A2F8-82CD-0A482654FA49}",
	"name": "UniFi DM Internet Controller",
	 */

	/*
	CODE	STATUS
	101	Instanz wird erstellt
	102	Instanz ist aktiv
	103	Instanz wird gelöscht
	104	Instanz ist inaktiv
	105	Instanz wurde nicht erstellt
	 */

	public function setUp(): void
	{
		//Reset
		IPS\Kernel::reset();
		//Register our library we need for testing
		IPS\ModuleLoader::loadLibrary(__DIR__.'/../library.json');
		parent::setUp();
	}

	public function testNoArchiveAvailable()
	{
		// defaul values von modul instance
		$ControllerType = 0;
		$Site = "default";
		$ServerAddress = "192.168.1.1";
		$ServerPort = "443";
		$UserName = "testuser";
		$Password = "testpass";
		$Timer = 0;
		// devices

		Unifi_setControllerType($ControllerType);

		// Modul erstellen
		$myModuleId = IPS_CreateInstance($this->moduleInstanceID);

		// Moduleigenschaften setzen
//		IPS_SetProperty($myModuleId, 'ControllerType', $ControllerType);
		IPS_SetProperty($myModuleId, 'ServerAddress', $ServerAddress);
		IPS_SetProperty($myModuleId, 'ServerPort', $ServerPort);
		IPS_SetProperty($myModuleId, 'UserName', $UserName);
		IPS_SetProperty($myModuleId, 'Password', $Password);
		IPS_SetProperty($myModuleId, 'Timer', $Timer);
		IPS_ApplyChanges($myModuleId);

		/* TC1:
			action: create module
			check: no childs created + module status = 104  + action + module status = 102
		 */
		$tdId = 1;
		$this->assertEquals(0, count(IPS_GetChildrenIDs($myModuleId)), "TC".$tdId.": initialCreation: no childs created");

		// timer = 0 --> instance inactive
		$this->assertEquals(104, IPS_GetInstance($myModuleId)['InstanceStatus'], "TC".$tdId.": Module GetStatus() return value");
		$checkSiteName = UIC_checkSiteName($myModuleId);
		$this->assertEquals(true, $checkSiteName, "TC".$tdId.": checkSiteName() return value");
		// action --> instance active
		$this->assertEquals(102, IPS_GetInstance($myModuleId)['InstanceStatus'], "TC".$tdId.": Module GetStatus() return value");

		/* TC2:
			action: set wrong ControllerType
			check: module status = 200
		 *-/
		$tdId++;
		IPS_SetProperty($myModuleId, 'ControllerType', 1);
		IPS_ApplyChanges($myModuleId);

		$checkSiteName = UIC_checkSiteName($myModuleId);
		$this->assertEquals(false, $checkSiteName, "TC".$tdId.": checkSiteName() return value");
		$this->assertEquals(200, IPS_GetInstance($myModuleId)['InstanceStatus'], "TC".$tdId.": Module GetStatus() return value");

		IPS_SetProperty($myModuleId, 'ControllerType', $ControllerType);
		IPS_ApplyChanges($myModuleId);
		 */

		/* TC3:
			action: set wrong IP
			check: module status = 200
		 */
		$tdId++;
		IPS_SetProperty($myModuleId, 'ServerAddress', "192.168.55.55");
		IPS_ApplyChanges($myModuleId);

		$checkSiteName = UIC_checkSiteName($myModuleId);
		$this->assertEquals(false, $checkSiteName, "TC".$tdId.": checkSiteName() return value");
		$this->assertEquals(200, IPS_GetInstance($myModuleId)['InstanceStatus'], "TC".$tdId.": Module GetStatus() return value");

		IPS_SetProperty($myModuleId, 'ServerAddress', $ServerAddress);
		IPS_ApplyChanges($myModuleId);

		/* TC4:
			action: set wrong Port
			check: module status = 200
		 */
		$tdId++;
		IPS_SetProperty($myModuleId, 'ServerPort', "5555");
		IPS_ApplyChanges($myModuleId);

		$checkSiteName = UIC_checkSiteName($myModuleId);
		$this->assertEquals(false, $checkSiteName, "TC".$tdId.": checkSiteName() return value");
		$this->assertEquals(200, IPS_GetInstance($myModuleId)['InstanceStatus'], "TC".$tdId.": Module GetStatus() return value");

		IPS_SetProperty($myModuleId, 'ServerPort', $ServerPort);
		IPS_ApplyChanges($myModuleId);

		/* TC5:
			action: set wrong User
			check: module status = 201
		 */
		$tdId++;
		IPS_SetProperty($myModuleId, 'UserName', "wrong_user");
		IPS_ApplyChanges($myModuleId);

		$checkSiteName = UIC_checkSiteName($myModuleId);
		$this->assertEquals(false, $checkSiteName, "TC".$tdId.": checkSiteName() return value");
		$this->assertEquals(201, IPS_GetInstance($myModuleId)['InstanceStatus'], "TC".$tdId.": Module GetStatus() return value");

		IPS_SetProperty($myModuleId, 'UserName', $UserName);
		IPS_ApplyChanges($myModuleId);

		/* TC6:
			action: set wrong Password
			check: module status = 201
		 */
		$tdId++;
		IPS_SetProperty($myModuleId, 'Password', "wrong_password");
		IPS_ApplyChanges($myModuleId);

		$checkSiteName = UIC_checkSiteName($myModuleId);
		$this->assertEquals(false, $checkSiteName, "TC".$tdId.": checkSiteName() return value");
		$this->assertEquals(201, IPS_GetInstance($myModuleId)['InstanceStatus'], "TC".$tdId.": Module GetStatus() return value");

		IPS_SetProperty($myModuleId, 'Password', $Password);
		IPS_ApplyChanges($myModuleId);

		/* TC7:
			action: set timer + set back to 0
			check: module status = 102 + module status = 104
		 */
		$tdId++;
		IPS_SetProperty($myModuleId, 'Timer', 300);
		IPS_ApplyChanges($myModuleId);
		// timer != 0 --> instance active
		$this->assertEquals(102, IPS_GetInstance($myModuleId)['InstanceStatus'], "TC".$tdId.": Module GetStatus() return value");

		IPS_SetProperty($myModuleId, 'Timer', $Timer);
		IPS_ApplyChanges($myModuleId);
		// timer = 0 --> instance inactive
		$this->assertEquals(104, IPS_GetInstance($myModuleId)['InstanceStatus'], "TC".$tdId.": Module GetStatus() return value");

		/* *******************************
		MODUL SPECIFIC TESTS
		 ****************************** */
		/* TC100:
			action: <<to be defined>>
			check: <<to be defined>>
		 */
		$tdId = 100;
	}
}