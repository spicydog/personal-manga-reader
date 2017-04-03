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

// Get manga job
if (isset($argv) && count($argv) === 3) {
  $job['crawler'] = $argv[1];
  $job['name'] = $argv[2];
} else if (! empty($_GET['crawler']) && ! empty($_GET['name'])) {
  $job['crawler'] = $_GET['crawler'];
  $job['name'] = $_GET['name'];
} else {
  $manga_list = get_manga_list();
  $selected_index = rand() % count($manga_list);
  $job = $manga_list[$selected_index];
}

// Include crawler
$crawler_path = CRAWLER_DIR . $job['crawler'] . '.php';
if (! file_exists($crawler_path)) {
  exit('Crawler '. $job['crawler'] .' does not exist');
}
/** @noinspection PhpIncludeInspection */
require_once($crawler_path);

// Get last download from meta directory
$meta_crawler_path = META_DIR . $job['crawler'] . '/';
if (! file_exists($meta_crawler_path)) {
  mkdir($meta_crawler_path);
}

// Load last job from meta directory
$meta_manga_path = $meta_crawler_path . $job['name'] . '.json';
$meta = [
  'chapter' => 0,
  'page' => 0,
  'last_page' => 0,
  'time' => 0,
];
if (file_exists($meta_manga_path)) {
  $content = file_get_contents($meta_manga_path);
  if (strlen($content) > 0) {
    $meta = json_decode($content, true);
    // Check if another crawler of the same manga is running
    if ($meta['time'] + BUSY_INTERVAL > time()) {
      echo sprintf("Another %s crawler of %s is recently running\n",
        $job['crawler'], $job['name']);
      exit;
    }
  }
}
$job = array_merge($job, $meta);

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

$has_update = false;
$retry_advance = 0;

$next_job = get_next_job($job);
for ($i = 0; $i < DOWNLOAD_LIMIT; $i++) {
  $success = false;
  for ($try_count = 0; $try_count < RETRY_LIMIT; $try_count++) {
    echo sprintf("Downloading %s chapter %d page %d\n",
      $next_job['name'], $next_job['chapter'], $next_job['page']);

    // Download the manga
    $result = download($next_job);

    if ($result['success']) {
      $job = $result['job'];
      $job['time'] = time();

      // Get path to save image
      $file_chapter_path = make_manga_dir($result['job']);
      $save_image_path = $file_chapter_path . $job['page'] . '.jpg';

      // Save the image
      file_put_contents($save_image_path, $result['image']);

      // Save the job
      file_put_contents($meta_manga_path, json_encode($job));

      unset($result);
      $has_update = true;

      $success = true;
      echo "Success\n";

      // Remove cache
      clear_cache($job);

      $next_job = get_next_job($job);

      // Break from retry loop
      break;
    } else {
      echo "Fail\n";
    }
  }

  if ($success) {
    $retry_advance = 0;
  } else {
    $retry_advance++;
    if ($retry_advance < ADVANCE_RETRY_LIMIT) {
      $next_job = get_next_job($next_job);
      echo "Retry to download in advance\n";
    } else {
      echo "Retry to download in advance reach limit\n";
      break;
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

if ($has_update) {
  echo sprintf("There are update for %s\n", $job['name']);
  if (NOTIFY_VIA_PUSHBULLET) {
    echo sprintf("Sending pushbullet notification\n");
    notify_pushbullet($job);
  }
} else {
  echo sprintf("No update for %s\n", $job['name']);
}