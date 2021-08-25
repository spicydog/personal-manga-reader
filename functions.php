<?php

require_once('config.php');

function get_manga_list() {
  $list = [];
  foreach (MANGA_LIST as $crawler => $names) {
    foreach ($names as $name) {
      $list[] = [
        'crawler' => $crawler,
        'name' => $name,
      ];
    }
  }
  return $list;
}

// Get into the next job
function get_next_job($job) {
  if ($job['page'] < $job['last_page']) {
    $job['page'] += 1;
  } else {
    $job['chapter'] += 1;
    $job['page'] = 1;
    $job['last_page'] = 99999;
  }
  return $job;
}

// Create manga file directory
function make_manga_dir($job) {
  $file_crawler_path = FILES_DIR . $job['crawler'] . '/';
  if (! file_exists($file_crawler_path)) {
    mkdir($file_crawler_path);
  }
  $file_manga_path = $file_crawler_path . $job['name'] . '/';
  if (! file_exists($file_manga_path)) {
    mkdir($file_manga_path);
  }
  $file_chapter_path = $file_manga_path . $job['chapter'] . '/';
  if (! file_exists($file_chapter_path)) {
    mkdir($file_chapter_path);
  }
  return $file_chapter_path;
}

// Clear file cache
function clear_cache($job) {
  @unlink(FILES_DIR . 'index.json');
  $file_crawler_path = FILES_DIR . $job['crawler'] . '/';
  if (file_exists($file_crawler_path)) {
    @unlink($file_crawler_path . 'index.json');
    $file_manga_path = $file_crawler_path . $job['name'] . '/';
    if (file_exists($file_manga_path)) {
      @unlink($file_manga_path . 'index.json');
      $file_chapter_path = $file_manga_path . $job['chapter'] . '/';
      if (file_exists($file_chapter_path)) {
        @unlink($file_chapter_path . 'index.json');
      }
    }
  }
}

function notify_pushbullet($job) {
  require_once('view.php');
  $message['type'] = 'link';
  $message['title'] = 'Manga ' . convert_name($job['name']) . ' has updated';
  $message['body'] = 'Check it out and have fun!!';
  $message['url'] = PUBLIC_URL . 'index.php?source='. $job['crawler']
    . '&name=' . $job['name']
    . '&chapter=' . $job['chapter'];

  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL,"https://api.pushbullet.com/v2/pushes");
  curl_setopt($ch, CURLOPT_POST, 1);
  curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($message));  //Post Fields

  $headers = array();
  $headers[] = 'Access-Token: ' . PUSHBULLET_ACCESS_TOKEN;
  $headers[] = 'Content-Type: application/json';
  curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);

  curl_exec($ch);
  curl_close ($ch);
}

function convert_name($id) {
  $name = MANGA_SLUG_NAMES[$id] ?? $id;
  $name = str_replace('-', ' ', $name);
  $name = ucwords($name);
  return $name;
}


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
  print_r([FILES_DIR,$crawler_path]);
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
  $chapter_path = FILES_DIR . $crawler . '/' . $name . '/' . $chapter . '/';
  foreach ($images as $image) {
    $urls[] = $chapter_path . $image;
  }
  return $urls;
}
