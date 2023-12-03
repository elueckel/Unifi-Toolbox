<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

include_once __DIR__.'/UnifiControllerStub/UnifiControllerStub.php';

include_once __DIR__.'/stubs/ConstantStubs.php';
include_once __DIR__.'/stubs/GlobalStubs.php';
include_once __DIR__.'/stubs/KernelStubs.php';
include_once __DIR__.'/stubs/MessageStubs.php';
include_once __DIR__.'/stubs/ModuleStubs.php';


class UnifiEndpointMonitorTest extends TestCase
{
	private $moduleInstanceID = "{18083971-8894-381C-F535-3DEB08A44DBD}";
	/*
	"id": "{18083971-8894-381C-F535-3DEB08A44DBD}",
	"name": "UniFi Multi Endpoint Monitor",
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
		/*
			TESTS WITH CONTROLLER TYPE = 0
		 */
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
		IPS_SetProperty($myModuleId, 'ControllerType', $ControllerType);
		IPS_SetProperty($myModuleId, 'ServerAddress', $ServerAddress);
		IPS_SetProperty($myModuleId, 'ServerPort', $ServerPort);
		IPS_SetProperty($myModuleId, 'UserName', $UserName);
		IPS_SetProperty($myModuleId, 'Password', $Password);
		IPS_SetProperty($myModuleId, 'Timer', $Timer);
		IPS_ApplyChanges($myModuleId);

		/* TC1:
			action: create module
			check: several childs created + module status = 104  + action + module status = 102
		 */
		$tdId = 1;
//		$this->assertEquals(true, 0 != count(IPS_GetChildrenIDs($myModuleId)), "TC".$tdId.": initialCreation: several childs created");

		// timer = 0 --> instance inactive
		$this->assertEquals(104, IPS_GetInstance($myModuleId)['InstanceStatus'], "TC".$tdId.": Module GetStatus() return value");
		$checkSiteName = UEM_checkSiteName($myModuleId);
		$this->assertEquals(true, $checkSiteName, "TC".$tdId.": checkSiteName() return value");
		// action --> instance active
		$this->assertEquals(102, IPS_GetInstance($myModuleId)['InstanceStatus'], "TC".$tdId.": Module GetStatus() return value");

		/* TC2:
			action: set wrong ControllerType
			check: module status = 200
		 */
		$tdId++;
		IPS_SetProperty($myModuleId, 'ControllerType', ($ControllerType + 1) % 2);
		IPS_ApplyChanges($myModuleId);

		$checkSiteName = UEM_checkSiteName($myModuleId);
		$this->assertEquals(false, $checkSiteName, "TC".$tdId.": checkSiteName() return value");
		$this->assertEquals(200, IPS_GetInstance($myModuleId)['InstanceStatus'], "TC".$tdId.": Module GetStatus() return value");

		IPS_SetProperty($myModuleId, 'ControllerType', $ControllerType);
		IPS_ApplyChanges($myModuleId);

		/* TC3:
			action: set wrong IP
			check: module status = 200
		 */
		$tdId++;
		IPS_SetProperty($myModuleId, 'ServerAddress', "192.168.55.55");
		IPS_ApplyChanges($myModuleId);

		$checkSiteName = UEM_checkSiteName($myModuleId);
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

		$checkSiteName = UEM_checkSiteName($myModuleId);
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

		$checkSiteName = UEM_checkSiteName($myModuleId);
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

		$checkSiteName = UEM_checkSiteName($myModuleId);
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


		/*
			TESTS WITH CONTROLLER TYPE = 1
		 */
		// defaul values von modul instance
		$ControllerType = 1;
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
		IPS_SetProperty($myModuleId, 'ControllerType', $ControllerType);
		IPS_SetProperty($myModuleId, 'ServerAddress', $ServerAddress);
		IPS_SetProperty($myModuleId, 'ServerPort', $ServerPort);
		IPS_SetProperty($myModuleId, 'UserName', $UserName);
		IPS_SetProperty($myModuleId, 'Password', $Password);
		IPS_SetProperty($myModuleId, 'Timer', $Timer);
		IPS_ApplyChanges($myModuleId);

		/* TC21:
			action: create module
			check: several childs created + module status = 104  + action + module status = 102
		 */
		$tdId = 21;
//		$this->assertEquals(true, 0 != count(IPS_GetChildrenIDs($myModuleId)), "TC".$tdId.": initialCreation: several childs created");

