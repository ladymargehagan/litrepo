<?php
require_once __DIR__ . '/../php/functions.php';
require_once __DIR__ . '/../actions/TranslateAPI.php';

header('Content-Type: application/json');

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$words = $data['words'] ?? [];

$translateAPI = new TranslateAPI();
$translations = [];

foreach ($words as $word) {
    $translation = $translateAPI->translateWord($word, 'en', 'fr');
    if ($translation) {
        $translations[] = [
            'english' => $word,
            'french' => $translation['translation'],
            'pronunciation' => $translation['pronunciation'] ?? null,
            'examples' => $translation['examples'] ?? []
        ];
    }
}

echo json_encode([
    'success' => true,
    'translations' => $translations
]); 