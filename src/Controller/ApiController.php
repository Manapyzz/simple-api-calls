<?php

namespace App\Controller;

use App\Entity\Movie;
use App\Repository\DirectorRepository;
use App\Repository\MovieRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ApiController extends AbstractController
{
    #[Route('/api/movies', name: 'list_movies', methods: 'GET')]
    public function getMovies(MovieRepository $movieRepository): Response
    {
        return $this->json($movieRepository->findAll(), 200, [], ['groups' => 'show_movie']);
    }

    #[Route('/api/movies', name: 'create_movie', methods: 'POST')]
    public function createMovie(
        Request $request,
        SerializerInterface $serializer,
        ValidatorInterface $validator,
        DirectorRepository $directorRepository,
        EntityManagerInterface $entityManager
    ): Response
    {
        $director = $directorRepository->find(1);


        if (!empty($director))
        {
            $userContent = $request->getContent();
            $movie = $serializer->deserialize($userContent, Movie::class, 'json');
            $movie->setDirector($director);

            $errors = $validator->validate($movie);

            if (count($errors) > 0) {
                return $this->json($errors, 400);
            }
        }

        $entityManager->persist($movie);
        $entityManager->flush();

        return $this->json($movie, 201, [], ['groups' => 'show_movie']);
    }
}
