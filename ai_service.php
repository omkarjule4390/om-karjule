<?php
/**
 * AI Service - OpenAI / Gemini integration with demo fallback
 */

require_once __DIR__ . '/config.php';

class AIService
{
  /**
   * Analyze code and return structured response
   */
  public static function analyzeCode(string $code, string $language, bool $beginnerMode = false): array
  {
    if (AI_DEMO_MODE) {
      return self::demoAnalysis($code, $language, $beginnerMode);
    }

    if (AI_PROVIDER === 'gemini' && !empty(GEMINI_API_KEY)) {
      return self::callGemini($code, $language, $beginnerMode);
    }

    if (!empty(OPENAI_API_KEY)) {
      return self::callOpenAI($code, $language, $beginnerMode);
    }

    return self::demoAnalysis($code, $language, $beginnerMode);
  }

  private static function buildPrompt(string $code, string $language, bool $beginnerMode): string
  {
    $mode = $beginnerMode
      ? 'Use beginner-friendly explanations, simple words, analogies, and coding tips.'
      : 'Provide technical but clear explanations.';

    return <<<PROMPT
You are an expert programming tutor. Analyze the following {$language} code for syntax and logical errors.

{$mode}

Respond ONLY with valid JSON in this exact structure (no markdown):
{
  "errors": [
    {"line": 1, "message": "error description", "type": "syntax|logic|warning"}
  ],
  "corrected_code": "full corrected code as string",
  "explanation": "why the error occurred",
  "tips": ["tip1", "tip2"],
  "best_practices": ["practice1"],
  "common_mistakes": ["mistake1"]
}

If no errors found, return empty errors array and corrected_code same as input with explanation that code looks good.

CODE:
```
{$code}
```
PROMPT;
  }

