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

abstract class AbstractDriver
{
    protected $_aConf = array();

	protected $_oCon = null;
	
    protected $_aError = null;    
    
    
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
	 * @param array $paError
	 */
	public function setError ($paError)
	{
	    $this->_aError = $paError;
	    Debug::debug($this->_aError);
	    
	    return $this;
	}
	
	
	/**
	 *
	 * @return bool
	 */
	public function hasError ()
	{
		if ($this->_aError != null);
		{
			return true;
		}
		
		return false;
	}
	
	
	/**
	 * 
	 * @return string|null
	 */
	public function getErrorMsg ()
	{
		if (isset($this->_aError[2]))
		{
			return $this->_aError[2];
		}
		
		return null;
	}
	
	
	/**
	 *
	 * @return string|null
	 */
	public function getErrorCode ()
	{
		if (isset($this->_aError[1]))
		{
			return $this->_aError[1];
		}
	
		return null;
	}
	
	
	/**
	 *
	 * @return string|null
	 */
	public function getError ()
	{
		if (is_array($this->_aError))
		{
			return $this->_aError;
		}
	
		return null;
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
}