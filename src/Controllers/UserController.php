<?php
namespace Library\Controllers;

use Library\Services\LibraryService;
use Library\Repositories\UserRepository;
use Library\Models\User;
use Library\Http\Request;
use Library\Http\Response;
use Library\Exceptions\LibraryException;

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
        } catch (LibraryException $e) {
            return Response::error($e->getMessage(), $e->getStatusCode());
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
        }
    }

    public function store(Request $request): Response
    {
        try {
            $data = $request->getBody();
            if (!isset($data['id'])) {
                $data['id'] = 'user_' . uniqid();
            }

            $user = new User(
                $data['id'],
                $data['name'],
                $data['email'],
                $data['membership_type'] ?? 'regular'
            );

            $this->userRepository->save($user);
            return Response::created($user->toArray(), 'User created successfully');
        } catch (LibraryException $e) {
            return Response::error($e->getMessage(), $e->getStatusCode());
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
        }
    }
}