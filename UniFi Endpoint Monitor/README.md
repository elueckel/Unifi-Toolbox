# UniFi Endpoint Monitor
Dieses Modul ermöglicht es Endgeräte im Netz zu überwachen und diverse Daten in Symcon darzustellen.

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

Site die im Controller hinterlegt ist 

**IP-Adresse und Port**

Bei der DreamMachine ist der Port 443, bei einem Controller im Standard 8443. IP-Adresse des CloudKeys oder der DreamMachine.

**Aktualisierungsfrequenz**

Da der Controller aktiv abfragt werden muss, kann man hier eine Frequenz hinterlegen wie oft dies geschehen soll. 

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

Version 1.2 - 27-12-2021
* Neu - Modul verfügbar

Version 1.3 - 01-01-2022
* Fix - Geräte die nicht mit dem Netzwerk verbunden sind erzeugen keine Fehler mehr