<?php
namespace Library\Controllers;

use Library\Services\LibraryService;
use Library\Repositories\BookRepository;
use Library\Factories\MediaFactory;
use Library\Http\Request;
use Library\Http\Response;
use Library\Exceptions\LibraryException;

class BookController
{
    private LibraryService $service;
    private BookRepository $bookRepository;

    public function __construct(LibraryService $service, BookRepository $bookRepository)
    {
        $this->service = $service;
        $this->bookRepository = $bookRepository;
    }

    public function index(Request $request): Response
    {
        try {
            $search = $request->getQuery('search');
            $author = $request->getQuery('author');
            $available = $request->getQuery('available');

            if ($search || $author || $available !== null) {
                $criteria = array_filter([
                    'title' => $search,
                    'author' => $author,
                    'available' => $available === 'true' ? true : ($available === 'false' ? false : null)
                ], fn($v) => $v !== null);

                $result = $this->service->searchBooks($criteria);
            } else {
                $books = $this->bookRepository->findAll();
                $result = [
                    'success' => true,
                    'count' => count($books),
                    'data' => array_map(fn($book) => $book->toArray(), $books)
                ];
            }

            return Response::json($result);
        } catch (LibraryException $e) {
            return Response::error($e->getMessage(), $e->getStatusCode());
        }
    }

    public function show(Request $request): Response
    {
        try {
            $id = $request->getParam('id');
            $book = $this->bookRepository->find($id);
            return Response::success($book->toArray());
        } catch (LibraryException $e) {
            return Response::error($e->getMessage(), $e->getStatusCode());
        }
    }

    public function store(Request $request): Response
    {
        try {
            $data = $request->getBody();
            if (!isset($data['id'])) {
                $data['id'] = 'book_' . uniqid();
            }

            $book = MediaFactory::createMedia('book', $data);
            $this->bookRepository->save($book);

            return Response::created($book->toArray(), 'Book created successfully');
        } catch (LibraryException $e) {
            return Response::error($e->getMessage(), $e->getStatusCode());
        }
    }

    public function delete(Request $request): Response
    {
        try {
            $id = $request->getParam('id');
            $this->bookRepository->delete($id);
            return Response::success(null, 'Book deleted successfully');
        } catch (LibraryException $e) {
            return Response::error($e->getMessage(), $e->getStatusCode());
        }
    }

    public function available(Request $request): Response
    {
        try {
            $result = $this->service->getAvailableBooks();
            return Response::json($result);
        } catch (LibraryException $e) {
            return Response::error($e->getMessage(), $e->getStatusCode());
        }
    }
}