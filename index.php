<?php

require __DIR__ . '/App/autoload.php';

try {
    $ctrl = isset($_GET['ctrl']) ? ucfirst($_GET['ctrl']) : 'Index';
    $class = '\App\Controllers\\' . $ctrl;
    $ctrl = new $class;
    $ctrl();
} catch (\App\Exceptions\DbException $error) {
    echo 'Ошибка в БД: ' . $error->getMessage();
    $log = new \App\Logger();
    $log->log($error);
    die;
} catch (\App\Exceptions\Error404 $error) {
    echo $error->getCode() . ' ' . $error->getMessage();
    $log = new \App\Logger();
    $log->log($error);
    die;
}

//Пункт 4 ДЗ
/*
try {
    $data = [1, 'эаовлфыоадлвы', '382udcshkjfhdsakjlfhlkjsahflk', 7];
    $article = new \App\Models\Article;
    $article->fill($data);
} catch (\App\Errors $errors) {
    $errorsList = $errors->all();
    $log = new \App\Logger();
    foreach ($errorsList as $error) {
        echo $error->getCode() . ' ' . $error->getMessage() . '<br>';
        $log->log($error);
    }
    die;
} */