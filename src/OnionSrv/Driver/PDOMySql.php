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
namespace OnionSrv\Driver;
use OnionSrv\Debug;
use OnionSrv\Abstracts\AbstractDriver;

class PDOMySql extends AbstractDriver
{
    protected $_sQuery = null;
    
    
	/**
	 * 
	 * @param array $paConf
	 */
	public function __construct (array $paConf = array())
	{
		$this->setConf($paConf);
	}

	
	/**
	 * 
	 * @param string $psQuery
	 * @return OnionSrv\Abstracts\PDOMySql
	 */
	public function setQuery ($psQuery)
	{
	    $this->_sQuery = $psQuery;
	    
	    return $this;
	}
	
	
	/**
	 * 
	 * @param array $paConf
	 * @return OnionSrv\Abstracts\PDOMySql
	 */
	public function setConf (array $paConf)
	{
		if (is_array($paConf) && count($paConf) > 0)
		{
			$this->_aConf['driver'] = (isset($paConf['driver']) ? $paConf['driver'] : 'PDOMySql');		    
			$this->_aConf['hostname'] = (isset($paConf['hostname']) ? $paConf['hostname'] : null);
			$this->_aConf['username'] = (isset($paConf['username']) ? $paConf['username'] : null);
			$this->_aConf['password'] = (isset($paConf['password']) ? $paConf['password'] : null);
			$this->_aConf['database'] = (isset($paConf['database']) ? $paConf['database'] : null);
			$this->_aConf['port'] = (isset($paConf['port']) ? $paConf['port'] : '3306');
			$this->_aConf['charset'] = (isset($paConf['charset']) ? $paConf['charset'] : 'UTF8');
		}
		
		Debug::debug($this->_aConf);
		
		return $this;
	}

	
	
