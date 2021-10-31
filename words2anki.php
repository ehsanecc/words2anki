<?php
// API: https://api.dictionaryapi.dev/api/v2/entries/en/<word>

if(count($argv) < 2) {
	echo "Usage: php {$argv[0]} <words.txt>\nwords.txt should contain words separated with \\n (new-line) character\n";
	exit(1);
}

if(!file_exists($argv[1])) {
	echo "Cannot open file {$argv[1]}. File not exists!\n";
	exit(2);
}

$api = "https://api.dictionaryapi.dev/api/v2/entries/en/%s";
$words = file_get_contents($argv[1]);
$oarr = [];
$words_arr = explode("\n", $words);
foreach($words_arr as $word) {
    $word = trim($word);
    if(strlen($word) > 0) {
		printf("Processing %s ...    \r", $word);
		$url = sprintf($api, urlencode($word));
		$result = json_decode(file_get_contents($url), true);
		if(count($result)) {
			$wmeaning = "";
			$wexample = "";
			foreach($result[0]['meanings'] as $meaning) {
				$wmeaning .= "[{$meaning['partOfSpeech']}]<br>- " . join("<br>- ", array_column($meaning['definitions'], "definition")) . "<br>";
				$wexample .= join("<br>", array_column($meaning['definitions'], "example"));
			}
			$oarr[] = [
				'word'=>$result[0]['word'],
				'meaning'=>$wmeaning,
				'examples'=>$wexample,
				'phonetic'=>$result[0]['phonetic']
			];
		}
	}
}
echo "\nDone";
if(preg_match("/(.*)\.[^\.]+$/", $argv[1], $matches))
	$ofname = $matches[1];
else
	$ofname = "words";
file_put_contents("$ofname.json", json_encode($oarr));
$csv = [];
foreach($oarr as $row)
    $csv[] = "\"{$row['word']}\",\"{$row['meaning']}\",\"{$row['examples']}\",\"{$row['phonetic']}\"";
file_put_contents("$ofname.csv", join("\n", $csv));
