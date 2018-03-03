<?php
namespace WPDev\Tests\Unit;

use InvalidArgumentException;
use WP_Mock\Tools\TestCase;
use WPDev\Facades\PostType;

class PostTypeTest extends TestCase {
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
        PostType::create($name);
    }

    public function testSingularNameGeneratesCorrectly()
    {
        $names = [
            // capitalizes
            'project'           => 'Project',
            //turns underscores into spaces and capitalizes
            'dummy_name'        => 'Dummy Name',
            //turns underscores into spaces and capitalizes
            'dummy_sample_name' => 'Dummy Sample Name',
            // capitalizes hyphenated words
            'dummy-name'        => 'Dummy-Name',
            // capitalizes hyphenated words
            'dummy-sample-name' => 'Dummy-Sample-Name',
            // handles both spaces and hyphenated fine
            'dummy_sample-name' => 'Dummy Sample-Name',
            // doesn't mess with last hyphen, edge case
            'dummy-sample-'     => 'Dummy-Sample-',
            // trims spaces
            '_dummy_sample_'    => 'Dummy Sample',
        ];

        foreach ($names as $name => $expected) {
            $postType = PostType::create($name);
            $this->assertEquals($expected, $postType->singularName);
        }
    }

    public function testSetArg()
    {
        $post = PostType::create('project')->setArg('something', 'some val');
        $this->assertSame('some val', $post->overrideArgs['something']);

        /** 'supports' works a little differently and will be tested in
         * We'll test this in @see testSupports
         */
    }

    public function testSupports()
    {
        $post = PostType::create('project');

        // boolean overwrites
        $post->supports(false);
        $this->assertFalse($post->supports);

        // array overwrites
        $post->supports([1,2,3]);
        $this->assertSame([1,2,3], $post->supports);

        // anything else gets appended
        $post->supports(4);
        $this->assertSame([1,2,3,4], $post->supports);

        // one more time, array overwrites
        $post->supports([1]);
        $this->assertSame([1], $post->supports);
    }

    public function testSetPluralName()
    {
        $post = PostType::create('story')->setPluralName('Stories');
        $this->assertSame('Stories', $post->pluralName);
    }

    public function testSetSingularName()
    {
        $post = PostType::create('custom_story')->setSingularName('Story');
        $this->assertSame('Story', $post->singularName);
    }

    /*
    |--------------------------------------------------------------------------
    | Data Providers
    |--------------------------------------------------------------------------
    */

    /**
     * Valid names
     *
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