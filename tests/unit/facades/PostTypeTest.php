<?php
namespace WPDev\Tests\Unit;

use InvalidArgumentException;
use WP_Mock;
use WP_Mock\Tools\TestCase;
use WPDev\Facades\PostType;

class PostTypeTest extends TestCase {
    /** @var  PostType */
    protected $postType;
    protected $postTypeName;

    /*
    |--------------------------------------------------------------------------
    | Setup and Teardown
    |--------------------------------------------------------------------------
    */

    /**
     * This will run before each test
     */
    public function setUp()
    {
        parent::setUp();
        $this->postTypeName = 'project';
        $this->postType = PostType::create($this->postTypeName);
    }

    /**
     * This will run after each test
     */
    public function tearDown()
    {
        parent::tearDown();
        $this->postType = null;
    }

    /*
    |--------------------------------------------------------------------------
    | Tests
    |--------------------------------------------------------------------------
    */

    public function testCreate()
    {
        $this->assertInstanceOf(PostType::class, PostType::create('project'));
    }

    public function testDeregister()
    {
        WP_Mock::passthruFunction('unregister_post_type');
        $this->assertSame($this->postTypeName, $this->postType->deregister());
    }

    public function testMenuIcon()
    {
        $this->_testSetterWithString('menuIcon', 'menu_icon');
    }

    public function testMenuPosition()
    {
        $this->_testSetterWithIntegers('menuPosition', 'menu_position');
    }

    public function testPermalinkEpMask()
    {
        $this->postType->permalinkEPMask(1);
        $this->assertSame(1, $this->postType->overrideArgs['permalink_epmask']);

        $this->postType->permalinkEPMask(50);
        $this->assertSame(50, $this->postType->overrideArgs['permalink_epmask']);
    }

    public function testQueryVar()
    {
        $this->_testSetterWithBoolean('queryVar', 'query_var');
        $this->_testSetterWithString('queryVar', 'query_var');
    }

    public function testRegisterManually()
    {
        WP_Mock::passthruFunction('register_post_type');
        $this->assertSame($this->postTypeName, $this->postType->registerManually());
    }

    public function testRegisterAddsActivatePluginAction()
    {
        WP_Mock::expectActionAdded('activate_plugin', [$this->postType, 'registerManually']);

        $this->postType->register();

        $this->assertHooksAdded();
    }

    public function testRegisterAddsDeactivatePluginAction()
    {
        WP_Mock::expectActionAdded('deactivate_plugin', [$this->postType, 'deregister']);

        $this->postType->register();

        $this->assertHooksAdded();
    }

    public function testRegisterAddsInitAction()
    {
        WP_Mock::expectActionAdded('init', [$this->postType, 'registerManually']);

        $this->postType->register();

        $this->assertHooksAdded();
    }

    public function testRegisterMetaBoxCB()
    {
        WP_Mock::userFunction('sampleCallback');
        $this->postType->registerMetaBoxCB('sampleCallback');
        $this->assertSame('sampleCallback', $this->postType->overrideArgs['register_meta_box_cb']);
    }

    public function testRemoveSupport()
    {
        $this->postType->removeSupport('title');
        $this->assertSame(['editor', 'thumbnail'], $this->postType->supports);

        $this->postType->removeSupport('nonexistent');
        $this->assertSame(['editor', 'thumbnail'], $this->postType->supports);
    }

    public function testRestBase()
    {
        $this->_testSetterWithString('restBase', 'rest_base');
    }

    public function testRestControllerClass()
    {
        $this->_testSetterWithString('restControllerClass', 'rest_controller_class');
    }

    public function testRewrite()
    {
        $this->_testSetterWithAny('rewrite', 'rewrite');
    }

    /**
     * Tests the post type name validator
     *
     * @param $name PostType name
     *
     * @dataProvider validNameProvider
     */
    public function testSingularNameValidatesCorrectly($name)
    {
        $this->expectException(InvalidArgumentException::class);
        PostType::create($name);
    }

    /**
     * @param $name PostType name
     * @param $expected_singular_name
     *
     * @dataProvider nameProvider
     */
    public function testSingularNameGeneratesCorrectly($name, $expected_singular_name)
    {
        $postType = PostType::create($name);
        $this->assertEquals($expected_singular_name, $postType->singularName);
    }

    /**
     * @param $name PostType name
     * @param $expected_singular_name
     * @param $expected_plural_name
     *
     * @dataProvider nameProvider
     */
    public function testPluralNameGeneratesCorrectly($name, $expected_singular_name, $expected_plural_name)
    {
        $postType = PostType::create($name);
        $this->assertEquals($expected_plural_name, $postType->pluralName);
    }

    /**
     * While most other methods use this internally let's not assume
     * it will be like that in the future. Let's write tests for each method to
     * know 100% that it's working as intended.
     */
    public function testSetArg()
    {
        /**
         * 'supports' works a little differently and will be tested in
         * We'll test this in @see testSupports
         */
        $this->postType->setArg('something', 'some val');
        $this->assertSame('some val', $this->postType->overrideArgs['something']);
    }

    public function testSupports()
    {
        $post = PostType::create('project');

        // boolean overwrites
        $post->supports(false);
        $this->assertFalse($post->supports);

        // array overwrites
        $post->supports(['feature1','feature2','feature3']);
        $this->assertSame(['feature1','feature2','feature3'], $post->supports);

        // anything else gets appended
        $post->supports('another');
        $this->assertSame(['feature1','feature2','feature3','another'], $post->supports);

        // one more time, array overwrites
        $post->supports(['test']);
        $this->assertSame(['test'], $post->supports);
    }

