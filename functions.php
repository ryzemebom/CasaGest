<?php
session_start();

// Ler dados de JSON
function readData($file) {
    $path = "data/$file.json";
    if (!file_exists($path)) {
        file_put_contents($path, json_encode([])); // cria arquivo vazio
    }
    $json = file_get_contents($path);
    $data = json_decode($json, true);
    if (!is_array($data)) {
        $data = []; // garante que seja array
    }
    return $data;
}


// Escrever dados no JSON
function writeData($file, $data) {
    $path = "data/$file.json";
    file_put_contents($path, json_encode(array_values($data), JSON_PRETTY_PRINT));
}

// Gerar próximo ID
function nextId($file) {
    $items = readData($file);
    if (empty($items)) return 1;
    $ids = array_column($items, "id");
    return max($ids) + 1;
}
