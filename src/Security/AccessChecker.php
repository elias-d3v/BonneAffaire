<?php

namespace App\Security;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use App\Entity\Post;
use App\Entity\User;

class AccessChecker
{
    /**
     * Vérifie si un utilisateur est autorisé à accéder à une ressource.
     * 
     * - OWNER_OR_ADMIN : seul le propriétaire de l'annonce OU un admin peut continuer
     * - ADMIN : seulement les admins
     * 
     * Si l'utilisateur n'est pas connecté ou n'a pas les droits → redirection directe vers /login.
     */
    public static function checkAccess(?User $user, string $rule, ?Post $post = null): void
    {
        // Si pas connecté 
        if (!$user) {
            header("Location: /login");
            exit;
        }

        // Règle OWNER_OR_ADMIN
        if ($rule === 'OWNER_OR_ADMIN') {
            if ($post && ($user !== $post->getUser() && !in_array('ROLE_ADMIN', $user->getRoles()))) {
                header("Location: /login");
                exit;
            }
        }

        // Règle ADMIN uniquement
        if ($rule === 'ADMIN') {
            if (!in_array('ROLE_ADMIN', $user->getRoles())) {
                header("Location: /login");
                exit;
            }
        }
    }
}

