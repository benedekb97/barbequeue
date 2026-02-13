<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\OneToMany;
use Gedmo\Timestampable\Traits\TimestampableEntity;

#[Entity]
class Workspace
{
    use TimestampableEntity;

    #[Id]
    #[GeneratedValue]
    #[Column(type: Types::BIGINT)]
    private ?int $id = null;

    #[Column(type: Types::STRING)]
    private ?string $name = null;

    #[Column(type: Types::STRING, unique: true)]
    private ?string $slackId = null;

    /** @var Collection<int, Queue> */
    #[OneToMany(targetEntity: Queue::class, mappedBy: 'workspace')]
    private Collection $queues;

    /** @var Collection<int, Administrator> */
    #[OneToMany(targetEntity: Administrator::class, mappedBy: 'workspace')]
    private Collection $administrators;

    #[Column(type: Types::STRING)]
    private ?string $botToken = null;

    /** @var Collection<int, Repository> */
    #[OneToMany(targetEntity: Repository::class, mappedBy: 'workspace')]
    private Collection $repositories;

    /** @var Collection<int, User> */
    #[OneToMany(targetEntity: User::class, mappedBy: 'workspace')]
    private Collection $users;

    public function __construct()
    {
        $this->queues = new ArrayCollection();
        $this->administrators = new ArrayCollection();
        $this->repositories = new ArrayCollection();
        $this->users = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getSlackId(): ?string
    {
        return $this->slackId;
    }

    public function setSlackId(?string $slackId): static
    {
        $this->slackId = $slackId;

        return $this;
    }

    /** @return Collection<int, Queue> */
    public function getQueues(): Collection
    {
        return $this->queues;
    }

    public function addQueue(Queue $queue): static
    {
        if (!$this->queues->contains($queue)) {
            $this->queues->add($queue);
            $queue->setWorkspace($this);
        }

        return $this;
    }

    public function removeQueue(Queue $queue): static
    {
        if ($this->queues->contains($queue)) {
            $this->queues->removeElement($queue);
            $queue->setWorkspace(null);
        }

        return $this;
    }

    /** @return Collection<int, Administrator> */
    public function getAdministrators(): Collection
    {
        return $this->administrators;
    }

    public function addAdministrator(Administrator $administrator): static
    {
        if (!$this->administrators->contains($administrator)) {
            $this->administrators->add($administrator);
            $administrator->setWorkspace($this);
        }

        return $this;
    }

    public function removeAdministrator(Administrator $administrator): static
    {
        if ($this->administrators->contains($administrator)) {
            $this->administrators->removeElement($administrator);
            $administrator->setWorkspace(null);
        }

        return $this;
    }

    public function hasAdministratorWithUserId(string $userId): bool
    {
        return $this->administrators->exists(function (int $key, Administrator $administrator) use ($userId) {
            return $administrator->getUserId() === $userId;
        });
    }

    public function getBotToken(): ?string
    {
        return $this->botToken;
    }

    public function setBotToken(#[\SensitiveParameter] ?string $botToken): static
    {
        $this->botToken = $botToken;

        return $this;
    }

    /** @return Collection<int, Repository> */
    public function getRepositories(): Collection
    {
        return $this->repositories;
    }

    public function addRepository(Repository $repository): static
    {
        if (!$this->repositories->contains($repository)) {
            $this->repositories->add($repository);
            $repository->setWorkspace($this);
        }

        return $this;
    }

    public function removeRepository(Repository $repository): static
    {
        if ($this->repositories->contains($repository)) {
            $this->repositories->removeElement($repository);
            $repository->setWorkspace(null);
        }

        return $this;
    }

    /** @return Collection<int, User> */
    public function getUsers(): Collection
    {
        return $this->users;
    }

    public function addUser(User $user): static
    {
        if (!$this->users->contains($user)) {
            $this->users->add($user);
            $user->setWorkspace($this);
        }

        return $this;
    }

    public function removeUser(User $user): static
    {
        if ($this->users->contains($user)) {
            $this->users->removeElement($user);
            $user->setWorkspace(null);
        }

        return $this;
    }

    public function hasUserWithId(string $userId): bool
    {
        return $this->getUserById($userId) instanceof User;
    }

    public function getUserById(string $userId): ?User
    {
        return $this->users->filter(function (User $user) use ($userId) {
            return $user->getSlackId() === $userId;
        })->first() ?: null;
    }
}
