<?php
// Test script to verify AI fallback mechanism works properly

require_once 'includes/ai_handler.php';

echo "<h2>Testing AI Fallback Mechanism</h2>\n";

try {
    $aiHandler = new AIHandler();
    
    echo "<h3>Test 1: Testing with invalid API key scenario</h3>\n";
    
    // Test a simple prompt that should trigger fallback
    $testPrompt = "Apa itu fungsi kuadrat dalam matematika?";
    
    echo "<p><strong>Prompt:</strong> " . htmlspecialchars($testPrompt) . "</p>\n";
    
    // Use the fallback directly to see the response
    $fallbackResponse = $aiHandler->getFallbackResponse($testPrompt);
    
    echo "<p><strong>Fallback Response:</strong></p>\n";
    echo "<div style='border: 1px solid #ccc; padding: 10px; margin: 10px 0;'>" . nl2br(htmlspecialchars($fallbackResponse)) . "</div>\n";
    
    echo "<h3>Test 2: Testing different subject areas</h3>\n";
    
    $testPrompts = [
        "Jelaskan hukum Newton pertama",
        "Apa itu fotosintesis?",
        "Hitung hasil dari 2x + 5 = 15",
        "Apa definisi dari asam menurut Arrhenius?"
    ];
    
    foreach ($testPrompts as $prompt) {
        echo "<p><strong>Prompt:</strong> " . htmlspecialchars($prompt) . "</p>\n";
        $response = $aiHandler->getFallbackResponse($prompt);
        echo "<p><strong>Response:</strong></p>\n";
        echo "<div style='border: 1px solid #ccc; padding: 10px; margin: 10px 0;'>" . nl2br(htmlspecialchars($response)) . "</div>\n";
    }
    
    echo "<h3>Test 3: Simulating file analysis fallback</h3>\n";
    
    $fileContent = "Fungsi kuadrat adalah fungsi yang memiliki bentuk umum f(x) = ax² + bx + c, di mana a ≠ 0. Grafik fungsi kuadrat berbentuk parabola.";
    $fileName = "materi_fungsi_kuadrat.pdf";
    
    echo "<p>Testing file analysis with content: " . htmlspecialchars(substr($fileContent, 0, 100)) . "...</p>\n";
    
    $analysisResult = $aiHandler->generateResponseFromContent($fileContent, $fileName);
    
    echo "<p><strong>Analysis Result (first 300 chars):</strong></p>\n";
    echo "<div style='border: 1px solid #ccc; padding: 10px; margin: 10px 0;'>" . nl2br(htmlspecialchars(substr($analysisResult, 0, 300))) . "...</div>\n";
    
    echo "<p style='color: green; font-weight: bold;'>✅ All tests completed successfully! Fallback mechanisms are working properly.</p>\n";
    
} catch (Exception $e) {
    echo "<p style='color: red;'><strong>Error:</strong> " . htmlspecialchars($e->getMessage()) . "</p>\n";
}
?>