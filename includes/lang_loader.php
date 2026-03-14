<?php
// Language loader
function loadLanguage($requestedLang = null) {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    // Detect language: URL > Session > Default
    $lang = $requestedLang ?? ($_GET['lang'] ?? $_SESSION['lang'] ?? 'en');
$lang = in_array($lang, ['en', 'es', 'fr']) ? $lang : 'en';
    
    $_SESSION['lang'] = $lang;
    
    $langFile = __DIR__ . '/../lang/' . $lang . '.php';
    if (file_exists($langFile)) {
        $langArray = include $langFile;
        return is_array($langArray) ? $langArray : [];
    }
    
    return [];
}
?>
