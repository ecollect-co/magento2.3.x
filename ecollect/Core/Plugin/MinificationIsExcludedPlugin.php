<?php

namespace ecollect\Core\Plugin;
/**
 * Class MinificationIsExcludedPlugin
 *
 * @package ecollect\Core\Plugin
 */
class MinificationIsExcludedPlugin
{
    public function __construct()
    {
    }

    public function aroundGetExcludes(\Magento\Framework\View\Asset\Minification $minification, callable $proceed, $contentType)
    {
        $returnValue = $proceed($contentType);
        if ($contentType == 'js') {
            $returnValue[] = 'ecollect.js';
            $returnValue[] = 'mptools/buttons/render.js';
        }

        return $returnValue;
    }
}