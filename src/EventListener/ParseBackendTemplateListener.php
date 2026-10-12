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

namespace Markocupic\ContaoCustomGlobalOperation\EventListener;

use Contao\CoreBundle\DependencyInjection\Attribute\AsHook;
use Markocupic\ContaoCustomGlobalOperation\MenuBuilder\MenuBuilder;
use Symfony\Component\HttpFoundation\RequestStack;
use Twig\Environment;

/**
 * Moves the global operations with "custom_glob_op => true" out of the button bar
 * into the custom menus.
 *
 * The links are taken over exactly as Contao renders them (attributes, icon and
 * label), so the extension works with every Contao version without knowing the
 * markup of the icon: Contao 5.3 renders the icon as inline background-image,
 * Contao 5.6+ as <img> inside the link.
 */
#[AsHook('parseBackendTemplate', priority: 100)]
class ParseBackendTemplateListener
{
    public function __construct(
        private readonly RequestStack $requestStack,
        private readonly Environment $twig,
        private readonly MenuBuilder $menuBuilder,
    ) {
    }

    public function __invoke(string $buffer, string $template): string
    {
        if ('be_main' !== $template) {
            return $buffer;
        }

        $strTable = (string) $this->requestStack->getCurrentRequest()?->query->get('table');

        if ('' === $strTable || !\is_array($GLOBALS['TL_DCA'][$strTable] ?? null)) {
            return $buffer;
        }

        // Find the links of the custom global operations. In Contao 5.6+ a link can
        // be rendered twice (in the button bar and in the operations menu).
        $regexp = '/<a\s[^>]*'.LoadDataContainerListener::DATA_ATTRIBUTE.'="([^"]*)"[^>]*>.*?<\/a>/si';

        if (!preg_match_all($regexp, $buffer, $matches, PREG_SET_ORDER)) {
            return $buffer;
        }

        $links = [];

        foreach ($matches as [$html, $name]) {
            $links[$name] ??= $this->cleanUpLink($html);

            // Remove the original link (and its list item) from the global operations
            $buffer = $this->removeLink($html, $buffer);
        }

        $menus = $this->menuBuilder->buildMenus($GLOBALS['TL_DCA'][$strTable], $links);

        if (!$menus) {
            return $buffer;
        }

        $strMenuContainer = $this->twig->render('@MarkocupicContaoCustomGlobalOperation/be_nav_container.html.twig', [
            'menus' => $menus,
        ]);

        return str_replace('<div class="tl_listing_container', $strMenuContainer.'<div class="tl_listing_container', $buffer);
    }

    /**
     * The link is moved out of the operations menu of Contao 5.6+, so the action
     * closing this menu would point to a missing controller.
     */
    private function cleanUpLink(string $html): string
    {
        return preg_replace('/\s*contao--operations-menu#close/', '', $html) ?? $html;
    }

    private function removeLink(string $html, string $buffer): string
    {
        $listItem = '/<li\b[^>]*>\s*'.preg_quote($html, '/').'\s*<\/li>/s';
        $result = preg_replace($listItem, '', $buffer, -1, $count);

        if (null !== $result && $count > 0) {
            return $result;
        }

        return str_replace($html, '', $buffer);
    }
}
