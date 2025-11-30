<?php
namespace Library\Controllers;

use Library\Services\LibraryService;
use Library\Repositories\UserRepository;
use Library\Models\User;
use Library\Http\Request;
use Library\Http\Response;
use Library\Exceptions\LibraryException;
use Library\Exceptions\ValidationException;

class UserController
{
    private LibraryService $service;
    private UserRepository $userRepository;

    public function __construct(LibraryService $service, UserRepository $userRepository)
    {
        $this->service = $service;
        $this->userRepository = $userRepository;
    }

    public function index(Request $request): Response
    {
        try {
            $users = $this->userRepository->findAll();
            return Response::success(array_map(fn($user) => $user->toArray(), $users));
        } catch (\Exception $e) {
            return Response::error('Error fetching users: ' . $e->getMessage(), 500);
        }
    }

    public function show(Request $request): Response
    {
        try {
            $id = $request->getParam('id');
            $user = $this->userRepository->find($id);
            return Response::success($user->toArray());
        } catch (LibraryException $e) {
            return Response::error($e->getMessage(), $e->getStatusCode());
        } catch (\Exception $e) {
            return Response::error('Error fetching user: ' . $e->getMessage(), 500);
        }
    }

    public function store(Request $request): Response
    {
        try {
            // Get body data
            $data = $request->getBody();
            
            // Debug log
            error_log("Raw body data: " . json_encode($data));
            
            // Validate body is not empty
            if (empty($data) || !is_array($data)) {
                return Response::error('Request body is empty or invalid', 400);
            }
            
            // Validate required fields with detailed error
            $errors = [];
            
            if (!isset($data['name']) || empty(trim($data['name']))) {
                $errors['name'] = 'Name is required';
            }
            
            if (!isset($data['email']) || empty(trim($data['email']))) {
                $errors['email'] = 'Email is required';
            }
            
            if (!empty($errors)) {
                return Response::error('Validation failed', 400);
            }

            // Auto-generate ID if not provided
            if (!isset($data['id']) || empty($data['id'])) {
                $data['id'] = 'user_' . uniqid();
            }

            // Create user object
            $user = new User(
                $data['id'],
                trim($data['name']),
                trim($data['email']),
                $data['membership_type'] ?? 'regular'
            );

            // Save to database
            $saved = $this->userRepository->save($user);
            
            if (!$saved) {
                return Response::error('Failed to save user', 500);
            }

            return Response::created($user->toArray(), 'User created successfully');
            
        } catch (ValidationException $e) {
            return Response::error($e->getMessage(), 400);
        } catch (LibraryException $e) {
            return Response::error($e->getMessage(), $e->getStatusCode());
        } catch (\Throwable $e) {
            error_log("Error in UserController::store: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            return Response::error('Internal server error: ' . $e->getMessage(), 500);
        }
    }

    public function borrow(Request $request): Response
    {
        try {
            $userId = $request->getParam('id');
            $bookId = $request->getParam('bookId');
            $result = $this->service->borrowBook($userId, $bookId);
            return Response::json($result);
        } catch (LibraryException $e) {
            return Response::error($e->getMessage(), $e->getStatusCode());
        } catch (\Exception $e) {
            return Response::error('Error borrowing book: ' . $e->getMessage(), 500);
        }
    }

    public function returnBook(Request $request): Response
    {
        try {
            $userId = $request->getParam('id');
            $bookId = $request->getParam('bookId');
            $result = $this->service->returnBook($userId, $bookId);
            return Response::json($result);
        } catch (LibraryException $e) {
            return Response::error($e->getMessage(), $e->getStatusCode());
        } catch (\Exception $e) {
            return Response::error('Error returning book: ' . $e->getMessage(), 500);
        }
    }
}