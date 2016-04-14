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

	protected $_aConfDb = array();

	protected $_oDb;

	
	/**
	 * 
	 * @param array $paDb
	 */
	public function __construct (array $paDb)
	{
		$this->setDbConf($paDb);
	}

	
	/**
	 * 
	 * @param array $paDb
	 * @return \Lib\Abstracts\AbstractRepository
	 */
	public function setDbConf (array $paDb)
	{
		if (is_array($paDb))
		{
			$this->_aConfDb['host'] = (isset($paDb['host']) ? $paDb['host'] : null);
			$this->_aConfDb['user'] = (isset($paDb['user']) ? $paDb['user'] : null);
			$this->_aConfDb['pass'] = (isset($paDb['pass']) ? $paDb['pass'] : null);
			$this->_aConfDb['db'] = (isset($paDb['db']) ? $paDb['db'] : null);
			$this->_aConfDb['port'] = (isset($paDb['port']) ? $paDb['port'] : null);
		}
		
		Debug::debug($this->_aConfDb);
		
		return $this;
	}

	
	/**
	 * 
	 * @param array $paConfDb
	 */
	public function connect (array $paConfDb = null)
	{
		if ($paConfDb == null)
		{
			$paConfDb = $this->_aConfDb;
		}
		
		$lsCon = "mysql:host={$paConfDb['host']};port={$paConfDb['port']};dbname={$paConfDb['db']}";
		$lsUser = $paConfDb['user'];
		$lsPass = $paConfDb['pass'];
		
		Debug::debug(array($lsCon, $lsUser, $lsPass));
		$this->_oDb = new \PDO($lsCon, $lsUser, $lsPass);
		Debug::debug($this->_oDb);
	}

	
	/**
	 *
	 * @param string $psString        	
	 * @return string
	 */
	public function escapeString ($psString)
	{
		$laSearch = array(
			"\\",
			"\0",
			"\n",
			"\r",
			"\x1a",
			"'",
			'"'
		);
		
		$laReplace = array(
			"\\\\",
			"\\0",
			"\\n",
			"\\r",
			"\Z",
			"\'",
			'\"'
		);
		
		return str_replace($laSearch, $laReplace, $psString);
	}

	
	/**
	 *
	 * @param string $psSql        	
	 * @param string $psEntity        	
	 * @return array|array of object|bool:
	 */
	public function queryExec ($psSql, $psEntity = "", array $paConfDb = null)
	{
		Debug::debug($psSql);
		
		$this->connect($paConfDb);
		
		$loStantement = $this->_oDb->prepare($psSql);
		
		$this->_oDb = null;
		
		if ($loStantement->execute())
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
		}
		else
		{
			Debug::display($loStantement->errorInfo());
		}
		
		return false;
	}

	
	/**
	 * 
	 * @param string $psSql
	 * @param array $paConfDb
	 * @return boolean
	 */
	public function update ($psSql, array $paConfDb = null)
	{
		Debug::debug($psSql);
		
		$this->connect($paConfDb);
		
		$loStantement = $this->_oDb->prepare($psSql);
		$lbReturn = $loStantement->execute();
		
		if (!$lbReturn)
		{
			Debug::display($loStantement->errorInfo());
		}
		else
		{
			Debug::debug($lbReturn);
		}
		
		$this->_oDb = null;
		
		return $lbReturn;
	}

	
	/**
	 * 
	 * @param string $psSql
	 * @param array $paConfDb
	 * @return boolean
	 */
	public function insert ($psSql, array $paConfDb = null)
	{
		Debug::debug($psSql);
	
		$this->connect($paConfDb);
	
		$loStantement = $this->_oDb->prepare($psSql);
		$lbReturn = $loStantement->execute();
	
		if (!$lbReturn)
		{
			Debug::display($loStantement->errorInfo());
		}
		else
		{
			Debug::debug($lbReturn);
		}
		
		$this->_oDb = null;
		
		return $lbReturn;
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
				$lsGroup .= "{$lsComma}{$lsField}";
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
		
		$lsSql = "
		SELECT {$lsFields}
		FROM {$psTable}
		{$psJoin}
		WHERE 1 {$psWhere}
		{$lsGroup}
		{$lsOrder}
		{$lsLimit}";
		
		$lsSql = preg_replace(("/WHERE 1 AND /i"), "WHERE ", $lsSql);
		
		return $lsSql;
	}
}