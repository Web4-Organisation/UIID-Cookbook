<?php
declare(strict_types=1);

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

require __DIR__ . '/../vendor/autoload.php';

use UIID\Demosuite\Services\UIIDService;

// Start a session to store tokens
session_start();

// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

$uiidService = new UIID\Demosuite\Services\UIIDService();

$action = $_GET['action'] ?? 'home';

// If a 'code' parameter is present, it must be an OAuth callback.
if (isset($_GET['code'])) {
    $action = 'callback';
}

// Simple router
switch ($action) {
    case 'home':
        require __DIR__ . '/../views/home.php';
        break;

    case 'login':
        $authUrl = $uiidService->getAuthorizationUrl();
        header('Location: ' . $authUrl);
        exit;

    case 'callback':
        if (!isset($_GET['code'])) {
            die("Error: Authorization code not found.");
        }
        error_log("OAuth Callback: Code received: " . $_GET['code']);
        error_log("OAuth Callback: Attempting to exchange code for tokens.");
        try {
            $tokens = $uiidService->exchangeCodeForTokens($_GET['code'], $uiidService->getRedirectUri());
            $_SESSION['access_token'] = $tokens['access_token'];
            $_SESSION['refresh_token'] = $tokens['refresh_token'];
            $_SESSION['token_expires'] = time() + $tokens['expires_in'];
            header('Location: index.php?action=dashboard');
            exit;
        } catch (\Exception $e) {
            error_log('Error exchanging code for token: ' . $e->getMessage());
            die('Error: ' . $e->getMessage());
        }
        break;

    case 'dashboard':
        if (!$uiidService->isLoggedIn()) {
            header('Location: index.php?action=home');
            exit;
        }
        require __DIR__ . '/../views/dashboard.php';
        break;

    case 'logout':
        session_destroy();
        header('Location: index.php?action=home');
        exit;
        
    case 'api_call':
        if (!$uiidService->isLoggedIn()) {
            http_response_code(401);
            echo json_encode(['error' => 'Unauthorized']);
            exit;
        }
        
        $endpoint = $_POST['endpoint'] ?? '';
        $method = $_POST['method'] ?? 'GET';
        $params = $_POST['params'] ?? [];

        try {
            $result = $uiidService->callApi($method, $endpoint, $params);
            $_SESSION['last_api_result'] = $result;
        } catch (\Exception $e) {
            $_SESSION['last_api_result'] = ['error' => $e->getMessage()];
        }
        header('Location: index.php?action=dashboard');
        exit;

    default:
        http_response_code(404);
        echo "404 Not Found: Invalid Action";
        break;
}