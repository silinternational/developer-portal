<?php

/**
 * This file is adapted from the Yii 1 Framework's CDbTestCase file.
 *
 * ------- Yii's CDbTestCase license: -------
 * Copyright © 2008-2019 by Yii Software LLC
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 * 
 * - Redistributions of source code must retain the above copyright notice, this
 *   list of conditions and the following disclaimer.
 * - Redistributions in binary form must reproduce the above copyright notice,
 *   this list of conditions and the following disclaimer in the documentation
 *   and/or other materials provided with the distribution.
 * - Neither the name of Yii Software LLC nor the names of its contributors may
 *   be used to endorse or promote products derived from this software without
 *   specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE
 * ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE
 * LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR
 * CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF
 * SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS
 * INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN
 * CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
 * ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 * ------------------------------------------
 */

namespace Sil\DevPortal\tests;

use CActiveRecord;
use CDbFixtureManager;
use Exception;
use Yii;

class DbTestCase extends TestCase
{
    /**
     * @var array a list of fixtures that should be loaded before each test method executes.
     * The array keys are fixture names, and the array values are either AR class names
     * or table names. If table names, they must begin with a colon character (e.g. 'Post'
     * means an AR class, while ':post' means a table name).
     * Defaults to false, meaning fixtures will not be used at all.
     */
    protected $fixtures = false;
    
    /**
     * PHP magic method.
     * This method is overridden so that named fixture data can be accessed like a normal property.
     * @param string $name the property name
     * @return mixed the property value
     * @throws Exception if unknown property is used
     */
    public function __get($name)
    {
        if (is_array($this->fixtures) && ($rows = $this->getFixtureManager()->getRows($name)) !== false)
            return $rows;
        else
            throw new Exception("Unknown property '$name' for class '" . get_class($this) . "'.");
    }
    
    /**
     * PHP magic method.
     * This method is overridden so that named fixture ActiveRecord instances can be accessed in terms of a method call.
     * @param string $name method name
     * @param string $params method parameters
     * @return mixed the property value
     * @throws \Exception if unknown method is used
     */
    public function __call($name, $params)
    {
        if (is_array($this->fixtures) && isset($params[0]) && ($record = $this->getFixtureManager()->getRecord($name, $params[0])) !== false)
            return $record;
        else
            throw new Exception("Unknown method '$name' for class '" . get_class($this) . "'.");
    }
    
    /**
     * @return CDbFixtureManager the database fixture manager
     */
    public function getFixtureManager()
    {
        return Yii::app()->getComponent('fixture');
    }
    
    /**
     * @param string $name the fixture name (the key value in {@link fixtures}).
     * @return array the named fixture data
     */
    public function getFixtureData($name)
    {
        return $this->getFixtureManager()->getRows($name);
    }
    
    /**
     * @param string $name the fixture name (the key value in {@link fixtures}).
     * @param string $alias the alias of the fixture data row
     * @return CActiveRecord the ActiveRecord instance corresponding to the specified alias in the named fixture.
     * False is returned if there is no such fixture or the record cannot be found.
     */
    public function getFixtureRecord($name, $alias)
    {
        return $this->getFixtureManager()->getRecord($name, $alias);
    }
    
    /**
     * Sets up the fixture before executing a test method.
     * If you override this method, make sure the parent implementation is invoked.
     * Otherwise, the database fixtures will not be managed properly.
     */
    protected function setUp(): void
    {
        parent::setUp();
        if (is_array($this->fixtures))
            $this->getFixtureManager()->load($this->fixtures);
    }
}