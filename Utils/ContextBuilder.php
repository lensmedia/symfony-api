<?php

namespace Lens\Bundle\ApiBundle\Utils;

use Doctrine\Common\Annotations\Reader;
use Lens\Bundle\ApiBundle\Annotation\Context;
use ReflectionClass;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Role\Role;

class ContextBuilder implements ContextBuilderInterface
{
    private $request;
    private $tokenStorage;
    private $reader;

    public function __construct(RequestStack $requestStack, TokenStorageInterface $tokenStorage, Reader $reader)
    {
        $this->request = $requestStack->getCurrentRequest();
        $this->tokenStorage = $tokenStorage;
        $this->reader = $reader;
    }

    public function getContext(array $defaultContext = []): array
    {
        return array_merge_recursive($defaultContext, $this->generateGroupsContext());
    }

    private function generateGroupsContext(): array
    {
        $groups = $this->generateGroupsFromAnnotations();
        $groups = $this->mergeGroupContext($this->generateRoleGroups(), $groups);

        return [
            'groups' => $this->generateGroupsFromAnnotations(),
        ];
    }

    private function mergeGroupContext(array $originalGroups, array $newGroups): array
    {
        $groups = [];

        foreach ($newGroups as $newGroup) {
            $groups[] = $newGroup;

            foreach ($originalGroups as $originalGroup) {
                $groups[] = sprintf('%s_%s', $originalGroup, $newGroup);
            }
        }

        return array_merge($originalGroups, $groups);
    }

    private function generateGroupsFromAnnotations(): array
    {
        $groups = [];

        list($controller, $method) = preg_split('~::~', $this->request->get('_controller'));

        $classContext = $this->request->get('_route');
        $methodContext = $method;

        // Get class annotation (used as route name).
        $reflectionClass = new ReflectionClass($controller);
        $classContextAnnotation = $this->reader->getClassAnnotation($reflectionClass, Context::class);
        if ($classContextAnnotation) {
            $classContext = $classContextAnnotation->name;
        }

        // Get subclass annotation (or use method name).
        $reflectionMethod = $reflectionClass->getMethod($method);
        $methodContextAnnotation = $this->reader->getMethodAnnotation($reflectionMethod, Context::class);
        if ($methodContextAnnotation) {
            $methodContext = $methodContextAnnotation->name;
        }

        $groups[] = $classContext;
        $groups[] = $methodContext;
        $groups[] = sprintf('%s_%s', $classContext, $methodContext);

        return $groups;
    }

    private function generateRoleGroups(): array
    {
        $roles = [];
        foreach ($this->getRoles() as $role) {
            if ($role instanceof Role) {
                $role = $role->getRole();
            }

            $roles[] = strtolower(str_replace('ROLE_', '', $role));
        }

        return $roles;
    }

    private function getRoles(): array
    {
        if (!$this->tokenStorage) {
            return [];
        }

        $token = $this->tokenStorage->getToken();
        if (null == $token) {
            return [];
        }

        return $token->getRoles();
    }
}
