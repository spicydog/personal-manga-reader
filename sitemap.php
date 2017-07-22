<?php

require('config.php');


$recent_file = META_DIR . 'recent.json';
$content = @file_get_contents($recent_file);
$recents = explode("\n", $content);

$output = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
$output .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

foreach ($recents as $line) {
    if (strpos($line, '{') === 0) {
        $record = json_decode($line, true);

        $url = PUBLIC_URL . htmlspecialchars('index.php?source=' . $record['crawler'] . '&name=' . $record['name'] . '&chapter=' . $record['chapter']);
        $lastmod = gmdate("Y-m-d\TH:i:s\Z", $record['time']);

        $output .= '  <url>' . "\n";
        $output .= '    <loc>' . $url . '</loc>' . "\n";
        $output .= '    <lastmod>' . $lastmod . '</lastmod>' . "\n";
        $output .= '  </url>' . "\n";
    }
}

$output .= '</urlset>' . "\n";

header('Content-type: application/xml');
echo $output;