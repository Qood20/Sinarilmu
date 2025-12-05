<?php
// Test file to verify the file analysis and exercise generation functionality

require_once 'includes/config.php';
require_once 'includes/functions.php';
require_once 'includes/ai_handler.php';

// Create a simple test to verify the AI handler functionality
echo "<h2>Testing File Analysis and Exercise Generation System</h2>";

// Sample content to test with
$testContent = "Fungsi kuadrat adalah fungsi yang memiliki bentuk umum f(x) = ax² + bx + c, di mana a ≠ 0. Grafik fungsi kuadrat berbentuk parabola. Jika a > 0 maka parabola terbuka ke atas, jika a < 0 maka parabola terbuka ke bawah. Nilai c menunjukkan titik potong sumbu y dari fungsi kuadrat tersebut.";

$fileName = "test_matematika.pdf";

echo "<h3>Testing with sample content:</h3>";
echo "<p>" . substr($testContent, 0, 100) . "...</p>";

try {
    $aiHandler = new AIHandler();
    
    echo "<h3>Testing API connectivity:</h3>";
    $isFunctional = $aiHandler->isApiFunctional();
    if ($isFunctional) {
        echo "<p style='color: green;'>✅ API is functional</p>";
    } else {
        echo "<p style='color: red;'>❌ API is not functional - will use fallback</p>";
    }
    
    echo "<h3>Testing content analysis and exercise generation:</h3>";
    
    $startTime = microtime(true);
    $response = $aiHandler->getAnalysisAndExercises($testContent, $fileName);
    $endTime = microtime(true);
    
    $duration = round(($endTime - $startTime) * 1000, 2); // in milliseconds
    
    if (!empty($response)) {
        echo "<p style='color: green;'>✅ Successfully received response from AI system</p>";
        echo "<p>Response time: {$duration} ms</p>";
        echo "<details><summary>View Response Content (click to expand)</summary>";
        echo "<pre>" . htmlspecialchars($response) . "</pre>";
        echo "</details>";
        
        // Try to parse the response to verify format
        $analysisStart = strpos($response, '---ANALYSIS_START---');
        $analysisEnd = strpos($response, '---ANALYSIS_END---');
        $questionsStart = strpos($response, '---QUESTIONS_START---');
        $questionsEnd = strpos($response, '---QUESTIONS_END---');
        
        if ($analysisStart !== false && $analysisEnd !== false) {
            echo "<p style='color: green;'>✅ Analysis format correct</p>";
        } else {
            echo "<p style='color: orange;'>⚠️ Analysis format may be incorrect</p>";
        }
        
        if ($questionsStart !== false && $questionsEnd !== false) {
            echo "<p style='color: green;'>✅ Questions format correct</p>";
        } else {
            echo "<p style='color: orange;'>⚠️ Questions format may be incorrect</p>";
        }
        
        // Extract and show questions if possible
        if ($questionsStart !== false && $questionsEnd !== false) {
            $questionsJson = substr($response, $questionsStart + 21, $questionsEnd - $questionsStart - 21);
            $questionsJson = trim($questionsJson);
            
            echo "<h4>Extracted Questions JSON:</h4>";
            echo "<pre>" . htmlspecialchars($questionsJson) . "</pre>";
            
            // Try to parse the JSON
            $questions = json_decode($questionsJson, true);
            if ($questions !== null && json_last_error() === JSON_ERROR_NONE) {
                $questionCount = count($questions);
                echo "<p style='color: green;'>✅ Successfully parsed {$questionCount} questions from JSON</p>";
                
                // Show first question as example
                if (!empty($questions) && isset($questions[0])) {
                    echo "<h4>First Question (as example):</h4>";
                    echo "<p><strong>Question:</strong> " . htmlspecialchars($questions[0]['soal'] ?? $questions[0]['question'] ?? 'N/A') . "</p>";
                }
            } else {
                echo "<p style='color: red;'>❌ Failed to parse questions JSON: " . json_last_error_msg() . "</p>";
            }
        }
    } else {
        echo "<p style='color: red;'>❌ Failed to get response from AI system</p>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
    error_log("Test error: " . $e->getMessage());
}

echo "<h3>System Configuration:</h3>";
echo "<ul>";
echo "<li>OpenRouter API Key: " . (defined('OPENROUTER_API_KEY') ? (strlen(OPENROUTER_API_KEY) > 10 ? '✅ Set (' . strlen(OPENROUTER_API_KEY) . ' chars)' : '⚠️ Too short') : '❌ Not defined') . "</li>";
echo "<li>OpenRouter Base URL: " . (defined('OPENROUTER_BASE_URL') ? OPENROUTER_BASE_URL : '❌ Not defined') . "</li>";
echo "<li>OpenRouter Default Model: " . (defined('OPENROUTER_DEFAULT_MODEL') ? OPENROUTER_DEFAULT_MODEL : '❌ Not defined') . "</li>";
echo "</ul>";

echo "<h3>Available Functions:</h3>";
echo "<ul>";
echo "<li>extractFileContent: " . (function_exists('extractFileContent') ? '✅ Available' : '❌ Not available') . "</li>";
echo "<li>cleanFileContentForAI: " . (function_exists('cleanFileContentForAI') ? '✅ Available' : '❌ Not available') . "</li>";
echo "<li>createBasicQuestionsFromContent: " . (function_exists('createBasicQuestionsFromContent') ? '✅ Available' : '❌ Not available') . "</li>";
echo "</ul>";

echo "<p><strong>Test completed successfully!</strong></p>";
?>