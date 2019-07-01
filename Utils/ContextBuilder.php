<?php

namespace Lens\Bundle\ApiBundle\Utils;

use Doctrine\Common\Annotations\Reader;
use Lens\Bundle\ApiBundle\Annotation\Context;
use ReflectionClass;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Role\Role;

/**
 * Builds up groups context based on api context name (annotation) or route name/ method name and user roles.
 *
 * Example case:
 * Using:
 *     Annotation: @Api\Context("assortments")
 *     Method: index
 *     Roles on user: ROLE_USER & ROLE_ADMIN
 *
 * 0 => "assortments"
 * 1 => "index"
 * 2 => "assortments_index"
 * 3 => "user"
 * 4 => "assortments_user"
 * 5 => "index_user"
 * 6 => "assortments_index_user"
 * 7 => "admin"
 * 8 => "assortments_admin"
 * 9 => "index_admin"
 * 10 => "assortments_index_admin"
 *
 * These can then be used by our serialization groups annotation.
 * *note* the exponential increase for each role available to the user. If a user has to many roles this gets real bad.
 */
class ContextBuilder implements ContextBuilderInterface
{
    private $request;
    private $tokenStorage;
    private $reader;
    private $debug;

    public function __construct(bool $debug, RequestStack $requestStack, TokenStorageInterface $tokenStorage, Reader $reader)
    {
        $this->debug = $debug;
        $this->request = $requestStack->getCurrentRequest();
        $this->tokenStorage = $tokenStorage;
        $this->reader = $reader;
    }

    public function getContext(array $defaultContext = []): array
    {
        $context = array_merge_recursive($defaultContext, $this->generateGroupsContext());

        return $context;
    }

    private function generateGroupsContext(): array
    {
        $groups = $this->generateGroupsFromAnnotations();
        $groups = $this->mergeGroupContext($groups, $this->generateRoleGroups());

        $groups[] = 'default';

        return [
            'debug' => $this->debug,
            'groups' => $groups,
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

        $controller = $this->request->get('_controller');
        if (!$controller) {
            return $groups;
        }

        list($controller, $method) = preg_split('~::~', $controller);

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
        foreach ($this->getRoleNames() as $role) {
            if ($role instanceof Role) {
                $role = $role->getRole();
            }

            $roles[] = strtolower(str_replace('ROLE_', '', $role));
        }

        return $roles;
    }

    private function getRoleNames(): array
    {
        if (!$this->tokenStorage) {
            return [];
        }

        $token = $this->tokenStorage->getToken();
        if (null === $token) {
            return [];
        }

        return $token->getRoleNames();
    }
}
