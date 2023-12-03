# UniFi Internet Controller
This module makes it possible to retrieve information about the Internet connection through a USG or DreamMachine, such as the public IP address.

## Table of Contents
1. [Range of functions](#1-range of functions)
2. [Requirements](#2-requirements)
3. [Software installation](#3-software-installation)
4. [Setting up the instances in IP-Symcon](#4-setting-up-the-instances-in-ip-symcon)
5. [Version information](#5-version-information)

## 1. range of functions
* Support for UniFi CloudKey 1 (UC-CK)
* Support for UniFi CloudKey 2 (UCK-G2) and DreamMachine (UDM)
* Polling of the controllers is time-controlled every xx seconds
* Current data points: External IP address

## 2. requirements
- IP-Symcon version 5.5 or higher
- Local user (not owner with mail address!)

## 3. software installation
* Install the 'UniFi Internet Controller' module via the Module Store.
* Alternatively, add the following URL via the Module Control

## 4. set up the instances in IP-Symcon
 Under 'Add Instance' the 'UniFi Internet Controller' module can be found using the quick filter.  
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

**Connection data**
If this switch is activated, the module queries the controller for connection data.

**Debugging**
The module outputs various information in the debug area. 

## 5. version information
Version 0.3 (Beta) - 23-08-2021
* Support for UniFi CloudKey 1
* Support for UniFi CloudKey 2 and DreamMachine
* Query of the external IP address (WAN01)

Version 0.4 (Beta) - 26-08-2021
* Support for 2 WAN adapters

Version 1.0 - 09-09-2021
* WAN1 public IP
* WAN1 availability
* WAN1 latency-average
* WAN1 time-period
* WAN2 public IP
* WAN2 availability
* WAN2 latency-average
* WAN2 time-period
* ISP Name
* ISP Organization
* Unifi Network Version
* Update available
* Update downloaded
* Uptime
* UBNT Device Type
* UDM Version

Version 1.1 - 25-12-2021
* Fix - Memory Leak
* Fix - HTTP Response Error Message

Version 1.2 - 30-12-2021
* New - Improved error handling, especially for incorrect logins

Version 1.3 - 03-01-2022
* New - Unifi API access moved to its own function (better maintainability)

Version 1.31 - 25-01-2022
* New - Display of measured upload and download speed for the WAN connection

Version 1.5 - 03-12-2023
* New - UI tidied up