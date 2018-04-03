<?php

namespace Lens\Bundle\ApiBundle\Controller;

use Lens\Bundle\ApiBundle\HttpFoundation\ApiResponse;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\ConstraintViolation;

abstract class ApiController extends Controller
{
    /**
     * Helper function for our doctrine manager.
     *
     * @return EntityManager
     */
    protected function getManager()
    {
        return $this
            ->getDoctrine()
            ->getManager();
    }

    /**
     * Helper function for a specific repository.
     *
     * @param string $class
     *
     * @return Repository
     */
    protected function getRepository($class)
    {
        if (is_object($class)) {
            $class = get_class($class);
        }

        return $this
            ->getManager()
            ->getRepository($class);
    }

    /**
     * Get an array of all form errors.
     *
     * @param Form $form the form to check for errors
     *
     * @return array
     */
    protected function getFormErrors(Form $form)
    {
        $errors = [];

        foreach ($form->getErrors(true) as $error) {
            $cause = $error->getCause();

            if (!$cause instanceof ConstraintViolation) {
                return;
            }

            $pp = substr($cause->getPropertyPath(), 5);
            if (!isset($errors[$pp])) {
                $errors[$pp] = [];
            }

            $errors[$pp][] = [
                'message' => $cause->getMessage(),
                'parameters' => $cause->getParameters(),
            ];
        }

        return $errors;
    }

    /**
     * Get the request body content.
     *
     * @param Request $request the request to get the content from
     * @param bool    $decode  attempt to parse as JSON
     *
     * @return mixed
     */
    protected function getRequestContent(Request $request, bool $decode = true)
    {
        $content = $request->getContent();

        // JSON decode to array if enabled.
        if ($decode) {
            $content = json_decode($content, true);
            if (!is_array($content)) {
                $content = [];
            }
        }

        return $content;
    }

    /**
     * Can be configured to catch 404 under a specific directory.
     * This is used for routes not bound to a controller to test if it is an API request.
     *
     * @return ApiResponse generic API 404 response
     */
    public static function catchAllAction()
    {
        return ApiResponse::create(404);
    }
}
