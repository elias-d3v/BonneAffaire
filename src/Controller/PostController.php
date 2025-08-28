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
use App\Repository\CategoryRepository;

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
    public function list(PostRepository $repo, Request $request, CategoryRepository $catRepo): Response
    {
        $sort = $request->query->get('sort', 'date');
        $categoryId = $request->query->get('category');
        $categoryId = $categoryId ? (int) $categoryId : null;

        $dept = $request->query->get('dept');

        $posts = $repo->findAllSorted($sort, $categoryId, $dept);
        $categories = $catRepo->findAll();

        return $this->render('post/list.html.twig', [
            'posts' => $posts,
            'sort' => $sort,
            'categories' => $categories,
            'selectedCategory' => $categoryId,
            'selectedDept' => $dept,
        ]);
    }

    #[Route('/{id<\d+>}', name: 'post_show')]
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

                    $setter = 'set'.ucfirst($field);
                    $post->$setter($newFilename);
                }
            }
            $em->flush(); 
            return $this->redirectToRoute('post_show', ['id' => $post->getId()]);
        }

        return $this->render('post/edit.html.twig', [
            'form' => $form,
            'post' => $post,
        ]);
    }

    #[Route('/category/{id}', name: 'post_by_category')]
    public function byCategory(PostRepository $repo, int $id): Response
    {
        $posts = $repo->findByCategory($id);
        return $this->render('post/list.html.twig', [
            'posts' => $posts,
        ]);
    }

    #[Route('/search', name: 'post_search')]
    public function search(Request $request, PostRepository $repo): Response
    {
        $term = $request->query->get('q', '');
        $posts = $term ? $repo->search($term) : [];

        return $this->render('post/search.html.twig', [
            'posts' => $posts,
            'term' => $term,
        ]);
    }
}
