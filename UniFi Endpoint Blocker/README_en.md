# UniFi Endpoint Blocker
This module makes it possible to block end devices in the network, e.g. to block children's access to the Internet after 8 pm.

## Table of contents
1. [Range of functions](#1-range of functions)
2. [Requirements](#2-requirements)
3. [Software installation](#3-software-installation)
4. [Setting up the instances in IP-Symcon](#4-setting-up-instances-in-ip-symcon)
5.[PHP-command-reference](#5-php-command-reference)
6. [Version information](#6-version-information)

## 1. feature set
* Support for UniFi CloudKey 1 (UC-CK)
* Support for UniFi CloudKey 2 (UCK-G2) and DreamMachine (UDM)
* Creation of devices to be monitored with name and MAC address 
* Creates one variable per device which can be used e.g. for automation or monitoring (Boolean)
* The module reacts to the change of a variable

## 2. requirements
- IP-Symcon version 5.5 or higher
- Unifi user with owner (not with mail address!) or super admin rights (limited admin rights are not sufficient!)

## 3. software installation
* Install the 'UniFi Endpoint Blocker' module via the Module Store.
* Alternatively, add the following URL via the Module Control

## 4. set up the instances in IP-Symcon
 Under 'Add Instance' the 'UniFi Endpoint Blocker' module can be found using the quick filter.  
	- Further information on adding instances in the [Documentation of the instances](https://www.symcon.de/service/dokumentation/konzepte/instanzen/#Instanz_hinzufügen)

Configuration page__:

**Type of controller**
As the APIs of CloudKey 1 and CloudKey2/DreamMachine differ, the controller can be selected here

**Username & Password**
Account with which the module connects to the controller

**Site**
Site name that is stored in the controller (default: "default").

ATTENTION: Do not confuse this with the site description, which can be changed in the controller GUI, for example!

The site name can be checked by clicking on "check Site Name".

**IP address and port**
For the DreamMachine the port is 443, for a controller it is 8443 by default. IP address of the CloudKey or the DreamMachine.

**Update frequency**
As the controller must be actively polled, you can enter a frequency here for how often this should happen. 

**Devices**
Devices that are to be monitored are simply stored in the table with a name and a MAC address. 
The module then creates a Boolean variable with a switch profile, which can be integrated into further processes to block (=false) or unblock (=true) a device.
The module itself does not delete any variables; if a name changes, a new one is created and the old one is left in the object tree.

**Debugging**
The module outputs various information in the debug area. 

### 5. PHP command reference

#### Recommendation
If only one instance of the UniFi Endpoint Blocker is in use, the $InstanceID should be determined dynamically as follows and not set statically, as deleting and reinstalling the Unifi Endpoint Blocker instance has no effect on other scripts:

```PHP
$InstanzID = IPS_GetInstanceListByModuleID("{FC3E71F1-BF95-D45D-0676-BA3D10D02CB8}")[0];
```

#### Functions

```PHP
bool UEB_block(int $InstanzID, string $DeviceMacAddress)
```

Blocks the device with the MAC address $DeviceMacAddress, which was configured in the Endpoint Blocker instance $InstanceID.
Returns false if device was not found in Endpoint Blocker instance, otherwise true.

```PHP
bool UEB_unblock(int $InstanzID, string $DeviceMacAddress)
```
Allows the device with the MAC address $DeviceMacAddress, which was configured in the Endpoint Blocker instance $InstanceID.
Returns false if device was not found in Endpoint Blocker instance, otherwise true.

## 6. version information
Version 0.3 (Beta) - 23-08-2021
* Support for UniFi CloudKey 1
* Support for UniFi CloudKey 2 and DreamMachine
* Creation of devices to be monitored with name and MAC address 
* Creates a variable per device which can be used for automation or monitoring (Boolean)
* Polling of the controllers is time-controlled every xx seconds

Version 0.31 (Beta) - 27-08-2021
* Fix in the logic for blocking

Version 1.0 - 09-09-2021
* No further changes

Version 1.1 - 25-12-2021
* Fix - Memory leak
* Fix - HTTP response error message
* Changed variable management - the MAC is now used for Ident

Version 1.2 - 30-12-2021
* New - Improved error handling especially for wrong logins
* New - Block can now be called as a function

Version 1.3 - 03-01-2022
* New - Unifi API access moved to its own function (better maintainability)
* New - Device Blocker is now called Endpoint Blocker

Version 1.5 - 03-12-2023
* New - UI tidied up

Version 1.6 - 25-08-2024 
* No changes