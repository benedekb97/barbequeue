<?php

declare(strict_types=1);

namespace App\Form\QueuedUser\Data;

use App\Entity\Repository;
use App\Entity\User;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

class DeploymentData extends QueuedUserData
{
    private ?Repository $repository = null;

    private string $description;

    private string $link;

    /** @var Collection<int, User> */
    private Collection $notifyUsers;

    public function __construct()
    {
        $this->notifyUsers = new ArrayCollection();
    }

    public function getRepository(): ?Repository
    {
        return $this->repository;
    }

    public function setRepository(?Repository $repository): static
    {
        $this->repository = $repository;

        return $this;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getLink(): string
    {
        return $this->link;
    }

    public function setLink(string $link): static
    {
        $this->link = $link;

        return $this;
    }

    /** @return Collection<int, User> */
    public function getNotifyUsers(): Collection
    {
        return $this->notifyUsers;
    }

    /** @param Collection<int, User> $notifyUsers */
    public function setNotifyUsers(Collection $notifyUsers): static
    {
        $this->notifyUsers = $notifyUsers;

        return $this;
    }
}
