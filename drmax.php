<?php

$fetchTopUrl = "https://hacker-news.firebaseio.com/v0/topstories.json?print=pretty";
$fetchDetailUrl = "https://hacker-news.firebaseio.com/v0/item/%s.json?print=pretty";
$top500 = callAPI("GET", $fetchTopUrl);

$maxCount = 100;
$stories = [];

for($i = 0; $i < $maxCount; $i++) {
    $id = $top500[$i];
    echo $i .":". $id .PHP_EOL;

    $storyUrl = sprintf($fetchDetailUrl, $id);

    $storyData = callAPI("GET", $storyUrl);
    $stories[] = formatStoryData($storyData, $id);
}

if(!empty($stories)) {
    $fp = fopen('results.json', 'w');
    fwrite($fp, json_encode($stories));
    fclose($fp);
}

function formatStoryData($data, $storyId) {
    $internalStoryUrl = "https://news.ycombinator.com/item?id=%s";

    $date = date("Y-m-d\TH:i:s\Z", $data['time']);
    $title = $data['title'];
    $extUrl = $data['url'];
    $intUrl = sprintf($internalStoryUrl, $storyId);

    return [$storyId,$title,$date,$extUrl,$intUrl];
}

function callAPI($method, $url, $data = false) {
    $curl = curl_init();

    switch ($method)  {
        case "POST":
            curl_setopt($curl, CURLOPT_POST, 1);

            if ($data)
                curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
            break;
        case "PUT":
            curl_setopt($curl, CURLOPT_PUT, 1);
            break;
        default:
            if ($data)
                $url = sprintf("%s?%s", $url, http_build_query($data));
    }

    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

    $result = json_decode(trim(curl_exec($curl)), true);

    curl_close($curl);

    return $result;
}