	/**
	 * 
	 * @param array $paConf
	 * @return bool
	 */
	public function connect (array $paConf = null)
	{
		if ($paConf == null)
		{
			$paConf = $this->_aConf;
		}
		
		$lsCon = "mysql:host={$paConf['hostname']};port={$paConf['port']};dbname={$paConf['database']};charset={$paConf['charset']}";
		$lsUser = $paConf['username'];
		$lsPass = $paConf['password'];
		
		Debug::debug(array($lsCon, $lsUser, $lsPass));
		
		try {
			$this->_oCon = new \PDO($lsCon, $lsUser, $lsPass);
			Debug::debug($this->_oCon);
			
			return true;
		}
		catch (\PDOException $e) {
			$this->setError(array($e->getCode(), $e->getMessage()));
			
			return false;
		}
	}
	
	
	/**
	 *
	 * @param string $psQuery
	 * @param array $paConf
	 * @return boolean
	 */
	public function execute ($psQuery = null, array $paConf = null)
	{
	    $this->setQuery($psQuery);
	    
	    return $this->queryExec('', $paConf);
	}	
	
	
	/**
	 *
	 * @param string $psEntity
	 * @param array $paConf
	 * @return boolean|array|null
	 */
	public function descEntity ($psEntity, array $paConf = null)
	{
		$lsQuery = "DESC {$psEntity}";
		
		$this->setQuery($lsQuery);
		
		return $this->queryExec("", $paConf);
	}
	
	
	/**
	 *
	 * @param string $psEntity        	
	 * @return array|array of object|bool
	 */
	public function queryExec ($psEntity = "", array $paConf = null)
	{
    	Debug::debug("QUERY: " . $this->_sQuery);
		
		if ($this->connect($paConf))
		{
			$loStantement = $this->_oCon->prepare($this->_sQuery);
			
			$this->close();
			
			$lbReturn = $loStantement->execute();
			
			if ($lbReturn)
			{ 
				if (!empty($psEntity))
				{
					$laResultSet = $loStantement->fetchAll(\PDO::FETCH_CLASS, $psEntity);
				}
				else 
				{
					$laResultSet = $loStantement->fetchAll();
				}
				
				Debug::debug($laResultSet);
			
				if (is_array($laResultSet) && count($laResultSet) > 0)
				{
					return $laResultSet;
				}

			    return $lbReturn;
			}
			else
			{
				$this->setError($loStantement->errorInfo());
			}
		}
				
		return false;
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
	public function createQuerySelect ($psEntity, $psWhere = null, $pmFields = '*', $psJoin = '', $pnOffset = 0, $pnPage = 0, $pmOrdField = null, $psOrder = null, $pmGroup = null)
	{
		$pnOffset = $this->escapeString($pnOffset);
		$pnPage = $this->escapeString($pnPage);
		$psOrder = strtoupper($this->escapeString($psOrder));
		
		$lsFields = '';
		$lsGroup = '';
		$lsOrder = '';
		$lsLimit = '';

		if (is_array($pmOrdField))
		{
			$lsComma = "";
		
			foreach ($pmOrdField as $lsField => $lsOrd)
			{
				if ($lsOrd != "ASC" && $lsOrd != "DESC" && $lsOrd != "RAND")
				{
					$lsOrd = 'ASC';
				}
				elseif ($lsOrd == "RAND")
				{
					$lsOrd = 'rand()';
				}
				
				$lsField = $this->escapeString($lsField);
				
				$lsOrder .= "{$lsComma}{$lsField} {$lsOrd}";
				$lsComma = ", ";
			}
				
			if (!empty($lsOrder))
			{
				$lsOrder = "ORDER BY {$lsOrder}";
			}
		}
		elseif (is_string($pmOrdField) && !empty($pmOrdField))
		{
			if ($psOrder != "ASC" && $psOrder != "DESC" && $psOrder != "RAND")
			{
				$psOrder = 'ASC';
			}
			elseif ($psOrder == "RAND")
			{
				$psOrder = 'rand()';
			}
			
			$pmOrdField = $this->escapeString($pmOrdField);
			
			$lsOrder = "ORDER BY {$pmOrdField} {$psOrder}";
		}
		
		if ($pnOffset > 0)
		{
			if ($pnPage > 0)
			{
				$lsLimit = "LIMIT {$pnPage}, {$pnOffset}";
			}
			else
			{
				$lsLimit = "LIMIT {$pnOffset}";
			}
		}
		
		if (is_array($pmGroup))
		{
			$lsComma = "";
				
			foreach ($pmGroup as $lsField)
			{
				$lsGroup .= "{$lsComma}{$lsField}'";
				$lsComma = ", ";
			}
			
			if (!empty($lsGroup))
			{
				$lsGroup = "GROUP BY {$lsGroup}";
			}
		}
		elseif(is_string($pmGroup) && !empty($pmGroup))
		{
			$lsGroup .= "GROUP BY $pmGroup";
		}
		
		if (is_array($pmFields))
		{
			$lsComma = "";
			
			foreach ($pmFields as $lsAlias => $lsField)
			{
				if (is_string($lsAlias))
				{
					$lsFields .= "{$lsComma}{$lsField} AS {$lsAlias}";
				}
				else
				{
					$lsFields .= "{$lsComma}{$lsField}";
				}
				
				$lsComma = ", ";
			}
		}
		else
		{
			$lsFields = '*';
		}
		
		if (!empty($psWhere) && !preg_match("/^AND /i", $psWhere))
		{
		    $psWhere = "AND {$psWhere}";
		}
		
   		$lsSql = "
    		SELECT {$lsFields}
    		FROM {$psEntity}
    		{$psJoin}
    		WHERE 1 {$psWhere}
    		{$lsGroup}
    		{$lsOrder}
    		{$lsLimit}";
    		
    	$this->_sQuery = preg_replace(("/WHERE 1 AND /i"), "WHERE ", $lsSql);
    	
    	return $this->_sQuery;
	}	
	
	
    /**
     * 
     * @param object @$poEntity
     * @param boolean $pbIgnore
     * @return boolean
     */
	public function createQueryInsert ($poEntity, $pbIgnore = false)
	{
	    $lsFields = '';
	    $lsValues = '';
	    $lsComma = '';
	    $lsIgnore = '';
	    
	    if ($pbIgnore)
	    {
	        $lsIgnore = 'IGNORE';
	    }
	    
	    $poEntity->getReflection();
	    
	    $laEntity = $poEntity->getArrayCopy();
	    
	    if (is_array($laEntity))
	    {
	        foreach ($laEntity as $lsField => $lmValue)
	        {
	            if ($poEntity->get('_sPk') != $lsField || !empty($lmValue))
	            {
	                $laFieldType = $poEntity->get('_aFieldType');
	                
	                switch ($laFieldType[$lsField])
        	        {
        	            case 'num':
        	            case 'int':
        	            case 'decimal':
        	            case 'float':
        	            case 'integer':
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
	    
	    $lsEntity = $poEntity->get('_sEntity');
	    
	    if (!empty($lsEntity))
	    {
   	        $this->_sQuery = "INSERT {$lsIgnore} 
   	                          INTO `{$lsEntity}` 
   	                                ({$lsFields}) 
   	                          VALUES ({$lsValues})";
   	        
   	        return true;
	    }
	    
	    $this->setError(array("1", "There is no way to get the table name!"));
	    
        return false;
	}
	
	
	/**
	 * 
	 * @param object $poEntity
	 * @param string $psWhere
	 * @param number $pnLimit
	 * @return boolean
	 */
	public function createQueryUpdate ($poEntity, $psWhere = null, $pnLimit = 1)
	{
	    $lsPk = null;
	    $lsWhere = null;
	    $lsValues = '';
	    $lsComma = '';

	    $poEntity->getReflection();
	    
	    $laEntity = $poEntity->getArrayCopy();
	    
	    if (is_array($laEntity))
	    {
	        foreach ($laEntity as $lsField => $lmValue)
	        {
	            $laFieldType = $poEntity->get('_aFieldType');
	            
	            switch ($laFieldType[$lsField])
	            {
	                case 'num':
	                case 'int':
	                case 'decimal':
	                case 'float':
	                case 'integer':
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

	            $laChanged = $poEntity->get('_aChanged');
	            
	            if (isset($laChanged[$lsField]))
	            {
	                $lsValues .= $lsComma . $lsFieldValue;
	                $lsComma = ', ';
	            }
	            
	            if ($poEntity->get('_sPk') == $lsField)
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
    	    $this->setError(array("2", "There is no where clause!"));
    	    
    	    return false;
	    }

        if (empty($lsValues))
        {
            $this->setError(array("0", "There is no values changed to update!"));
    	    
    	    return true;
        }
        
	    $lsEntity = $poEntity->get('_sEntity');
	    
	    if (!empty($lsEntity))
	    {
   	        $this->_sQuery = "UPDATE `{$lsEntity}` 
   	                          SET {$lsValues} 
   	                          WHERE {$lsWhere} 
   	                          LIMIT {$pnLimit}";
   	        
   	        return true;
        }
        
        $this->setError(array("1", "There is no way to get the table name!"));
        
        return false;
	}
	
	
	/**
	 * 
	 * #param object $poEntity
	 * @param string $psWhere
	 * @param number $pnLimit
	 * @return boolean
	 */
	public function createQueryDelete ($poEntity, $psWhere = null, $pnLimit = 1)
	{
		$lsPk = null;
	    $lsWhere = null;
	    
	    $poEntity->getReflection();

	    $laFieldType = $poEntity->get('_aFieldType');
	    
	    if (isset($laFieldType[$poEntity->get('_sPk')]))
	    {
            switch ($laFieldType[$poEntity->get('_sPk')])
            {
                case 'num':
                case 'int':
                case 'decimal':
                case 'float':
                case 'integer':
                    $lsPk = "`{$poEntity->get('_sPk')}` = {$poEntity->get($poEntity->get('_sPk'))}";
                    break;
                default:
                    $lsPk = "`{$poEntity->get('_sPk')}` = '{$poEntity->get($poEntity->get('_sPk'))}'";
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
	        $this->setError(array("2", "There is no where clause!"));
    	    
    	    return false;
	    }	    

	    $lsEntity = $poEntity->get('_sEntity');
	    
	    if (!empty($lsEntity))
        {
    	    $this->_sQuery = "DELETE FROM `{$lsEntity}` 
    	                      WHERE {$lsWhere} 
    	                      LIMIT {$pnLimit}";
    	    
    	    return true;
        }

        $this->setError(array("1", "There is no way to get the table name!"));
	    
        return false;	    
	}
	
	
	/**
	 * 
	 * @param string $psQuery
	 */
	public function prepare ($psQuery = null)
	{
	    if ($psQuery == null)
	    {
	        $psQuery = $this->_sQuery;
	    }
	    
	    Debug::debug($psQuery);
	    
	    return $this->_oCon->prepare($psQuery);
	}
	
	
	/**
	 * 
	 */
	public function lastInsertId ()
	{
	    return $this->_oCon->lastInsertId();
	}
	
	
	/**
	 * 
	 */
	public function close ()
	{
	    $this->_oCon = null;
	}
	
	
	/**
	 * 
	 * @param object $poEntity
	 * @param mixed $pnId
	 * return boolean
	 */
	public function find ($poEntity, $pnId)
	{
	    $lsWhere = null;
	    $poEntity->getReflection();
	    
	    $laFieldType = $poEntity->get('_aFieldType');
	    
		if (isset($laFieldType[$poEntity->get('_sPk')]))
	    {
            switch ($laFieldType[$poEntity->get('_sPk')])
            {
                case 'num':
                case 'int':
                case 'decimal':
                case 'float':
                case 'integer':
                    $lsWhere = "`{$poEntity->get('_sPk')}` = {$pnId}";
                    break;
                default:
                    $lsWhere = "`{$poEntity->get('_sPk')}` = '{$pnId}'";
            }
	    }
	    
        return $this->findOneBy($poEntity, $lsWhere);    
	}
	
	
	/**
	 * 
	 * @param object $poEntity
	 * @param string $psWhere
	 * return boolean
	 */
	public function findOneBy ($poEntity, $psWhere = null)
	{
	    $poEntity->getReflection();
   
	    $lsEntity = $poEntity->get('_sEntity');
	    
	    if (!empty($lsEntity))
        {
   	        $this->createQuerySelect($lsEntity, $psWhere, '*', '', 1);

    		if ($this->connect())
    		{
    			$loStantement = $this->prepare();
    			
    			$this->close();
    			
    			if ($loStantement->execute())
    			{ 
    				$laResultSet = $loStantement->fetchAll(\PDO::FETCH_ASSOC);
   			        Debug::debug($laResultSet);
   			        
    				if (isset($laResultSet[0]))
    				{
    					$poEntity->populate($laResultSet[0]);
    					
    					return true;
    				}
    				else 
    				{
    				    return false;
    				}
    			}
    			else
    			{
    				$this->setError($loStantement->errorInfo());
    			}
    		}
        }

        $this->setError(array("1", "There is no way to get the table name!"));
        
        return false;	    
	}	
	
	
	/**
	 * 
	 * @param object $poEntity
	 * @param string $psWhere
	 * @param int $pnOffset        	
	 * @param int $pnPage        	
	 * @param mixed $pmOrdField        	
	 * @param string $psOrder
	 * @param mixed $pmGroup       	
	 * @return array|array of object|bool
	 */
    public function findBy ($poEntity, $psWhere = null, $pnOffset = 0, $pnPage = 0, $pmOrdField = null, $psOrder = null, $pmGroup = null)
	{
	    $poEntity->getReflection();
       
	    $lsEntity = $poEntity->get('_sEntity');
	    
	    if (!empty($lsEntity))
        {
   	        $this->createQuerySelect($lsEntity, $psWhere, '*', '', $pnOffset, $pnPage, $pmOrdField, $psOrder, $pmGroup);
   	        
   	        return $this->queryExec($poEntity->get('_sClass'));
        }

        $this->setError(array("1", "There is no way to get the table name!"));
        
        return false;
	}	
	
	
	/**
	 * 
	 * @param object $poEntity
	 * @param boolean $pbIgnore
	 * @return boolean
	 */
	public function flush ($poEntity, $pbIgnore = true)
	{
	    if ($this->createQueryInsert($poEntity, true))
	    {
    	    if ($this->connect())
		    {
			    $loStantement = $this->prepare();
	
			    $lbReturn = $loStantement->execute();
			
			    if ($lbReturn)
			    {
			        Debug::debug("SQL insert OK");
			        
			        $lnId = $this->lastInsertId();
                    $poEntity->set($poEntity->get('_sPk'), $lnId);
			    }
			    else
			    {
				    $this->setError($loStantement->errorInfo());
			    }
		    }
		}

		$this->close();
			
		return $lbReturn;
	}
	
	
	/**
	 * 
	 * @param object $poEntity
	 * @return string
	 */	
	public function getWhere ($poEntity)
	{
	    $poEntity->getReflection();
	    $lsField = $poEntity->get('_sPk');
	    $lmValue = $poEntity->get($lsField);
	    
		$laFieldType = $poEntity->get('_aFieldType');
	            
	    switch ($laFieldType[$lsField])
	    {
	        case 'num':
	        case 'int':
	        case 'decimal':
	        case 'float':
	        case 'integer':
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
	    
	    return $lsFieldValue;
	}
	
	
	/**
	 * 
	 * @param object $poEntity
	 * @return boolean
	 */
	public function update ($poEntity)
	{
        $lsWhere = $this->getWhere($poEntity);
	    
	    if ($this->createQueryUpdate($poEntity, $lsWhere, 1))
	    {
    	    if ($this->connect())
		    {
			    $loStantement = $this->prepare();
	
			    $lbReturn = $loStantement->execute();
			
			    if ($lbReturn)
			    {
			        Debug::debug("SQL update OK");
			    }
			    else
			    {
				    $this->setError($loStantement->errorInfo());
			    }
		    }
		}

		$this->close();
			
		return $lbReturn;
	}	
	
	
	/**
	 * 
	 * @param object $poEntity
	 * @return boolean
	 */
	public function delete ($poEntity)
	{
        $lsWhere = $this->getWhere($poEntity);
	    
	    if ($this->createQueryDelete($poEntity, $lsWhere, 1))
	    {
    	    if ($this->connect())
		    {
			    $loStantement = $this->prepare();
	
			    $lbReturn = $loStantement->execute();
			
			    if ($lbReturn)
			    {
			        Debug::debug("SQL delete OK");
			    }
			    else
			    {
				    $this->setError($loStantement->errorInfo());
			    }
		    }
		}

		$this->close();
			
		return $lbReturn;
	}	
}