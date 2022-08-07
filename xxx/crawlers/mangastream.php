<?php

function get_inner_string($string, $begin, $end) {
  if($begin === 0)
    return substr( $string, 0, strpos($string, $end) );
  if($end === 0)
    return substr($string, strpos($string, $begin) + strlen($begin));

  $string = ' ' . $string;
  $init = strpos($string, $begin);

  if ($init == 0)
    return '';

  $init += strlen($begin);
  $len = strpos($string, $end, $init) - $init;

  return substr($string, $init, $len);
}


function download($job) {

  $result = [
    'success' => false,
    'job' => $job,
    'image' => false,
  ];

  $name = str_replace('-', '_', $job['name']);
  $list_url = 'http://mangastream.com/manga/' . $name;
  $list_html = file_get_contents($list_url);


  $list_html = get_inner_string($list_html, '<table class="table table-striped">', '</table>');

  $html_chapters = explode('<a href=', $list_html);

  $chapters = [];
  $first_chapter = 99999;
  $first = true;
  foreach ($html_chapters as $html_chapter) {
    if ($first) {
      $first = false;
      continue;
    }
    $chapter_str = get_inner_string($html_chapter, '/1">', ' - ');
    $chapter = intval($chapter_str);
    if ($chapter_str != $chapter || $chapter <= 0) {
      continue;
    }

    $url = get_inner_string($html_chapter, '"', '/1">');

    $chapters[$chapter] = $url;
    if ($first_chapter > $chapter) {
      $first_chapter = $chapter;
    }
  }

  if ($first_chapter > $job['chapter']) {
    $job['chapter'] = $first_chapter;
  }

  if (isset($chapters[$job['chapter']])) {
    $url = $chapters[$job['chapter']] . '/' . $job['page'];

    $html = file_get_contents($url);

    $last_page = get_inner_string($html, '">Last Page (', ')</a');
    $last_page = intval($last_page);
    if ($last_page > 0) {
      $job['last_page'] = $last_page;
    }

    $image_url = get_inner_string($html, '<img id="manga-page" src="', '"');
    if (substr($image_url, 0, 2) === '//') {
      $image_url = 'http:' . $image_url;
    }

    if (strlen($image_url) > 10) {
      $image = file_get_contents($image_url);
      if (strlen($image) > 100) {
        $result['success'] = true;
        $result['image'] = $image;
        $result['job'] = $job;
      }
    }
  }
  return $result;
}
