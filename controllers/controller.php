<?php
namespace Controllers;

abstract class Controller {
    
    protected function sendJson($statusCode, array $data) {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    protected function success(array $data = [], $message = null) {
        $response = ['success' => true];
        
        if ($message) {
            $response['message'] = $message;
        }
        
        if (!empty($data)) {
            $response['data'] = $data;
        }
        
        $this->sendJson(200, $response);
    }
    
    protected function error($message, $statusCode = 400) {
        $this->sendJson($statusCode, [
            'success' => false,
            'error' => $message
        ]);
    }
    
    protected function getJsonInput() {
        $input = file_get_contents('php://input');
        return json_decode($input, true) ?? [];
    }
    
    protected function validateRequired(array $data, array $required) {
        $errors = [];
        
        foreach ($required as $field) {
            if (!isset($data[$field]) || empty($data[$field])) {
                $errors[] = "Le champ {$field} est requis";
            }
        }
        
        return $errors;
    }
}