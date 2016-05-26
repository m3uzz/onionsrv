<?php
/**
 * This file is part of Onion Service
 *
 * Copyright (c) 2014-2016, Humberto Lourenço <betto@m3uzz.com>.
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions
 * are met:
 *
 *   * Redistributions of source code must retain the above copyright
 *     notice, this list of conditions and the following disclaimer.
 *
 *   * Redistributions in binary form must reproduce the above copyright
 *     notice, this list of conditions and the following disclaimer in
 *     the documentation and/or other materials provided with the
 *     distribution.
 *
 *   * Neither the name of Humberto Lourenço nor the names of his
 *     contributors may be used to endorse or promote products derived
 *     from this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS
 * FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE
 * COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT,
 * INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING,
 * BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT
 * LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN
 * ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 *
 * @category   PHP
 * @package    OnionSrv
 * @author     Humberto Lourenço <betto@m3uzz.com>
 * @copyright  2014-2016 Humberto Lourenço <betto@m3uzz.com>
 * @license    http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @link       http://github.com/m3uzz/onionsrv
 */

namespace OnionSrv\Abstracts;
use OnionSrv\Debug;

abstract class AbstractRepository extends MysqlPDO
{
	protected $_sEntity = null;

	
	/**
	 * 
	 * @param string $psClass
	 * @param array $paConfDb
	 * @return object Entity
	 */
	public function getEntity($psEntity, $paConfDb = null)
	{
	   if ($paConfDb == null)
	   {
	       $paConfDb = $this->_aConfDb;
	   }
	   
	   $loEntity = new $psEntity($paConfDb);
	   
	   return $loEntity;
	}
	
	
	/**
	 * 
	 * @param object $poEntity
	 * @return object Entity
	 */
	public function persiste ($poEntity)
	{
	    $poEntity->setDbConf($this->_aConfDb);
	    
	    return $poEntity;
	}
	
	
	/**
	 *
	 * @param string $psTable        	
	 * @param string $psWhere        	
	 * @param mixed $pmFields 
	 * @param string $psJoin       	
	 * @param int $pnOffset        	
	 * @param int $pnPage        	
	 * @param mixed $pmOrdField        	
	 * @param string $psOrder
	 * @param mixed $pmGroup       	
	 * @return string
	 */
	public function select ($psTable, $psWhere = null, $pmFields = '*', $psJoin = '', $pnOffset = 0, $pnPage = 0, $pmOrdField = null, $psOrder = null, $pmGroup = null)
	{
	    return $this->selectQuery($psTable, $psWhere, $pmFields, $psJoin, $pnOffset, $pnPage, $pmOrdField, $psOrder, $pmGroup);
	}
	
	
	/**
	 * 
	 * @param string $psSql
	 * @param array $paConfDb
	 * @return boolean
	 */
	public function update ($psSql, array $paConfDb = null)
	{
		return $this->execute($psSql, $paConfDb);
	}

	
	/**
	 * 
	 * @param string $psSql
	 * @param array $paConfDb
	 * @return boolean
	 */
	public function insert ($psSql, array $paConfDb = null)
	{
		return $this->execute($psSql, $paConfDb);
	}

	
	/**
	 *
	 * @param string $psSql
	 * @param array $paConfDb
	 * @return boolean
	 */
	public function create ($psSql, array $paConfDb = null)
	{
		return $this->execute($psSql, $paConfDb);
	}
	
	
	/**
	 *
	 * @param string $psSql
	 * @param array $paConfDb
	 * @return boolean|array|null
	 */
	public function descTable ($psSql, array $paConfDb = null)
	{
		Debug::debug($psSql);
	
		if ($this->connect($paConfDb))
		{
			$loStantement = $this->_oDb->prepare($psSql);
            $laResultSet = null;
            
			if ($loStantement->execute())
			{
			    $laResultSet = $loStantement->fetchAll();
				Debug::debug("SQL execute OK");
			}
			else
			{
				$this->_aError = $loStantement->errorInfo();
				Debug::debug($this->_aError);
			}
		
			$this->_oDb = null;
			
			return $laResultSet;
		}
			
		return false;
	}
	
	
	/**
	 * 
	 * @param string $psEntity
	 * @param string|int $pmId
	 */
	public function find ($psEntity, $pmId)
	{
	    $loEntity = $this->getEntity($psEntity);
	    
	    if ($loEntity->find($pmId))
	    {
	       return $loEntity;
	    }
	    
	    return null;
	}
	
	
	/**
	 * 
	 * @param string $psEntity
	 * @param string $psWhere
	 */
	public function findOneBy ($psEntity, $psWhere)
	{
	    $loEntity = $this->getEntity($psEntity);
	    
	    if ($loEntity->findOneBy($psWhere))
	    {
	       return $loEntity;
	    }
	    
	    return null;
	}
	
	
	/**
	 * 
	 * @param string $psEntity
	 * @param string $psWhere
	 * @param int $pnOffset        	
	 * @param int $pnPage        	
	 * @param mixed $pmOrdField        	
	 * @param string $psOrder
	 * @param mixed $pmGroup       	
	 * @return string
	 */
	public function findBy ($psEntity, $psWhere = null, $pnOffset = 0, $pnPage = 0, $pmOrdField = null, $psOrder = null, $pmGroup = null)
	{
	    $loEntity = $this->getEntity($psEntity);
   
	    return $loEntity->findBy($psWhere, $pnOffset, $pnPage, $pmOrdField, $psOrder, $pmGroup);
	}	
	
}