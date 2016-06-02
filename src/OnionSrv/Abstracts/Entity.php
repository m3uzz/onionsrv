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

abstract class Entity
{
    protected $_sEntity;
    
    protected $_sClass;
    
    protected $_oConnection = null;
    
    protected $_sPk;
    
    protected $_aFieldType = array();
    
    protected $_aChanged = array();

    
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
	 * @param string $psProperty
	 * @param string $pmValue
	 */
	public function __set ($psProperty, $pmValue)
	{
		if (property_exists($this, $psProperty))
		{
			$lsMethod = 'set' . ucfirst($psProperty);
				
			if (method_exists($this, $lsMethod))
			{
				$this->$lsMethod($pmValue);
			}
			else
			{
				$this->$psProperty = $pmValue;
				$this->_aChanged[$psProperty] = true;
			}
		}
	}
	
	
	/**
	 * 
	 * @param string $psVar
	 * @param string $pmValue
	 * @return OnionSrv\Abstracts\Entity
	 */
	public function set ($psVar, $pmValue)
	{
		if (property_exists($this, $psVar))
		{
			$this->$psVar = $pmValue;
			$this->_aChanged[$psVar] = true;
		}
		
		return $this;
	}

	
	/**
	 * 
	 * @param string $psProperty
	 * @return mixed
	 */
	public function __get ($psProperty)
	{
		return $this->get($psProperty);
	}
	
	
	/**
	 * 
	 * @param string $psProperty
	 * @return mixed
	 */
	public function get ($psProperty)
	{
		if (property_exists($this, $psProperty))
		{
			$lsMethod = 'get' . ucfirst($psProperty);
			
			if (method_exists($this, $lsMethod))
			{
				return $this->$lsMethod();	
			}
			else
			{
				return $this->$psProperty;
			}
		}
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
		    $lsDriverName = (isset($paDb['driver']) ? $paDb['driver'] : 'PDOMySql');
		    
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
	 * @param array|object $pmData
	 * @return OnionSrv\Abstracts\Entity
	 */
	public function populate ($pmData)
	{
	    $laData = null;
	    
	    if (is_object($pmData))
	    {
	        $laData = $pmData->getArrayCopy();
	    }
	    elseif (is_array($pmData))
	    {
	        $laData = $pmData;
	    }
	    
	    if (is_array($laData))
	    {
	        foreach ($laData as $lsField => $lmValue)
	        {
	            if (property_exists($this, $lsField))
	            {
	                $this->$lsField = $lmValue;
	            }
	        }
	    }
	    
	    return $this;
	}
	
	
	/**
	 * 
	 * @return array
	 */
	public function getArrayCopy ()
	{
		$laAllProperties = get_object_vars($this);
		
		if (is_array($laAllProperties))
		{
			foreach ($laAllProperties as $lsKey => $lmValue)
			{
				if (substr($lsKey, 0, 1) !== '_')
				{
					$laProperties[$lsKey] = $this->get($lsKey);
				}
			}
		}
		
		return $laProperties;
	}
	
	
	/**
	 * Return the whole object and its children as an array
	 *
	 * @return array
	 */
	public function toArray ()
	{
		$laProperties = get_object_vars($this);
	
		if (is_array($laProperties))
		{
			foreach ($laProperties as $lsVar => $lmValue)
			{
				if (substr($lsVar, 0, 1) !== '_')
				{
					if (is_array($lmValue) && count($lmValue) != 0)
					{
						foreach ($lmValue as $lsId => $lmObj)
						{
							if (is_object($lmObj) && method_exists($lmObj, 'toArray'))
							{
								$laReturn[$lsVar][$lsId] = $lmObj->toArray();
							}
							else
							{
								$lmGet = $this->get($lsId);
								$laReturn[$lsVar][$lsId] = (!empty($lmGet) ? $lmGet : $lmObj);
							}
						}
					}
					elseif (is_object($lmValue) && method_exists($lmValue, 'toArray'))
					{
						$laReturn[$lsVar] = $lmValue->toArray();
					}
					else
					{
						$lmGet = $this->get($lsVar);						
						$laReturn[$lsVar] = (!empty($lmGet) ? $lmGet : $lmValue);
					}
				}
			}
		}
	
		return $laReturn;
	}
	
	
	/**
	 * 
	 * @return OnionSrv\Abstracts\Entity
	 */
	public function getReflection ()
	{
	    $loRC = new \ReflectionClass($this);
	    $lsDoc = $loRC->getDocComment();
	    
	    if (empty($this->_sClass))
	    {
            $this->_sClass = $loRC->getName();
	    }
	    
        if (empty($this->_sEntity) && preg_match('/\*[\s]*@table[\s]*=[\s]*(.*?)[\s]*\n/i', $lsDoc, $laEntityName))
        {
            $this->_sEntity = $laEntityName[1];
        }

        $laEntity = $this->getArrayCopy();
        	    
		if (is_array($laEntity))
	    {
	        foreach ($laEntity as $lsField => $lmValue)
	        {
	            $loProperty = $loRC->getProperty($lsField);
	            $lsDoc = $loProperty->getDocComment();
	            
	        	if (preg_match('/\*[\s]*@var[\s]*(.*?)[\s]*(PK)[\s]*\n/i', $lsDoc, $laResult))
	            {
	                if (empty($this->_sPk))
	                {
	                   $this->_sPk = $lsField;
	                }
	                
	                $this->_aFieldType[$lsField] = $laResult[1];
	            }
	            elseif (preg_match('/\*[\s]*@var[\s]*(.*?)[\s]*\n/i', $lsDoc, $laResult))
	            {
	                $this->_aFieldType[$lsField] = $laResult[1];
	            }
	            elseif(preg_match('/^num.*$/', $lsField))
	            {
	                $this->_aFieldType[$lsField] = 'num';
	            }
	        	elseif (is_int($lmValue) || is_float($lmValue))
	            {
	        	    $this->_aFieldType[$lsField] = 'num';
	            }
	        }
	    }
	    
	    return $this;
	}
	
	
	/**
	 * 
	 * @param mixed $pnId
	 * return boolean
	 */
	public function find ($pnId)
	{
	    return $this->_oConnection->find($this, $pnId);
	}
	
	
	/**
	 * 
	 * @param string $psWhere
	 * return boolean
	 */
	public function findOneBy ($psWhere = null)
	{
	    return $this->_oConnection->findOneBy($this, $psWhere);
	}	
	
	
	/**
	 * 
	 * @param string $psWhere
	 * @param int $pnOffset        	
	 * @param int $pnPage        	
	 * @param mixed $pmOrdField        	
	 * @param string $psOrder
	 * @param mixed $pmGroup       	
	 * @return array|array of object|bool
	 */
    public function findBy ($psWhere = null, $pnOffset = 0, $pnPage = 0, $pmOrdField = null, $psOrder = null, $pmGroup = null)
	{
        return $this->_oConnection->findBy($this, $psWhere, $pnOffset, $pnPage, $pmOrdField, $psOrder, $pmGroup);
	}	
	
	
	/**
	 * 
	 * @param boolean $pbIgnore
	 * @return boolean
	 */
	public function flush ($pbIgnore = true)
	{
	    return $this->_oConnection->flush($this, $pbIgnore);
	}
	
	
	/**
	 * 
	 * @param string $psWhere
	 * @param number $pnLimit
	 * @return boolean
	 */
	public function update ($psWhere = null, $pnLimit = 1)
	{
	    Debug::debug($this);
	    if ($this->_oConnection->createQueryUpdate($this, $psWhere, $pnLimit))
	    {
	        if ($this->_oConnection->execute())
	        {
	            $this->_aChanged = array();
	            
	            return true;
	        }
	    }
	    
	    return false;
	}
	
	
	/**
	 * 
	 * @param string $psWhere
	 * @param number $pnLimit
	 * @return boolean
	 */
	public function delete ($psWhere = null, $pnLimit = 1)
	{
	    if ($this->_oConnection->createQueryDelete($this, $psWhere, $pnLimit))
	    {
	        if ($this->_oConnection->execute())
	        {
	            $this->ResetObject();
	            return true;
	        }
	    }
	    
	    return false;
	}
	
	
	/**
	 * 
	 */
    public function ResetObject()
    {
        foreach ($this as $lsKey => $lmValue)
        {
            unset($this->$lsKey);
        }
    }
}