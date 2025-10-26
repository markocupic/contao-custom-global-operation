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
        $request = $this->requestStack->getCurrentRequest();

        $strTable = $request->query->get('table');

        if ('be_main' === $template && $strTable) {
            $dca = $GLOBALS['TL_DCA'][$strTable];

            if (empty($dca) || !\is_array($dca)) {
                return $buffer;
            }

            $strTable = $request->query->get('table');

            if ($strTable) {
                $regexp = '<a\\s[^>]*href="([^"]*)"[^>]*data-customglobop="([^"]*)"[^>]*>(.*)<\\/a>';
                preg_match_all("/$regexp/siU", $buffer, $matches);

                if ($matches) {
                    $arrGlobOp = [];

                    foreach (array_keys($matches[0]) as $i) {
                        $arrGlobOp[] = [
                            'html' => $matches[0][$i],
                            'href' => $matches[1][$i],
                            'name' => $matches[2][$i],
                            'label' => $matches[3][$i],
                        ];

                        // Remove original link from global operation list
                        $buffer = str_replace($matches[0][$i], '', $buffer);
                    }

                    // Inject menu
                    $buffer = $this->injectMenu($strTable, $arrGlobOp, $buffer);
                }
            }
        }

        return $buffer;
    }

    private function injectMenu(string $strTable, array $globOp, string $buffer): string
    {
        $dca = $GLOBALS['TL_DCA'][$strTable];

        if (empty($dca) || !\is_array($dca)) {
            return $buffer;
        }

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
