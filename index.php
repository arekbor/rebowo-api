<?php

$lostowice_kierunek = getTimetable('https://ztm.gda.pl/rozklady/pobierz_SIP2.php?n[0]=1963&sn=67fccb98f48a6b971cafd2ac81ca74bc&t=&l=');
$wrzeszcz_kierunek = getTimetable('https://ztm.gda.pl/rozklady/pobierz_SIP2.php?n[0]=1964&sn=67fccb98f48a6b971cafd2ac81ca74bc&t=&l=');

$lostowice_kierunek = DOMNodeListToArray($lostowice_kierunek);
$wrzeszcz_kierunek = DOMNodeListToArray($wrzeszcz_kierunek);

$data = array_merge($lostowice_kierunek, $wrzeszcz_kierunek);

$limit = $_GET['limit'];

if (isset($limit) && !empty($limit)) {
    $count = count($data);
    $limit = $limit > $count ? 5 : $limit;
    $data = array_slice($data, 0, $limit);
}

header('Content-Type: application/json');

echo json_encode($data, JSON_UNESCAPED_UNICODE);

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
