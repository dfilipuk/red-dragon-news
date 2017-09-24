<?php
/**
 * Created by PhpStorm.
 * User: dmitry
 * Date: 24.9.17
 * Time: 19.24
 */

namespace AppBundle\Filter;

class StyleSelectorExtension extends \Twig_Extension
{
    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('styleSelector', array($this, 'styleSelectorFilter')),
        );
    }

    public function styleSelectorFilter(string $style, bool $condition1, bool $condition2)
    {
        if ($condition1 && $condition2) {
            return $style;
        } else {
           return '';
        }

    }
}