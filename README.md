Abfallkalender API für den WZV Kreis Segeberg

Dieses PHP-Skript ruft die Abfuhrtermine für eine bestimmte Adresse im Kreis Segeberg vom WZV ab und gibt sie als JSON aus. Zudem kann die iCal-Datei heruntergeladen werden. Die API ist zur Einbindung in Loxone gedacht.

Funktionen

📅 Abruf der Abfalltermine als JSON

🗑️ Filterung nach Müllart

📥 Download der iCal-Datei

Anforderungen

✅ PHP 7.4 oder höher

🌐 Internetverbindung

Installation

📂 Kopiere die Datei auf deinen Server.

⚙️ Stelle sicher, dass der Server PHP ausführt.

✏️ Passe die Adresse in den Variablen $ort, $strasse und $hausnr an.

$ort="Musterstadt";
$strasse="Musterstraße";
$hausnr="12a";

Verwendung

JSON-Abruf aller Abfuhrtermine

curl "https://deinserver.de/abfallkalender.php"

JSON-Abruf für eine bestimmte Müllart (z. B. Bio)

curl "https://deinserver.de/abfallkalender.php?tonne=Bio"

iCal-Datei herunterladen

curl -O "https://deinserver.de/abfallkalender.php?download=true"

Anpassung

🎨 Die Farben der Mülltypen können in der $colors-Variable geändert werden.

🏷️ Die Namen der Mülltypen sind in der $titles-Variable definiert.

🛠️ Falls weitere Filter oder Anpassungen benötigt werden, kann der reguläre Ausdruck in parseIcalData() angepasst werden.

📡 Einbindung in Loxone

Die JSON-Daten können in Loxone über den HTTP-Request Eingang verwendet werden. Dies ermöglicht die Anzeige der nächsten Abfuhrtermine direkt im Loxone Smart Home System.

🔒 Sicherheitshinweise

Da die Adresse direkt im Code steht, sollte das Skript nicht öffentlich zugänglich sein oder die Adresse extern über Parameter definiert werden.

Es wird empfohlen, das Skript nur auf vertrauenswürdigen Servern zu hosten.

📜 Lizenz

Dieses Projekt steht unter der MIT-Lizenz.

## Donation

If this project helps you, you can give me a cup of coffee

[![Donate](https://img.shields.io/badge/Donate-PayPal-green.svg)](https://paypal.me/bastyjuice)
