# UniFi PoE Control
This module allows to restart single PoE ports of UniFi Switch.

## Table of Contents

1. [Range of functions](#1-range of functions)
2. [Requirements](#2-requirements)
3. [Software Installation](#3-software-installation)
4. [Setting up the instances in IP-Symcon](#4-setting-up-instances-in-ip-symcon)
5.[PHP-command-reference](#5-php-command-reference)
6. [Version information](#6-version-information)

## 1. feature set

* Support for UniFi CloudKey 1 (UC-CK)
* Support for UniFi CloudKey 2 (UCK-G2) and DreamMachine (UDM)
* Creation of devices to be monitored with name and MAC address 
* Creates a variable per switch port which can be used for the power cycle of the PoE port (Boolean)
* The module reacts to the change of a variable

## 2. requirements

- IP-Symcon version 5.5 or higher
- Unifi user with owner (not with mail address!) or super admin rights (limited admin rights are not sufficient!)

## 3. software installation

* Install the 'UniFi PoE Control' module via the Module Store.
* Alternatively, add the following URL via the Module Control

## 4. set up the instances in IP-Symcon

Under 'Add Instance' the 'UniFi PoE Control' module can be found using the quick filter.  
Further information on adding instances in the [Documentation of the instances](https://www.symcon.de/service/dokumentation/konzepte/instanzen/#Instanz_hinzufügen)

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

Switches whose ports are to be cycled are simply entered in the table with a name and a MAC address. The number of ports must also be specified.
The module then creates a Boolean variable with a switch profile for each switch port, which can be integrated into further processes to restart a device (=true). After a successful power cycle, the variable is reset to false.
The module itself does not delete any variables; if a name changes, a new one is created and the old one is left in the object tree.

**Debugging**

The module outputs various information in the debug area. 

## 5. version information

Version 1.4 - 27.11-2022
* New module: PoE Control for restarting PoE devices via power cycle of the switch port

Version 1.5 - 03-12-2023
* New - UI tidied up
