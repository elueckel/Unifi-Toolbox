# UniFi Presence Manager
Dieses Modul ermöglicht es Geräte im Netz zu überwachen, um z.B. eine Anwsenheitskontrolle zu ermöglichen.

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

Kann ermittelt werden mit: https://<< IP >>:<< PORT >>/proxy/network/api/self/sites bzw. https://<< IP >>:<< PORT >>/api/self/sites

**IP-Adresse und Port**

Bei der DreamMachine ist der Port 443, bei einem Controller im Standard 8443. IP-Adresse des CloudKeys oder der DreamMachine.

**Aktualisierungsfrequenz**

Da der Controller aktiv abfragt werden muss, kann man hier eine Frequenz hinterlegen wie oft dies geschehen soll. 

**Allgemeine Anwesenheit Aktualisier**

Die Allgemeine Anwesenheit Aktualisiert Variable wird immer unabhängig vom Gerät aktualisiert und kann somit eine generische Aktion auslösen. 

**Geräte**

Geräte die Überwacht werden sollen, werden einfach mit einem Namen und einer MAC Addresse in der Tabelle hinterlegt. Das Modul erstellt dann eine Boolean Variable mit Switch Profil welche in weiter Prozesse eingebunden werden kann um ein Gerät zu blocken oder eine blockade aufzulösen. 
Das Modul selbst löscht keine Variablen, sollte sich ein Name ändern, dann wird eine neue erstellt und die alte im Objektbaum belassen.

**Debugging**

Das Modul gibt diverse Informationen im Debug Bereich aus. 

## 5. Versionsinformation

Version 0.3 (Beta) - 23-08-2021
* Unterstützung für UniFi CloudKey 1
* Unterstützung für UniFi CloudKey 2 und DreamMachine
* Anlegen von zu überwachenden Geräten mit Name und MAC Adresse 
* Erstellt pro Gerät eine Variable welche z.B. für die Automation oder Überwachung genutzt werden kann (Boolean)
* Abfragen der Controller erfolgt zeitgesteuert alle xx Sekunden

Version 0.31 (Beta) - 25-08-2021
* Neue Variable die bei jedem Update unabhängig vom Gerät aktualisiert wird

Version 1.0 - 09-09-2021
* keine weiteren Änderungen

Version 1.1 - 25-12-2021
* Fix - Memory Leak
* Fix - HTTP Response Error Message

Version 1.2 - 30-12-2021
* Neu - Verbessertes Fehlerhandling vor allem bei falschen Logins

Version 1.3 - 03-01-2022
* Neu - Unifi API Zugriff in eigene Funktion ausgelagert (bessere Wartbarkeit)
