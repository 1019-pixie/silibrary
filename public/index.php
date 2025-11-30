<?php
declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use Library\Config\Database;
use Library\Repositories\BookRepository;
use Library\Repositories\UserRepository;
use Library\Services\LibraryService;
use Library\Controllers\BookController;
use Library\Controllers\UserController;
use Library\Http\Request;
use Library\Http\Response;
use Library\Http\Router;
use Library\Exceptions\LibraryException;

// CORS Headers
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

try {
    $db = Database::getInstance();
    $bookRepository = new BookRepository($db);
    $userRepository = new UserRepository($db);
    $libraryService = new LibraryService($bookRepository, $userRepository);
    $bookController = new BookController($libraryService, $bookRepository);
    $userController = new UserController($libraryService, $userRepository);
    
    $router = new Router();
    
    // Health check
    $router->get('/api/health', function(Request $req) {
        return Response::json([
            'status' => 'ok',
            'message' => 'Digital Library API is running',
            'timestamp' => date('Y-m-d H:i:s')
        ]);
    });
    
    // API Info
    $router->get('/api', function(Request $req) {
        return Response::json([
            'message' => 'Digital Library API',
            'version' => '1.0.0',
            'endpoints' => [
                'GET /api/health' => 'Health check',
                'GET /api/statistics' => 'Library statistics',
                'GET /api/books' => 'List all books',
                'GET /api/books/available' => 'List available books',
                'GET /api/books/{id}' => 'Get book details',
                'POST /api/books' => 'Create new book',
                'DELETE /api/books/{id}' => 'Delete book',
                'GET /api/users' => 'List all users',
                'GET /api/users/{id}' => 'Get user details',
                'POST /api/users' => 'Create new user',
                'POST /api/users/{id}/borrow/{bookId}' => 'Borrow a book',
                'POST /api/users/{id}/return/{bookId}' => 'Return a book',
            ]
        ]);
    });
    
    // Statistics
    $router->get('/api/statistics', function(Request $req) use ($libraryService) {
        try {
            $stats = $libraryService->getStatistics();
            return Response::json($stats);
        } catch (LibraryException $e) {
            return Response::error($e->getMessage(), $e->getStatusCode());
        }
    });
    
    // Book routes
    $router->get('/api/books', [$bookController, 'index']);
    $router->get('/api/books/available', [$bookController, 'available']);
    $router->get('/api/books/{id}', [$bookController, 'show']);
    $router->post('/api/books', [$bookController, 'store']);
    $router->delete('/api/books/{id}', [$bookController, 'delete']);
    
    // User routes
    $router->get('/api/users', [$userController, 'index']);
    $router->get('/api/users/{id}', [$userController, 'show']);
    $router->post('/api/users', [$userController, 'store']);
    
    // Borrow/Return routes
    $router->post('/api/users/{id}/borrow/{bookId}', [$userController, 'borrow']);
    $router->post('/api/users/{id}/return/{bookId}', [$userController, 'returnBook']);
    
    $request = new Request();
    $response = $router->dispatch($request);
    $response->send();
    
} catch (LibraryException $e) {
    Response::error($e->getMessage(), $e->getStatusCode())->send();
} catch (\Throwable $e) {
    Response::error('Internal server error: ' . $e->getMessage(), 500)->send();
}