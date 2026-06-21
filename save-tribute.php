<?php
header('Content-Type: application/json');

$file = 'tribute.json';
$rawText = '';

$badWords = ['badword1', 'badword2', 'spamword3'];

if (file_exists('words.json')) {

    $badWords = json_decode(file_get_contents('words.json'), true);
    

    if ($badWords) {
        foreach ($badWords as $badWord) {

            if (trim($badWord) === "") continue;

            $pattern = '/\b' . preg_quote($badWord, '/') . '\b/i';
            

            if (preg_match($pattern, $rawText)) {
                echo json_encode(["status" => "error", "message" => "Please keep tributes respectful."]);
                exit; 
            }
        }
    }
}
$json_input = file_get_contents('php://input');
$newTribute = json_decode($json_input, true);

if ($newTribute && isset($newTribute['text'])) {
    

    $rawText = trim($newTribute['text']);

    if (strlen($rawText) < 3) {
        echo json_encode(["status" => "error", "message" => "Tribute is too short."]);
        exit;
    }


    if (preg_match('/(.)\1{4,}/', $rawText)) {
        echo json_encode(["status" => "error", "message" => "Please write a meaningful tribute."]);
        exit;
    }


    $words = explode(' ', $rawText);
    foreach ($words as $word) {
        if (strlen($word) > 25) {
            echo json_encode(["status" => "error", "message" => "Nonsense words detected."]);
            exit;
        }
    }


    
    foreach ($badWords as $badWord) {
        if (stripos($rawText, $badWord) !== false) {
            echo json_encode(["status" => "error", "message" => "Please keep tributes respectful."]);
            exit;
        }
    }

    
    $safeText = htmlspecialchars(strip_tags($rawText), ENT_QUOTES, 'UTF-8');
    $newTribute['text'] = $safeText;
    

    $tributes = [];

    if (file_exists($file)) {
        $currentData = file_get_contents($file);
        $tributes = json_decode($currentData, true) ?: [];
    }

    $tributes[] = $newTribute;

    file_put_contents($file, json_encode($tributes, JSON_PRETTY_PRINT));

    echo json_encode(["status" => "success", "message" => "Tribute saved!"]);
} else {
    echo json_encode(["status" => "error", "message" => "Invalid data."]);
}
?>