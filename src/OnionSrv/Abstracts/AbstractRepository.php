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

abstract class AbstractRepository
{
	protected $_sEntity = 'OnionSrv\Abstracts\Entity';
	
	protected $_oConnection = null;
	
	
	/**
	 * 
	 * @param array $paConf
	 */
	public function __construct (array $paConf = array())
	{
		$this->setDbConf($paConf);
	}
	
	
	/**
	 * 
	 * @param array $paConf
	 * @return object
	 */
	public function setDbConf (array $paConf)
	{
		if (is_array($paConf) && count($paConf) > 0)
		{
		    $lsDriverName = (isset($paConf['driver']) ? $paConf['driver'] : 'PDOMySql');
		    
		    if ($lsDriverName != null && class_exists("\\OnionSrv\\Driver\\{$lsDriverName}", true))
		    {
		        $lsDriver = "\\OnionSrv\\Driver\\{$lsDriverName}";
		        $this->_oConnection = new $lsDriver($paConf);
		    }
		    else 
		    {
		        throw new \Exception("Database driver '{$lsDriverName}' do not exists");
		    }
		}
		
		return $this;
	}
	
	
	/**
	 *
	 * @return bool
	 */
	public function hasError ()
	{
		return $this->_oConnection->hasError();
	}
	
	
	/**
	 * 
	 * @return string|null
	 */
	public function getErrorMsg ()
	{
		return $this->_oConnection->getErrorMsg();
	}
	
	
	/**
	 *
	 * @return string|null
	 */
	public function getErrorCode ()
	{
		return $this->_oConnection->getErrorCode();
	}
	
	
	/**
	 *
	 * @return string|null
	 */
	public function getError ()
	{
		return $this->_oConnection->getError();
	}
	
	
	/**
	 * 
	 * @param string $psClass
	 * @param array $paConf
	 * @return object Entity
	 */
	public function getEntity($psEntity, $paConf = null)
	{
	   if ($paConf == null)
	   {
	       $paConf = $this->_oConnection->get('_aConf');
	   }
	   
	   $loEntity = new $psEntity($paConf);
	   
	   return $loEntity;
	}
	
	
	/**
	 * 
	 * @param object $poEntity
	 * @return object Entity
	 */
	public function persist ($poEntity)
	{
	    $poEntity->setDbConf($this->_oConnection->get('_aConf'));
	    
	    return $poEntity;
	}
	
	
	/**
	 *
	 * @param string $psString        	
	 * @return string
	 */
	public function escapeString ($psString)
	{
	    return $this->_oConnection->escapeString ($psString);
	}
	
	
	/**
	 *
	 * @param string $psEntity        	
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
	public function select ($psEntity, $psWhere = null, $pmFields = '*', $psJoin = '', $pnOffset = 0, $pnPage = 0, $pmOrdField = null, $psOrder = null, $pmGroup = null)
	{
	    return $this->_oConnection->createQuerySelect($psEntity, $psWhere, $pmFields, $psJoin, $pnOffset, $pnPage, $pmOrdField, $psOrder, $pmGroup);
	}
	
	
	/**
	 *
	 * @param string $psQuery        	
	 * @param string $psEntity        	
	 * @return array|array of object|bool
	 */
	public function queryExec ($psQuery, $psEntity = "", array $paConf = null)
	{
	    $this->_oConnection->setQuery($psQuery);
	    
		return $this->_oConnection->queryExec($psEntity, $paConf);
	}
	
	
	/**
	 * 
	 * @param string $psQuery
	 * @param array $paConf
	 * @return boolean
	 */
	public function execute ($psQuery, array $paConf = null)
	{
		return $this->queryExec($psQuery, '', $paConf);
	}
	
	
	/**
	 * 
	 * @param string $psQuery
	 * @param array $paConf
	 * @return boolean
	 */
	public function update ($psQuery, array $paConf = null)
	{
		return $this->execute($psQuery, $paConf);
	}

	
	/**
	 * 
	 * @param string $psQuery
	 * @param array $paConf
	 * @return boolean
	 */
	public function insert ($psQuery, array $paConf = null)
	{
		return $this->execute($psQuery, $paConf);
	}

	
	/**
	 *
	 * @param string $psQuery
	 * @param array $paConf
	 * @return boolean
	 */
	public function create ($psQuery, array $paConf = null)
	{
		return $this->execute($psQuery, $paConf);
	}
	
	
	/**
	 *
	 * @param string $psEntity
	 * @param array $paConf
	 * @return boolean|array|null
	 */
	public function descEntity ($psEntity, array $paConf = null)
	{
        return $this->_oConnection->descEntity ($psEntity, $paConf);	    
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