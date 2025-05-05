<?php
// src/EventListener/AccessDeniedListener.php
namespace App\EventListener;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Twig\Environment;

class AccessDeniedListener
{
    private $twig;
    private $environment;

    public function __construct(Environment $twig, string $environment)
    {
        $this->twig = $twig;
        $this->environment = $environment;
    }

    public function onKernelException(ExceptionEvent $event)
    {
        $exception = $event->getThrowable();
        
        if (!$exception instanceof AccessDeniedException) {
            return;
        }

        $content = $this->twig->render('errors/access_denied.html.twig', [
            'is_production' => $this->environment === 'prod'
        ]);

        $response = new Response();
        $response->setContent($content);
        $response->setStatusCode(Response::HTTP_FORBIDDEN);
        
        $event->setResponse($response);
    }
}