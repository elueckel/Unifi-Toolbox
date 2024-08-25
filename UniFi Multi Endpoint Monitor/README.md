# UniFi Multi Endpoint Monitor
Dieses Modul ermöglicht es viele Endgeräte im Netz zu überwachen und diverse Daten in Symcon darzustellen.

## Inhaltsverzeichnis

1. [Funktionsumfang](#1-funktionsumfang)
2. [Voraussetzungen](#2-voraussetzungen)
3. [Software-Installation](#3-software-installation)
4. [Einrichten der Instanzen in IP-Symcon](#4-einrichten-der-instanzen-in-ip-symcon)
5. [Versionsinformation](#5-versionsinformation)

## 1. Funktionsumfang

* Unterstützung für UniFi CloudKey 1 (UC-CK)
* Unterstützung für UniFi CloudKey 2 (UCK-G2) und DreamMachine (UDM)
* Anlegen von zu überwachenden Geräten mit Name und MAC Adresse 
* Erstellt pro Gerät eine Variable welche z.B. für die Automation oder Überwachung genutzt werden kann (Boolean)
* Abfragen der Controller erfolgt zeitgesteuert alle xx Sekunden

## 2. Voraussetzungen

- IP-Symcon ab Version 5.5
- lokaler User (nicht Owner mit Mailadresse!)

## 3. Software-Installation

* Über den Module Store das 'UniFi Presence Manager'-Modul installieren.
* Alternativ über das Module Control folgende URL hinzufügen

## 4. Einrichten der Instanzen in IP-Symcon

 Unter 'Instanz hinzufügen' kann das 'UniFi Presence Manager'-Modul mithilfe des Schnellfilters gefunden werden.  
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

**Tabelle für Endgeräte**
In der Tabelle die Geräte die überwacht werden sollen eintragen. Hierfür muss ein Name vergeben werden (mindestens 3 Buchstaben), die MAC Adresse und die Art der Verbindung.

**MAC Adresse**

Die MAC Adresse des zu überwachenden Geräts. Diese am besten aus dem Controller auslesen.

**Art der Verbindung**

Basierend auf der Art der Verbindung stehen unterschiedliche Informationen zur Verfügung. Die meisten werden von Geräten im WLAN generiert. 

**Netzwerkdaten**

Netzwerkdaten umfassen logische Daten wie IP-Adresse oder Hostname. 

**Verbindungsdaten**

Hier werden verfügbare physische Daten bereitgestellt. Hierzu zählen bei WLAN-Geräten Informationen zu Verbindungsqualität, Zuletzt gesehen, Uptime, Kanal, Art der Technologie, SSID, Dämpfung, Signalstärke. 

**Übertragungsdaten**

Bei WLAN-Geräten werden hier Informationen zu übertragenen Daten und Paketen geliefert.

**Debugging**

Das Modul gibt diverse Informationen im Debug Bereich aus. 

## 5. Versionsinformation

Version 1.5 - 02-12-2023
* Neu - Modul verfügbar

Version 1.6 - 25-08-2024 (Credit M70 - Danke)
* Fix - Connection wurde nicht gesetzt
* Fix - Satisfaction wurde nicht aktualisiert
* Fix - IP wurde nicht gesetzt
* Fix - LastSeen wird auf Zeit von Symcon korrigiert
* Fix - Modul zieht jetzt nicht mehr die Werte für WLAN Geräte bei LAN Komponenten
* Change - SetValue durch $this->SetValue ersetzt