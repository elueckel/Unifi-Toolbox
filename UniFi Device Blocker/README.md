# UniFi Endpoint Blocker
Dieses Modul ermöglicht es Endgeräte im Netz zu blockieren, um z.B. den Zugang der Kinder zu Internet nach 20 Uhr zu blockieren.

## Inhaltsverzeichnis

1. [Funktionsumfang](#1-funktionsumfang)
2. [Voraussetzungen](#2-voraussetzungen)
3. [Software-Installation](#3-software-installation)
4. [Einrichten der Instanzen in IP-Symcon](#4-einrichten-der-instanzen-in-ip-symcon)
5. [PHP-Befehlsreferenz](#5-php-befehlsreferenz)
6. [Versionsinformation](#6-versionsinformation)


## 1. Funktionsumfang

* Unterstützung für UniFi CloudKey 1 (UC-CK)
* Unterstützung für UniFi CloudKey 2 (UCK-G2) und DreamMachine (UDM)
* Anlegen von zu überwachenden Geräten mit Name und MAC Adresse 
* Erstellt pro Gerät eine Variable welche z.B. für die Automation oder Überwachung genutzt werden kann (Boolean)
* Das Modul reagiert auf die Änderung einer Variable

## 2. Voraussetzungen

- IP-Symcon ab Version 5.5
- Unifi Benutzer mit Owner (nicht mit Mailadresse!) oder Super-Admin Rechten (Limited-Admin Rechte sind nicht ausreichend!)

## 3. Software-Installation

* Über den Module Store das 'UniFi Endpoint Blocker'-Modul installieren.
* Alternativ über das Module Control folgende URL hinzufügen

## 4. Einrichten der Instanzen in IP-Symcon

 Unter 'Instanz hinzufügen' kann das 'UniFi Endpoint Blocker'-Modul mithilfe des Schnellfilters gefunden werden.  
	- Weitere Informationen zum Hinzufügen von Instanzen in der [Dokumentation der Instanzen](https://www.symcon.de/service/dokumentation/konzepte/instanzen/#Instanz_hinzufügen)

__Konfigurationsseite__:

**Art des Controllers**

Da sich die APIs von CloudKey 1 und CloudKey2/DreamMachine unterscheiden, kann hier der Controller gewählt werden

**Benutzername & Kennwort**

Account mit dem sich das Modul mit dem Controller verbindet

**Site**

Site-Name der im Controller hinterlegt ist (Standard: "default").

ACHTUNG: Nicht mit Site-Description verwechseln, welche bspw. in der Controller GUI geändert werden kann!

Der Site-Name kann überprüft werden mit einem Klick auf "check Site Name".

**IP-Adresse und Port**

Bei der DreamMachine ist der Port 443, bei einem Controller im Standard 8443. IP-Adresse des CloudKeys oder der DreamMachine.

**Aktualisierungsfrequenz**

Da der Controller aktiv abfragt werden muss, kann man hier eine Frequenz hinterlegen wie oft dies geschehen soll. 

**Geräte**

Geräte die Überwacht werden sollen, werden einfach mit einem Namen und einer MAC Addresse in der Tabelle hinterlegt. 
Das Modul erstellt dann eine Boolean-Variable mit Switch-Profil, welche in weiter Prozesse eingebunden werden kann um ein Gerät zu blocken (=false) oder eine blockade aufzulösen (=true).
Das Modul selbst löscht keine Variablen, sollte sich ein Name ändern, dann wird eine neue erstellt und die alte im Objektbaum belassen.

**Debugging**

Das Modul gibt diverse Informationen im Debug Bereich aus. 

### 5. PHP-Befehlsreferenz

#### Empfehlung
Sofern nur eine Instanz des UniFi Endpoint Blocker im Einsatz ist, sollte die $InstanzID wie folgt dynamisch ermittelt werden und nicht statisch gesetzt werden, da somit ein Löschen und Neuinstallieren der Unifi Device Blocker Instanz keine Auswirkung auf andere Skripte hat:

`$InstanzID = IPS_GetInstanceListByModuleID("{FC3E71F1-BF95-D45D-0676-BA3D10D02CB8}")[0];`


#### Funktionen

`bool UEB_block(int $InstanzID, string $DeviceMacAddress)`

Blockiert das Gerät mit der MAC Adresse $DeviceMacAddress, welche in der DeviceBlocker Instanz $InstanzID konfiguriert wurde.
Gibt false zurück, wenn Gerät in EndpointBlocker Instanz nicht gefunden wurde, ansonsten true.

`bool UEB_unblock(int $InstanzID, string $DeviceMacAddress)`

Erlaubt das Gerät mit der MAC Adresse $DeviceMacAddress, welche in der DeviceBlocker Instanz $InstanzID konfiguriert wurde.
Gibt false zurück, wenn Gerät in DeviceBlocker Instanz nicht gefunden wurde, ansonsten true.


## 6. Versionsinformation

Version 0.3 (Beta) - 23-08-2021
* Unterstützung für UniFi CloudKey 1
* Unterstützung für UniFi CloudKey 2 und DreamMachine
* Anlegen von zu überwachenden Geräten mit Name und MAC Adresse 
* Erstellt pro Gerät eine Variable welche z.B. für die Automation oder Überwachung genutzt werden kann (Boolean)
* Abfragen der Controller erfolgt zeitgesteuert alle xx Sekunden

Version 0.31 (Beta) - 27-08-2021
* Fix bei der Logik zum blocken

Version 1.0 - 09-09-2021
* keine weiteren Änderungen

Version 1.1 - 25-12-2021
* Fix - Memory Leak
* Fix - HTTP Response Error Message
* Variablen Management umgestellt - es Ident wird jetzt die MAC verwendet

Version 1.2 - 30-12-2021
* Neu - Verbessertes Fehlerhandling vor allem bei falschen Logins
* Neu - Block kann jetzt als Funktion aufgerufen werden

Version 1.3 - 03-01-2022
* Neu - Unifi API Zugriff in eigene Funktion ausgelagert (bessere Wartbarkeit)
* Neu Device Blocker heisst jetzt Endpoint Blocker
