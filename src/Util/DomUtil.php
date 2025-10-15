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

namespace Markocupic\ContaoCustomGlobalOperation\Util;

class DomUtil
{
    public static function getAttributesFromTag(string $html): array
    {
        $dom = new \DOMDocument();
        $dom->loadHTML($html);
        $attributes = [];
        $p = $dom->getElementsByTagName('a')->item(0);

        if ($p->hasAttributes()) {
            foreach ($p->attributes as $attr) {
                $name = $attr->nodeName;
                $value = $attr->nodeValue;
                $attributes[$name] = (string) $value;
            }
        }

        return $attributes;
    }

    public static function getNodeValueFromTag(string $html): string
    {
        $dom = new \DOMDocument();
        libxml_use_internal_errors(true); // suppress warnings for malformed HTML
        $dom->loadHTML($html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        libxml_clear_errors();

        $link = $dom->getElementsByTagName('a')->item(0);

        $innerText = $link ? mb_convert_encoding((string) $link->nodeValue, 'ISO-8859-1', 'UTF-8') : '';

        return (string) $innerText;
    }
}
