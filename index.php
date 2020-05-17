<?php

require('functions.php');
require('view.php');

if (count($_GET) === 0) {

  $data = [];
  $data['title'] = SITE_TITLE;
  $data['description'] = 'The simplest manga online website!';

  
  $data['breadcrumb'][0] = ['link' => 'index.php', 'name' => 'Home'];
  $data['content'] = '';

  $crawlers  = get_crawlers();

  $recent_file = META_DIR . 'recent.json';
  $content = @file_get_contents($recent_file);
  $recents = explode("\n", $content);
  if (strlen($content) > 0) {
    $data['content'] .= '<div><h2>Recent Update</h2></div>';
    $data['content'] .= '<ul>';
    $exists = [];
    foreach ($recents as $line) {
      if (strpos($line, '{') === 0) {
        $record = json_decode($line, true);
        $name = sprintf('[%s] %s - %d', convert_name($record['crawler']), convert_name($record['name']), $record['chapter']);
        if (!isset($exists[$name])) {
          $exists[$name] = true;
          $link = 'index.php?source=' . $record['crawler'] . '&name=' . $record['name'] . '&chapter=' . $record['chapter'];
          $date = date('Y-m-d G:H', $record['time']);
          $data['content'] .= "<li>$date - <a href=\"$link\">" . $name . "</a></li>";
        }
      }
    }
    $data['content'] .= '</ul>';
  }

  foreach ($crawlers as $crawler) {
    $data['content'] .= '<div><h3>' . convert_name($crawler) . '</h3></div>';
    $names = get_names($crawler);
    $data['names'] = $names;
    $data['content'] .= '<ul>';
    foreach ($names as $name) {
      $pretty = convert_name($name);
      $data['content'] .= "<li><a href=\"index.php?source=$crawler&name=$name\">$pretty</a></li>";
    }
    $data['content'] .= '</ul>';
  }

  echo view($data);
}


if (count($_GET) === 1 && isset($_GET['source'])) {

  $crawler = $_GET['source'];

  $data = [];
  $data['title'] = $crawler;
  $data['description'] = 'Enjoy your favorite manga from ' . convert_name($crawler);

  $data['breadcrumb'][0] = ['link' => 'index.php', 'name' => 'Home'];
  $data['breadcrumb'][1] = ['link' => "index.php?source=$crawler", 'name' => convert_name($crawler)];
  $data['content'] = '';

  $data['content'] .= '<div><h3>Manga List</h3></div>';

  $names = get_names($crawler);

  if (count($names) === 0) {
    page_not_found();
  }
  
  $data['names'] = $names;

  $data['content'] .= '<ul>';
  foreach ($names as $name) {
    $pretty = convert_name($name);
    $data['content'] .= "<li><a href=\"index.php?source=$crawler&name=$name\">$pretty</a></li>";
  }
  $data['content'] .= '</ul>';

  echo view($data);
}

if (count($_GET) === 2 && isset($_GET['source']) && isset($_GET['name'])) {

  $name = $_GET['name'];
  $crawler = $_GET['source'];

  $chapters = get_chapters($crawler, $name);

  $names = get_names($crawler);
  sort($names);

  if (count($chapters) === 0) {
    page_not_found();
  }

  $data = [];
  $data['source'] = $crawler;
  $data['names'] = $names;
  $data['name'] = $name;

  $data['title'] = $name;
  $data['description'] = 'List of the latest ' . convert_name($name) . ' manga';

  $data['content'] = '<div><h3>Chapters</h3></div>';
  $data['content'] .= '<ul>';
  rsort($chapters);
  foreach ($chapters as $chapter) {
    $pretty = convert_name($name);
    $data['content'] .= "<li><a href=\"index.php?source=$crawler&name=$name&chapter=$chapter\">$pretty $chapter</a></li>";
  }
  $data['content'] .= '</ul>';


  $data['breadcrumb'][0] = ['link' => 'index.php', 'name' => 'Home'];
  $data['breadcrumb'][1] = ['link' => "index.php?source=$crawler", 'name' => convert_name($crawler)];
  $data['breadcrumb'][2] = ['link' => "index.php?source=$crawler&name=$name", 'name' => convert_name($name)];

  echo view($data);
}

if (count($_GET) === 3 && isset($_GET['source'])  && isset($_GET['name']) && isset($_GET['chapter'])) {

  $crawler = $_GET['source'];
  $name = $_GET['name'];
  $chapter = $_GET['chapter'];

  $images = get_images($crawler, $name, $chapter);

  if (count($images) === 0) {
    page_not_found();
  }

  $urls = generate_image_urls($crawler, $name, $chapter, $images);

  $names = get_names($crawler);
  sort($names);

  $data = [];
  $data['source'] = $crawler;
  $data['names'] = $names;
  $data['name'] = $name;

  $data['title'] = convert_name($name) . ' ' . $chapter;
  $data['description'] = 'Read manga ' . convert_name($name) . ' chapter ' . $chapter;

  $data['chapter'] = $chapter;


  $data['chapter_nav'] = [
    'source' => $crawler,
    'name' => $name,
    'chapter' => $chapter,
    'prev' => null, 
    'next' => null
  ];

  if ($chapter >= 1) {

    // Previous chapter
    if ($chapter >= 2) {
      $prev = $chapter - 1;
      $prev_imgs = get_images($crawler, $name, $prev);
      if (count($prev_imgs) > 0) {
        $data['chapter_nav']['prev'] = $prev;
      }
    }

    // Next chapter
    $next = $chapter + 1;
    $next_imgs = get_images($crawler, $name, $next);
    if (count($next_imgs) > 0) {
      $data['chapter_nav']['next'] = $next;
    }
  }

  $data['content'] = '';
  if (count($urls) > 0) {
    foreach ($urls as $i => $url) {
      $page = $i + 1;
      $data['content'] .= "<h2>$page</h2>";
      $data['content'] .= "<img src=\"$url\"><br />";
      $data['content'] .= '<hr />';
    }
  }

  $data['breadcrumb'][0] = ['link' => 'index.php', 'name' => 'Home'];
  $data['breadcrumb'][1] = ['link' => "index.php?source=$crawler", 'name' => convert_name($crawler)];
  $data['breadcrumb'][2] = ['link' => "index.php?source=$crawler&name=$name", 'name' => convert_name($name)];
  $data['breadcrumb'][3] = ['link' => "index.php?source=$crawler&name=$name&chapter=$chapter", 'name' => "Chapter $chapter"];

  echo view($data);
}
