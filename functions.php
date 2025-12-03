<?php
if (session_status() === PHP_SESSION_NONE) session_start();

function dataFilePath($file) {
    $dir = __DIR__ . DIRECTORY_SEPARATOR . 'data';
    if (!is_dir($dir)) mkdir($dir, 0755, true);
    return $dir . DIRECTORY_SEPARATOR . $file . '.json';
}

function readData($file) {
    $path = dataFilePath($file);
    if (!file_exists($path)) {
        // create an empty file
        file_put_contents($path, json_encode([]));
        return [];
    }
    $json = file_get_contents($path);
    $data = json_decode($json, true);
    if (!is_array($data)) {
        // if JSON parsing failed, return empty array to avoid crashes
        return [];
    }
    return $data;
}

function writeData($file, $data) {
    $path = dataFilePath($file);
    $tmp = $path . '.tmp';
    $out = array_values($data);
    $json = json_encode($out, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    // write atomically with exclusive lock
    $fp = fopen($tmp, 'wb');
    if ($fp === false) {
        throw new RuntimeException('Unable to open temp file for writing: ' . $tmp);
    }
    if (!flock($fp, LOCK_EX)) {
        fclose($fp);
        throw new RuntimeException('Unable to acquire lock for file: ' . $tmp);
    }
    fwrite($fp, $json);
    fflush($fp);
    flock($fp, LOCK_UN);
    fclose($fp);
    // replace original
    rename($tmp, $path);
}

function nextId($file) {
    $items = readData($file);
    $max = 0;
    foreach ($items as $it) {
        $id = intval($it['id'] ?? 0);
        if ($id > $max) $max = $id;
    }
    return $max + 1;
}

// Helpers: get item by id
function getById($file, $id) {
    $items = readData($file);
    foreach ($items as $it) {
        if ((int)($it['id'] ?? 0) === (int)$id) return $it;
    }
    return null;
}

// Helpers: save (create or update) item. If item has 'id' it updates, otherwise creates new id.
function saveItem($file, $item) {
    $items = readData($file);
    if (isset($item['id'])) {
        $found = false;
        foreach ($items as &$it) {
            if ((int)($it['id'] ?? 0) === (int)$item['id']) {
                $it = array_merge($it, $item);
                $found = true;
                break;
            }
        }
        if (!$found) {
            $items[] = $item;
        }
    } else {
        $item['id'] = nextId($file);
        $items[] = $item;
    }
    writeData($file, $items);
    return $item['id'];
}

function deleteById($file, $id) {
    $items = readData($file);
    $items = array_filter($items, fn($it) => (int)($it['id'] ?? 0) !== (int)$id);
    writeData($file, array_values($items));
}

