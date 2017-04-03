<?php

require('config.php');
require('view.php');

function filter_dir($names) {
  $results = [];
  foreach ($names as $name) {
    if ($name != 'index.html' && $name != 'index.json' && substr($name, 0, 1) != '.') {
      $results[] = $name;
    }
  }
  return $results;
}

function get_crawlers() {
  $crawlers = [];
  $file_path = FILES_DIR;
  if (file_exists($file_path)) {
    $content = @file_get_contents(FILES_DIR . 'index.json');
    if (strlen($content) > 2) {
      $crawlers = json_decode($content, true);
    } else {
      $crawlers = filter_dir(scandir(FILES_DIR));
      sort($crawlers);
      file_put_contents(FILES_DIR . 'index.json', json_encode($crawlers));
    }
  }
  return $crawlers;
}

function get_names($crawler) {
  $names = [];
  $crawler_path = FILES_DIR . '/' . $crawler . '/';
  if (file_exists($crawler_path)) {
    $content = @file_get_contents($crawler_path . 'index.json');
    if (strlen($content) > 2) {
      $names = json_decode($content, true);
    } else {
      $names = filter_dir(scandir($crawler_path));
      sort($names);
      file_put_contents($crawler_path . 'index.json', json_encode($names));
    }
  }
  return $names;
}

function get_chapters($crawler, $name) {
  $chapters = [];
  $name_path = FILES_DIR . '/' . $crawler . '/' . $name . '/';
  if (file_exists($name_path)) {
    $content = @file_get_contents($name_path . 'index.json');
    if (strlen($content) > 2) {
      $chapters = json_decode($content, true);
    } else {
      $chapters = filter_dir(scandir($name_path));
      sort($chapters);
      file_put_contents($name_path . 'index.json', json_encode($chapters));
    }
  }
  return $chapters;
}

function get_images($crawler, $name, $chapter) {
  $images = [];
  $chapter_path = FILES_DIR . '/' . $crawler . '/' . $name . '/' . $chapter . '/';
  if (file_exists($chapter_path)) {
    $content = @file_get_contents($chapter_path . 'index.json');
    if (strlen($content) > 2 && false) {
      $images = json_decode($content, true);
    } else {
      $images = filter_dir(scandir($chapter_path));
      sort($images, SORT_NUMERIC);
      file_put_contents($chapter_path . 'index.json', json_encode($images));
    }
  }
  return $images;
}

function generate_image_urls($crawler, $name, $chapter, $images) {
  $urls = [];
  $chapter_path = FILES_DIR . '/' . $crawler . '/' . $name . '/' . $chapter . '/';
  foreach ($images as $image) {
    $urls[] = $chapter_path . $image;
  }
  return $urls;
}

if (count($_GET) === 0) {

  $data = [];
  $data['title'] = SITE_TITLE;
  $data['breadcrumb'][0] = ['link' => 'index.php', 'name' => 'Home'];
  $data['content'] = '';

  $crawlers  = get_crawlers();

  foreach ($crawlers as $crawler) {

    $data['content'] .= '<div><h2>' . convert_name($crawler) . '</h2></div>';

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
  $data['breadcrumb'][0] = ['link' => 'index.php', 'name' => 'Home'];
  $data['breadcrumb'][1] = ['link' => "index.php?source=$crawler", 'name' => convert_name($crawler)];
  $data['content'] = '';

  $data['content'] .= '<div><h3>Manga List</h3></div>';

  $names = get_names($crawler);
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

  $data = [];
  $data['source'] = $crawler;
  $data['names'] = $names;
  $data['title'] = $name;

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
  $urls = generate_image_urls($crawler, $name, $chapter, $images);

  $names = get_names($crawler);
  sort($names);

  $data = [];
  $data['source'] = $crawler;
  $data['names'] = $names;
  $data['title'] = $name;
  $data['chapter'] = $chapter;
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
