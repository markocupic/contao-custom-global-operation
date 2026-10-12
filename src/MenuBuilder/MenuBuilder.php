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

/**
 * Groups the links of the custom global operations into menus.
 */
class MenuBuilder
{
    public const DEFAULT_GROUP = 'default';

    /**
     * @param array<string, mixed>  $dca   The DCA of the current table
     * @param array<string, string> $links The rendered links (HTML), indexed by the operation name
     *
     * @return array<string, list<array{name: string, html: string, sorting: int}>> The menu items, indexed by the menu group
     */
    public function buildMenus(array $dca, array $links): array
    {
        $menus = [];

        foreach ($links as $name => $html) {
            $operation = $dca['list']['global_operations'][$name] ?? null;

            if (!\is_array($operation)) {
                continue;
            }

            $options = $operation['custom_glob_op_options'] ?? [];
            $group = \is_string($options['add_to_menu_group'] ?? null) ? $options['add_to_menu_group'] : self::DEFAULT_GROUP;

            $menus[$group][] = [
                'name' => (string) $name,
                'html' => $html,
                'sorting' => \is_int($options['sorting'] ?? null) ? $options['sorting'] : -1,
            ];
        }

        // Items with a higher sorting value come first
        foreach ($menus as $group => $items) {
            usort($items, static fn (array $a, array $b): int => $b['sorting'] <=> $a['sorting']);
            $menus[$group] = $items;
        }

        return $menus;
    }
}
