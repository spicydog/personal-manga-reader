<?php

require_once('functions.php');

if (isset($_SERVER['REQUEST_METHOD']) && ! ALLOW_GET_REQUEST_DOWNLOADER) {
  header('HTTP/1.1 400 BAD REQUEST');
  exit();
}

$engine = 'n/a';

// Get manga job
if (! empty($_ENV['CRAWLER']) && ! empty($_ENV['NAME'])) {
  $engine = 'env';
  $job['crawler'] = $_ENV['CRAWLER'];
  $job['name'] = $_ENV['NAME'];
  if(! empty($_ENV['CHAPTER'])) {
    $job['chapter'] = $_ENV['CHAPTER'];
  }
} else if (! empty($_GET['crawler']) && ! empty($_GET['name'])) {
  $engine = 'get';
  $job['crawler'] = $_GET['crawler'];
  $job['name'] = $_GET['name'];
} else {
  $manga_list = get_manga_list();
  $selected_index = rand() % count($manga_list);
  $job = $manga_list[$selected_index];
}

echo 'Run : ' . $engine . ' : ' . $job['crawler'] ?? 'n/a' . ' : ' . $job['name'] ?? 'n/a' . ' : ' . $job['chapter'] ?? 'n/a' . "\n";

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

$job = array_merge($meta, $job);

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

      if (REQUEST_AFTER_DOWNLOAD) {
        $page_url = PUBLIC_URL . FILES_DIR . $job['crawler'] .
          '/' . $job['name'] . '/' . $job['chapter'] . '/' . $job['page'] . '.jpg';
        file_get_contents($page_url);
      }

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

if ($has_update) {
  $row = json_encode($job) . "\n";
  $recent_file = META_DIR . 'recent.json';
  $content = file_get_contents($recent_file);
  $content = $row . $content;
  $lines = explode("\n", $content);
  $lines = array_slice($lines, 0, 10);
  file_put_contents($recent_file, implode("\n", $lines));

  echo sprintf("There are update for %s\n", $job['name']);
  if (NOTIFY_VIA_PUSHBULLET) {
    echo sprintf("Sending pushbullet notification\n");
    notify_pushbullet($job);
  }
} else {
  echo sprintf("No update for %s\n", $job['name']);
}

// Sleep before next run
$delay = $_ENV['DELAY'] ?? 0;
if ($delay > 0) {
  echo "Delay before next run $delay seconds\n";
  sleep($delay);
}