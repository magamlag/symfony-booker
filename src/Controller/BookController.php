<?php

// src/Controller/BookController.php

namespace App\Controller;

use App\Entity\Book;
use App\Repository\BookRepository;
use App\Repository\AuthorRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\File;

/**
 * @Route("/api/books")
 */
class BookController extends AbstractController
{
    /**
     * @Route("", methods={"POST"})
     */
    public function create(Request $request, ValidatorInterface $validator, AuthorRepository $authorRepository): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $book = new Book();
        $book->setTitle($data['title']);
        $book->setDescription($data['description'] ?? null);
        $book->setPublicationDate(new \DateTime($data['publicationDate']));

        foreach ($data['authors'] as $authorId) {
            $author = $authorRepository->find($authorId);
            if ($author) {
                $book->addAuthor($author);
            }
        }

// Загрузка изображения
        if (isset($data['image'])) {
            $file = new File($data['image']);
            $book->setImageFile($file);
        }

        $errors = $validator->validate($book);
        if (count($errors) > 0) {
            return $this->json($errors, 400);
        }

        $em = $this->getDoctrine()->getManager();
        $em->persist($book);
        $em->flush();

        return $this->json($book, 201);
    }

    /**
     * @Route("", methods={"GET"})
     */
    public function list(BookRepository $bookRepository, Request $request, SerializerInterface $serializer): JsonResponse
    {
        $page = $request->query->getInt('page', 1);
        $limit = $request->query->getInt('limit', 10);
        $books = $bookRepository->findAllPaginated($page, $limit);

        $data = $serializer->serialize($books, 'json');
        return new JsonResponse($data, 200, [], true);
    }

    /**
     * @Route("/{id}", methods={"GET"})
     */
    public function view(Book $book, SerializerInterface $serializer): JsonResponse
    {
        $data = $serializer->serialize($book, 'json');
        return new JsonResponse($data, 200, [], true);
    }

    /**
     * @Route("/{id}", methods={"PUT", "PATCH"})
     */
    public function update(Request $request, Book $book, ValidatorInterface $validator, AuthorRepository $authorRepository): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $book->setTitle($data['title']);
        $book->setDescription($data['description'] ?? null);
        $book->setPublicationDate(new \DateTime($data['publicationDate']));

// Обновление авторов
        $book->getAuthors()->clear();
        foreach ($data['authors'] as $authorId) {
            $author = $authorRepository->find($authorId);
            if ($author) {
                $book->addAuthor($author);
            }
        }

// Обновление изображения
        if (isset($data['image'])) {
            $file = new File($data['image']);
            $book->setImageFile($file);
        }

        $errors = $validator->validate($book);
        if (count($errors) > 0) {
            return $this->json($errors, 400);
        }

        $this->getDoctrine()->getManager()->flush();

        return $this->json($book, 200);
    }

    /**
     * @Route("/search", methods={"GET"})
     */
    public function search(Request $request, BookRepository $bookRepository, SerializerInterface $serializer): JsonResponse
    {
        $authorLastName = $request->query->get('author');
        $books = $bookRepository->findByAuthorLastName($authorLastName);

        $data = $serializer->serialize($books, 'json');
        return new JsonResponse($data, 200, [], true);
    }
}
