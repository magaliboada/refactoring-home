<?php
// src/EventSubscriber/LocaleSubscriber.php
namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\KernelInterface;
use Negotiation\LanguageNegotiator;
use Symfony\Component\HttpFoundation\RedirectResponse;

class LocaleCustomSubscriber implements EventSubscriberInterface
{
    private $supportedLanguages = ['es', 'en'];

    public function __construct(array $supportedLanguages = ['es', 'en'])
    {
        if (empty($supportedLanguages)) {
            throw new \InvalidArgumentException('At least one supported language must be given.');
        }

        $this->supportedLanguages = $supportedLanguages;
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::REQUEST  => ['redirectToLocalizedHomepage', 100],
        ];
    }

    public function redirectToLocalizedHomepage( $event)
    {
        // Do not modify sub-requests
        if (KernelInterface::MASTER_REQUEST !== $event->getRequestType()) {
            return;
        }
        // Assume all routes except the frontpage use the _locale parameter
        if ($event->getRequest()->getPathInfo() !== '/') {
            return;
        }

        $language = $this->supportedLanguages[0];
        // if (null !== $acceptLanguage = $event->getRequest()->headers->get('Accept-Language')) {
        //     $negotiator = new LanguageNegotiator();
        //     $best       = $negotiator->getBest(
        //         $event->getRequest()->headers->get('Accept-Language'),
        //         $this->supportedLanguages
        //     );

        //     if (null !== $best) {
        //         $language = $best->getType();
        //     }
        // }

        // $response = new RedirectResponse('/' . $language);
        $response = new RedirectResponse('/' . 'en');
        $event->setResponse($response);
    }
}