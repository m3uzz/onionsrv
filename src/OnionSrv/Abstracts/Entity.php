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
 * @package    Onion Service
 * @author     Humberto Lourenço <betto@m3uzz.com>
 * @copyright  2014-2016 Humberto Lourenço <betto@m3uzz.com>
 * @license    http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @link       http://github.com/m3uzz/onionsrv
 */
namespace OnionSrv\Abstracts;
use OnionSrv;

abstract class Entity
{
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
				return $this->$lsMethod($pmValue);
			}
			else
			{
				$this->$psProperty = $pmValue;
			}
		}
	}
	
	
	/**
	 * 
	 * @param string $psVar
	 * @param string $pmValue
	 * @return \Lib\Abstracts\Entity
	 */
	public function set ($psVar, $pmValue)
	{
		if (property_exists($this, $psVar))
		{
			$this->$psVar = $pmValue;
		}
		
		return $this;
	}
	
	
	/**
	 * 
	 * @param string $psProperty
	 */
	public function __get ($psProperty)
	{
		return $this->get($psProperty);
	}
	
	
	/**
	 * 
	 * @param string $psProperty
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
	 * @return array
	 */
	public function getArrayCopy ()
	{
		$laProperties = get_object_vars($this);
		
		if (is_array($laProperties))
		{
			foreach ($laProperties as $lsKey => $lmValue)
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
}