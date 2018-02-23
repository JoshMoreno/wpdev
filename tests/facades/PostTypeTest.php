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
            $postType = \WPDev\Facades\PostType::create($name);
            $this->assertEquals($expected, $postType->singularName);
        }
    }

    public function testPostTypeRegistersWithoutError()
    {
        $post = \WPDev\Facades\PostType::create('project')->registerManually();
        $this->assertInstanceOf('WP_Post_Type', $post);
    }

    public function testPostTypeRegistersWithCorrectArgs()
    {
        $post = \WPDev\Facades\PostType::create('project')->registerManually();

        // labels
        $this->assertSame('Projects', $post->labels->name);
        $this->assertSame('Project', $post->labels->singular_name);
        $this->assertSame("Add New", $post->labels->add_new);
        $this->assertSame("Add New Project", $post->labels->add_new_item);
        $this->assertSame("Edit Project", $post->labels->edit_item);
        $this->assertSame("New Project", $post->labels->new_item);
        $this->assertSame("View Project", $post->labels->view_item);
        $this->assertSame("View Projects", $post->labels->view_items);
        $this->assertSame("Search Projects", $post->labels->search_items);
        $this->assertSame("No Projects found", $post->labels->not_found);
        $this->assertSame("No Projects found in Trash", $post->labels->not_found_in_trash);
        $this->assertSame("Parent Project:", $post->labels->parent_item_colon);
        $this->assertSame("All Projects", $post->labels->all_items);
        $this->assertSame("Project Archives", $post->labels->archives);
        $this->assertSame("Project Attributes", $post->labels->attributes);
        $this->assertSame("Insert into Project", $post->labels->insert_into_item);
        $this->assertSame("Uploaded to this Project", $post->labels->uploaded_to_this_item);
        $this->assertSame('Featured Image', $post->labels->featured_image);
        $this->assertSame('Set featured image', $post->labels->set_featured_image);
        $this->assertSame('Remove featured image', $post->labels->remove_featured_image);
        $this->assertSame('Use as featured image', $post->labels->use_featured_image);
        $this->assertSame('Projects', $post->labels->menu_name);
        $this->assertSame("Filter Projects list", $post->labels->filter_items_list);
        $this->assertSame("Projects list navigation", $post->labels->items_list_navigation);
        $this->assertSame("Projects list", $post->labels->items_list);
        $this->assertSame('Project', $post->labels->name_admin_bar);

        // description
        $this->assertSame('Handles the Projects', $post->description);

        // public
        $this->assertSame(true, $post->public);
        //'public'      => true,

        // menu position
        $this->assertSame(5, $post->menu_position);
            //'menu_position' => 5, // below posts

        // why..why...😞
        $actual_supports = array_keys($GLOBALS['_wp_post_type_features']['project']);

        $this->assertEquals(['title', 'editor', 'thumbnail'], $actual_supports);
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