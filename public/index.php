<?php

require_once __DIR__ . '/../vendor/autoload.php';

$url = $_SERVER['REQUEST_URI'];

if ($url == '' || $url == '/' || $url == '/?') {
    if (isset($_POST['altDataSource']) && $_POST['altDataSource'] == 'true') {
        header('location: http://localhost:8080/gorest/users/all/1');
    } else {
        header('location: http://localhost:8080/users/all/1');
    }
    die();
}

$data = [];
$data['path'] = $url;
if (!isset($_POST['method'])) {
    $data['method'] = 'GET';
} else {
    $data['method'] = $_POST['method'];
}

if ($data['method'] == 'POST' || $data['method'] == 'PUT') {
    $data['body'] = [
        'email' => $_POST['email'],
        'name' => $_POST['name'],
        'gender_id' => $_POST['gender'],
        'status_id' => $_POST['status'],
    ];
    if ($_POST['id'] != '') {
        $data['body']['id'] = $_POST['id'];
    }
} elseif ($data['method'] == 'DELETE' && isset($_POST['ids'])) {
    $data['body'] = $_POST['ids'];
}

$request = \App\Request\HttpRequestBuilder::build($data);
$response = \App\Application::run($request);
echo $response;
