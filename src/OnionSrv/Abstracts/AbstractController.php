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
use OnionSrv\Config;
use OnionSrv\Debug;
use OnionSrv\Help;
use OnionSrv\System;

abstract class AbstractController
{

	protected $_aParams;

	protected $_aFilters;

	protected $_oRepository;
	
	protected $_aRepository = array();
	
	protected $_sControllerPath;
	
	protected $_sConfigPath;
	
	protected $_sViewPath;
	
	protected $_sClass;
	
	protected $_sMethod;
	
	protected $_sModule;
	
	protected $_sController;
	
	protected $_sAction;
	
	protected $_oView;
	
	protected $_sLayoutTemplate;
	
	protected $_sViewTemplate;
	
	public $_bHelp = false;

	
	/**
	 * 
	 * @param array $paParams
	 * @param array $paService
	 */
	public function __construct (array $paParams = array(), array $paService = array())
	{
		$this->_sControllerPath = dirname($paService['service']);
		
		$this->_sConfigPath = dirname(dirname(dirname(dirname($paService['service'])))) . DS . 'config';
		
		$this->_sViewPath = dirname(dirname($paService['service'])) . DS . 'View';
		
		$this->_sClass = $paService['class'];
		
		$this->_sMethod = $paService['method'];
		
		$this->_sModule = $paService['module'];
		
		$this->_sController = $paService['controller'];

		$this->_sAction = $paService['action'];
		
		$this->_aParams = $paParams;
		
		$this->_aFilters = $this->getParamsFilters();
		
		Debug::debug($this->_aFilters);
		
		$this->validateParams();
		
		if (method_exists($this, 'init'))
		{
			$this->init();
		}

		$this->help();
	}


	/**
	 * 
	 */
	public function getParamsfilters()
	{
	    if (is_file($this->_sConfigPath . DS . 'srv-validate.php'))
	    {
	        return include($this->_sConfigPath . DS . 'srv-validate.php');
	    }
	    else 
	    {
	        return Config::getOptions('params');
	    }
	}

