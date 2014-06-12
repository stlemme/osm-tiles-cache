<?php

if (!isset($_GET["west"]) || !isset($_GET["east"]) || !isset($_GET["south"]) || !isset($_GET["north"]) || !isset($_GET["provider"])) {
	header("HTTP/1.1 400 Bad Request");
	die("Missing parameters!");
}

set_time_limit(300);

$west = floatval($_GET["west"]);
$east = floatval($_GET["east"]);
$south = floatval($_GET["south"]);
$north = floatval($_GET["north"]);

$provider = $_GET["provider"];


$minlevel = isset($_GET["minlevel"]) ? intval($_GET["minlevel"]) : 4;
$maxlevel = isset($_GET["maxlevel"]) ? intval($_GET["maxlevel"]) : 18;


$provider_config = __DIR__ . "/provider/" . $provider . ".php";

if (!file_exists($provider_config)) {
	header("HTTP/1.1 400 Bad Request");
	die("Provider not found: " . $provider);
}


header("Content-type: text/plain");

$cache_dir = realpath(__DIR__ . "/cache");

if (!is_dir($cache_dir))
	mkdir($cache_dir, 0775);

function xtile($zoom, $lon) {
	return intval(floor(pow(2, $zoom) * ($lon + 180.0) / 360.0));
}

function ytile($zoom, $lat) {
	return intval(floor((1 - log(tan(deg2rad($lat)) + 1 / cos(deg2rad($lat))) / pi()) /2 * pow(2, $zoom)));
}

for ($l = $minlevel; $l <= $maxlevel; $l++)
{
	$z = $l;
	
	$minx = xtile($z, $west);
	$maxx = xtile($z, $east);

	$miny = ytile($z, $north);
	$maxy = ytile($z, $south);
	
	echo "\n$z: x = [$minx, $maxx], y = [$miny, $maxy]\n";

	for ($x = $minx; $x <= $maxx; $x++)
	{
		$path = $cache_dir . "/" . $provider . "/" . $z . "/" . $x. "/";

		if (!is_dir($path)) {
			if (!mkdir($path, 0755, true)) {
				echo "\nSkip z = $z, x = $x\n";
				continue;
			}
		}

		for ($y = $miny; $y <= $maxy; $y++)
		{
			require($provider_config);
			$url = $urls[array_rand($urls)];

			$file = $path . $y . ".png";
			
			//echo $file;
			//echo $url;

			if (!copy($url, $file)) {
				echo "\nSkip z = $z, x = $x, y = $y\n";
			}
			
			echo '.';
		}
	}
}

echo "\nFinished!";

?>