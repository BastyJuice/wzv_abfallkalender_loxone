<?php

header('Content-Type: application/json; charset=UTF-8'); // Setzt den Header für JSON-Ausgabe mit UTF-8

date_default_timezone_set('Europe/Berlin'); // Setzt die Zeitzone explizit auf Europe/Berlin

// Dein Wohnort
$ort="Musterstadt";
$strasse="Musterstraße";
$hausnr="1";

$thisyear = date("Y");
$url = "https://web.c-trace.de/segebergwzv-abfallkalender/(S(fj4kgdn2fjl1y3beftgp4ofn))/abfallkalender/cal/$thisyear?Ort=$ort&Strasse=$strasse&Hausnr=$hausnr&abfall=0|1|2|3|";

// Prüfen, ob die Datei heruntergeladen werden soll
if (isset($_GET['download'])) {
    downloadIcalFile($url);
    exit;
}

$icalData = fetchIcalData($url);
$filter = isset($_GET['tonne']) ? $_GET['tonne'] : null;
parseIcalData($icalData, $filter);

function fetchIcalData($url)
{
    $icalData = file_get_contents($url);
    if ($icalData === false) {
        die(json_encode(["error" => "Fehler beim Abrufen der iCal-Datei"]));
    }
    return utf8_encode($icalData); // Sicherstellen, dass Umlaute korrekt sind
}

function downloadIcalFile($url)
{
    $icalData = fetchIcalData($url);
    if (!$icalData) {
        die(json_encode(["error" => "Fehler beim Abrufen der iCal-Datei"]));
    }
    
    header('Content-Type: text/calendar; charset=UTF-8');
    header('Content-Disposition: attachment; filename="abfallkalender_' . date('Y') . '.ics"');
    echo $icalData;
    exit;
}

function cleanLocation($location)
{
    $location = utf8_decode(trim($location));
    $location = str_replace(["\\", "/"], "", $location); // Entfernt Backslashes und Schrägstriche
    $location = preg_replace('/\s+/', ' ', $location); // Entfernt doppelte Leerzeichen
    return $location;
}

function parseIcalData($icalData, $filter = null)
{
    $events = [];
    preg_match_all(
        '/BEGIN:VEVENT.*?UID:(.*?)\nDTSTAMP:(.*?)\nCATEGORIES:.*?\nDESCRIPTION:(.*?)\nDTSTART;VALUE=DATE:(.*?)\nDTEND;VALUE=DATE:.*?\nLOCATION:(.*?)\nSUMMARY:Abfuhr: (.*?)\n.*?END:VEVENT/s',
        $icalData,
        $matches,
        PREG_SET_ORDER
    );

    $colors = [
        "Restabfall" => "44484A",
        "Papier" => "0E518D",
        "Bio" => "8A6642",
        "Gelber Sack" => "f4ff00"
    ];

    $titles = [
        "Restabfall" => "Schwarze Tonne",
        "Papier" => "Blaue Tonne",
        "Bio" => "Braune Tonne",
        "Gelber Sack" => "Gelbe Säcke"
    ];

    // Loxone Offset für Zeitrechnung (1.1.2009 - 1.1.1970 in Sekunden)
    $loxone_offset = 1230768000;

    foreach ($matches as $match) {
        $uid = trim($match[1]);
        $timestamp = trim($match[2]);
        $description = utf8_decode(trim($match[3]));
        $date = trim($match[4]);
        $location = cleanLocation($match[5]);
        $trash_name = trim($match[6]);
        
        $formatted_date = date("d.m.Y", strtotime($date));
        $time = "00:00:00";

        // Timestamp wird auf 23:59 Uhr gesetzt, damit der Termin den ganzen Tag sichtbar bleibt
        $datetime = new DateTime($date . " 23:59:59", new DateTimeZone('UTC'));
        $unix_timestamp = $datetime->getTimestamp();

        if ($unix_timestamp === false) {
            continue; // Fehlerhafte Datumswerte überspringen
        }

        $days_remaining = ceil(($unix_timestamp - time()) / 86400);
        $loxone_timestamp = round($unix_timestamp - $loxone_offset);
        
        $events[] = [
            "id"=>$uid,
            "title"=>$titles[$trash_name] ?? "Unbekannte Tonne",
            "trash_name"=>$trash_name,
            "location"=>$location,
            "time"=>$time,
            "date"=>$formatted_date,
            "timestamp"=>$unix_timestamp,
            "timestamp_loxone"=>$loxone_timestamp,
            "days_left"=>max(0, $days_remaining),
            "description"=>$description,
            "color"=>$colors[$trash_name] ?? "000000"
        ];
    }

    usort($events, function ($a, $b) {
        return $a['timestamp'] - $b['timestamp'];
    });

    if ($filter) {
        $filteredEvents = array_filter($events, function ($event) use ($filter) {
            return strtolower($event['trash_name']) === strtolower($filter);
        });
        echo json_encode(array_values($filteredEvents), JSON_UNESCAPED_UNICODE);
        exit;
    }

    $now = time();
    $nextEvents = [];
    $seenTrashTypes = [];

    foreach ($events as $event) {
        if ($event['timestamp'] >= $now && !isset($seenTrashTypes[$event['trash_name']])) {
            $nextEvents[] = $event;
            $seenTrashTypes[$event['trash_name']] = true;
        }
        if (count($nextEvents) == 4) {
            break;
        }
    }

    echo json_encode($nextEvents, JSON_UNESCAPED_UNICODE);
    exit;
}

?>
