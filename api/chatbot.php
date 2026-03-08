<?php
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json');

// Input
$message = isset($_POST['message']) ? trim((string)$_POST['message']) : '';
if ($message === '') {
    echo json_encode(['message' => 'Please type a message to begin.']);
    exit;
}
// Soft limit to avoid abuse
if (strlen($message) > 2000) {
    $message = substr($message, 0, 2000);
}

// Local deterministic fallback to guarantee an answer
function local_answer($message){
    $q = strtolower($message);
    $options = [];
    $suggestEnquiry = false;
    $action = null;

    // Simple intents
    $isServices = (strpos($q,'service') !== false) || (strpos($q,'offer') !== false) || (strpos($q,'what can you do') !== false);
    $isBudget = (strpos($q,'budget') !== false) || (strpos($q,'estimate') !== false) || (strpos($q,'cost') !== false) || (strpos($q,'price') !== false) || (strpos($q,'pricing') !== false) || (strpos($q,'quotation') !== false) || (strpos($q,'quote') !== false);
    $isConsult = (strpos($q,'consult') !== false) || (strpos($q,'appointment') !== false) || (strpos($q,'meet') !== false) || (strpos($q,'visit') !== false) || (strpos($q,'contact') !== false);
    $isTimeline = (strpos($q,'timeline') !== false) || (strpos($q,'how long') !== false) || (strpos($q,'time') !== false) || (strpos($q,'duration') !== false);
    $isLocation = (strpos($q,'where') !== false && strpos($q,'located') !== false) || (strpos($q,'location') !== false) || (strpos($q,'areas') !== false) || (strpos($q,'serve') !== false);
    $isProcess = (strpos($q,'process') !== false) || (strpos($q,'how it works') !== false) || (strpos($q,'steps') !== false);
    $isWarranty = (strpos($q,'warranty') !== false) || (strpos($q,'guarantee') !== false) || (strpos($q,'after') !== false && strpos($q,'support') !== false);

    if ($isServices){
        $options = [
            ['label' => 'Residential Design', 'value' => 'Tell me about residential design services'],
            ['label' => 'Commercial Design', 'value' => 'Tell me about commercial design services'],
            ['label' => 'Modular Kitchens', 'value' => 'Tell me about modular kitchen solutions'],
            ['label' => 'See all services', 'value' => 'List all services']
        ];
    }
    if ($isBudget || $isConsult){ $suggestEnquiry = true; }
    if ($isBudget){ $action = 'start_budget'; }

    // Default responses
    if ($isServices){
        $msg = "We offer end‑to‑end interior design for homes and commercial spaces: space planning, 3D visualization, civil and electrical, modular kitchens, wardrobes, false ceiling, lighting, and on‑site project management. Tell me what you’re planning and I’ll guide you.";
    } elseif ($isProcess){
        $msg = "Our process: 1) Free consultation 2) Site visit & measurements 3) Concept + 3D designs 4) Estimate & scope finalization 5) Execution with quality checks 6) Handover and support.";
    } elseif ($isTimeline){
        $msg = "Timelines vary by scope. Typical 2BHK interiors take ~6–10 weeks from finalization; commercial projects vary. We can provide a schedule after a quick consultation.";
    } elseif ($isLocation){
        $msg = "We serve Hyderabad and nearby regions. For out‑station projects, share your location and we’ll confirm availability and timeline.";
    } elseif ($isWarranty){
        $msg = "We use branded materials with manufacturer warranties. Workmanship assurance is provided; specifics depend on the chosen materials and scope.";
    } elseif ($isBudget){
        $msg = "Sure—let’s get you a quick estimate here in chat. I’ll ask a few questions (project type, area and finish level) and calculate an approximate budget. Ready to begin?";
    } else {
        $msg = "I’m here to help with services, budgets, timelines, materials and consultations. Ask anything—or tell me your project details for tailored guidance.";
    }

    return [
        'message' => $msg,
        'options' => $options,
        'suggest_enquiry' => $suggestEnquiry,
        'action' => $action,
    ];
}

// OpenRouter config
$apiUrl = 'https://openrouter.ai/api/v1/chat/completions';
$apiKey = getenv('OPENROUTER_API_KEY');
if (!$apiKey || trim($apiKey) === '') {
    // Fallback to DB settings if available
    $apiKey = getSetting('OPENROUTER_API_KEY') ?: getSetting('openrouter_api_key');
}
if (!$apiKey || trim($apiKey) === '') {
    echo json_encode(local_answer($message));
    exit;
}

// System prompt and model
$systemPrompt = "You are the helpful, friendly assistant for Living 360 Interiors.\n- Understand user needs (services, budget, timelines).\n- Provide clear answers without fabricating company-specific details you don't know.\n- When user shows interest in quotes, budgets, consultations, or contacting, suggest filling a short enquiry form (name, email, phone, project type, brief message).\n- Be concise and professional.";
$model = 'deepseek/deepseek-chat';

// Compose request
$requestData = [
    'model' => $model,
    'messages' => [
        ['role' => 'system', 'content' => $systemPrompt],
        ['role' => 'user', 'content' => $message],
    ],
    'max_tokens' => 500,
    'temperature' => 0.7,
];

$ch = curl_init($apiUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($requestData));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Authorization: Bearer ' . $apiKey,
    'HTTP-Referer: https://living360.in',
    'X-Title: Living 360 Interiors',
]);
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 8);
curl_setopt($ch, CURLOPT_TIMEOUT, 15);

$response = curl_exec($ch);
if ($response === false) {
    curl_close($ch);
    echo json_encode(local_answer($message));
    exit;
}
curl_close($ch);

$responseData = json_decode($response, true);
$botMessage = $responseData['choices'][0]['message']['content'] ?? null;
if (!$botMessage) {
    echo json_encode(local_answer($message));
    exit;
}

// Lightweight intent detection to suggest an enquiry inline form on the frontend
$q = strtolower($message);
$options = [];
$suggestEnquiry = false;
if (strpos($q, 'service') !== false || strpos($q, 'what do you offer') !== false) {
    $options = [
        ['label' => 'Residential Design', 'value' => 'Tell me about residential design services'],
        ['label' => 'Commercial Design', 'value' => 'Tell me about commercial design services'],
        ['label' => 'Hospitality Design', 'value' => 'Tell me about hospitality design services'],
        ['label' => 'All Services', 'value' => 'Tell me about all your services'],
    ];
}
if (strpos($q, 'budget') !== false || strpos($q, 'quote') !== false || strpos($q, 'cost') !== false || strpos($q, 'price') !== false) {
    $suggestEnquiry = true;
}
if (strpos($q, 'consult') !== false || strpos($q, 'appointment') !== false || strpos($q, 'meet') !== false || strpos($q, 'contact') !== false) {
    $suggestEnquiry = true;
}

// Forward budget trigger to frontend flow when asked
$action = (strpos($q, 'budget') !== false || strpos($q, 'estimate') !== false || strpos($q, 'price') !== false || strpos($q, 'cost') !== false) ? 'start_budget' : null;

echo json_encode([
    'message' => $botMessage,
    'options' => $options,
    'suggest_enquiry' => $suggestEnquiry,
    'action' => $action,
]);
?>