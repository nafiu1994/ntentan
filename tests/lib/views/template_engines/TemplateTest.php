<?php

namespace ntentan\views\template_engines;

require_once 'PHPUnit/Framework.php';

require_once dirname(__FILE__) . '/../../../../lib/views/template_engines/Template.php';

/**
 * Test class for Template.
 * Generated by PHPUnit on 2010-12-17 at 09:02:31.
 */
class TemplateTest extends \PHPUnit_Framework_TestCase {

    /**
     * @var Template
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp() {
        $this->object = new Template;
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown() {

    }

    /**
     * @todo Implement testOut().
     */
    public function testOut() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

}

?>
