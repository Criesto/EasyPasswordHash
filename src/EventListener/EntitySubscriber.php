<?php

namespace App\EventListener;

use App\Entity\User;
use Doctrine\ORM\Events;
use JetBrains\PhpStorm\Pure;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class EntitySubscriber implements EventSubscriberInterface
{
    private TokenStorageInterface $tokenStorage;
    private LoggerInterface $logger;
    private UserPasswordHasherInterface $passwordHasher;
    public function __construct(LoggerInterface $logger, TokenStorageInterface $tokenStorage, UserPasswordHasherInterface $passwordHasher)
    {
        $this->tokenStorage = $tokenStorage;
        $this->logger = $logger;
        $this->passwordHasher = $passwordHasher;
    }

    public function getUser($tokenStorage): \Symfony\Component\Security\Core\User\UserInterface|int
    {
        if (!$token = $this->tokenStorage->getToken()) {
            return 0;
        }

        if (!$token->isAuthenticated()) {
            return 0;
        }

        if (!$user = $token->getUser()) {
            return 0;
        }
        return $user;
    }

    #[Pure] public function checkEntity(LifecycleEventArgs $args): object|int
    {
        $managedClassesName = [User::class];
        $entity = $args->getObject();
        $entityClassName = get_class($entity);

        if (!in_array($entityClassName, $managedClassesName))
        {
            return 0;
        }

        return $entity;
    }

    public function prePersist(LifecycleEventArgs $args): void
    {
        $entity = $this->checkEntity($args);
        $entityManager = $args->getObjectManager();
        $user = $this->getUser($this->tokenStorage);
        $hashedPassword = $this->passwordHasher->hashPassword(
            $args->getObject(),
            $args->getObject()->getPassword()
        );
        $entity->setPassword($hashedPassword);
        $this->logger->info('Ustawiono hasło użytkownika: '.$args->getObject()->getUsername());
    }

    public function preUpdate(LifecycleEventArgs $args): void
    {
        $entity = $this->checkEntity($args);
        $entityManager = $args->getObjectManager();
        $user = $this->getUser($this->tokenStorage);
        $hashedPassword = $this->passwordHasher->hashPassword(
            $args->getObject(),
            $args->getObject()->getPassword()
        );
        $entity->setPassword($hashedPassword);
        $this->logger->info('Zmieniono hasło użytkownika: '.$args->getObject()->getUsername());
    }

    public static function getSubscribedEvents(): array
    {
        return [
            Events::prePersist,
            Events::preUpdate,
        ];
    }
}