# UniFi Presence Manager
This module makes it possible to monitor devices in the network, e.g. to enable presence control.

## Table of Contents
1. [Scope of functions](#1-scope of functions)
2. [Requirements](#2-requirements)
3. [Software installation](#3-software-installation)
4. [Setting up the instances in IP-Symcon](#4-setting-up-the-instances-in-ip-symcon)
5. [Version information](#5-version-information)

## 1. range of functions
* Support for UniFi CloudKey 1 (UC-CK)
* Support for UniFi CloudKey 2 (UCK-G2) and DreamMachine (UDM)
* Creation of devices to be monitored with name and MAC address 
* Creates one variable per device which can be used for automation or monitoring (Boolean)
* Polling of the controllers is time-controlled every xx seconds

## 2. requirements
- IP-Symcon version 5.5 or higher
- Local user (not owner with e-mail address!)

## 3. software installation
* Install the 'UniFi Presence Manager' module via the Module Store.
* Alternatively, add the following URL via the Module Control

## 4. set up the instances in IP-Symcon
 Under 'Add Instance' the 'UniFi Presence Manager' module can be found using the quick filter.  
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

**General presence update**
The general presence update variable is always updated independently of the device and can therefore trigger a generic action. 

**Devices**
Devices that are to be monitored are simply stored in the table with a name and a MAC address. The module then creates a Boolean variable with a switch profile which can be integrated into further processes to block or unblock a device. 
The module itself does not delete any variables; if a name changes, a new one is created and the old one is left in the object tree.

**Debugging**
The module outputs various information in the debug area. 

## 5. version information
Version 0.3 (Beta) - 23-08-2021
* Support for UniFi CloudKey 1
* Support for UniFi CloudKey 2 and DreamMachine
* Creation of devices to be monitored with name and MAC address 
* Creates one variable per device which can be used for automation or monitoring (Boolean)
* Polling of the controllers is time-controlled every xx seconds

Version 0.31 (Beta) - 25-08-2021
* New variable that is updated independently of the device with every update

Version 1.0 - 09-09-2021
* No further changes

Version 1.1 - 25-12-2021
* Fix - Memory leak
* Fix - HTTP response error message

Version 1.2 - 30-12-2021
* New - Improved error handling, especially for incorrect logins

Version 1.3 - 03-01-2022
* New - Unifi API access moved to its own function (better maintainability)

Version 1.5 - 03-12-2023
* New - UI tidied up