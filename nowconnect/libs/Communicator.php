<?php
/**
 * @brief API 서버와 통신하기 위한 모듈
 */
class CommunicatorBase extends ApiServer
{
	protected $buffer = NULL; // API 요청 결과값
	protected $options = array();
	private $result = NULL; // 파싱 결과값
	private $httpHeader = array();
	private $httpCode;
	private $ch;
	private $method;
	private $params;

	/**
	 * Constructor
	 */
	public function __construct()
	{
		$this->ch = curl_init();

		curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, TRUE);
	}

	/**
	 * PUT
	 */
	public function put()
	{
		curl_setopt($this->ch, CURLOPT_CUSTOMREQUEST, 'PUT');
		array_push($this->httpHeader, 'X-HTTP-Method-Override: PUT');

		return $this;
	}

	public function option($options = array())
	{
		$this->options = $options;

		return $this;
	}

	public function addOption($key, $value)
	{
		if(!$key || !isset($value))
		{
			return FALSE;
		}

		$this->options[$key] = $value;

		return $this;
	}

	public function param($params)
	{
		$this->params = $params;

		if(is_object($this->params))
		{
			$this->params = (array)$this->params;
		}

		if(!is_array($this->params))
		{
			$this->params = array();
		}
 
		$api_key = parent::getApiKey();
		if($api_key)
		{
			$this->params['X-API-KEY'] = $api_key;
		}

		if(count($this->params))
		{
			$this->queryString =  http_build_query($this->params, NULL, '&');
			curl_setopt($this->ch, CURLOPT_POSTFIELDS, $this->queryString);
		}

		return $this;
	}

	/**
	 * GET 요청을 보냄
	 * @access public
	 */
	public function get($method)
	{
		$this->method = $method;
		$this->addOption('post', FALSE);

		return $this;
	}

	/**
	 * POST 요청을 보냄
	 * @access public
	 */
	public function post($method)
	{
		$this->method = $method;
		$this->addOption('post', TRUE);

		curl_setopt($this->ch, CURLOPT_POST, TRUE);
		curl_setopt($this->ch, CURLOPT_CUSTOMREQUEST, 'POST');

		return $this;
	}

	/**
	 * API 요청을 보냄
	 * @access public
	 */
	public function send()
	{
		$url = $this->getServer() . $this->method . '?format=json';

		$this->httpHeader = array(
			'Content-Type : application/json',
			'Content-Length : ' . strlen($this->queryString),
			'Accept : application/json'
		);

		curl_setopt($this->ch, CURLOPT_URL, $url);
		curl_setopt($this->ch, CURLOPT_USERAGENT, 'XpressEngine Nowconnect Module Communicator');
		curl_setopt($this->ch, CURLOPT_HTTPHEADER, $this->httpHeader);
		curl_setopt($this->ch, CURLOPT_CONNECTTIMEOUT, 1);
		curl_setopt($this->ch, CURLOPT_TIMEOUT, 1);
		curl_setopt($this->ch, CURLOPT_REFERER, getNotEncodedFullUrl());

		$this->buffer = curl_exec($this->ch);

		$headerSeparator = PHP_EOL . PHP_EOL;
		list($header, $data) = explode($headerSeparator, $this->buffer, 2);

		$this->httpCode = curl_getinfo($this->ch, CURLINFO_HTTP_CODE);

		// Redirection이 발생한 경우 Redirection된 주소로 한 번 더 요청을 보냄
		if ($this->httpCode == 301 || $this->httpCode == 302)
		{
			curl_close($this->ch);
			preg_match('/Location: (.*?)\n/', $header, $matches);

			$url = $matches[1];

			$this->ch = curl_init();
			curl_setopt($this->ch, CURLOPT_URL, $url);
			curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, TRUE);
			curl_setopt($this->ch, CURLOPT_USERAGENT, 'XpressEngine Nowconnect Module Communicator v0.3.4');
			curl_setopt($this->ch, CURLOPT_HTTPHEADER, $this->httpHeader);

			if(is_array($this->params) && count($this->params))
			{
				curl_setopt($ch, CURLOPT_POSTFIELDS, $queryString);
			}

			if(isset($this->options['auth']))
			{
				curl_setopt($this->ch, CURLOPT_USERPWD, $this->options['auth']);
			}

			$this->buffer = curl_exec($this->ch);
			$this->httpCode = curl_getinfo($this->ch, CURLINFO_HTTP_CODE);
		}

		curl_close($this->ch);

		if(!$this->buffer)
		{
			return $this;
		}

		$this->result = new ApiResult($this->buffer);

		return $this;
	}

	public function buffer()
	{
		return $this->buffer;
	}
	public function result()
	{
		return $this->result;
	}

	public function getResult()
	{
		return $this->result;
	}

	/**
	 * HTTP 응답 코드를 반환합니다
	 */
	public function getHttpCode()
	{
		return (int)$this->httpCode;
	}
}