<?php
header("Content-Type: application/json; charset=UTF-8");
require_once 'estimator.php';
$results = showResults();
echo json_encode($results, JSON_FORCE_OBJECT);