		// timer = 0 --> instance inactive
		$this->assertEquals(104, IPS_GetInstance($myModuleId)['InstanceStatus'], "TC".$tdId.": Module GetStatus() return value");
		$checkSiteName = UEM_checkSiteName($myModuleId);
		$this->assertEquals(true, $checkSiteName, "TC".$tdId.": checkSiteName() return value");
		// action --> instance active
		$this->assertEquals(102, IPS_GetInstance($myModuleId)['InstanceStatus'], "TC".$tdId.": Module GetStatus() return value");

		/* TC22:
			action: set wrong ControllerType
			check: module status = 200
		 */
		$tdId++;
		IPS_SetProperty($myModuleId, 'ControllerType', ($ControllerType + 1) % 2);
		IPS_ApplyChanges($myModuleId);

		$checkSiteName = UEM_checkSiteName($myModuleId);
		$this->assertEquals(false, $checkSiteName, "TC".$tdId.": checkSiteName() return value");
		// expected module status: $this->assertEquals(200, IPS_GetInstance($myModuleId)['InstanceStatus'], "TC".$tdId.": Module GetStatus() return value");
		$this->assertEquals(201, IPS_GetInstance($myModuleId)['InstanceStatus'], "TC".$tdId.": Module GetStatus() return value");

		IPS_SetProperty($myModuleId, 'ControllerType', $ControllerType);
		IPS_ApplyChanges($myModuleId);

		/* TC23:
			action: set wrong IP
			check: module status = 200
		 */
		$tdId++;
		IPS_SetProperty($myModuleId, 'ServerAddress', "192.168.55.55");
		IPS_ApplyChanges($myModuleId);

		$checkSiteName = UEM_checkSiteName($myModuleId);
		$this->assertEquals(false, $checkSiteName, "TC".$tdId.": checkSiteName() return value");
		$this->assertEquals(200, IPS_GetInstance($myModuleId)['InstanceStatus'], "TC".$tdId.": Module GetStatus() return value");

		IPS_SetProperty($myModuleId, 'ServerAddress', $ServerAddress);
		IPS_ApplyChanges($myModuleId);

		/* TC24:
			action: set wrong Port
			check: module status = 200
		 */
		$tdId++;
		IPS_SetProperty($myModuleId, 'ServerPort', "5555");
		IPS_ApplyChanges($myModuleId);

		$checkSiteName = UEM_checkSiteName($myModuleId);
		$this->assertEquals(false, $checkSiteName, "TC".$tdId.": checkSiteName() return value");
		$this->assertEquals(200, IPS_GetInstance($myModuleId)['InstanceStatus'], "TC".$tdId.": Module GetStatus() return value");

		IPS_SetProperty($myModuleId, 'ServerPort', $ServerPort);
		IPS_ApplyChanges($myModuleId);

		/* TC25:
			action: set wrong User
			check: module status = 201
		 */
		$tdId++;
		IPS_SetProperty($myModuleId, 'UserName', "wrong_user");
		IPS_ApplyChanges($myModuleId);

		$checkSiteName = UEM_checkSiteName($myModuleId);
		$this->assertEquals(false, $checkSiteName, "TC".$tdId.": checkSiteName() return value");
		$this->assertEquals(201, IPS_GetInstance($myModuleId)['InstanceStatus'], "TC".$tdId.": Module GetStatus() return value");

		IPS_SetProperty($myModuleId, 'UserName', $UserName);
		IPS_ApplyChanges($myModuleId);

		/* TC26:
			action: set wrong Password
			check: module status = 201
		 */
		$tdId++;
		IPS_SetProperty($myModuleId, 'Password', "wrong_password");
		IPS_ApplyChanges($myModuleId);

		$checkSiteName = UEM_checkSiteName($myModuleId);
		$this->assertEquals(false, $checkSiteName, "TC".$tdId.": checkSiteName() return value");
		$this->assertEquals(201, IPS_GetInstance($myModuleId)['InstanceStatus'], "TC".$tdId.": Module GetStatus() return value");

		IPS_SetProperty($myModuleId, 'Password', $Password);
		IPS_ApplyChanges($myModuleId);

		/* TC27:
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