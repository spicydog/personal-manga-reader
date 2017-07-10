<?php

function page_not_found() {
  http_response_code(404);
  die();
}

$html = '<!DOCTYPE html>
<html lang="en">

<head>

    <title>{{title}}</title>
    <meta name="description" content="{{description}}">

    <meta charset="utf-8">
    <meta name="viewport" content="initial-scale=1.0">


  <!-- Latest compiled and minified CSS -->
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">

  <!-- Optional theme -->
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap-theme.min.css" integrity="sha384-rHyoN1iRsVXV4nD0JutlnGaslCJuC7uwjduW9SVrLvRYooPp2bWYgmgJQIXwl/Sp" crossorigin="anonymous">

  <!-- Latest compiled and minified JavaScript -->
  <script src="https://code.jquery.com/jquery-3.0.0.min.js"></script>

  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>

    
</head>


<body>

      <nav class="navbar navbar-inverse navbar-fixed-top">
      <div class="container">
        <div class="navbar-header">
          <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
            <span class="sr-only">Toggle navigation</span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </button>
          <a class="navbar-brand" href="index.php">SPICYDOG\'s Manga Reader</a>
        </div>
        <div id="navbar" class="collapse navbar-collapse">
          <ul class="nav navbar-nav">
          <!-- 
          {{navbar}}
          -->
          </ul>
        </div><!--/.nav-collapse -->
      </div>
    </nav>

    <div class="container" style="margin-top: 50px;">

      <div class="starter-template">
      
        <h1>{{title}}</h1>
                
        {{breadcrumb}}
        
        {{chapter_nav}}

        {{content}}

        {{chapter_bottom}}

      </div>

    </div>

    {{google_analytics}}

  </body>
</html>';

define('HTML_TEMPLATE', $html);

require_once('functions.php');

function view($data) {
  $html = HTML_TEMPLATE;

  $html = str_replace('{{title}}', convert_name($data['title']), $html);
  $html = str_replace('{{description}}', $data['description'], $html);

  $html = str_replace('{{content}}', $data['content'], $html);
  $html = str_replace('{{breadcrumb}}', breadcrumb($data['breadcrumb']), $html);
  $html = str_replace('{{navbar}}', navbar($data['names'], $data['title']), $html);

  $html = str_replace('{{chapter_nav}}', isset($data['chapter_nav']) ? chapter_nav($data['chapter_nav']) : '', $html);

  $html = str_replace('{{chapter_bottom}}', isset($data['chapter_nav']) && strpos($data['content'], 'img') ? chapter_nav($data['chapter_nav']) : '', $html);

  $html = str_replace('{{google_analytics}}', ga_script(GA_TAG), $html);
  
  ob_start("sanitize_output");
  return $html;
}

function chapter_nav($info) {

  $source = $info['source'];
  $name = $info['name'];
  $prev = $info['prev'];
  $next = $info['next'];

  $html = '<div>';
  if ($prev > 0) {
    $html .= '
    <a href="index.php?source=' . $source . '&name=' . $info['name'] . '&chapter=' . $info['prev'] . '">
      <button type="button" class="btn btn-default">
        <span class="glyphicon glyphicon-chevron-left" aria-hidden="true"></span>
      </button>
    </a>
    ';
  }

  $html .= '<button type="button" class="btn btn-default" disabled><strong>Chapter ' . $info['chapter'] . '<strong></button>';

  if ($next > 0) {
    $html .= '
    <a href="index.php?source=' . $source . '&name=' . $info['name'] . '&chapter=' . $info['next'] . '">
      <button type="button" class="btn btn-default">
        <span class="glyphicon glyphicon-chevron-right" aria-hidden="true"></span>
      </button>
    </a>
    ';
  }

  $html .= '</div>';

  return $html;
}

function navbar($items, $title) {

  $html = '';
  foreach ($items as $item) {
    $active = '';
    if ($title === $item) {
      $active = 'class="active"';
    }
    $html .= '<li ' . $active . '><a href="index.php?name=' . $item . '">' . convert_name($item) . '</a></li>';
  }
  return $html;
}

function breadcrumb($items) {
  $html  = '<ol class="breadcrumb">';
  for ($i=0; $i < count($items); $i++) {
    $item = $items[$i];

    $active = $i == count($items) - 1 ? 'class="active"' : '';
    $html .= '<li ' . $active . '><a href="' . $item['link'] . '">' . $item['name'] . '</a></li>';
  }
  $html .= '</ol>';
  return $html;
}

function ga_script($tag) {
  if (empty($tag) || $tag === 'GA_TAG') {
    return '';
  }

  return "<script>
  (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
  })(window,document,'script','https://www.google-analytics.com/analytics.js','ga');

  ga('create', '$tag', 'auto');
  ga('send', 'pageview');

  </script>";
}


function sanitize_output($buffer) {
  // Reference: https://stackoverflow.com/a/6225706/967802

  $search = array(
      '/\>[^\S ]+/s',     // strip whitespaces after tags, except space
      '/[^\S ]+\</s',     // strip whitespaces before tags, except space
      '/(\s)+/s',         // shorten multiple whitespace sequences
      '/<!--(.|\s)*?-->/' // Remove HTML comments
  );

  $replace = array(
      '>',
      '<',
      '\\1',
      ''
  );

  $buffer = preg_replace($search, $replace, $buffer);

  return $buffer;
}