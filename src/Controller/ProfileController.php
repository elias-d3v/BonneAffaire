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
    // Affiche le profil d’un utilisateur
    #[Route('/profile/{id}', name: 'app_profile_show')]
    public function show(User $user): Response
    {
        $currentUser = $this->getUser();
        $isOwner = $currentUser && $currentUser->getId() === $user->getId();

        // Récupère les favoris du visiteur connecté
        $favorisIds = [];
        if ($this->getUser()) {
            foreach ($this->getUser()->getFavorites() as $favori) {
                $favorisIds[] = $favori->getPost()->getId();
            }
        }

        // Affiche la page du profil
        return $this->render('profile/show.html.twig', [
            'user' => $user,
            'isOwner' => $isOwner,
            'favorisIds' => $favorisIds,
        ]);
    }

    // Permet à un utilisateur de modifier son propre profil
    #[Route('/profile/{id}/edit', name: 'app_profile_edit')]
    public function edit(User $user, Request $request, EntityManagerInterface $em): Response
    {
        $currentUser = $this->getUser();

        // Vérifie que seul le propriétaire peut accéder à cette page
        if (!$currentUser || $currentUser->getId() !== $user->getId()) {
            throw $this->createAccessDeniedException('Tu ne peux pas modifier le profil de quelqu’un d’autre.');
        }

        // Création du formulaire de modification de profil
        $form = $this->createForm(ProfileType::class, $user);
        $form->handleRequest($request);

        // Validation et enregistrement
        if ($form->isSubmitted() && $form->isValid()) {
            // Gestion de l’upload de l’avatar
            $avatarFile = $form->get('avatar')->getData();

            if ($avatarFile) {
                $newFilename = uniqid().'.'.$avatarFile->guessExtension();

                // Déplacement du fichier vers le dossier configuré
                $avatarFile->move(
                    $this->getParameter('avatars_directory'),
                    $newFilename
                );

                $user->setAvatar($newFilename);
            }

            // Sauvegarde des modifications
            $em->flush();
            return $this->redirectToRoute('app_profile_show', ['id' => $user->getId()]);
        }

        // Affiche le formulaire d’édition
        return $this->render('profile/edit.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }
}
