<?php
/**
 * @brief API 서버와 통신하기 위한 모듈
 */
class CommunicatorBase extends ApiServer
{
	private $format = 'xml'; // << API 요청 타입 (xml, json, csv)
	protected $supported_formats = array(
		'xml' 				=> 'application/xml',
		'json' 				=> 'application/json',
		'serialize' 		=> 'application/vnd.php.serialized',
		'php' 				=> 'text/plain',
		'csv'				=> 'text/csv'
	);

    protected $auto_detect_formats = array(
		'application/xml' 	=> 'xml',
		'text/xml' 			=> 'xml',
		'application/json' 	=> 'json',
		'text/json' 		=> 'json',
		'text/csv' 			=> 'csv',
		'application/csv' 	=> 'csv',
		'application/vnd.php.serialized' => 'serialize'
	);
	protected $buffer = NULL; // API 요청 결과값
	protected $options = array();
	private $result = NULL; // 파싱 결과값
	private $httpHeader = array();
	private $httpCode;
	private $ch;
	private $method;

	/**
	 * Constructor
	 */
	public function __construct($format = NULL)
	{
		if (array_key_exists($format, $this->supported_formats))
		{
			$this->format = $format;
			$this->mime_type = $this->supported_formats[$format];
		}
		else
		{
			$this->mime_type = $format;
		}

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

	public function setOption($options = array())
	{
		$this->options = $options;
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

	public function send()
	{

	}

	/**
	 * GET 요청을 보냄
	 * @access public
	 */
	public function get($method, $params = array(), $options = array())
	{
		$options['post'] = FALSE;

		return $this->_send($method, $params, $options);
	}

	/**
	 * POST 요청을 보냄
	 * @access public
	 */
	public function post($method, $params = array(), $options = array())
	{
		$this->options['post'] = TRUE;

		curl_setopt($this->ch, CURLOPT_POST, TRUE);
		curl_setopt($this->ch, CURLOPT_CUSTOMREQUEST, 'POST');

		$options['post'] = TRUE;

		return $this->_send($method, $params, $options);
	}

	/**
	 * API 요청을 보냄
	 * @access protected
	 */
	protected function _send($method, $params = array(), $options = array())
	{
		$url = $this->getServer() . $method;

		if(is_object($params))
		{
			$params = (array)$params;
		}

		if(!is_array($params))
		{
			$params = array();
		}

		if(parent::getApiKey())
		{
			$params['X-API-KEY'] = parent::getApiKey();
		}

		$queryString =  http_build_query($params, NULL, '&');

		$this->httpHeader = array(
			'Content-Type : ' . $this->mime_type,
			'Content-Length : ' . strlen($queryString),
			'Accept : ' . $this->mime_type
		);
 
		if(is_array($params) && count($params))
		{
			curl_setopt($this->ch, CURLOPT_POSTFIELDS, $queryString);
		}

		if($this->format)
		{
			$url .= '?format=' . $this->format;
		}

		curl_setopt($this->ch, CURLOPT_URL, $url);

		if(isset($options['auth']))
		{
			curl_setopt($this->ch, CURLOPT_USERPWD, $options['auth']);
		}

		curl_setopt($this->ch, CURLOPT_USERAGENT, 'XpressEngine Nowconnect Module Communicator');
		curl_setopt($this->ch, CURLOPT_HTTPHEADER, $this->httpHeader);
		curl_setopt($this->ch, CURLOPT_CONNECTTIMEOUT, 3);
		curl_setopt($this->ch, CURLOPT_TIMEOUT, 3);
		curl_setopt($this->ch, CURLOPT_REFERER, getNotEncodedFullUrl());

		$this->buffer = curl_exec($this->ch);

		list($header, $data) = explode("\n\n", $this->buffer, 2);
		$http_code = curl_getinfo($this->ch, CURLINFO_HTTP_CODE);

		// Redirection이 발생한 경우 Redirection된 주소로 한 번 더 요청을 보냄
		if ($http_code == 301 || $http_code == 302)
		{
			curl_close($this->ch);
			preg_match('/Location: (.*?)\n/', $header, $matches);

			$url = $matches[1];

			$this->ch = curl_init();
			curl_setopt($this->ch, CURLOPT_URL, $url);
			curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, TRUE);
			curl_setopt($this->ch, CURLOPT_USERAGENT, 'XpressEngine Nowconnect Module Communicator');
			curl_setopt($this->ch, CURLOPT_HTTPHEADER, $this->httpHeader);

			if(is_array($params) && count($params))
			{
				curl_setopt($ch, CURLOPT_POSTFIELDS, $queryString);
			}

			if(isset($options['auth']))
			{
				curl_setopt($this->ch, CURLOPT_USERPWD, $options['auth']);
			}

			$this->buffer = curl_exec($this->ch);
		}


		$this->httpCode = curl_getinfo($this->ch, CURLINFO_HTTP_CODE);

		curl_close($this->ch);

		if(!$this->buffer)
		{
			return $this;
		}

		$this->result = new ApiResult($this->format, $this->buffer);

		return $this;
	}

	public function flush()
	{
		$this->buffer = NULL;
		$this->result = NULL;
	}

	public function getBuffer()
	{
		return $this->buffer;
	}

	public function getResult()
	{
		return $this->result;
	}

	public function getHttpCode()
	{
		return $this->httpCode;
	}
}