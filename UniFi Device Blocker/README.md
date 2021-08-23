# UniFi Device Blocker
Dieses Modul ermöglicht es Geräte im Netz zu blockieren, um z.B. den Zugang der Kinder zu Internet nach 20 Uhr zu blockieren.

### Inhaltsverzeichnis

1. [Funktionsumfang](#1-funktionsumfang)
2. [Voraussetzungen](#2-voraussetzungen)
3. [Software-Installation](#3-software-installation)
4. [Einrichten der Instanzen in IP-Symcon](#4-einrichten-der-instanzen-in-ip-symcon)
5. [Versionsinformation](#5-versionsinformation)


### 1. Funktionsumfang

* Unterstützung für UniFi Cloudkey 1
* Unterstützung für UniFi Cloudkey 2 und Dream Maschine
* Anlegen von zu überwachenden Geräten mit Name und MAC Adresse 
* Erstellt pro Gerät eine Variable welche z.B. für die Automation oder Überwachung genutzt werden kann (Boolean)
* Abfragen der Controller erfolgt zeitgesteuert alle xx Sekunden

### 2. Vorraussetzungen

- IP-Symcon ab Version 5.5

### 3. Software-Installation

* Über den Module Store das 'UniFi Presence Manager'-Modul installieren.
* Alternativ über das Module Control folgende URL hinzufügen

### 4. Einrichten der Instanzen in IP-Symcon

 Unter 'Instanz hinzufügen' kann das 'UniFi Presence Manager'-Modul mithilfe des Schnellfilters gefunden werden.  
	- Weitere Informationen zum Hinzufügen von Instanzen in der [Dokumentation der Instanzen](https://www.symcon.de/service/dokumentation/konzepte/instanzen/#Instanz_hinzufügen)

__Konfigurationsseite__:

**Art des Controllers**
Da sich die APIs von CloudKey 1 und CloudKey2/Dreammaschine unterscheiden kann hier der Controller gewählt werden
**Benutzername & Kennwort**
Account mit dem sich das Modul mit dem Controller verbindet
**Site**
Site die im Controller hinterlegt ist 
**IP Adresse und Port**
Bei der Dream Maschine ist der Port 443, bei einem Controller im Standard 8443. IP Addresse des CloudKeys oder der Dream Maschine.
**Aktualisierungsfrequenz**
Da der Controller aktiv abfragt werden muss, kann man hier eine Frequenz hinterlegen wie oft dies geschehen soll. 
**Geräte**
Geräte die Überwacht werden sollen, werden einfach mit einem Namen und einer MAC Addresse in der Tabelle hinterlegt. Das Modul erstellt dann eine Boolean Variable mit Switch Profil welche in weiter Prozesse eingebunden werden kann um ein Gerät zu blocken oder eine blockade aufzulösen. 
Das Modul selbst löscht keine Variablen, sollte sich ein Name ändern, dann wird eine neue erstellt und die alte im Objektbaum belassen. **
**Debugging**
Das Modul gibt divers Informatioen im Debug Bereich des Moduls aus. 

### 5. Versionsinformation

Version 0.3 (Beta) - 23-08-2021
* Unterstützung für UniFi Cloudkey 1
* Unterstützung für UniFi Cloudkey 2 und Dream Maschine
* Anlegen von zu überwachenden Geräten mit Name und MAC Adresse 
* Erstellt pro Gerät eine Variable welche z.B. für die Automation oder Überwachung genutzt werden kann (Boolean)
* Abfragen der Controller erfolgt zeitgesteuert alle xx Sekunden