	/**
	 * 
	 * @param bool $pbForce
	 * @param bool $lbGeneral
	 */
	public function help ($pbForce = false, $lbGeneral = false)
	{
		global $gbHelp;
		
		if ($gbHelp || $pbForce)
		{
			$this->_bHelp = true;
			
			$loHelp = new Help();
			
			if (method_exists($this, 'moduleHelp'))
			{
				$this->moduleHelp($loHelp);
			}
			
			if (!$lbGeneral)
			{
				$laHelpContent = $loHelp->getActionHelp($this->_sModule, $this->_sController, $this->_sAction);
			
				if (count($laHelpContent) == 0)
				{
					$laHelpContent = $loHelp->getControllerHelp($this->_sModule, $this->_sController);
				}
			
				$loHelp->setModuleHelp($laHelpContent);
			}
			
			$loHelp->display();
		}
	}
	
	
	/**
	 *
	 */
	public function moduleHelp ($poHelp)
	{
		$poHelp->factory($this->_sConfigPath);
		
	}
	
	
	/**
	 * 
	 */
	public function thisTest ()
	{
		Debug::display($this);
		Debug::display($this->_aParams);
	}
	
	
	/**
	 * 
	 * @param string $psVar
	 * @param string $pmDefault
	 * @return string
	 */
	public function getRequestGet ($psVar, $pmDefault = null)
	{
		if (isset($this->_aParams['GET'][$psVar]))
		{
			return $this->_aParams['GET'][$psVar];
		}
		else
		{
			return $pmDefault;
		}
	}

	
	/**
	 * 
	 * @param string $psVar
	 * @param string $pmDefault
	 * @return string
	 */
	public function getRequestPost ($psVar, $pmDefault = null)
	{
		if (isset($this->_aParams['POST'][$psVar]))
		{
			return $this->_aParams['POST'][$psVar];
		}
		else
		{
			return $pmDefault;
		}
	}

	
	/**
	 * 
	 * @param string $psVar
	 * @param string $pmDefault
	 * @param bool $pbRequired
	 * @return string
	 */
	public function getRequestArg ($psVar, $pmDefault = null, $pbRequired = false)
	{
		if (isset($this->_aParams['ARG'][$psVar]))
		{
			return $this->_aParams['ARG'][$psVar];
		}
		else
		{
			if (PHP_SAPI == "cli" && PROMPT)
			{
				$lsMsgHelp = "";
				$lsMsgHelpSeparate = "";
				
				$loHelp = new Help();
				$loHelp->factory($this->_sConfigPath);
				$lsVarHelp = $loHelp->getParamHelp($this->_sModule, $this->_sController, $this->_sAction, $psVar);
				
				if (!empty($lsVarHelp))
				{
					$lsMsgHelp = $lsVarHelp;
					$lsMsgHelpSeparate = ". ";
				}
				
				if ($pmDefault != null)
				{
					$lsMsgHelp .= "{$lsMsgHelpSeparate}Default: ({$pmDefault})";
					$lsMsgHelpSeparate = ". ";
				}
				
				if ($pbRequired)
				{
					$lsMsgHelp .= "{$lsMsgHelpSeparate}[required]";
				}
				
				if (!empty($lsMsgHelp))
				{
					echo("{$lsMsgHelp}\n");
				}
				
				$lsAnswer = System::prompt("Enter param [$psVar]:");
					
				if ($this->validateValue($psVar, $lsAnswer, 'ARG'))
				{
					$this->_aParams['ARG'][$psVar] = $lsAnswer;
					
					if (!empty($lsAnswer))
					{
						return $lsAnswer;
					}
					elseif (!empty($pmDefault)) 
					{
						$this->_aParams['ARG'][$psVar] = $pmDefault;
						return $pmDefault;
					}
					elseif(!$pbRequired)
					{
						return null;
					}
					else 
					{
						System::echoError("The param value is required to continue!");
						System::echoError("Try --help!");
						System::exitError("ABORTING SCRIPT EXECUTION!");
					}
				}
				else
				{
					if (empty($lsAnswer) && !empty($pmDefault))
					{
						$this->_aParams['ARG'][$psVar] = $pmDefault;
						return $pmDefault;
					}
					elseif(empty($lsAnswer) && empty($pmDefault) && !$pbRequired)
					{
						return null;
					}
					else
					{
						System::echoError("The param value do not match to the expected!");
						System::echoError("Try --help!");
						System::exitError("ABORTING SCRIPT EXECUTION!");
					}
				}
			}
			else
			{
				return $pmDefault;
			}
		}
	}

	
	/**
	 * 
	 * @param string $psVar
	 * @param string $pmDefault
	 * @param bool $pbRequired
	 * @return Ambigous <string, string>
	 */
	public function getRequest ($psVar, $pmDefault = null, $pbRequired = false)
	{
		return $this->getRequestArg($psVar, $this->getRequestGet($psVar, $this->getRequestPost($psVar, $pmDefault)), $pbRequired);
	}

	
	/**
	 * 
	 */
	public function validateParams ()
	{
		$this->validateParamsType('GET');
		$this->validateParamsType('POST');
		$this->validateParamsType('ARG');
		
		Debug::debug($this->_aParams);
	}

	
	/**
	 * 
	 * @param string $psType
	 */
	public function validateParamsType ($psType = 'GET')
	{
		if (isset($this->_aParams[$psType]) && is_array($this->_aParams[$psType]))
		{
			foreach ($this->_aParams[$psType] as $lsVar => $lsValue)
			{
				if (!$this->validateValue($lsVar, $lsValue, $psType))
				{
					unset($this->_aParams[$psType][$lsVar]);
				}
			}
		}
	}
	
	
	/**
	 * @param string $psVar
	 * @param string $psValue
	 * @param string $psType
	 * @return bool
	 */
	public function validateValue ($psVar, $psValue, $psType = 'GET')
	{
		if (isset($this->_aFilters[$psType][$psVar]))
		{
			$lsFilter = $this->_aFilters[$psType][$psVar];
			
			if (empty($lsFilter) || preg_match("/$lsFilter/", $psValue))
			{
				return true;
			}
		}
		
		return false;
	}
	
	
	public function setLayoutTemplate ($psTemplate = null)
	{
	    //TODO: criar template para layout
	}
	
	
	public function setViewTemplate ($psTemplate = null)
	{
	    //TODO: criar template para view
	}
	
	
	public function setView ()
	{
	    //TODO: criar classe view
	}
	
	
    /**
     * 
     * @param mixed $pmView
     */
	public function httpView ($pmView)
	{
	    if ($_SERVER['HTTP_ACCEPT'] == 'application/json')
	    {
	        header('Content-type: application/json');
	        echo json_encode($pmView);
	    }
	    else 
	    {
	       Debug::display($pmView);
	    }
	}
	
	
    /**
     * 
     * @param mixed $pmView
     */
	public function lineView ($pmView)
	{
		if (!DEBUG)
		{
			header('Content-type: application/json');
		}
		
		echo json_encode($pmView);
	}
}