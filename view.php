<?php

$html = '<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="initial-scale=1.0">

    <title>{{title}}</title>

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
        
        {{episode_nav}}

        {{content}}

        {{episode_bottom}}

      </div>

    </div>

  </body>
</html>';

define('HTML_TEMPLATE', $html);

function view($data) {
  $html = HTML_TEMPLATE;

  $html = str_replace('{{title}}', convert_name($data['title']), $html);
  $html = str_replace('{{content}}', $data['content'], $html);
  $html = str_replace('{{breadcrumb}}', breadcrumb($data['breadcrumb']), $html);
  $html = str_replace('{{navbar}}', navbar($data['names'], $data['title']), $html);

  $html = str_replace('{{episode_nav}}', isset($data['episode']) ? episode_nav($data['title'], $data['episode']) : '', $html);

  $html = str_replace('{{episode_bottom}}', isset($data['episode']) && strpos($data['content'], 'img') ? episode_nav($data['title'], $data['episode']) : '', $html);

  return $html;
}

function convert_name($id) {
  $name = str_replace('-', ' ', $id);
  $name = ucwords($name);
  return $name;
}

function episode_nav($name, $episode) {

  $html = '<div>';
  if ($episode > 1) {
    $prev = intval($episode) - 1;
    $html .= '
    <a href="index.php?name=' . $name . '&episode=' . $prev . '">
      <button type="button" class="btn btn-default">
        <span class="glyphicon glyphicon-chevron-left" aria-hidden="true"></span>
      </button>
    </a>
    ';
  }

  $html .= '<button type="button" class="btn btn-default" disabled><strong>Chapter ' . $episode . '<strong></button>';

  $next = intval($episode) + 1;
  $html .= '  
    <a href="index.php?name=' . $name . '&episode=' . $next . '">
      <button type="button" class="btn btn-default">
        <span class="glyphicon glyphicon-chevron-right" aria-hidden="true"></span>
      </button>
    </a>
  ';

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