<?php

// src/Controller/AuthorController.php
namespace App\Controller;

use App\Entity\Author;
use App\Repository\AuthorRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @Route("/api/authors")
 */
class AuthorController extends AbstractController
{
    /**
     * @Route("", methods={"POST"})
     */
    public function create(Request $request, ValidatorInterface $validator): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $author = new Author();
        $author->setFirstName($data['firstName']);
        $author->setLastName($data['lastName']);
        $author->setMiddleName($data['middleName'] ?? null);

        $errors = $validator->validate($author);
        if (count($errors) > 0) {
            return $this->json($errors, 400);
        }

        $em = $this->getDoctrine()->getManager();
        $em->persist($author);
        $em->flush();

        return $this->json($author, 201);
    }

    /**
     * @Route("", methods={"GET"})
     */
    public function list(AuthorRepository $authorRepository, Request $request, SerializerInterface $serializer): JsonResponse
    {
        $page = $request->query->getInt('page', 1);
        $limit = $request->query->getInt('limit', 10);
        $authors = $authorRepository->findAllPaginated($page, $limit);

        $data = $serializer->serialize($authors, 'json');
        return new JsonResponse($data, 200, [], true);
    }
}