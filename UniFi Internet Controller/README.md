# UniFi Internet Controller
Dieses Modul ermöglicht es Informationen über die Internetverbindung durch eine USG oder DreamMachine, wie z.B. die öffentliche IP-Adresse abzurufen.

## Inhaltsverzeichnis

1. [Funktionsumfang](#1-funktionsumfang)
2. [Voraussetzungen](#2-voraussetzungen)
3. [Software-Installation](#3-software-installation)
4. [Einrichten der Instanzen in IP-Symcon](#4-einrichten-der-instanzen-in-ip-symcon)
5. [Versionsinformation](#5-versionsinformation)

## 1. Funktionsumfang

* Unterstützung für UniFi CloudKey 1 (UC-CK)
* Unterstützung für UniFi CloudKey 2 (UCK-G2) und DreamMachine (UDM)
* Abfragen der Controller erfolgt zeitgesteuert alle xx Sekunden
* Aktuelle Datenpunkte: Externe IP-Adresse

## 2. Voraussetzungen

- IP-Symcon ab Version 5.5
- lokaler User (nicht Owner mit Mailadresse!)

## 3. Software-Installation

* Über den Module Store das 'UniFi Internet Controller'-Modul installieren.
* Alternativ über das Module Control folgende URL hinzufügen

## 4. Einrichten der Instanzen in IP-Symcon

 Unter 'Instanz hinzufügen' kann das 'UniFi Internet Controller'-Modul mithilfe des Schnellfilters gefunden werden.  
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

**Verbindungsdaten**

Wenn dieser Schalter aktiviert ist, fragt das Modul den Controller in Bezug auf Verbindungsdaten ab.

**Debugging**

Das Modul gibt diverse Informationen im Debug Bereich aus. 

## 5. Versionsinformation

Version 0.3 (Beta) - 23-08-2021
* Unterstützung für UniFi CloudKey 1
* Unterstützung für UniFi CloudKey 2 und DreamMachine
* Abfrage der externen IP-Adresse (WAN01)

Version 0.4 (Beta) - 26-08-2021
* Unterstützung für 2 WAN Adapter

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
* Neu - Verbessertes Fehlerhandling vor allem bei falschen Logins

Version 1.3 - 03-01-2022
* Neu - Unifi API Zugriff in eigene Funktion ausgelagert (bessere Wartbarkeit)

Version 1.31 - 25-01-2022
* Neu - Anzeige gemessener Up und Download Speed für die WAN Verbidnung

Version 1.5 - 03-12-2023
* Neu - UI Aufgeräumt
