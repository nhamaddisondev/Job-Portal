<?php
require 'config/config.php';

$query = trim($_GET['q'] ?? $_GET['search'] ?? '');
$params = [];

if ($query !== '') {
    $params['q'] = $query;
}

foreach (['category', 'region', 'type'] as $key) {
    if (!empty($_GET[$key])) {
        $params[$key] = trim((string) $_GET[$key]);
    }
}

$target = BASEURL . '/findjobs.php';
if ($params) {
    $target .= '?' . http_build_query($params);
}

header('Location: ' . $target);
exit();
