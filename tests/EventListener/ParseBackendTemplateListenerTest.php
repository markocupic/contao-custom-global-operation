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

use Markocupic\ContaoCustomGlobalOperation\EventListener\ParseBackendTemplateListener;
use Markocupic\ContaoCustomGlobalOperation\MenuBuilder\MenuBuilder;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Twig\Environment;
use Twig\Loader\ArrayLoader;

class ParseBackendTemplateListenerTest extends TestCase
{
    protected function setUp(): void
    {
        $GLOBALS['TL_DCA']['tl_test']['list']['global_operations'] = [
            'all' => ['href' => 'act=select'],
            'export' => ['href' => 'key=export', 'custom_glob_op' => true, 'custom_glob_op_options' => ['add_to_menu_group' => 'tools', 'sorting' => 20]],
            'import' => ['href' => 'key=import', 'custom_glob_op' => true, 'custom_glob_op_options' => ['add_to_menu_group' => 'tools', 'sorting' => 10]],
        ];
    }

    protected function tearDown(): void
    {
        unset($GLOBALS['TL_DCA']['tl_test']);
    }

    public function testMovesTheOperationsIntoTheMenuContao53Markup(): void
    {
        $buffer = '<div id="tl_buttons">'
            .'<a href="contao?do=test&amp;act=select" class="header_edit_all">Edit multiple</a> '
            .'<a href="contao?do=test&amp;key=export" class="export" title="Export" data-customglobop="export">Export</a> '
            .'<a href="contao?do=test&amp;key=import" class="import" title="Import" data-customglobop="import">Import</a>'
            .'</div><div class="tl_listing_container list_view"></div>';

        $result = $this->createListener()($buffer, 'be_main');

        $this->assertStringContainsString('header_edit_all', $result);
        $this->assertStringContainsString('<div class="nav">', $result);
        $this->assertSame(1, substr_count($result, 'key=export'));
        $this->assertSame(1, substr_count($result, 'key=import'));
        $this->assertMatchesRegularExpression('/key=import.*key=export/s', $result, 'Sorted by the "sorting" option (descending)');
        $this->assertMatchesRegularExpression('/<div class="nav">\s*<ul class="custom-glob-op-menu" data-name="tools">/', $result);
        $this->assertStringNotContainsString('&amp;amp;', $result);
    }

    public function testMovesTheOperationsIntoTheMenuContao6Markup(): void
    {
        // In Contao 6 the href comes after the other attributes, the link is wrapped
        // in a list item, contains an icon and can be rendered twice.
        $link = '<a class="export" data-customglobop="export" data-action="contao--operations-menu#close" href="contao?do=test&amp;key=export"><img src="export.svg" alt=""> Export</a>';

        $buffer = '<div class="operations"><ul>'
            .'<li><a class="all" href="contao?do=test&amp;act=select">Edit multiple</a></li>'
            .'<li>'.$link.'</li>'
            .'<li class="operations-menu-container"><ul class="operations-menu"><li data-contao--operations-menu-target="title">'.$link.'</li></ul></li>'
            .'</ul></div><div class="tl_listing_container list_view"></div>';

        $result = $this->createListener()($buffer, 'be_main');

        $this->assertSame(1, substr_count($result, 'key=export'));
        $this->assertStringNotContainsString('<li></li>', $result);
        $this->assertStringContainsString('class="all"', $result);
        $this->assertStringContainsString('>Export</a>', $result);
        $this->assertStringNotContainsString('<img', $result);
    }

    public function testIgnoresOtherTemplates(): void
    {
        $buffer = '<a href="x" data-customglobop="export">Export</a><div class="tl_listing_container"></div>';

        $this->assertSame($buffer, $this->createListener()($buffer, 'be_login'));
    }

    private function createListener(): ParseBackendTemplateListener
    {
        $requestStack = new RequestStack();
        $requestStack->push(new Request(['table' => 'tl_test']));

        $twig = new Environment(new ArrayLoader([
            '@MarkocupicContaoCustomGlobalOperation/be_nav_container.html.twig' => '<div class="nav">{{ menu|raw }}</div>',
        ]));

        return new ParseBackendTemplateListener($requestStack, $twig, new MenuBuilder());
    }
}
