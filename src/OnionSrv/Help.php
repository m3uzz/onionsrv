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

namespace OnionSrv;
use OnionSrv\Config;

class Help
{
	const VERSION	= '2.16-04';
	
	const BLACK 	= "30m";
	const RED 		= "31m";
	const GREEN 	= "32m";
	const BROWN 	= "33m";
	const BLUE 		= "34m";
	const PURPLE	= "35m";
	const CYAN 		= "36m";
	const GRAY 		= "37m"; 
	
	const BGBLACK 	= "\e[40m";
	const BGRED 	= "\e[41m";
	const BGGREEN 	= "\e[42m";
	const BGYELLOW 	= "\e[43m";
	const BGBLUE 	= "\e[44m";
	const BGPURPLE 	= "\e[45m";
	const BGCYAN 	= "\e[46m";
	const BGWHITE 	= "\e[47m";
	
	const N 		= "0;"; //normal
	const B 		= "1;"; //bold
	const I 		= "3;"; //italic
	const S 		= "4;"; //sublinhado
	
	const CLOSE		= "\e[0m\n\n";
	
	private $_aList = array();
	private $_aHelp = array();
	private $_aModuleHelp = null;
	private $_sLastTopic = "";
	
	
	public function __construct ()
	{
		$this->set("        *** m3uzz OnionSrv - Version: " . self::VERSION . " ***        ", self::PURPLE, self::BGBLACK);
		$this->set("\n");
		$this->set("AUTHOR:  Humberto Lourenço <betto@m3uzz.com>             ", self::CYAN, self::BGBLACK);
		$this->set("\n");
		$this->set("LINK:    http://github.com/m3uzz/onionsrv                ", self::CYAN, self::BGBLACK);
		$this->set("\n");
		$this->set("LICENCE: http://www.opensource.org/licenses/BSD-3-Clause ", self::CYAN, self::BGBLACK);
		$this->set("\n\n");
		
		$this->set("Usage: \n", self::BROWN, "", self::B);
		$this->set("  php ./onionsrv.php [Module] [param1=<value1> [paramN=<valueN>]] [options]\n", self::GREEN, "", self::I);
		$this->set("  php ./onionsrv.php [--m=<ModuleName>] --help\n", self::GREEN, "", self::I);
		
		$this->setTopic("Module");
		$this->setLine("--m=<ModuleName>", "Module name");
		$this->setLine("--c=<ControllerName>", "Controller name");
		$this->setLine("--a=<ActionName>", "Action name");
		$this->setTopic("Options");
		$this->setLine("--debug", "Activate debug mod");
		$this->setLine("--error", "Activate php display error");
		$this->setLine("--help", "Show this help");
		$this->setLine("--test", "Activate test mod");
		$this->setLine("--time", "Activate time count");
		
		return $this;
	}
	
	
	/**
	 * 
	 */
	public function display ()
	{
		if (is_array($this->_aHelp))
		{
			foreach ($this->_aHelp as $lsTopic => $laLine)
			{
				$this->set("\n\n" . $lsTopic . ":\n", self::BROWN, "", self::B);
				
				if (is_array($laLine))
				{
					foreach ($laLine as $lsLine => $lsDescription)
					{
						$this->set("  " . str_pad($lsLine, 30), self::GREEN);
						$this->set($lsDescription . "\n");
					}
				}
			}
		}
		
		$this->renderModuleHelp();
		
		if (is_array($this->_aList))
		{
			foreach ($this->_aList as $lsLine)
			{
				echo $lsLine;
			}
		}
		
		echo self::CLOSE;	
	}

	
	/**
	 * 
	 * @param string $psTxt
	 * @param string $psColor
	 * @param string $psBg
	 * @param string $psStyle
	 */
	public function set ($psTxt, $psColor = self::GRAY, $psBg = "", $psStyle = self::N)
	{
		$this->_aList[] = "\e[" . $psStyle . $psColor . $psBg . $psTxt . "\e[0m";
	}
	

	/**
	 * 
	 * @param string $psTopic
	 * @return \OnionSrv\Help
	 */
	public function setTopic ($psTopic)
	{
		$this->_aHelp[$psTopic] = array();
		$this->_sLastTopic = $psTopic;
		
		return $this;
	}
	
	
	/**
	 * 
	 * @param string $psLine
	 * @param string $psDesctiption
	 * @param string $psTopic
	 * @return \OnionSrv\Help
	 */
	public function setLine ($psLine, $psDesctiption = "", $psTopic = null)
	{
		if ($psTopic == null)
		{
			$psTopic = $this->_sLastTopic;
		}
		
		$this->_aHelp[$psTopic][$psLine] = $psDesctiption;
		
		return $this;
	}
	
	
	/**
	 * 
	 * @param unknown $lsModule
	 * @return \OnionSrv\Help
	 */
	public function setModule ($lsModule)
	{
		

		return $this;
	}
	
	
	/**
	 * 
	 * @param string $psPathModule
	 * @return \OnionSrv\Help
	 */
	public function factory ($psHelpPath)
	{
		$lsHelpPath = $psHelpPath . DS . "help.php";
		
		if (file_exists($lsHelpPath))
		{
			$this->_aModuleHelp = include ($lsHelpPath);
		}

		return $this;
	}

	
	/**
	 * 
	 * @return \OnionSrv\Help
	 */
	public function renderModuleHelp ()
	{
		if (is_array($this->_aModuleHelp))
		{
			foreach ($this->_aModuleHelp as $lsModule => $laControllerHelp)
			{
				$this->set("\n\nModule: --m=" . $lsModule . "\n", self::BROWN, "", self::B);
				
				if (is_array($laControllerHelp))
				{
					foreach ($laControllerHelp as $lsController => $laActionsHelp)
					{
						$this->set(" - Controller: --c=" . $lsController . "\n", self::BROWN);
						
						if (is_array($laActionsHelp))
						{
							foreach ($laActionsHelp as $lsAction => $laAction)
							{
								$this->set("  - Action: --a=" . $lsAction, self::CYAN);
									
								if (isset($laAction['desc']) && !empty($laAction['desc']))
								{
									$this->set(" - " . $laAction['desc'], self::GRAY, "", self::I);
								}
								
								$this->set("\n");
								
								if (isset($laAction['params']) && is_array($laAction['params']) && count($laAction['params']) > 0)
								{
									foreach ($laAction['params'] as $lsParam => $lsParamDesc)
									{
										$this->set("    " . str_pad($lsParam, 28), self::GREEN);
										$this->set($lsParamDesc . "\n");
									}
								}
								else 
								{
									$this->set("    No param request\n", self::BLACK);
								}
								
								$this->set("\n");
							}
						}
					}
				}
			}
		}
		
		return $this;
	}
}