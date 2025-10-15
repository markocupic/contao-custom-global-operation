<?php

declare(strict_types=1);

/*
 * This file is part of Contao Custom Global Operation.
 *
 * (c) Marko Cupic <m.cupic@gmx.ch>
 * @license MIT
 * For the full copyright and license information,
 * please view the LICENSE file that was distributed with this source code.
 * @link https://github.com/markocupic/contao-custom-global-operation
 */

namespace Markocupic\ContaoCustomGlobalOperation\EventSubscriber;

use Contao\CoreBundle\Routing\ScopeMatcher;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class BackendAssetsSubscriber implements EventSubscriberInterface
{
    protected ScopeMatcher $scopeMatcher;

    public function __construct(ScopeMatcher $scopeMatcher)
    {
        $this->scopeMatcher = $scopeMatcher;
    }

    public static function getSubscribedEvents()
    {
        return [KernelEvents::REQUEST => 'registerBackendAssets'];
    }

    public function registerBackendAssets(RequestEvent $e): void
    {
        $request = $e->getRequest();

        if ($this->scopeMatcher->isBackendRequest($request)) {
            // Add Backend CSS
            $GLOBALS['TL_CSS'][] = 'bundles/markocupiccontaocustomglobaloperation/css/styles.css';
        }
    }
}
