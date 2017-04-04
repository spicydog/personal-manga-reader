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
      @unlink($file_crawler_path . 'index.json');
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
  $name = str_replace('-', ' ', $id);
  $name = ucwords($name);
  return $name;
}