<?php

class PostTypeTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Tests the post type name validator
     *
     * @param $name
     *
     * @dataProvider validNameProvider
     */
    public function testNameValidatesCorrectly($name)
    {
        $this->expectException(InvalidArgumentException::class);
        \WPDev\Facades\PostType::create($name);
    }

    /**
     * Valid names
     * @return array
     */
    public function validNameProvider()
    {
        $tests = [
            'empty'                    => [''],
            'contains spaces'          => ['some name'],
            'contains only spaces'     => [' '],
            'contains capital letters' => ['Somename'],
            'more than 20 characters'  => ['123456789123456789123'],
        ];

        return array_merge($this->reservedNamesProvider(), $tests);
    }

    /**
     * Reserved post type names
     *
     * @return array
     */
    public function reservedNamesProvider()
    {
        return [
            'reserved name of post'                => ['post'],
            'reserved name of page'                => ['page'],
            'reserved name of attachment'          => ['attachment'],
            'reserved name of revision'            => ['revision'],
            'reserved name of nav_menu_item'       => ['nav_menu_item'],
            'reserved name of custom_css'          => ['custom_css'],
            'reserved name of customize_changeset' => ['customize_changeset'],
            'reserved name of action'              => ['action'],
            'reserved name of author'              => ['author'],
            'reserved name of order'               => ['order'],
            'reserved name of theme'               => ['theme'],
        ];
    }
}