    public function testVariousSupportSetterWrappers()
    {
        $this->postType->supports([]);
        $this->_testSupportSetter('supportsAuthor', 'author');
        $this->_testSupportSetter('supportsComments', 'comments');
        $this->_testSupportSetter('supportsCustomFields', 'custom-fields');
        $this->_testSupportSetter('supportsEditor', 'editor');
        $this->_testSupportSetter('supportsExcerpt', 'excerpt');
        $this->_testSupportSetter('supportsFeaturedImage', 'thumbnail');
        $this->_testSupportSetter('supportsPageAttributes', 'page-attributes');
        $this->_testSupportSetter('supportsPostFormats', 'post-formats');
        $this->_testSupportSetter('supportsRevisions', 'revisions');
        $this->_testSupportSetter('supportsThumbnail', 'thumbnail');
        $this->_testSupportSetter('supportsTitle', 'title');
        $this->_testSupportSetter('supportsTrackbacks', 'trackbacks');
    }

    public function testSetPluralName()
    {
        $this->postType->setPluralName('Stories');
        $this->assertSame('Stories', $this->postType->pluralName);
    }

    public function testSetSingularName()
    {
        $this->postType->setSingularName('Story');
        $this->assertSame('Story', $this->postType->singularName);
    }

    public function testBooleanSetters()
    {
        $this->_testSetterWithBoolean('canExport', 'can_export');
        $this->_testSetterWithBoolean('deleteWithUser', 'delete_with_user');
        $this->_testSetterWithBoolean('showInRest', 'show_in_rest');
        $this->_testSetterWithBoolean('excludeFromSearch', 'exclude_from_search');
        $this->_testSetterWithBoolean('hierarchical', 'hierarchical');
        $this->_testSetterWithBoolean('mapMetaCap', 'map_meta_cap');
        $this->_testSetterWithBoolean('isPublic', 'public');
        $this->_testSetterWithBoolean('publiclyQueryable', 'publicly_queryable');
        $this->_testSetterWithBoolean('showInAdminBar', 'show_in_admin_bar');
        $this->_testSetterWithBoolean('showInMenu', 'show_in_menu');
        $this->_testSetterWithBoolean('showInNavMenus', 'show_in_nav_menus');
        $this->_testSetterWithBoolean('showUI', 'show_ui');
    }

    public function testHasArchive()
    {
        $this->postType->hasArchive();
        $this->assertTrue($this->postType->overrideArgs['has_archive']);

        $this->postType->hasArchive(false);
        $this->assertFalse($this->postType->overrideArgs['has_archive']);

        $this->postType->hasArchive('something');
        $this->assertSame('something', $this->postType->overrideArgs['has_archive']);
    }

    public function testTaxonomies()
    {
        $this->_testSetterWithArray('taxonomies', 'taxonomies');
    }

    /*
    |--------------------------------------------------------------------------
    | Data Providers
    |--------------------------------------------------------------------------
    */

    public function nameProvider()
    {
        // 'test name' => ['PostType name', 'Expected singular', 'Expected plural']
        return [
            // capitalizes
            'project'           => ['project', 'Project', 'Projects'],
            //turns underscores into spaces and capitalizes
            'dummy_name'        => ['dummy_name', 'Dummy Name', 'Dummy Names'],
            //turns underscores into spaces and capitalizes
            'dummy_sample_name' => ['dummy_sample_name', 'Dummy Sample Name', 'Dummy Sample Names'],
            // capitalizes hyphenated words
            'dummy-name'        => ['dummy-name', 'Dummy-Name', 'Dummy-Names'],
            // capitalizes hyphenated words
            'dummy-sample-name' => ['dummy-sample-name', 'Dummy-Sample-Name', 'Dummy-Sample-Names'],
            // handles both spaces and hyphenated fine
            'dummy_sample-name' => ['dummy_sample-name', 'Dummy Sample-Name', 'Dummy Sample-Names'],
            // doesn't mess with last hyphen, edge case
            'dummy-sample-'     => ['dummy-sample-', 'Dummy-Sample-', 'Dummy-Sample-s'],
            // trims spaces
            '_dummy_sample_'    => ['_dummy_sample_', 'Dummy Sample', 'Dummy Samples'],
        ];
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

    /*
    |--------------------------------------------------------------------------
    | Protected
    |--------------------------------------------------------------------------
    */
    protected function _testSetterWithAny($method, $key)
    {
        $this->_testSetterWithArray($method, $key);
        $this->_testSetterWithBoolean($method, $key);
        $this->_testSetterWithIntegers($method, $key);
        $this->_testSetterWithString($method, $key);
    }

    protected function _testSetterWithArray($method, $key)
    {
        $this->postType->$method([1,2,3]);
        $this->assertSame([1,2,3], $this->postType->overrideArgs[$key]);
    }

    protected function _testSetterWithBoolean($method, $key)
    {
        $this->postType->$method();
        $this->assertTrue($this->postType->overrideArgs[$key]);

        $this->postType->$method(false);
        $this->assertFalse($this->postType->overrideArgs[$key]);
    }

    protected function _testSetterWithIntegers($method, $key)
    {
        $this->postType->$method(999);
        $this->assertSame(999, $this->postType->overrideArgs[$key]);
    }

    protected function _testSetterWithString($method, $key)
    {
        $this->postType->$method('test');
        $this->assertSame('test', $this->postType->overrideArgs[$key]);
    }

    protected function _testSupportSetter($method, $feature)
    {
        $this->postType->$method();
        $this->assertContains($feature, $this->postType->supports);

        // test that we can remove by passing false
        $this->postType->$method(false);
        $this->assertNotContains($feature, $this->postType->supports);
    }
}