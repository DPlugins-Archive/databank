<?php

namespace App\Doctrine;

use ApiPlatform\Core\Bridge\Doctrine\Orm\Extension\QueryCollectionExtensionInterface;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Extension\QueryItemExtensionInterface;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use App\Entity\Blob;
use App\Entity\Snippet;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\Security\Core\Security;

class CurrentUserExtension implements QueryCollectionExtensionInterface, QueryItemExtensionInterface
{
    private $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    public function applyToCollection(QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, ?string $operationName = null): void
    {
        $this->addWhere($queryBuilder, $resourceClass);
    }

    public function applyToItem(QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, array $identifiers, ?string $operationName = null, array $context = [])
    {
    }

    private function addWhere(QueryBuilder $queryBuilder, string $resourceClass): void
    {
        if ($this->security->isGranted('ROLE_ADMIN') || null === $user = $this->security->getUser()) {
            return;
        }


        switch ($resourceClass) {
            case Blob::class:
                // $blobRootAlias = $queryBuilder->getRootAliases()[0];
                // $snippetRootAlias = 'snippet';
                // $queryBuilder->leftJoin("$blobRootAlias.snippet", $snippetRootAlias);
                // $queryBuilder->orWhere(sprintf('%s.isPublic = :is_public', $snippetRootAlias));
                // $queryBuilder->setParameter('is_public', true);
                // do not break here !

            case Snippet::class:
                $snippetRootAlias = $snippetRootAlias ?? $queryBuilder->getRootAliases()[0];
                $queryBuilder->andWhere(sprintf('%s.person = :current_user', $snippetRootAlias));
                $queryBuilder->setParameter('current_user', $user->getId());
                break;

            default:
                # code...
                break;
        }
    }
}
