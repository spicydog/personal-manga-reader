<?php

require('functions.php');

$records = [];

if ( isset($_GET['action']) ) {
  switch ($_GET['action']) {
    case 'recent':
      $records = get_recent_sitemap();
      break;
    case 'manga':
      $records = get_manga_sitemap();
      break;
    case 'chapter':
      if (isset($_GET['source']) && isset($_GET['name']))
        $records = get_chapter_sitemap($_GET['source'], $_GET['name']);
      break;
  }
} else {
  $records = get_sitemaps();
}


$output = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
$output .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

foreach ($records as $record) {
  $output .= '  <url>' . "\n";
  $output .= '    <loc>' . $record['url'] . '</loc>' . "\n";
  if ( isset($record['lastmod']) ) {
    $output .= '    <lastmod>' . $record['lastmod'] . '</lastmod>' . "\n";
  }
  $output .= '  </url>' . "\n";
}

$output .= '</urlset>' . "\n";

header('Content-type: application/xml');
echo $output;


function get_sitemaps() {
  $records = [
    ['url' => PUBLIC_URL . htmlspecialchars('sitemap.php?action=recent')],
    ['url' => PUBLIC_URL . htmlspecialchars('sitemap.php?action=list')],
  ];

  return $records;
}

function get_recent_sitemap() {
  $recent_file = META_DIR . 'recent.json';
  $content = @file_get_contents($recent_file);
  $recents = explode("\n", $content);

  $records = [];
  foreach ($recents as $line) {
    if (strpos($line, '{') === 0) {
      $record = json_decode($line, true);

      $url = PUBLIC_URL . htmlspecialchars('index.php?source=' . $record['crawler'] . '&name=' . $record['name'] . '&chapter=' . $record['chapter']);
      $lastmod = gmdate("Y-m-d\TH:i:s\Z", $record['time']);

      $records[] = [
        'url' => $url,
        'lastmod' => $lastmod
      ];
    }
  }

  return $records;
}

function get_manga_sitemap() {
  $mangas = get_manga_list();

  $records = [];
  foreach ($mangas as $manga) {
    $url = PUBLIC_URL . htmlspecialchars('sitemap.php?action=chapter&source=' . $manga['crawler'] . '&name=' . $manga['name']);
    $record = [
      'url' => $url
    ];
    $records[] = $record;
  }

  return $records;
}

function get_chapter_sitemap($source, $name) {

  $chapters = get_chapters($source, $name);
  rsort($chapters);
  $records = [];
  foreach ($chapters as $chapter) {
    $url = PUBLIC_URL . htmlspecialchars('index.php?source=' . $source . '&name=' . $name . '&chapter=' . $chapter);
    $record = [
      'url' => $url
    ];
    $records[] = $record;
  }

  return $records;
}