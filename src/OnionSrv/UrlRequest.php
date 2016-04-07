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

namespace OnionSrv;
use OnionSrv\Debug;

defined('DS') 			|| define('DS', DIRECTORY_SEPARATOR);
defined('BASE_DIR') 	|| define('BASE_DIR', realpath(dirname(__FILE__) . DS . ".." . DS . ".."));
defined('CONFIG_DIR') 	|| define('CONFIG_DIR', realpath(dirname(__DIR__)) . DS . 'Config');
defined('LIB_DIR') 		|| define('LIB_DIR', realpath(dirname(__DIR__)) . DS . 'Lib');
defined('LOG_DIR') 		|| define('LOG_DIR', realpath(dirname(__DIR__)) . DS . 'Log');

class UrlRequest
{

	/**
	 * Link para o servidor
	 * @var string
	 */
	private $_sServer = null;
	
	/**
	 * HTTP Version, informa o tipo de requisição http
	 * @var string
	 */
	private $_sHttpVersion = "HTTP/1.0";
	
	/**
	 * Token de autenticação para acesso ao server
	 * @var string
	 */
	private $_sToken = null;

	/**
	 * UserAgent, identificação do browser
	 * @var string
	 */
	private $_sUserAgent = null;

	/**
	 * UserAgent, identificação do browser
	 * @var string
	 */
	private $_sUUID = null;
	
	/**
	 * UserAgent, identificação do browser
	 * @var string
	 */
	private $_sAccept = null;
	
	/**
	 * UserEncoding, identificação do browser
	 * @var string
	 */
	private $_sAcceptEncoding = null;
	
	/**
	 * UserLanguage, identificação do browser
	 * @var string
	 */
	private $_sAcceptLanguage = null;
	
	/**
	 * Referer, identificação de onde está vindo a chamada
	 * @var string
	 */
	private $_sReferer = null;
		
	/**
	 * Charset
	 * @var string
	 */
	private $_sCharset = "utf-8";
		
	/**
	 * Metodo de requisição ao server, GET ou POST
	 * @var string
	 */
	private $_sMethod = "GET";

	/**
	 * Manter conexão ativa ou encerrar ao final da trasação
	 * @var string
	 */
	private $_sConnection = "Close"; //keep-alive
		
	/**
	 * Porta padrão de requisição
	 * @var int
	 */
	private $_nPort = 80;
	
	/**
	 * Tempo de espera para resposta de uma requisição
	 * @var int
	 */
	private $_nTimeOut = 15;
	
	/**
	 * Verificar se há uma conexão ativa com o server
	 * @var boolean
	 */
	private $_bCheckOnly = false;

	/**
	 * Tamanho do requisição de dados para o servidor
	 * @var unknown
	 */
	private $_nGetLength = 1024;
	
	/**
	 * Objeto para callback da requisição
	 * @var object
	 */
	private $_oObject = null;
	
	/**
	 * Metodo de callback para a requisição
	 * @var string
	 */
	private $_sCallBackMethod = null;
	
	/**
	 * Se deve fechar a conexão ao final da chamada
	 * @var boolean
	 */
	private $_bClose = true;

	/**
	 * Conteúdo do cabeçalho da última requisição
	 * @var array
	 */
	private $_aResponseHeader = null;

	/**
	 * http header de retorno
	 * @var string
	 */
	private $_sHttpHeader = "text/plain";

	/**
	 * Array de parametros a ser passado para o service
	 * @var array
	 */
	private $_aParams = null;

	/**
	 * parametros codificados em json mode
	 * @var string
	 */
	private $_sJson = null;
		
