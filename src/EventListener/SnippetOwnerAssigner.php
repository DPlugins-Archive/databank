<?php

namespace App\EventListener;

use App\Entity\Snippet;
use Symfony\Component\Security\Core\Security;

class SnippetOwnerAssigner
{
    private $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    public function prePersist(Snippet $snippet): void
    {
        $snippet->setPerson($this->security->getUser());
    }
}
