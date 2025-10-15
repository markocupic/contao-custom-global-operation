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

namespace Markocupic\ContaoCustomGlobalOperation\MenuBuilder;

use Knp\Menu\Matcher\Matcher;
use Knp\Menu\MenuFactory;
use Knp\Menu\Renderer\ListRenderer;
use Markocupic\ContaoCustomGlobalOperation\Util\DomUtil;

class MenuBuilder
{
    private ?array $globOps = null;

    private ?array $dca = null;

    private ?array $arrMenus = null;

    public function generateMenus(array $globOps, array $dca): string
    {
        $this->globOps = $globOps;
        $this->dca = $dca;
        $this->initialize();

        return $this->generate();
    }

    private function generate(): string
    {
        $markup = '';

        if (empty($this->arrMenus)) {
            return $markup;
        }

        foreach ($this->arrMenus as $menuName => $arrMenuItems) {
            $factory = new MenuFactory();
            $menu = $factory->createItem('custom_global_operations_'.$menuName);
            $menu->setChildrenAttribute('class', 'custom-glob-op-menu');
            $menu->setChildrenAttribute('data-name', $menuName);

            foreach ($arrMenuItems as $v) {
                $arrClasses = [
                    'custom-glob-op-menu-item',
                ];

                // Add link item
                $menu
                    ->addChild($v['name'], ['uri' => $v['href']])
                    ->setLabel($v['label'])
                ;

                // Push css classes to the li-tag
                $menu
                    ->getChild($v['name'])
                    ->setAttribute('class', implode(' ', $arrClasses))
                ;

                // Push original attributes to the link tag
                if (!empty($v['attributes'])) {
                    foreach ($v['attributes'] as $attrKey => $attrVal) {
                        $menu
                            ->getChild($v['name'])
                            ->setLinkAttribute($attrKey, (string) $attrVal)
                        ;
                    }
                }
            }

            $renderer = new ListRenderer(new Matcher());

            $markup .= $renderer->render($menu);
        }

        return $markup;
    }

    private function initialize(): void
    {
        foreach ($this->globOps as $globOp) {
            $menuItem = $this->dca['list']['global_operations'][$globOp['name']] ?? null;

            if (!empty($menuItem) && \is_array($menuItem)) {
                $sorting = 0;

                --$sorting;
                $v = $menuItem;

                // Set defaults
                $v['name'] = $globOp['name'];
                $v['custom_glob_op_group'] = !isset($v['custom_glob_op_options']['add_to_menu_group']) || !\is_string($v['custom_glob_op_options']['add_to_menu_group']) ? 'default' : $v['custom_glob_op_options']['add_to_menu_group'];
                $v['href'] = $globOp['href'];
                $v['label'] = $globOp['label'];
                $v['sorting'] = !isset($v['custom_glob_op_options']['sorting']) || !\is_int($v['custom_glob_op_options']['sorting']) ? $sorting : $v['custom_glob_op_options']['sorting'];
                $v['attributes'] = DomUtil::getAttributesFromTag($globOp['html']);

                if (!isset($this->arrMenus[$v['custom_glob_op_group']]) || !\is_array($this->arrMenus[$v['custom_glob_op_group']])) {
                    $this->arrMenus[$v['custom_glob_op_group']] = [];
                }

                $this->arrMenus[$v['custom_glob_op_group']][] = $v;

                // Set the correct sorting order
                foreach ($this->arrMenus as $key => $arrMenus) {
                    $arrSorting = array_column($arrMenus, 'sorting');
                    array_multisort($arrSorting, SORT_DESC, $arrMenus);
                    $this->arrMenus[$key] = $arrMenus;
                }
            }
        }
    }
}
