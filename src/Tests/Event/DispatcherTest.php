<?php
/**
 * AnimeDb package
 *
 * @package   AnimeDb
 * @author    Peter Gribanov <info@peter-gribanov.ru>
 * @copyright Copyright (c) 2011, Peter Gribanov
 * @license   http://opensource.org/licenses/GPL-3.0 GPL v3
 */

namespace AnimeDb\Bundle\AnimeDbBundle\Tests\Event;

use AnimeDb\Bundle\AnimeDbBundle\Event\Dispatcher;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Test Dispatcher
 *
 * @package AnimeDb\Bundle\AnimeDbBundle\Tests\Event
 * @author  Peter Gribanov <info@peter-gribanov.ru>
 */
class DispatcherTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Filesystem
     */
    protected $fs;

    /**
     * @var string
     */
    protected $root_dir;

    protected function setUp()
    {
        $this->fs = new Filesystem();
        $this->root_dir = sys_get_temp_dir().'/tests/';
    }

    protected function tearDown()
    {
        $this->fs->remove($this->root_dir);
    }

    public function testEmptyDriver()
    {
        $dispatcher = new Dispatcher($this->root_dir);
        $dispatcher->shippingDeferredEvents();
    }

    public function testDispatch()
    {
        $event1 = new Event();
        $event2 = new Event();

        /* @var $driver \PHPUnit_Framework_MockObject_MockObject|EventDispatcherInterface */
        $driver = $this->getMock('\Symfony\Component\EventDispatcher\EventDispatcherInterface');
        $driver
            ->expects($this->at(0))
            ->method('dispatch')
            ->with('bar', $event1)
            ->will($this->returnArgument(1));
        $driver
            ->expects($this->at(1))
            ->method('dispatch')
            ->with('baz', $event2)
            ->will($this->returnArgument(1));

        $dispatcher = new Dispatcher($this->root_dir);
        $dispatcher->setDispatcherDriver($driver);
        $dispatcher->dispatch('bar', $event1);
        $dispatcher->dispatch('baz', $event2);
        $dispatcher->shippingDeferredEvents();
    }
}
