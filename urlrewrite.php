<?php
$arUrlRewrite=array (
  0 => 
  array (
    'CONDITION' => '#^/services/#',
    'RULE' => '',
    'ID' => 'bitrix:catalog',
    'PATH' => '/services/index.php',
    'SORT' => 100,
  ),
  1 => 
  array (
    'CONDITION' => '#^/products/#',
    'RULE' => '',
    'ID' => 'bitrix:catalog',
    'PATH' => '/products/index.php',
    'SORT' => 100,
  ),
    2 =>
        array(
            "CONDITION" => "#^/rest/news.list#",
            "RULE" => "",
            "PATH" => "/local/php_interface/api/news_list.php",
            "SORT" => 100,
        ),

);

