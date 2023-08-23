<?php
// function to parse the properties in here
function parseProperties($fileContent) {
	$result = [];
    $fileContent = str_replace("\r\n", "\n", $fileContent);
    $lines = explode("\n", $fileContent);
    $lastkey = '';
    $appendNextLine = false;
    foreach ($lines as $l) {
        $cleanLine = trim($l);
        if ($cleanLine === '') continue;

        $endsWithSlash = substr($l, -1) === '\\';
        if ($appendNextLine) {
            $result[$lastkey] .= "\n" . substr($l, 0, $endsWithSlash ? -1 : 10000);
            if (!$endsWithSlash) {
                $appendNextLine = false;
            }
        } else {
            $key = trim(substr($l, 0, strpos($l, '=')));
            $value = substr($l,strpos($l,'=') + 1, $endsWithSlash ? -1 : 10000);
            $lastkey = $key;
            $result[$key] = $value;
            $appendNextLine = $endsWithSlash;
        }
    }
    return $result;
}

//opening porpeties to read what's inside of it
$handle = fopen("resource.properties", "r");
if ($handle) {
    while (($buffer = fgets($handle, 4096)) !== false) {
		$lines[] = parseProperties($buffer);
    }
    if (!feof($handle)) {
        echo "Error: unexpected of fgets()\n";
    }

    fclose($handle);
} else {
    echo "It wasn't possible to find the file";
    exit;
}

//separate what is lines and what is commentaries after document is parsed
$text = '';
foreach($lines as &$line){
	$key = array_keys($line);
	if ($key[0] == '') {
		$text .= '# '.$line[""].PHP_EOL;
		unset($line[""]);
		continue;
	}
	if ($key[0] != '') {
		if(!empty($text)){
			$text = $text;
		}
		if(empty($text))
		{
			continue;
		}
		$line['commentary'] = $text;
		$text = '';
	}
}

//reset the indexes of lines and concatenate them
$filteredArray = array_values(array_filter($lines));
foreach($filteredArray as $array => $sub)
{
	foreach($sub as $key => $value)
	{
        if($key === "commentary"){
            $commentaries[] = $array;
        }
		if($key !== "commentary"){
			$translations[] = $key.'='.$value;
            $keys[] = $key;
		}
	}
}

//sort the translations and write them sorted according translation keys
asort($translations);
foreach($commentaries as $index){
    $translations[$index] =  $filteredArray[$index]['commentary'].$translations[$index];
}
$orderedTranslations = array_values($translations);
$myfile = fopen("ordered-resource.properties", "w") or die("Unable to open file!");
$textFinal = '';
foreach($orderedTranslations as $key => $line){
    if($key === count($orderedTranslations)) {
        fwrite($myfile, $line);
        continue;
    }
    fwrite($myfile, $line.PHP_EOL);
}
fclose($myfile);

echo '<a href="ordered-resource.properties" download="ordered-resource">Download file as a plain txt</a>';