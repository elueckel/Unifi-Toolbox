![Code](https://img.shields.io/badge/Code-PHP-blue.svg)
[![Version](https://img.shields.io/badge/Symcon-PHPModul-red.svg)](https://www.symcon.de/service/dokumentation/entwicklerbereich/sdk-tools/sdk-php/)
[![Version](https://img.shields.io/badge/Symcon%20Version-5.5%20%3C-green.svg)](https://www.symcon.de/service/dokumentation/installation/migrationen/)
[![GitHub Stars](https://img.shields.io/github/stars/elueckel/Unifi-Toolbox.svg?logo=github)](https://github.com/elueckel/Unifi-Toolbox/stargazers)
[![GitHub Release](https://img.shields.io/github/v/release/elueckel/Unifi-Toolbox.svg?logo=github)](https://github.com/elueckel/Unifi-Toolbox/releases)

# UniFi Toolbox

The UniFi Toolbox is a collection of modules that support various actions in conjunction with a UniFi Network Controller. 

## Table of Contents

1. [Prerequisites](#1-requirements)
2. [Installation](#2-installation)
3. [Components of the module](#3-components-of-the-module)
4. [Help is needed or do you have suggestions/ideas](#4-help-is-needed-or-do-you-have-suggestions-ideas)
5. [Collaboration](#5-collaboration)


## 1. requirements

* Symcon 5.5 or higher
* UniFi DreamMachine / UniFi Controller
* local user (not owner with mail address!)


## 2. installation

#### Variant 1 (recommended): Module Management (Module Control)

Manually add the URL `https://github.com/elueckel/Unifi-Toolbox` via the Module Control contained in the IP Symcon Console (under Core Instances/Core Instances).

The module is then available and a Unifi-Toolbox module instance can be added.

Note: By clicking on the gear icon, you can easily switch between "main" (stable and tested versions) and "beta" (contains the latest functions, some of which have been tested and may contain errors) for testing purposes.


#### Variant 2: Module Store

Install the 'Unifi Toolbox' module via the Module Store integrated in the IP Symcon Console.

The module is then available and a Unifi-Toolbox module instance can be added.


## 3. components of the module

The UniFi Toolbox Repository contains the following modules:

- __UniFi Presence Manager__ ([Documentation](UniFi%20Presence%20Manager))  
	With the Presence Manager it is possible to monitor devices that are connected to the network, e.g. to determine the presence.

- __UniFi Internet Controller__ ([Documentation](UniFi%20Internet%20Controller))  
	The Internet Controller enables the collection of information about the Internet connection when a USG or DreamMachine is used.
	
- __UniFi Endpoint Blocker__ ([Documentation](UniFi%20Endpoint%20Blocker))  
	With the Endpoint Blocker, devices can be blocked from accessing the network based on their MAC address, e.g. to block the use of devices in the children's room after 8 p.m. and reactivate them in the morning.

- __UniFi Device Monitor__ ([Documentation](UniFi%20Device%20Monitor))  
	The Device Monitor can be used to monitor UniFi devices - for firewalls (UDM/USG) data on the Internet connection is available, for generic devices data on the status and hardware.

- __UniFi Endpoint Monitor__ ([Documentation](UniFi%20Endpoint%20Monitor))  
	The Multi Endpoint Monitor can be used to monitor multiple devices connected to the UniFi network. A distinction is made between cable and WLAN connections, as much more data is available in the WLAN. 

- __UniFi Multi Endpoint Monitor__ ([Documentation](UniFi%20Multi%20Endpoint%20Monitor))  
	The Endpoint Monitor can be used to monitor individual devices connected to the UniFi network. A distinction is made between cable and WLAN connections, as much more data is available in the WLAN. 

- __UniFi PoE Control__ ([Documentation](UniFi%20PoE%20Control))  
	With the PoE Control module, individual PoE ports can be restarted, e.g. to perform a test on a webcam or similar. 

For detailed information about the modules, such as the version, please visit the help pages of the modules. 

This module is free for non-commercial use - for commercial use please contact the author. 


## 4. Do you need help or do you have suggestions/ideas?

We are happy to help you. If you have any questions or problems, just post a question in [GitHub Discussions](https://github.com/elueckel/Unifi-Toolbox/discussions).

All suggestions for enhancements and improvements are also welcome and can be discussed in [GitHub-Discussions](https://github.com/elueckel/Unifi-Toolbox/discussions).

If a bug/problem is found, a bug ticket can be created directly at [GitHub-Issues](https://github.com/elueckel/Unifi-Toolbox/issues).