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

abstract class Entity extends MysqlPDO
{
    protected $_sTable;
    
    protected $_sClass;
    
    protected $_sPk;
    
    protected $_aFieldType = array();
    
    protected $_aChanged = array();
    
    protected $_sQueryInsert = null;
    
    protected $_sQueryUpdate = null;
    
    protected $_sQuerySelect = null;
    
    protected $_sQueryDelete = null;

    
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
	    
        if (empty($this->_sTable) && preg_match('/\*[\s]*@table[\s]*=[\s]*(.*?)[\s]*\n/i', $lsDoc, $laTable))
        {
            $this->_sTable = $laTable[1];
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
     * @param boolean $pbIgnore
     * @return boolean
     */
	public function insertQuery ($pbIgnore = false)
	{
	    $lsFields = '';
	    $lsValues = '';
	    $lsComma = '';
	    $lsIgnore = '';
	    
	    if ($pbIgnore)
	    {
	        $lsIgnore = 'IGNORE';
	    }
	    
	    $this->getReflection();
	    
	    $laEntity = $this->getArrayCopy();
	    
	    if (is_array($laEntity))
	    {
	        foreach ($laEntity as $lsField => $lmValue)
	        {
	            if ($this->_sPk != $lsField || !empty($lmValue))
	            {
	                switch ($this->_aFieldType[$lsField])
        	        {
        	            case 'num':
        	            case 'int':
        	            case 'decimal':
        	            case 'float':
        	               if (!empty($lmValue))
        	               {
        	                   $lsValues .= $lsComma . "{$lmValue}";
        	               }
        	               else 
        	               {
        	                   $lsValues .= $lsComma . "NULL";
        	               }
        	               break;
        	            default:
        	               $lsValues .= $lsComma . "'{$lmValue}'";
        	        }
        	            
        	        $lsFields .= $lsComma . "`{$lsField}`";
        	        $lsComma = ', ';
	            }	            
	        }
	    }
	    
	    if (!empty($this->_sTable))
	    {
   	        $this->_sQueryInsert = "INSERT {$lsIgnore} 
   	                                INTO `{$this->_sTable}` 
   	                                    ({$lsFields}) 
   	                                VALUES ({$lsValues})";
   	        
   	        return true;
	    }
	    
	    $this->_aError[] = "1";
	    $this->_aError[] = "There is no way to get the table name!";
	    
        return false;
	}
	
	
	/**
	 * 
	 * @param string $psWhere
	 * @param number $pnLimit
	 * @return boolean
	 */
	public function updateQuery ($psWhere = null, $pnLimit = 1)
	{
	    $lsPk = null;
	    $lsWhere = null;
	    $lsValues = '';
	    $lsComma = '';

	    $this->getReflection();
	    
	    $laEntity = $this->getArrayCopy();
	    
	    if (is_array($laEntity))
	    {
	        foreach ($laEntity as $lsField => $lmValue)
	        {
	            switch ($this->_aFieldType[$lsField])
	            {
	                case 'num':
	                case 'int':
	                case 'decimal':
	                case 'float':
	                   if (!empty($lmValue))
        	           {
        	               $lsFieldValue = "`{$lsField}` = {$lmValue}";
        	           }
        	           else 
        	           {
        	               $lsFieldValue = "`{$lsField}` = NULL";
        	           }	                    
	                   break;
	                default:
	                   $lsFieldValue = "`{$lsField}` = '{$lmValue}'";
	            }

	            if (isset($this->_aChanged[$lsField]))
	            {
	                $lsValues .= $lsComma . $lsFieldValue;
	                $lsComma = ', ';
	            }
	            
	            if ($this->_sPk == $lsField)
	            {
	                $lsPk = $lsFieldValue;
	            }
	        }
	    }
	    
		if ($psWhere != null)
	    {
	        $lsWhere = $psWhere;
	    }
	    elseif ($lsPk != null)
	    {
            $lsWhere = $lsPk;
	    }
	    else 
	    {
    	    $this->_aError[] = "2";
    	    $this->_aError[] = "There is no where clause!";
    	    
    	    return false;
	    }

        if (empty($lsValues))
        {
    	    $this->_aError[] = "0";
    	    $this->_aError[] = "There is no values changed to update!";
    	    
    	    return true;
        }
        
        if (!empty($this->_sTable))
        {
   	        $this->_sQueryUpdate = "UPDATE `{$this->_sTable}` 
   	                                SET {$lsValues} 
   	                                WHERE {$lsWhere} 
   	                                LIMIT {$pnLimit}";
   	        
   	        return true;
        }

	    $this->_aError[] = "1";
	    $this->_aError[] = "There is no way to get the table name!";
        
        return false;
	}
	
	
	/**
	 * 
	 * @param string $psWhere
	 * @param number $pnLimit
	 * @return boolean
	 */
	public function deleteQuery ($psWhere = null, $pnLimit = 1)
	{
		$lsPk = null;
	    $lsWhere = null;
	    
	    $this->getReflection();

	    if (isset($this->_aFieldType[$this->_sPk]))
	    {
            switch ($this->_aFieldType[$this->_sPk])
            {
                case 'num':
                case 'int':
                case 'decimal':
                case 'float':
                    $lsPk = "`{$this->_sPk}` = {$this->get($this->_sPk)}";
                    break;
                default:
                    $lsPk = "`{$this->_sPk}` = '{$this->get($this->_sPk)}'";
            }
	    }
	    
		if ($psWhere != null)
	    {
	        $lsWhere = $psWhere;
	    }
	    elseif ($lsPk != null)
	    {
            $lsWhere = $lsPk;
	    }
		else 
	    {
    	    $this->_aError[] = "2";
    	    $this->_aError[] = "There is no where clause!";
    	    
    	    return false;
	    }	    

        if (!empty($this->_sTable))
        {
    	    $this->_sQueryDelete = "DELETE FROM `{$this->_sTable}` 
    	                            WHERE {$lsWhere} 
    	                            LIMIT {$pnLimit}";
    	    
    	    return true;
        }

	    $this->_aError[] = "1";
	    $this->_aError[] = "There is no way to get the table name!";
	    
        return false;	    
	}
	
	
	/**
	 * 
	 * @param mixed $pnId
	 * return boolean
	 */
	public function find ($pnId)
	{
	    $lsWhere = null;
	    $this->getReflection();
	    
		if (isset($this->_aFieldType[$this->_sPk]))
	    {
            switch ($this->_aFieldType[$this->_sPk])
            {
                case 'num':
                case 'int':
                case 'decimal':
                case 'float':
                    $lsWhere = "`{$this->_sPk}` = {$pnId}";
                    break;
                default:
                    $lsWhere = "`{$this->_sPk}` = '{$pnId}'";
            }
	    }
	    
        return $this->findOneBy($lsWhere);    
	}
	
	
	/**
	 * 
	 * @param string $psWhere
	 * return boolean
	 */
	public function findOneBy ($psWhere = null)
	{
	    $this->getReflection();
   
        if (!empty($this->_sTable))
        {
            if (!empty($psWhere))
            {
                $psWhere = "AND {$psWhere}";
            }
            
   	        $this->selectQuery($this->_sTable, $psWhere, '*', '', 1);

    		if ($this->connect())
    		{
    		    Debug::debug($this->_sQuerySelect);
    		    
    			$loStantement = $this->_oDb->prepare($this->_sQuerySelect);
    			
    			$this->_oDb = null;
    			
    			if ($loStantement->execute())
    			{ 
    				$laResultSet = $loStantement->fetchAll(\PDO::FETCH_ASSOC);
   			        Debug::debug($laResultSet);
   			        
    				if (isset($laResultSet[0]))
    				{
    					$this->populate($laResultSet[0]);
    					
    					return true;
    				}
    				else 
    				{
    				    return false;
    				}
    			}
    			else
    			{
    				$this->_aError = $loStantement->errorInfo();
    				Debug::debug($this->_aError);
    			}
    		}
        }

	    $this->_aError[] = "1";
	    $this->_aError[] = "There is no way to get the table name!";
        
        return false;	    
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
	    $this->getReflection();
       
        if (!empty($this->_sTable))
        {
            if (!empty($psWhere))
            {
                $psWhere = "AND {$psWhere}";
            }
            
   	        $this->selectQuery($this->_sTable, $psWhere, '*', '', $pnOffset, $pnPage, $pmOrdField, $psOrder, $pmGroup);
   	        
   	        return $this->queryExec($this->_sQuerySelect, $this->_sClass);
        }
        
        $this->_aError[] = "1";
	    $this->_aError[] = "There is no way to get the table name!";
        
        return false;
	}	
	
	
	/**
	 * 
	 * @return boolean
	 */
	public function flush ()
	{
	    if ($this->insertQuery(true))
	    {
		    Debug::debug($this->_sQueryInsert);
	
		    if ($this->connect())
		    {
			    $loStantement = $this->_oDb->prepare($this->_sQueryInsert);
	
			    $lbReturn = $loStantement->execute();
			
			    if ($lbReturn)
			    {
			        Debug::debug("SQL insert OK");
			        
			        $lnId = $this->_oDb->lastInsertId();
                    $this->set($this->_sPk, $lnId);
			    }
			    else
			    {
				    $this->_aError = $loStantement->errorInfo();
				    Debug::debug($this->_aError);
			    }
		    }
		}

		$this->_oDb = null;
			
		return $lbReturn;
	}
	
	
	/**
	 * 
	 * @return boolean
	 */
	public function update ()
	{
	    if ($this->updateQuery())
	    {
	        if ($this->execute($this->_sQueryUpdate))
	        {
	            $this->_aChanged = array();
	            
	            return true;
	        }
	    }
	    
	    return false;
	}
	
	
	/**
	 * 
	 * @return boolean
	 */
	public function delete ()
	{
	    if ($this->deleteQuery())
	    {
	        if ($this->execute($this->_sQueryDelete))
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