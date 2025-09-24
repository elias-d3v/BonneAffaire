<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\ProfileType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ProfileController extends AbstractController
{
    #[Route('/profile/{id}', name: 'app_profile_show')]
    public function show(User $user): Response
    {
        $currentUser = $this->getUser();
        $isOwner = $currentUser && $currentUser->getId() === $user->getId();

        // Favoris
        $favorisIds = [];
        if ($this->getUser()) {
            foreach ($this->getUser()->getFavorites() as $favori) {
                $favorisIds[] = $favori->getPost()->getId();
            }
        }

        return $this->render('profile/show.html.twig', [
            'user' => $user,
            'isOwner' => $isOwner,
            'favorisIds' => $favorisIds,
        ]);
    }

    #[Route('/profile/{id}/edit', name: 'app_profile_edit')]
    public function edit(User $user, Request $request, EntityManagerInterface $em): Response
    {
        $currentUser = $this->getUser();

        // sécurité : seul le propriétaire peut modifier
        if (!$currentUser || $currentUser->getId() !== $user->getId()) {
            throw $this->createAccessDeniedException('Tu ne peux pas modifier le profil de quelqu’un d’autre.');
        }

        $form = $this->createForm(ProfileType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // gestion upload avatar
            $avatarFile = $form->get('avatar')->getData();

            if ($avatarFile) {
                $newFilename = uniqid().'.'.$avatarFile->guessExtension();

                $avatarFile->move(
                    $this->getParameter('avatars_directory'),
                    $newFilename
                );

                $user->setAvatar($newFilename);
            }

            $em->flush();
            $this->addFlash('success', 'Profil mis à jour avec succès.');
            return $this->redirectToRoute('app_profile_show', ['id' => $user->getId()]);
        }

        return $this->render('profile/edit.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }
}
