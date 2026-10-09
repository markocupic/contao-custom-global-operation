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

#[AsHook('parseBackendTemplate', priority: 100)]
class ParseBackendTemplateListener
{
    private RequestStack $requestStack;

    private Environment $twig;

    private MenuBuilder $menuBuilder;

    public function __construct(RequestStack $requestStack, Environment $twig, MenuBuilder $menuBuilder)
    {
        $this->requestStack = $requestStack;
        $this->twig = $twig;
        $this->menuBuilder = $menuBuilder;
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

        // Find the links of the custom global operations. The attribute order differs
        // between Contao 5.3 and Contao 6, and in Contao 6 a link can be rendered twice
        // (in the button bar and in the operations menu).
        $regexp = '/<a\\s[^>]*'.LoadDataContainerListener::DATA_ATTRIBUTE.'="([^"]*)"[^>]*>(.*?)<\\/a>/si';

        if (!preg_match_all($regexp, $buffer, $matches, PREG_SET_ORDER)) {
            return $buffer;
        }

        $arrGlobOp = [];

        foreach ($matches as [$html, $name, $innerHtml]) {
            $arrGlobOp[$name] ??= [
                'html' => $html,
                'name' => $name,
                'label' => trim(html_entity_decode(strip_tags($innerHtml), ENT_QUOTES | ENT_HTML5)),
            ];

            // Remove the original link (and its list item) from the global operations
            $buffer = $this->removeLink($html, $buffer);
        }

        return $this->injectMenu($strTable, array_values($arrGlobOp), $buffer);
    }

    private function removeLink(string $html, string $buffer): string
    {
        $listItem = '/<li\\b[^>]*>\\s*'.preg_quote($html, '/').'\\s*<\\/li>/s';
        $result = preg_replace($listItem, '', $buffer, -1, $count);

        if (null !== $result && $count > 0) {
            return $result;
        }

        return str_replace($html, '', $buffer);
    }

    private function injectMenu(string $strTable, array $globOp, string $buffer): string
    {
        $dca = $GLOBALS['TL_DCA'][$strTable];

        $strMenus = $this->menuBuilder->generateMenus($strTable, $globOp, $dca);

        if (!\strlen($strMenus)) {
            return $buffer;
        }

        $strMenuContainer = $this->twig->render('@MarkocupicContaoCustomGlobalOperation/be_nav_container.html.twig', [
            'menu' => $strMenus,
        ]);

        return str_replace('<div class="tl_listing_container', $strMenuContainer.'<div class="tl_listing_container', $buffer);
    }
}
