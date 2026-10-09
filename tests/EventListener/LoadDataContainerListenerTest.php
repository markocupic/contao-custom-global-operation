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

namespace Markocupic\ContaoCustomGlobalOperation\Tests\EventListener;

use Contao\CoreBundle\String\HtmlAttributes;
use Markocupic\ContaoCustomGlobalOperation\EventListener\LoadDataContainerListener;
use PHPUnit\Framework\TestCase;

class LoadDataContainerListenerTest extends TestCase
{
    protected function tearDown(): void
    {
        unset($GLOBALS['TL_DCA']['tl_test']);
    }

    public function testAddsTheDataAttributeToStringAttributes(): void
    {
        $GLOBALS['TL_DCA']['tl_test']['list']['global_operations'] = [
            'all' => ['href' => 'act=select', 'attributes' => 'accesskey="e"'],
            'export' => ['href' => 'key=export', 'attributes' => 'accesskey="x"', 'custom_glob_op' => true],
            'import' => ['href' => 'key=import', 'custom_glob_op' => true],
        ];

        (new LoadDataContainerListener())('tl_test');

        $operations = $GLOBALS['TL_DCA']['tl_test']['list']['global_operations'];

        $this->assertSame('accesskey="e"', $operations['all']['attributes']);
        $this->assertSame('accesskey="x" data-customglobop="export"', $operations['export']['attributes']);
        $this->assertSame('data-customglobop="import"', $operations['import']['attributes']);
    }

    public function testAddsTheDataAttributeToArrayAndObjectAttributes(): void
    {
        $GLOBALS['TL_DCA']['tl_test']['list']['global_operations'] = [
            'export' => ['attributes' => ['accesskey' => 'x'], 'custom_glob_op' => true],
            'import' => ['attributes' => new HtmlAttributes(['accesskey' => 'i']), 'custom_glob_op' => true],
            '-',
        ];

        (new LoadDataContainerListener())('tl_test');

        $operations = $GLOBALS['TL_DCA']['tl_test']['list']['global_operations'];

        $this->assertSame(['accesskey' => 'x', 'data-customglobop' => 'export'], $operations['export']['attributes']);
        $this->assertSame(' accesskey="i" data-customglobop="import"', (string) $operations['import']['attributes']);
        $this->assertSame('-', $operations[0]);
    }
}
