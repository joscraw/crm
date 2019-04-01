<?php

namespace App\Mailer;

use Swift_Mailer;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\Routing\RouterInterface;
use Twig\Environment;

/**
 * Class AbstractMailer
 * @package App\Mailer
 */
class AbstractMailer
{
    /**
     * @var Swift_Mailer
     */
    protected $mailer;

    /**
     * @var RouterInterface
     */
    protected $router;

    /**
     * @var Environment
     */
    protected $templating;

    /**
     * AbstractMailer constructor.
     * @param Swift_Mailer $mailer
     * @param RouterInterface $router
     * @param Environment $templating
     */
    public function __construct(Swift_Mailer $mailer, RouterInterface $router, Environment $templating)
    {
        $this->mailer = $mailer;
        $this->router = $router;
        $this->templating = $templating;
    }

    /**
     * Generate the fully qualified base URL (scheme + host + port, if not default + app base path)
     *
     * @return string
     */
    protected function getFullyQualifiedBaseUrl()
    {
        $routerContext = $this->router->getContext();
        $port = $routerContext->getHttpPort();

        return sprintf(
            '%s://%s%s%s',
            $routerContext->getScheme(),
            $routerContext->getHost(),
            ($port !== 80 ? ':'.$port : ''),
            $routerContext->getBaseUrl()
        );
    }
}