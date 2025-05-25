<?php
$file = basename($_GET['file'] ?? '');
$likeFile = __DIR__ . "/data/likes/$file.json";
@mkdir(dirname($likeFile), 0777, true);
$data = file_exists($likeFile) ? json_decode(file_get_contents($likeFile), true) : ['likes' => 0];

if(isset($_GET['view'])){
    echo $data['likes'];
    exit;
}
$data['likes']++;
file_put_contents($likeFile, json_encode($data));
echo json_encode($data);