<?php
ini_set("include_path", "../views/helpers/images".PATH_SEPARATOR."../../../../../views/helpers/images".PATH_SEPARATOR.ini_get("include_path"));
require_once 'PHPUnit/Framework.php';

require_once '/home/abaka/Projects/GhanaBox/ntentan/views/helpers/images/imagecache.php';

/**
 * Test class for ImageCache.
 * Generated by PHPUnit on 2010-02-12 at 21:27:55.
 */
class ImageCacheTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var ImageCache
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->object = new ImageCache;
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
    }

    /**
     * @todo Implement testResize_image().
     */
    public function testResize_image()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
          'This test has not been implemented yet.'
        );
    }

    /**
     * @todo Implement testCrop_image().
     */
    public function testCrop_image()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
          'This test has not been implemented yet.'
        );
    }

    /**
     * @todo Implement testWidth().
     */
    public function testWidth()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
          'This test has not been implemented yet.'
        );
    }

    /**
     * @todo Implement testHeight().
     */
    public function testHeight()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
          'This test has not been implemented yet.'
        );
    }

    /**
     * @todo Implement testThumbnail().
     */
    public function testThumbnail()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
          'This test has not been implemented yet.'
        );
    }
}
?>