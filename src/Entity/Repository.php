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
use Doctrine\ORM\Mapping\InverseJoinColumn;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\JoinTable;
use Doctrine\ORM\Mapping\ManyToMany;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToMany;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use OpenApi\Attributes as OA;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Attribute\Context;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints\Count;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Url;

#[Entity]
#[UniqueEntity(['name', 'workspace'], entityClass: Repository::class, errorPath: 'name')]
class Repository
{
    use TimestampableEntity;

    #[Id]
    #[GeneratedValue]
    #[Column(type: Types::INTEGER)]
    #[Groups(['queue', 'repository', 'blocked-repository', 'queued-user'])]
    private ?int $id = null;

    #[Column(type: Types::STRING)]
    #[Groups(['queue', 'repository', 'blocked-repository', 'queued-user'])]
    #[NotBlank]
    private ?string $name = null;

    #[Column(type: Types::STRING, nullable: true)]
    #[Groups(['repository'])]
    #[Url(requireTld: true)]
    private ?string $url = null;

    #[ManyToOne(targetEntity: Workspace::class, inversedBy: 'repositories')]
    #[JoinColumn(nullable: false)]
    private ?Workspace $workspace = null;

    /** @var Collection<int, Repository> */
    #[ManyToMany(targetEntity: Repository::class, inversedBy: 'blockedByDeployment')]
    #[JoinTable(name: 'repository_blocked_by_deployment')]
    #[JoinColumn(name: 'blocker_id', referencedColumnName: 'id')]
    #[InverseJoinColumn(name: 'repository_id', referencedColumnName: 'id')]
    #[Groups(['repository'])]
    #[Context(['groups' => ['blocked-repository']])]
    #[OA\Property(
        type: 'array',
        items: new OA\Items(ref: '#/components/schemas/BlockedRepository'),
    )]
    private Collection $deploymentBlocksRepositories;

    /** @var Collection<int, Repository> */
    #[ManyToMany(targetEntity: Repository::class, mappedBy: 'deploymentBlocksRepositories')]
    #[Groups(['repository'])]
    #[Context(['groups' => ['blocked-repository']])]
    #[OA\Property(
        type: 'array',
        items: new OA\Items(ref: '#/components/schemas/BlockedRepository'),
    )]
    private Collection $blockedByDeployment;

    /** @var Collection<int, DeploymentQueue> */
    #[ManyToMany(targetEntity: DeploymentQueue::class, mappedBy: 'repositories')]
    #[Groups(['repository'])]
    private Collection $deploymentQueues;

    /** @var Collection<int, Deployment> */
    #[OneToMany(targetEntity: Deployment::class, mappedBy: 'repository')]
    #[Groups(['repository'])]
    #[Count(exactly: 0, groups: ['delete'])]
    private Collection $deployments;

    public function __construct()
    {
        $this->deploymentBlocksRepositories = new ArrayCollection();
        $this->blockedByDeployment = new ArrayCollection();
        $this->deploymentQueues = new ArrayCollection();
        $this->deployments = new ArrayCollection();
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

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(?string $url): static
    {
        $this->url = $url;

        return $this;
    }

    public function getWorkspace(): ?Workspace
    {
        return $this->workspace;
    }

    public function setWorkspace(?Workspace $workspace): static
    {
        $this->workspace = $workspace;

        return $this;
    }

    /** @return Collection<int, Repository> */
    public function getDeploymentBlocksRepositories(): Collection
    {
        return $this->deploymentBlocksRepositories;
    }

    public function clearDeploymentBlocksRepositories(): static
    {
        foreach ($this->deploymentBlocksRepositories as $repository) {
            $this->removeDeploymentBlocksRepository($repository);
        }

        return $this;
    }

    public function addDeploymentBlocksRepository(Repository $repository): static
    {
        if (!$this->deploymentBlocksRepositories->contains($repository)) {
            $this->deploymentBlocksRepositories->add($repository);
            $repository->addBlockedByDeployment($this);
        }

        return $this;
    }

    public function removeDeploymentBlocksRepository(Repository $repository): static
    {
        if ($this->deploymentBlocksRepositories->contains($repository)) {
            $this->deploymentBlocksRepositories->removeElement($repository);
            $repository->removeBlockedByDeployment($this);
        }

        return $this;
    }

    /** @return Collection<int, Repository> */
    public function getBlockedByDeployment(): Collection
    {
        return $this->blockedByDeployment;
    }

    public function addBlockedByDeployment(Repository $repository): static
    {
        if (!$this->blockedByDeployment->contains($repository)) {
            $this->blockedByDeployment->add($repository);
            $repository->addDeploymentBlocksRepository($this);
        }

        return $this;
    }

    public function removeBlockedByDeployment(Repository $repository): static
    {
        if ($this->blockedByDeployment->contains($repository)) {
            $this->blockedByDeployment->removeElement($repository);
            $repository->removeDeploymentBlocksRepository($this);
        }

        return $this;
    }

    /** @return Collection<int, DeploymentQueue> */
    public function getDeploymentQueues(): Collection
    {
        return $this->deploymentQueues;
    }

    /** @return Collection<int, Deployment> */
    public function getDeployments(): Collection
    {
        return $this->deployments;
    }

    public function addDeployment(Deployment $deployment): static
    {
        if (!$this->deployments->contains($deployment)) {
            $this->deployments->add($deployment);
            $deployment->setRepository($this);
        }

        return $this;
    }

    public function removeDeployment(Deployment $deployment): static
    {
        if ($this->deployments->contains($deployment)) {
            $this->deployments->removeElement($deployment);
            $deployment->setRepository(null);
        }

        return $this;
    }

    public function getActiveDeployment(): ?Deployment
    {
        return $this->deployments->findFirst(function (int $id, Deployment $deployment) {
            return $deployment->isActive();
        });
    }

    public function getBlockingDeployment(): ?Deployment
    {
        if (($activeDeployment = $this->getActiveDeployment()) !== null) {
            return $activeDeployment;
        }

        foreach ($this->blockedByDeployment as $repository) {
            foreach ($repository->getDeployments() as $deployment) {
                if ($deployment->isActive()) {
                    return $deployment;
                }
            }
        }

        return null;
    }

    public function isBlockedByDeployment(): bool
    {
        return null !== $this->getBlockingDeployment();
    }

    /** @return Deployment[] */
    public function getSortedDeployments(): array
    {
        if ($this->deployments->isEmpty()) {
            return [];
        }

        /** @var Deployment[] $deployments */
        $deployments = $this->deployments->toArray();

        uasort($deployments, function (Deployment $firstDeployment, Deployment $secondDeployment): int {
            return $firstDeployment->getCreatedAt() <=> $secondDeployment->getCreatedAt();
        });

        return array_values($deployments);
    }

    /** @return Deployment[] */
    public function getSortedDeploymentsIncludingBlockedRepositories(): array
    {
        $deployments = $this->deployments->toArray();

        foreach ($this->deploymentBlocksRepositories as $repository) {
            if (null !== $repository->getActiveDeployment()) {
                continue;
            }

            foreach ($repository->getDeployments() as $deployment) {
                if (!in_array($deployment, $deployments, true)) {
                    $deployments[] = $deployment;
                }
            }
        }

        uasort($deployments, function (Deployment $firstDeployment, Deployment $secondDeployment): int {
            return $firstDeployment->getCreatedAt() <=> $secondDeployment->getCreatedAt();
        });

        return array_values($deployments);
    }
}