	/**
	 * Dados de arquivo
	 * @var string
	 */
	private $_sFile = null;
		
	
	public function __construct()
	{
		include_once(LIB_DIR . DS . 'Debug.php');
		
		if(isset($_SERVER['HTTP_USER_AGENT'])){
			$this->_sUserAgent = $_SERVER['HTTP_USER_AGENT'];
		}
		else
		{
			$this->_sUserAgent = "Onion_WebService";
		}
		
		if(isset($_SERVER['HTTP_ACCEPT'])){
			$this->_sAccept = $_SERVER['HTTP_ACCEPT']; 
		}
		
		if(isset($_SERVER['UUID'])){
			$this->_sUUID = $_SERVER['UUID'];
		}
		
		if(isset($_SERVER['HTTP_ACCEPT_ENCODING'])){
			$this->_sAcceptEncoding = $_SERVER['HTTP_ACCEPT_ENCODING'];
		}
		
		if(isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])){
			$this->_sAcceptLanguage = $_SERVER['HTTP_ACCEPT_LANGUAGE'];
		}
	}

	/**
	 * __set
	 * 
	 * @param string $psVar
	 * @param string $psValue
	 */
	public function __set($psVar, $psValue)
	{
		return $this->set($psVar, $psValue);
	}
	
	/**
	 * set
	 * 
	 * @param string $psVar
	 * @param string $psValue
	 */
	public function set($psVar, $psValue)
	{
		$lsVar = "_".$psVar;
		
		if(property_exists($this, $lsVar))
		{
			$this->$lsVar = $psValue;
		}
		
		return $this;
	}

	/**
	 * __get
	 * 
	 * @param string $psVar
	 * @return boolean|string
	 */
	public function __get($psVar)
	{		
		return $this->get($psVar);
	}

	/**
	 * get
	 *
	 * @param string $psVar
	 * @return boolean|string
	 */
	public function get($psVar)
	{
		$lsVar = "_".$psVar;
	
		if(property_exists($this, $lsVar))
		{
			return $this->$lsVar;
		}
	
		return false;
	}
	
	public function __print()
	{
		var_dump(get_object_vars($this));	
	}
	
	/**
	 * Metodo de requisição ao servidor ou local
	 * 
	 * @param string $psLink link para conexão e requisição ao servidor
	 * @return string|boolean
	 */
	public function urlrequest($psLink)
	{  
		$lsStream = null;
		$laLink = parse_url($psLink); //interpreta o link e retorna seus componentes
	
		if(isset($laLink['port']))
		{
			//Se a porta não estiver setada no link é considerada a porta padrão
			$this->_nPort = $laLink['port'];
		}
		
		Debug::debug($laLink);
		
		//Verificando se o host está setado
		if(isset($laLink['host']))
		{
			//Tentando abrir uma conexão com o servidor até o prazo limit (_nTimeOut), retornando um token de conexão
			//Caso ocorra algum erro, é retornado false e o número ($lnErrNo) e mensagem ($lsErrStr) do erro
			$lrConnection = fsockopen($laLink['host'], $this->_nPort, $lnErrNo, $lsErrStr, $this->_nTimeOut);
		
			if(!$lrConnection)
			{
				//Se a conexão não tiver sido estabelecida é gerado um log de erro
				Debug::debug(array("Failed to connect on server (".$psLink.")", $lsErrStr));
			}
			else
			{
				$lsPost = "";
				$lsGet = "";
				$lsQuery = "";
				$lsPath = "";
				
				if(isset($laLink['query']))
				{
					$lsPost = "Content-length: " . strlen($laLink['query']) . "\r\n";
					$lsGet = "?" . $laLink['query'];
					$lsQuery = $laLink['query'];
				}

				if(isset($laLink['path']))
				{
					$lsPath = $laLink['path'];
				}
				
				//Caso a conexão tenha sido estabelecida verifica o metodo de requisição
				if($this->_sMethod === "POST")
				{
					$lsRequest  = "POST " . $lsPath . " $this->_sHttpVersion\r\n";
					$lsRequest .= "Host: " . $laLink['host'] . "\r\n";
					$lsRequest .= "User-Agent: " . $this->_sUserAgent . "\r\n";
					
					if(!empty($this->_sUUID))
						$lsRequest .= "UUID: " . $this->_sUUID . "\r\n";
					
					if(!empty($this->_sAccept))
						$lsRequest .= "Accept: " . $this->_sAccept . "\r\n";
					
					if(!empty($this->_sAcceptLanguage))
						$lsRequest .= "Accept-Language: " . $this->_sAcceptLanguage . "\r\n";
					
					if(!empty($this->_sAcceptEncoding))
						$lsRequest .= "Accept-Encoding: " . $this->_sAcceptEncoding . "\r\n";
					
					if(!empty($this->_sToken))
						$lsRequest .= "Token: " . $this->_sToken . "\r\n";
					
					if(!empty($this->_sJson))					
						$lsRequest .= "Json: " . $this->_sJson . "\r\n";
					
					$lsRequest .= "Content-type: application/x-www-form-urlencoded; charset=" . $this->_sCharset . "\r\n";
					$lsRequest .= $lsPost;
					$lsRequest .= "Connection: Close\r\n\r\n";
					$lsRequest .= $lsQuery;
				}
				elseif($this->_sMethod === "JSON")
				{
					$lsRequest  = "POST " . $lsPath . " $this->_sHttpVersion\r\n";
					$lsRequest .= "Host: " . $laLink['host'] . "\r\n";
					$lsRequest .= "User-Agent: " . $this->_sUserAgent . "\r\n";
					
					if(!empty($this->_sUUID))
						$lsRequest .= "UUID: " . $this->_sUUID . "\r\n";
					
					if(!empty($this->_sAccept))
						$lsRequest .= "Accept: " . $this->_sAccept . "\r\n";
					
					if(!empty($this->_sAcceptLanguage))
						$lsRequest .= "Accept-Language: " . $this->_sAcceptLanguage . "\r\n";
					
					if(!empty($this->_sAcceptEncoding))
						$lsRequest .= "Accept-Encoding: " . $this->_sAcceptEncoding . "\r\n";
					
					if(!empty($this->_sToken))
						$lsRequest .= "Token: " . $this->_sToken . "\r\n";
					
					$lsRequest .= "Data-type: json\r\n";
					$lsRequest .= "Content-type: application/x-www-form-urlencoded; charset=" . $this->_sCharset . "\r\n";
					$lsRequest .= "Content-length: " . strlen($this->_sJson) . "\r\n";
					$lsRequest .= "Connection: " . $this->_sConnection . "\r\n\r\n";
					$lsRequest .= "data=" . $this->_sJson . "\r\n";					
				}
				elseif($this->_sMethod === "PUT")
				{
					$lsRequest  = "PUT " . $lsPath . " $this->_sHttpVersion\r\n";
					$lsRequest .= "Host: " . $laLink['host'] . "\r\n";
					$lsRequest .= "User-Agent: " . $this->_sUserAgent . "\r\n";
						
					if(!empty($this->_sUUID))
						$lsRequest .= "UUID: " . $this->_sUUID . "\r\n";
						
					if(!empty($this->_sAccept))
						$lsRequest .= "Accept: " . $this->_sAccept . "\r\n";
						
					if(!empty($this->_sAcceptLanguage))
						$lsRequest .= "Accept-Language: " . $this->_sAcceptLanguage . "\r\n";
						
					if(!empty($this->_sAcceptEncoding))
						$lsRequest .= "Accept-Encoding: " . $this->_sAcceptEncoding . "\r\n";
						
					if(!empty($this->_sReferer))
						$lsRequest .= "Referer: " . $this->_sReferer . "\r\n";
						
					if(!empty($this->_sToken))
						$lsRequest .= "Token: " . $this->_sToken . "\r\n";
						
					$lsRequest .= "Content-type: application/x-www-form-urlencoded; charset=" . $this->_sCharset . "\r\n";
					$lsRequest .= "Content-length: " . strlen($this->_sFile) . "\r\n";
					$lsRequest .= "Connection: " . $this->_sConnection . "\r\n\r\n";
					$lsRequest .= $this->_sFile;
				}				
				else
				{
					$lsRequest = "GET " . $lsPath . $lsGet . " $this->_sHttpVersion\r\n";
					$lsRequest .= "Host: " . $laLink['host'] . "\r\n";
					$lsRequest .= "User-Agent: " . $this->_sUserAgent . "\r\n";
					
					if(!empty($this->_sUUID))
						$lsRequest .= "UUID: " . $this->_sUUID . "\r\n";
					
					if(!empty($this->_sAccept))
						$lsRequest .= "Accept: " . $this->_sAccept . "\r\n";
					
					if(!empty($this->_sAcceptLanguage))
						$lsRequest .= "Accept-Language: " . $this->_sAcceptLanguage . "\r\n";
					
					if(!empty($this->_sAcceptEncoding))
						$lsRequest .= "Accept-Encoding: " . $this->_sAcceptEncoding . "\r\n";
					
					if(!empty($this->_sToken))
						$lsRequest .= "Token: " . $this->_sToken . "\r\n";
					
					$lsRequest .= "Connection: " . $this->_sConnection . "\r\n\r\n";
				}
	
				Debug::debug($lsRequest);
				
				$lbErro = false;
				$lbHeaderSection = true;
				$lsLastHeader = "";
				$this->_aResponseHeader = array();

				//Enviando requisição para o servidor
				if(!fwrite($lrConnection, $lsRequest))
				{
					//Se a requisição falhar retorna um erro
					Debug::debug(array("Failed to write on server (".$psLink.")"));
					$lbErro = true;
				}
		
				$lsHeaderStatusLine = '';
				
				//Lendo a requisição do servidor em pacotes
				while(!feof($lrConnection) && !$lbErro)
				{
					if($lbHeaderSection)
					{
						//Lendo o cabeçado da requisição
						$lsHeader = fgets($lrConnection, $this->_nGetLength);
		
						if($lsHeader !== "\r\n")
						{
							
							//Enquanto não chegar ao fim do cabeçalho, atribuir cada linha a um array
							$laHeaderLine = explode(': ', trim($lsHeader));
							$lsValue = isset($laHeaderLine[1])?$laHeaderLine[1]:$laHeaderLine[0];
							$lsVar = isset($laHeaderLine[1])?$laHeaderLine[0]:'';

							if (!empty($lsValue) && empty($lsVar))
							{
								$lsVar = 'Status' . $lsHeaderStatusLine;
								$lsHeaderStatusLine++;
							}
							
							if (isset($this->_aResponseHeader[$lsVar]))
							{
								$this->_aResponseHeader[$lsVar][] = $lsValue;
							}
							else
							{
								$this->_aResponseHeader[$lsVar] = $lsValue;
							}
						}
						else
						{
							//Se a leitura do cabeçalho chegar ao fim, fechar a seção para a leitura do conteúdo
							$lbHeaderSection = false;
						}
		
						if(!$lbHeaderSection)
						{
							//Se o cabeçalho da requisição tiver acabado, verifica o status da requisição
							if(!preg_match("/HTTP.*200 OK/", $this->_aResponseHeader['Status']))
							{
								$this->_aResponseHeader['Error'] = true;
								//Se a requisição não encontar o arquivo e retornar um código diferente de 200, é gerado um log de erro
								Debug::debug(array($this->_aResponseHeader['Status']." (".$psLink.")"));
								$lbErro = true;
							}
		
							if($this->_bCheckOnly)
							{
								//Se _bCheckOnly estiver setada como true, verifica somente o cabeçalho
								return !$lbErro;
							}
						}
					}
					else
					{
						//if(is_object($this->_oObj) && method_exists($this->_oObj, "trata_linha_request"))
						if(is_object($this->_oObject) && method_exists($this->_oObject, $this->_sCallBackMethod))
						{
							$lsMethod = $this->_sCallBackMethod;
							//Lendo o arquivo do servidor e tratando linha a linha através da função callback
							$this->_sObject->$lsMethod(fgets($lrConnection, $this->_nGetLength));
						}
						else
						{
							//Lendo o arquivo do servidor e armazenando em uma string 
							$lsStream .= fgets($lrConnection, $this->_nGetLength);
						}
					}
				}
		
				if($this->_bClose)
				{
					//Se a conexão deve ser fechada ao final da requisição e leitura
					fclose($lrConnection);
				}

				if (!$lbErro && isset($this->_aResponseHeader['Content-Encoding']) && $this->_aResponseHeader['Content-Encoding'] == 'gzip')
				{
					$lsStream = gzdecode($lsStream);
				}
				
				//Retornando o conteúdo do arquivo
				return $lsStream;
			}
		}
	
		//Retorno falso caso não tenha estabelicido a conexão com o servidor
		return false;
	}
	
	/**
	 * Metodo de requisição de serviço no servidor
	 * 
	 * @param string $psService caminho do arquivo no servidor (modulo/controller ou service)
	 * @param string $psAction metodo ou action a ser executada pelo service
	 * @return string
	 */
	public function callService($psService = null, $psAction = null)
	{
		$lsQuery = "";

		if(is_array($this->_aParams))
		{
			$lsSeparate = "";

			//Transformando array de parametros em string
			foreach($this->_aParams as $lsVar => $lsValue)
			{
				$lsQuery .= $lsSeparate . urlencode($lsVar) . "=" . urlencode($lsValue);
				$lsSeparate = "&";
			}
		}
		
		$psService = isset($psService) ? "/" . $psService : "";
		$psAction = isset($psAction) ? "/" . $psAction : "";
		$lsQuery = !empty($lsQuery) ? "/?" . $lsQuery : "";
		
		$lsLink = $this->_sServer . $psService . $psAction . $lsQuery;

		return $this->urlrequest($lsLink);		
	} 
}