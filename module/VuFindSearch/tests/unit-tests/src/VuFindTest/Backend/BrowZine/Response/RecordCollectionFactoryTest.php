<?php

/**
 * Unit tests for BrowZine record collection factory.
 *
 * PHP version 7
 *
 * Copyright (C) Villanova University 2017.
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
 * @package  Search
 * @author   Demian Katz <demian.katz@villanova.edu>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     https://vufind.org
 */
namespace VuFindTest\Backend\BrowZine\Response;

use PHPUnit\Framework\TestCase;
use VuFindSearch\Backend\BrowZine\Response\RecordCollectionFactory;

/**
 * Unit tests for BrowZine record collection factory.
 *
 * @category VuFind
 * @package  Search
 * @author   Demian Katz <demian.katz@villanova.edu>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     https://vufind.org
 */
class RecordCollectionFactoryTest extends TestCase
{
    /**
     * Test that the factory creates a collection.
     *
     * @return void
     */
    public function testFactory()
    {
        $resp = ['data' => [['id' => 1], ['id' => 2], ['id' => 3]]];
        $fact = new RecordCollectionFactory();
        $coll = $fact->factory($resp);
        $this->assertEquals(3, count($coll));
    }

    /**
     * Test invalid input.
     *
     * @return void
     *
     */
    public function testInvalidInput()
    {
        $this->setExpectedException(\VuFindSearch\Exception\InvalidArgumentException::class, 'Unexpected type of value: Expected array, got string');

        $fact = new RecordCollectionFactory();
        $coll = $fact->factory('garbage');
    }
}
