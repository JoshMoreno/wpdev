<?php

use WPDev\Facades\PostType;

class PostTypeTest extends WP_UnitTestCase
{
    public function testPostTypeRegistersSuccessfully()
    {
        $post = PostType::create('project')->registerManually();
        $this->assertInstanceOf('WP_Post_Type', $post);
        unregister_post_type('project');
    }

    public function testPostTypeRegistersWithCorrectDefaultArgs()
    {
        $post = PostType::create('project')->registerManually();

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
        unregister_post_type('project');
    }

    public function testCanExportArg()
    {
        $this->booleanArgTest('canExport', 'can_export');
    }

    public function testDeleteWithUserArg()
    {
        $this->booleanArgTest('deleteWithUser', 'delete_with_user');
    }

    public function testDeregister()
    {
        $post = PostType::create('project');

        // make sure it adds to the global before we check if deregister works
        $post->registerManually();
        $this->assertArrayHasKey('project', $GLOBALS['wp_post_types']);

        $post->deregister();
        $this->assertArrayNotHasKey('project', $GLOBALS['wp_post_types']);
    }

    public function testHasArchiveArg()
    {
        $this->booleanArgTest('hasArchive', 'has_archive');
    }

    public function testPermalinkEPMaskArg()
    {
        $this->argTest('permalinkEPMask', [2], 2, 'permalink_epmask');
    }

    public function testQueryVarArg()
    {
        $this->argTest('queryVar', [], 'project', 'query_var');
        $this->argTest('queryVar', ['something'], 'something', 'query_var');
    }

    public function testRegisterMetaBoxCB()
    {
    	function sampleCallback(){}
        $this->argTest('registerMetaBoxCB', ['sampleCallback'], 'sampleCallback', 'register_meta_box_cb');
    }

    public function testRestBase()
    {
        $this->argTest('restBase', ['test'], 'test', 'rest_base');
    }

    public function testRestControllerClass()
    {
        $this->argTest('restControllerClass', [], 'WP_REST_Posts_Controller', 'rest_controller_class');
        $this->argTest('restControllerClass', ['SampleController'], 'SampleController', 'rest_controller_class');
    }

    public function testRewrite()
    {
        $this->booleanArgTest('rewrite', 'rewrite');
        $this->argTest('rewrite', [['slug' => 'test']], ['slug' => 'test'], 'rewrite');
    }

    public function testShowInRest()
    {
        $this->booleanArgTest('showInRest', 'show_in_rest');
    }

    public function testExcludeFromSearch()
    {
        $this->booleanArgTest('excludeFromSearch', 'exclude_from_search');
    }

    public function testHierarchical()
    {
        $this->booleanArgTest('hierarchical', 'hierarchical');
    }

    public function testMapMetaCap()
    {
        $this->booleanArgTest('mapMetaCap', 'map_meta_cap');
    }

    public function testMenuIcon()
    {
        $this->argTest('menuIcon', ['test'], 'test', 'menu_icon');
    }

    public function testMenuPosition()
    {
        $this->argTest('menuPosition', [10], 10, 'menu_position');
    }

    public function testRemoveSupportArg()
    {
        $post = PostType::create('project')->removeSupport('title');
        $this->assertNotContains('title', $post->supports);
        unregister_post_type('project');
    }

    public function testPublic()
    {
        $this->booleanArgTest('public', 'public');
    }

    public function testPubliclyQueryable()
    {
        $this->booleanArgTest('publiclyQueryable', 'publicly_queryable');
    }

    public function testShowInAdminBar()
    {
        $this->booleanArgTest('showInAdminBar', 'show_in_admin_bar');
    }

    public function testShowInMenu()
    {
        $this->booleanArgTest('showInMenu', 'show_in_menu');
        $this->argTest('showInMenu', ['tools.php'], 'tools.php', 'show_in_menu');
    }

    public function testShowInNavMenus()
    {
        $this->booleanArgTest('showInNavMenus', 'show_in_nav_menus');
    }

    public function testShowUI()
    {
        $this->booleanArgTest('showUI', 'show_ui');
    }

    /**
     * @param $supports
     *
     * @dataProvider supportsProvider
     */
    public function testSupports($supports, $expected)
    {
        $post = PostType::create('project')->supports($supports)->registerManually();
        if ( ! isset($GLOBALS['_wp_post_type_features']['project'])) {
            $actual_supports = [];
        } else {
            $actual_supports = array_keys($GLOBALS['_wp_post_type_features']['project']);
        }
        $this->assertSame($expected, $actual_supports);
        unregister_post_type('project');
    }

    /**
     * @dataProvider supportHelpersProvider
     */
    public function testSupportHelpers($helperMethod, $feature)
    {
        $post = PostType::create('project');

        // set this up before we add to it
        $expected = $post->supports;
        $expected[] = $feature;

        // call the helper method
        call_user_func([$post, $helperMethod]);

        $post->registerManually();

        $actual = array_keys($GLOBALS['_wp_post_type_features']['project']);
        $this->assertSame(array_unique($expected), $actual);
    }

    public function testTaxonomies()
    {
        $post = PostType::create('project')->taxonomies(['category'])->registerManually();
        $this->assertContains('category', get_object_taxonomies('project'));
    }

    public function tearDown()
    {
        unregister_post_type('project');
    }

    /*
    |--------------------------------------------------------------------------
    | Protected
    |--------------------------------------------------------------------------
    */
    protected function argTest($method, $method_parameters = [], $expected, $post_property)
    {
        // create PostType
        $post_type = PostType::create('project');

        // call the method
        call_user_func_array([$post_type, $method], $method_parameters);

        // register the PostType
        $post = $post_type->registerManually();

        // The test
        $this->assertSame($expected, $post->{$post_property});

        // cleanup
        unregister_post_type('project');
    }

    /**
     * Wrapper for @see argTest for simple boolean args
     *
     * @param $method
     * @param $post_property
     */
    protected function booleanArgTest($method, $post_property)
    {
        $this->argTest($method, [], true, $post_property);
        $this->argTest($method, [false], false, $post_property);
    }

    /*
    |--------------------------------------------------------------------------
    | Data Provider
    |--------------------------------------------------------------------------
    */
    public function supportsProvider()
    {
        return [
            'Override with bool'  => [false, []],
            'Override with array' => [['title'], ['title']],
            'Append'              => [
                'trackbacks',
                ['title', 'editor', 'thumbnail', 'trackbacks'],
            ],
        ];
    }

    public function supportHelpersProvider()
    {
        return [
            'supportsAuthor' => ['supportsAuthor', 'author'],
            'supportsComments' => ['supportsComments', 'comments'],
            'supportsCustomFields' => ['supportsCustomFields', 'custom-fields'],
            'supportsEditor' => ['supportsEditor', 'editor'],
            'supportsExcerpt' => ['supportsExcerpt', 'excerpt'],
            'supportsFeaturedImage' => ['supportsFeaturedImage', 'thumbnail'],
            'supportsPageAttributes' => ['supportsPageAttributes', 'page-attributes'],
            'supportsPostFormats' => ['supportsPostFormats', 'post-formats'],
            'supportsRevisions' => ['supportsRevisions', 'revisions'],
            'supportsThumbnail' => ['supportsThumbnail', 'thumbnail'],
            'supportsTitle' => ['supportsTitle', 'title'],
            'supportsTrackbacks' => ['supportsTrackbacks', 'trackbacks'],
        ];
    }
}