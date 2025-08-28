<?php

namespace App\Entity;

use App\Repository\ReportRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ReportRepository::class)]
class Report
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'reports')]
    private ?User $author = null;

    #[ORM\ManyToOne(inversedBy: 'reports')]
    private ?User $reporter = null;

    #[ORM\ManyToOne(inversedBy: 'reports')]
    private ?Post $post = null;

    #[ORM\Column]
    private ?bool $isHandled = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAuthor(): ?User
    {
        return $this->author;
    }

    public function setAuthor(?User $author): static
    {
        $this->author = $author;

        return $this;
    }

    public function getReporter(): ?User
    {
        return $this->reporter;
    }

    public function setReporter(?User $reporter): static
    {
        $this->reporter = $reporter;

        return $this;
    }

    public function getPost(): ?Post
    {
        return $this->post;
    }

    public function setPost(?Post $post): static
    {
        $this->post = $post;

        return $this;
    }

    public function isHandled(): ?bool
    {
        return $this->isHandled;
    }

    public function setIsHandled(bool $isHandled): static
    {
        $this->isHandled = $isHandled;

        return $this;
    }
}
