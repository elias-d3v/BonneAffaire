<?php

namespace App\Twig;

use App\Repository\MessageRepository;
use Symfony\Bundle\SecurityBundle\Security;
use Twig\Extension\AbstractExtension;
use Twig\Extension\GlobalsInterface;

class AppExtension extends AbstractExtension implements GlobalsInterface
{
    private $messageRepo;
    private $security;

    public function __construct(MessageRepository $messageRepo, Security $security)
    {
        $this->messageRepo = $messageRepo;
        $this->security = $security;
    }

    public function getGlobals(): array
    {
        $user = $this->security->getUser();

        return [
            'unreadCount' => $user ? $this->messageRepo->countUnread($user) : 0,
        ];
    }
}