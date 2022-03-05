<?php

namespace App\Controller\App;

use App\Repository\SnippetRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/app/snippets')]
class SnippetController extends AbstractController
{
    private SnippetRepository $snippetRepository;

    public function __construct(SnippetRepository $snippetRepository) {
        $this->snippetRepository = $snippetRepository;        
    }

    #[Route('/', name: 'app_snippet_index', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('app/snippet/index.html.twig', [
            'snippets' => $this->snippetRepository->findAll(),
        ]);
    }

    #[Route('/{uuid}', name: 'app_snippet_show')]
    public function snippet(string $uuid, ValidatorInterface $validator): Response
    {
        $errors = $validator->validate($uuid, [
            new Assert\Uuid(),
        ]);

        if (count($errors) > 0) {
            throw new BadRequestHttpException((string) $errors);
        }

        $snippet = $this->snippetRepository->findOneBy(['uuid' => $uuid]);

        if (!$snippet) {
            throw new NotFoundHttpException('The snippet does not exist');
        }

        if ($snippet->getIsPublic() === false) {
            $user = $this->getUser();
            if (!$user) {
                throw new AccessDeniedHttpException('This snippet is not public and You are not authorized to view this snippet. Please login to view this snippet.');
            }

            if ($user !== $snippet->getPerson()) {
                throw new AccessDeniedHttpException('This snippet is not belongs to you');
            }
        }

        return $this->render('app/snippet/show.html.twig', [
            'snippet' => $snippet,
        ]);
    }
}
