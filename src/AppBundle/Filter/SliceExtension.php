<?php
/**
 * Created by PhpStorm.
 * User: Алексей
 * Date: 07.09.2017
 * Time: 15:39
 */

namespace AppBundle\Filter;


class SliceExtension extends \Twig_Extension
{
    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('sliceAdvanced', array($this, 'sliceFilter')),
        );
    }

    public function sliceFilter($text)
    {
        $text = substr($text, 0, 780);
        $text = rtrim($text, "!,.-");
        $text = substr($text, 0, strrpos($text, ' '));
        $text .= '...';
        return $text;
    }
}