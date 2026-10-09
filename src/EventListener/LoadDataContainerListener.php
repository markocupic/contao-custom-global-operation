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
use Contao\CoreBundle\String\HtmlAttributes;

#[AsHook('loadDataContainer', priority: 100)]
class LoadDataContainerListener
{
    public const DATA_ATTRIBUTE = 'data-customglobop';

    public function __invoke(string $table): void
    {
        foreach (($GLOBALS['TL_DCA'][$table]['list']['global_operations'] ?? []) as $name => $operation) {
            if (!\is_array($operation) || true !== ($operation['custom_glob_op'] ?? null)) {
                continue;
            }

            $GLOBALS['TL_DCA'][$table]['list']['global_operations'][$name]['attributes'] = $this->addDataAttribute($operation['attributes'] ?? null, (string) $name);
        }
    }

    /**
     * The attributes can be a string (Contao 5.3) or an HtmlAttributes object or an
     * array (Contao 6).
     */
    private function addDataAttribute(mixed $attributes, string $name): mixed
    {
        if ($attributes instanceof HtmlAttributes) {
            return $attributes->set(self::DATA_ATTRIBUTE, $name);
        }

        if (\is_array($attributes)) {
            $attributes[self::DATA_ATTRIBUTE] = $name;

            return $attributes;
        }

        return trim(\sprintf('%s %s="%s"', (string) $attributes, self::DATA_ATTRIBUTE, $name));
    }
}
