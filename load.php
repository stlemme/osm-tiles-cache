<?php

if (!isset($_GET["x"]) || !isset($_GET["y"]) || !isset($_GET["z"]) || !isset($_GET["provider"])) {
	header("HTTP/1.1 404 Not Found");
	die("Missing parameters!");
}


$x = intval($_GET["x"]);
$y = intval($_GET["y"]);
$z = intval($_GET["z"]);

$provider = $_GET["provider"];

$provider_config = __DIR__ . "/provider/" . $provider . ".php";

if (!file_exists($provider_config)) {
	header("HTTP/1.1 404 Not Found");
	die("Provider not found: " . $provider);
}

require($provider_config);


$url = $urls[array_rand($urls)];

$cache_dir = realpath(__DIR__ . "/cache");

if (!is_dir($cache_dir))
	mkdir($cache_dir, 0775);

$path = $cache_dir . "/" . $provider . "/" . $z . "/" . $x. "/";

if (!is_dir($path)) {
	if (!mkdir($path, 0755, true)) {
		header("HTTP/1.1 500 Internal Server Error");
		die("Could not create path: " . $path);
	}
}

$file = $path . $y . ".png";

if (!copy($url, $file)) {
	header("HTTP/1.1 404 Not Found");
	die("Could not copy remote file: " . $url);
}

header("HTTP/1.1 200 Found");
header("Content-type: image/png");
echo file_get_contents($file);

?>