<?php

namespace App\Controller;

use App\Entity\Post;
use App\Form\PostType;
use App\Repository\PostRepository;
use App\Security\AccessChecker;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/post')]
class PostController extends AbstractController
{
    #[Route('/new', name: 'post_new')]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $post = new Post();
        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $post->setUser($this->getUser());
            $post->setPublishedAt(new \DateTimeImmutable());

            // Gestion des 3 images
            foreach (['image1', 'image2', 'image3'] as $field) {
                $imageFile = $form->get($field)->getData();
                if ($imageFile) {
                    $newFilename = uniqid().'.'.$imageFile->guessExtension();
                    $imageFile->move(
                        $this->getParameter('kernel.project_dir').'/public/uploads',
                        $newFilename
                    );
                    $setter = 'set'.ucfirst($field); // ex: setImage1
                    $post->$setter($newFilename);
                }
            }

            $em->persist($post);
            $em->flush();

            return $this->redirectToRoute('post_list');
        }

        return $this->render('post/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/list', name: 'post_list')]
    public function list(PostRepository $repo): Response
    {
        $posts = $repo->findAll();
        return $this->render('post/list.html.twig', [
            'posts' => $posts,
        ]);
    }

    #[Route('/{id}', name: 'post_show')]
    public function show(Post $post): Response
    {
        return $this->render('post/show.html.twig', [
            'post' => $post,
        ]);
    }

    #[Route('/{id}/edit', name: 'post_edit')]
    public function edit(Post $post, Request $request, EntityManagerInterface $em): Response
    {
        AccessChecker::checkAccess($this->getUser(), 'OWNER_OR_ADMIN', $post);

        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // On récupère les fichiers
            $imageFiles = [
                'image1' => $form['image1']->getData(),
                'image2' => $form['image2']->getData(),
                'image3' => $form['image3']->getData(),
            ];
             foreach ($imageFiles as $field => $imageFile) {
                if ($imageFile) {
                    $newFilename = uniqid().'.'.$imageFile->guessExtension();
                    $imageFile->move(
                        $this->getParameter('kernel.project_dir').'/public/uploads',
                        $newFilename
                    );

                    // setter dynamique : setImage1, setImage2...
                    $setter = 'set'.ucfirst($field);
                    $post->$setter($newFilename);
                }
            }
            $em->flush(); // sauvegarde la modification
            return $this->redirectToRoute('post_show', ['id' => $post->getId()]);
        }

        return $this->render('post/edit.html.twig', [
            'form' => $form,
            'post' => $post,
        ]);
    }
}
