<?php
/**
 * DoiLookup test class.
 *
 * PHP version 7
 *
 * Copyright (C) Villanova University 2018.
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License version 2,
 * as published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301  USA
 *
 * @category VuFind
 * @package  Tests
 * @author   Demian Katz <demian.katz@villanova.edu>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     https://vufind.org Main Page
 */
namespace VuFindTest\AjaxHandler;

use VuFind\AjaxHandler\DoiLookup;
use VuFind\AjaxHandler\DoiLookupFactory;
use VuFind\Config\PluginManager as ConfigManager;
use VuFind\DoiLinker\DoiLinkerInterface;
use VuFind\DoiLinker\PluginManager;
use Zend\Config\Config;

/**
 * DoiLookup test class.
 *
 * @category VuFind
 * @package  Tests
 * @author   Demian Katz <demian.katz@villanova.edu>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     https://vufind.org Main Page
 */
class DoiLookupTest extends \VuFindTest\Unit\AjaxHandlerTest
{
    /**
     * Set up configuration for a test.
     *
     * @param array $config Configuration to set.
     *
     * @return void
     */
    protected function setupConfig($config)
    {
        $config = new Config($config);
        $cm = $this->container->createMock(ConfigManager::class, ['get']);
        $cm->expects($this->once())->method('get')->with($this->equalTo('config'))
            ->will($this->returnValue($config));
        $this->container->set(ConfigManager::class, $cm);
    }

    /**
     * Create a mock plugin.
     *
     * @param mixed $value Value to return in response to DOI request.
     *
     * @return DoiLinkerInterface
     */
    protected function getMockPlugin($value)
    {
        $mockPlugin = $this->container
            ->createMock(DoiLinkerInterface::class, ['getLinks']);
        $mockPlugin->expects($this->once())->method('getLinks')
            ->with($this->equalTo(['bar']))
            ->will(
                $this->returnValue(
                    [
                        'bar' => [['link' => 'http://' . $value, 'label' => $value]]
                    ]
                )
            );
        return $mockPlugin;
    }

    /**
     * Set up a plugin manager for a test.
     *
     * @param array $plugins Plugins to insert into container.
     *
     * @return void
     */
    protected function setupPluginManager($plugins)
    {
        $pm = new PluginManager($this->container);
        foreach ($plugins as $name => $plugin) {
            $pm->setService($name, $plugin);
        }
        $this->container->set(PluginManager::class, $pm);
    }

    /**
     * After setupConfig() and setupPluginManager() have been called, run the
     * standard default test.
     *
     * @return array
     */
    protected function getHandlerResults()
    {
        $factory = new DoiLookupFactory();
        $handler = $factory($this->container, DoiLookup::class);
        $params = $this->getParamsHelper(['doi' => ['bar']]);
        return $handler->handleRequest($params);
    }

    /**
     * Test a single DOI lookup.
     *
     * @return void
     */
    public function testSingleLookup()
    {
        // Set up config manager:
        $this->setupConfig(['DOI' => ['resolver' => 'foo']]);

        // Set up plugin manager:
        $this->setupPluginManager(
            ['foo' => $this->getMockPlugin('baz')]
        );

        // Test the handler:
        $this->assertEquals(
            [['bar' => [['link' => 'http://baz', 'label' => 'baz']]]],
            $this->getHandlerResults()
        );
    }

    /**
     * Test a DOI lookup in two handlers, with "first" mode turned on by default.
     *
     * @return void
     */
    public function testFirstDefaultLookup()
    {
        // Set up config manager:
        $this->setupConfig(['DOI' => ['resolver' => 'foo,foo2']]);

        // Set up plugin manager:
        $this->setupPluginManager(
            [
                'foo' => $this->getMockPlugin('baz'),
                'foo2' => $this->getMockPlugin('baz2')
            ]
        );

        // Test the handler:
        $this->assertEquals(
            [['bar' => [['link' => 'http://baz', 'label' => 'baz']]]],
            $this->getHandlerResults()
        );
    }

    /**
     * Test a DOI lookup in two handlers, with "first" mode turned on explicitly.
     *
     * @return void
     */
    public function testFirstExplicitLookup()
    {
        // Set up config manager:
        $this->setupConfig(
            ['DOI' => ['resolver' => 'foo,foo2', 'multi_resolver_mode' => 'first']]
        );

        // Set up plugin manager:
        $this->setupPluginManager(
            [
                'foo' => $this->getMockPlugin('baz'),
                'foo2' => $this->getMockPlugin('baz2')
            ]
        );

        // Test the handler:
        $this->assertEquals(
            [['bar' => [['link' => 'http://baz', 'label' => 'baz']]]],
            $this->getHandlerResults()
        );
    }

    /**
     * Test a DOI lookup in two handlers, with "merge" mode turned on.
     *
     * @return void
     */
    public function testMergeLookup()
    {
        // Set up config manager:
        $this->setupConfig(
            ['DOI' => ['resolver' => 'foo,foo2', 'multi_resolver_mode' => 'merge']]
        );

        // Set up plugin manager:
        $this->setupPluginManager(
            [
                'foo' => $this->getMockPlugin('baz'),
                'foo2' => $this->getMockPlugin('baz2')
            ]
        );
        // Test the handler:
        $this->assertEquals(
            [
                [
                    'bar' => [
                        ['link' => 'http://baz', 'label' => 'baz'],
                        ['link' => 'http://baz2', 'label' => 'baz2'],
                    ]
                ]
            ],
            $this->getHandlerResults()
        );
    }
}
