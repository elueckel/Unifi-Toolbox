# UniFi Toolbox

Die UniFi Toolbox ist eine Sammlung von Modulen, welche verschiedene Aktionen in Verbindung mit einem UniFi Netzwerk Controller unterstützen. 

## Inhaltsverzeichnis

1. [Voraussetzungen](#1-voraussetzungen)
2. [Installation](#2-installation)
3. [Komponenten des Moduls](#3-komponenten-des-moduls)
4. [Wird Hilfe benötigt oder hast du Vorschläge/Ideen?](#4-wird-hilfe-benötigt-oder-hast-du-vorschlägeideen)
5. [Mitarbeit](#5-mitarbeit)


## 1. Voraussetzungen

* Symcon 5.5 oder höher
* UniFi DreamMachine / UniFi Controller
* lokaler User (nicht Owner mit Mailadresse!)


## 2. Installation

* Das Modul kann einfach über das Module Control oder zukünftig den Module Store geladen werden. Die URL hierzu ist: https://github.com/elueckel/Unifi-Toolbox 


## 3. Komponenten des Moduls

Folgende Module beinhaltet das UniFi Toolbox Repository:

- __UniFi Presence Manager__ ([Dokumentation](UniFi%20Presence%20Manager))  
	Mit dem Presence oder Anwesenheitsmanager ist es möglich Geräte die mit dem Netzwerk verbunden sind zu überwachen, z.B. um die Anwesenheit zu bestimmen.

- __UniFi Internet Controller__ ([Dokumentation](UniFi%20Internet%20Controller))  
	Der Internet Controller ermöglich die Erfassung von Informationen zur Internetverbindung, wenn eine USG oder DreamMachine eingesetzt wird.
	
- __UniFi Device Blocker__ ([Dokumentation](UniFi%20Device%20Blocker))  
	Mit dem Device Blocker können Geräte anhand ihrer MAC Adresse vom Zugang auf das Netzwerk geblockt werden, z.B. um die Nutzung von Geräten im Kinderzimmer nach 20 Uhr zu sperren und am Morgen wieder zu aktivieren.

- __UniFi Device Monitor__ ([Dokumentation](UniFi%20Device%20Monitor))  
	Mit dem Device Monitor können Geräte von UniFi überwacht werden - es stehen bei Firewalls (UDM/USG) Daten zur Internetverbindung zur Verfügung, bei generischen Geräten Daten zum Status und Hardware.

- __UniFi Endpoint Monitor__ ([Dokumentation](UniFi%20Endpoint%20Monitor))  
	Mit dem Endpoint Monitor können mit den UniFi Netzwerkverbunde Geräte überwacht werden. Hierbei wird zwischen Kabel und WLAN Verbindungen unterschieden, da im WLAN weit mehr Daten zur Verfügung stehen. 

Für detaillierte Informationen zu den Modulen, wie z.B. zur Version bitte die Hilfeseiten der Module besuchen. 

Dieses Modul ist für die nicht kommerzielle Nutzung kostenlos - bei kommerzieller Nutzung bitte den Author kontaktieren. 


## 4. Wird Hilfe benötigt oder hast du Vorschläge/Ideen?

Wir sind gerne bereit dir zu helfen. Hast du Fragen oder Probleme, erstelle einfach eine Frage in [GitHub-Discussions](https://github.com/elueckel/Unifi-Toolbox/discussions).

Es sind auch alle Vorschläge für Erweiterungen und Verbesserungen willkommen, welche ebenfalls in [GitHub-Discussions](https://github.com/elueckel/Unifi-Toolbox/discussions) diskutiert werden können.

Sofern ein Fehler/Problem gefunden wurde, kann direkt ein Fehlerticket unter [GitHub-Issues](https://github.com/elueckel/Unifi-Toolbox/issues) erstellt werden.


## 5. Mitarbeit

Es ist noch einiges an Arbeit zu tun, daher ist jede Unterstützung willkommen!

Wenn du gerne mitarbeiten möchtest und deinen eigenen Beitrag leisten möchtest, hast du folgende Möglichkeiten:
- ein Ticket öffnen und dort deine Code-Änderung erläutern
- deine Code-Änderung direkt zum Review einchecken und einen Pull-Request erstellen

Der Einstieg zum Aufsetzen von Visual Studio Code ist [hier](https://github.com/elueckel/Unifi-Toolbox/discussions/42) beschrieben und gerne wird dir auch dort geholfen, sofern du weitere Startschwierigkeiten haben solltest.
