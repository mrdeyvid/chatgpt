<?php
session_start();

// Substitua 'sua_chave_de_api' pela sua chave de API real da OpenAI
$apiKey = 'sua_chave_de_api';
$systemMessage = "Você é um assistente prestativo.";

// Função para enviar uma mensagem e receber a resposta do assistente
function sendMessage($userMessage) {
    global $apiKey, $systemMessage;

    // Array de mensagens, incluindo as armazenadas na sessão
    // if(isset($_SESSION['messages'])) {
    //     $_SESSION['messages'] = array_filter($_SESSION['messages'], function($value) {
    //         return $value !== null;
    //     });
    // }
    if(isset($_SESSION['messages'])){
        $messages = $_SESSION['messages'];
    } else {
        $messages[] = array('role' => 'system', 'content' => $systemMessage);
    }
    
    $messages[] = array('role' => 'user', 'content' => $userMessage);

    // Array de dados para enviar na requisição
    $requestData = array(
        'model' => 'gpt-3.5-turbo-0125',
        'messages' => $messages
    );

    $url = 'https://api.openai.com/v1/chat/completions';
    // Inicializa uma sessão cURL
    $ch = curl_init();

    // Configura as opções da requisição
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($requestData));
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Authorization: Bearer ' . $apiKey,
        'Content-Type: application/json'
    ));
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

    // Executa a requisição e obtém a resposta
    $response = curl_exec($ch);
    

    // Verifica se houve algum erro na requisição
    if (curl_errno($ch)) {
        return $ch;
    }

    // Fecha a sessão cURL
    curl_close($ch);

    // Decodifica a resposta JSON
    $responseData = json_decode($response, true);
    
    // Retorna a resposta do assistente
    $_SESSION['messages'][] = array('role' => 'assistant', 'content' => $responseData['choices'][0]['message']['content']);
    return $responseData['choices'][0]['message']['content'];

}

// Verifica se a requisição POST foi feita
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $rawBody = file_get_contents('php://input');
    $parsedBody = json_decode($rawBody, true);
    $userMessage = $parsedBody['userMessage'];

    // Obtém a mensagem enviada pelo usuário
    $userMessage = $userMessage ? $userMessage : 'Repita isso por favor: Precisa digitar ao menos 1 palavra';
    

    // Envia a mensagem e obtém a resposta do assistente
    $assistantResponse = sendMessage($userMessage);

    // Retorna a resposta do assistente como JSON
    echo json_encode(array('assistantResponse' => $assistantResponse));
}


?>

