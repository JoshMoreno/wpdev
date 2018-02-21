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

	public function testSingularNameGeneratesCorrectly() {

    	$names = [
    	    'project' => 'Project', // capitalizes
		    'dummy_name' => 'Dummy Name', //turns underscores into spaces and capitalizes
		    'dummy_sample_name' => 'Dummy Sample Name', //turns underscores into spaces and capitalizes
		    'dummy-name' => 'Dummy-Name', // capitalizes hyphenated words
		    'dummy-sample-name' => 'Dummy-Sample-Name', // capitalizes hyphenated words
		    'dummy_sample-name' => 'Dummy Sample-Name', // handles both spaces and hyphenated fine
		    'dummy-sample-' => 'Dummy-Sample-', // doesn't mess with last hyphen, edge case
		    '_dummy_sample_' => 'Dummy Sample', // trims spaces
	    ];

		foreach ($names as $name => $expected) {
			$postType = \WPDev\Facades\PostType::create($name);
			$this->assertEquals($expected, $postType->singularName);
    	}
    }

	/*
	|--------------------------------------------------------------------------
	| Data Providers
	|--------------------------------------------------------------------------
	*/

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