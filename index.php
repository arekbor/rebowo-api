<?php

$lostowice_kierunek = getTimetable('https://ztm.gda.pl/rozklady/pobierz_SIP2.php?n[0]=1963&sn=67fccb98f48a6b971cafd2ac81ca74bc&t=&l=');
$wrzeszcz_kierunek = getTimetable('https://ztm.gda.pl/rozklady/pobierz_SIP2.php?n[0]=1964&sn=67fccb98f48a6b971cafd2ac81ca74bc&t=&l=');

$lostowice_kierunek = DOMNodeListToArray($lostowice_kierunek);
$wrzeszcz_kierunek = DOMNodeListToArray($wrzeszcz_kierunek);

$lostowice_kierunek = array_slice($lostowice_kierunek, 0, 4);
$wrzeszcz_kierunek = array_slice($wrzeszcz_kierunek, 0, 4);

$data = array_merge($wrzeszcz_kierunek, $lostowice_kierunek);

$timetable = [];

$date = new \DateTime(timezone: new DateTimeZone('Europe/Warsaw'));

$timetable[0] = [
    'time' => date_format($date, "H:i")
];

foreach ($data as $index => $row) {
    $timetable[$index + 1] = [
        'line' => trim(substr($row, 0, strpos($row, " "))),
        'direction' => trim(substr($row, strpos($row, " "), getRowTimetableDirectionCutPos($row))),
        'departs' => trim(substr($row, getRowTimetableDirectionCutPos($row) + 1)),
        'color' => str_contains($row, '>>>') ? 'red' : 'white'
    ];
}

header('Content-Type: application/json');

echo json_encode($timetable, JSON_UNESCAPED_UNICODE);

function getRowTimetableDirectionCutPos(string $row): int
{
    $offsetRow = substr($row, 3);
    $markers = ['za', '>>>'];

    foreach ($markers as $marker) {
        $pos = strpos($offsetRow, $marker);
        if ($pos !== false) {
            return $pos;
        }
    }

    return strlen($offsetRow) - 5;
}

function getTimetable(string $url): mixed
{
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

    $response = curl_exec($curl);
    curl_close($curl);

    $response = mb_encode_numericentity(
        htmlspecialchars_decode(
            htmlentities($response, ENT_NOQUOTES, 'UTF-8', false),
            ENT_NOQUOTES
        ),
        [0x80, 0x10FFFF, 0, ~0],
        'UTF-8'
    );

    $dom = new DOMDocument();
    $dom->loadHTML($response);

    $xpath = new DOMXPath($dom);
    return $xpath->evaluate('//ul[@class="sip3"]//li');
}

function DOMNodeListToArray(DOMNodeList $list): array
{
    $elements = [];

    foreach ($list as $index => $element) {
        if ($index === 0) {
            continue;
        }

        $text = trim($element->textContent);
        $text = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $text);

        $elements[] = $text;
    }

    return $elements;
}
