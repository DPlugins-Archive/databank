<?php

namespace App\EventListener;

use App\Entity\Snippet;
use Symfony\Component\Security\Core\Security;

class SnippetOwnerAssigner
{
    public function __construct(private Security $security)
    {
    }

    public function prePersist(Snippet $snippet): void
    {
        $snippet->setPerson($this->security->getUser());
    }
}