  private static function callOpenAI(string $code, string $language, bool $beginnerMode): array
  {
    $prompt = self::buildPrompt($code, $language, $beginnerMode);

    $payload = [
      'model' => OPENAI_MODEL,
      'messages' => [
        ['role' => 'system', 'content' => 'You are a code error detection assistant. Always respond with valid JSON only.'],
        ['role' => 'user', 'content' => $prompt],
      ],
      'temperature' => 0.3,
      'max_tokens' => 4000,
    ];

    $ch = curl_init('https://api.openai.com/v1/chat/completions');
    curl_setopt_array($ch, [
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_POST => true,
      CURLOPT_HTTPHEADER => [
        'Content-Type: application/json',
        'Authorization: Bearer ' . OPENAI_API_KEY,
      ],
      CURLOPT_POSTFIELDS => json_encode($payload),
      CURLOPT_TIMEOUT => 60,
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode !== 200 || !$response) {
      return self::demoAnalysis($code, $language, $beginnerMode);
    }

    $data = json_decode($response, true);
    $content = $data['choices'][0]['message']['content'] ?? '';
    return self::parseAIResponse($content, $code, $language, $beginnerMode);
  }

  private static function callGemini(string $code, string $language, bool $beginnerMode): array
  {
    $prompt = self::buildPrompt($code, $language, $beginnerMode);
    $url = 'https://generativelanguage.googleapis.com/v1beta/models/' . GEMINI_MODEL . ':generateContent?key=' . GEMINI_API_KEY;

    $payload = [
      'contents' => [['parts' => [['text' => $prompt]]]],
      'generationConfig' => ['temperature' => 0.3, 'maxOutputTokens' => 4000],
    ];

    $ch = curl_init($url);
    curl_setopt_array($ch, [
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_POST => true,
      CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
      CURLOPT_POSTFIELDS => json_encode($payload),
      CURLOPT_TIMEOUT => 60,
    ]);

    $response = curl_exec($ch);
    curl_close($ch);

    $data = json_decode($response, true);
    $content = $data['candidates'][0]['content']['parts'][0]['text'] ?? '';
    return self::parseAIResponse($content, $code, $language, $beginnerMode);
  }

  private static function parseAIResponse(string $content, string $code, string $language, bool $beginnerMode): array
  {
    // Extract JSON from possible markdown wrapper
    if (preg_match('/\{[\s\S]*\}/', $content, $matches)) {
      $parsed = json_decode($matches[0], true);
      if ($parsed && isset($parsed['errors'])) {
        return [
          'success' => true,
          'errors' => $parsed['errors'] ?? [],
          'corrected_code' => $parsed['corrected_code'] ?? $code,
          'explanation' => $parsed['explanation'] ?? '',
          'tips' => $parsed['tips'] ?? [],
          'best_practices' => $parsed['best_practices'] ?? [],
          'common_mistakes' => $parsed['common_mistakes'] ?? [],
          'mode' => $beginnerMode ? 'beginner' : 'standard',
          'provider' => AI_PROVIDER,
        ];
      }
    }
    return self::demoAnalysis($code, $language, $beginnerMode);
  }

  /**
   * Rule-based demo analysis when no API key
   */
  private static function demoAnalysis(string $code, string $language, bool $beginnerMode): array
  {
    $errors = [];
    $lines = explode("\n", $code);
    $corrected = $code;

    foreach ($lines as $i => $line) {
      $lineNum = $i + 1;
      $trimmed = trim($line);

      // Common patterns across languages
      if (preg_match('/\(\s*\)/', $trimmed) === 0 && preg_match('/print\s+[^(]/', $trimmed)) {
        if (in_array($language, ['python'])) {
          $errors[] = ['line' => $lineNum, 'message' => 'print() requires parentheses in Python 3', 'type' => 'syntax'];
        }
      }
      if (strpos($trimmed, '=') !== false && strpos($trimmed, '==') === false && preg_match('/if\s+\w+\s*=/', $trimmed)) {
        $errors[] = ['line' => $lineNum, 'message' => 'Use == for comparison in if statements, not =', 'type' => 'logic'];
      }
      if (preg_match('/console\.log\s+[^(]/', $trimmed) && $language === 'javascript') {
        $errors[] = ['line' => $lineNum, 'message' => 'console.log requires parentheses', 'type' => 'syntax'];
      }
      if (preg_match('/<\/?\w+[^>]*$/', $trimmed) && $language === 'html') {
        $errors[] = ['line' => $lineNum, 'message' => 'HTML tag may be unclosed', 'type' => 'syntax'];
      }
      if (strpos($trimmed, ';') === false && $trimmed !== '' && !str_starts_with($trimmed, '//') && !str_starts_with($trimmed, '#')) {
        if (in_array($language, ['c', 'cpp', 'java']) && preg_match('/^(int|void|char|float|double|return|printf)/', $trimmed)) {
          if (!str_ends_with($trimmed, ';') && !str_ends_with($trimmed, '{') && !str_ends_with($trimmed, '}')) {
            $errors[] = ['line' => $lineNum, 'message' => 'Missing semicolon at end of statement', 'type' => 'syntax'];
          }
        }
      }
    }

    // Bracket balance check
    $open = substr_count($code, '{') + substr_count($code, '(') + substr_count($code, '[');
    $close = substr_count($code, '}') + substr_count($code, ')') + substr_count($code, ']');
    if ($open !== $close) {
      $errors[] = ['line' => 1, 'message' => 'Mismatched brackets or parentheses in code', 'type' => 'syntax'];
    }

    if (empty($errors)) {
      $explanation = $beginnerMode
        ? 'Great job! No obvious errors were detected. Your code structure looks good. Keep practicing!'
        : 'No syntax or logical issues detected by the analyzer.';
    } else {
      $explanation = $beginnerMode
        ? 'Errors happen when the computer cannot understand our instructions. Think of code like a recipe — every step must be exact!'
        : 'The analyzer found issues that may prevent your code from running correctly.';
    }

    // Simple auto-fix for Python print
    if ($language === 'python') {
      $corrected = preg_replace('/print\s+([^(\n]+)/', 'print($1)', $code);
    }

    return [
      'success' => true,
      'errors' => $errors,
      'corrected_code' => $corrected,
      'explanation' => $explanation,
      'tips' => $beginnerMode ? [
        'Always read error messages from top to bottom',
        'Use meaningful variable names',
        'Test small pieces of code before combining them',
      ] : ['Review language documentation for syntax rules'],
      'best_practices' => ['Comment your code', 'Use consistent indentation', 'Handle errors gracefully'],
      'common_mistakes' => ['Forgetting semicolons', 'Using = instead of ==', 'Mismatched brackets'],
      'mode' => $beginnerMode ? 'beginner' : 'standard',
      'provider' => 'demo',
    ];
  }
}
