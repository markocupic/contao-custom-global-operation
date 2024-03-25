<?php

declare(strict_types=1);

/*
 * This file is part of Contao Custom Global Operation.
 *
 * (c) Marko Cupic 2024 <m.cupic@gmx.ch>
 * @license MIT
 * For the full copyright and license information,
 * please view the LICENSE file that was distributed with this source code.
 * @link https://github.com/markocupic/contao-custom-global-operation
 */

namespace Markocupic\ContaoCustomGlobalOperation\EventListener;

use Contao\CoreBundle\DependencyInjection\Attribute\AsHook;

#[AsHook('loadDataContainer', priority: 100)]
class LoadDataContainerListener
{
    public function __invoke(string $table): void
    {
        foreach (($GLOBALS['TL_DCA'][$table]['list']['global_operations'] ?? []) as $globOpName => $glOp) {
            if (!empty($glOp['custom_glob_op']) && true === $glOp['custom_glob_op']) {
                $GLOBALS['TL_DCA'][$table]['list']['global_operations'][$globOpName]['attributes'] = ($glOp['attributes'] ?? '').sprintf(' data-customglobop="%s"', $globOpName);
            }
        }
    }
}
