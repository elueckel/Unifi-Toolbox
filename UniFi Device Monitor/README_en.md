# UniFi Device Monitor
This module makes it possible to monitor Unifi devices in the network and to query connection and hardware data, among other things.

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

**MAC address**
The MAC address of the device to be monitored. This is best read from the controller.

**Device type**
Choice between UDM/USG devices that are connected to the Internet and generic devices. Less data is available for generic devices. 

**Basic data - Available for all devices**
Network data includes logical data such as device model, software version, satisfaction, last seen, uptime, name. 

**Hardware data - Available for all devices**
This setting reads the CPU and memory load.

**Connection data - only available for firewalls such as UDM/USG**
Network data such as public IP, transmitted data, packets and errors

**Transmission data**
For WLAN devices, information on transmitted data and packets is provided here.

**Debugging**
The module outputs various information in the debug area. 

## 5. version information
Version 1.2 - 28-12-2021
* New - Module available

Version 1.3 - 03-01-2022
* New - Unifi API access moved to its own function (better maintainability)

Version 1.5 - 03-12-2023
* New - UI tidied up

Version 1.6 - 01-09-2024
* New - Number of connected devices are shown
* Change - SetValue replaced by $this->SetValue 