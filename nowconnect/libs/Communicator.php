<?php
/**
 * @brief API 서버와 통신하기 위한 모듈
 */
class CommunicatorBase extends ApiBase
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
	private $result = NULL; // 파싱 결과값
	private $httpCode;

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
	}

	/**
	 * GET 요청을 보냄
	 * @access public
	 */
	public function get($method, $params = array(), $options = array())
	{
		// 옵션을 입력하지 않았을 때, 에러가 나지 않도록 초기화함
		if(!is_array($options))
		{
			$options = array();
		}

		$options['post'] = FALSE;

		return $this->_send($method, $params, $options);
	}

	/**
	 * POST 요청을 보냄
	 * @access public
	 */
	public function post($method, $params = array(), $options = array())
	{
		// 옵션을 입력하지 않았을 때, 에러가 나지 않도록 초기화함
		if(!is_array($options))
		{
			$options = array();
		}

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

		$ch = curl_init();

		if(is_object($params))
		{
			$params = (array)$params;
		}

		if(!is_array($params))
		{
			$params = array();
		}

		$queryString =  http_build_query($params, NULL, '&');

		$httpHeader = array(
			'Content-Type : ' . $this->mime_type,
			'Content-Length : ' . strlen($queryString),
			'Accept : ' . $this->mime_type
		);
 
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);

		if(isset($options['post']) && $options['post'] == TRUE)
		{
			curl_setopt($ch, CURLOPT_POST, TRUE);
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
		}

		if(isset($options['put']) && $options['put'] == TRUE)
		{
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
			array_push($httpHeader, 'X-HTTP-Method-Override: PUT');
		}

		if(is_array($params) && count($params))
		{
			curl_setopt($ch, CURLOPT_POSTFIELDS, $queryString);
		}

		if($this->format)
		{
			$url .= '?format=' . $this->format;
		}

		curl_setopt($ch, CURLOPT_URL, $url);

		if(isset($options['auth']))
		{
			curl_setopt($ch, CURLOPT_USERPWD, $options['auth']);
		}

		curl_setopt($ch, CURLOPT_USERAGENT, 'XpressEngine Nowconnect Module Communicator');
		curl_setopt($ch, CURLOPT_HTTPHEADER, $httpHeader);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);
		curl_setopt($ch, CURLOPT_TIMEOUT, 3);

		$this->buffer = curl_exec($ch);

		list($header, $data) = explode("\n\n", $this->buffer, 2);
		$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

		// Redirection이 발생한 경우 Redirection된 주소로 한 번 더 요청을 보냄
		if ($http_code == 301 || $http_code == 302)
		{
			curl_close($ch);
			preg_match('/Location: (.*?)\n/', $header, $matches);

			$url = $matches[1];

			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
			curl_setopt($ch, CURLOPT_USERAGENT, 'XpressEngine Nowconnect Module Communicator');
			curl_setopt($ch, CURLOPT_HTTPHEADER, $httpHeader);

			if(is_array($params) && count($params))
			{
				curl_setopt($ch, CURLOPT_POSTFIELDS, $queryString);
			}

			if(isset($options['auth']))
			{
				curl_setopt($ch, CURLOPT_USERPWD, $options['auth']);
			}

			$this->buffer = curl_exec($ch);
		}


		$this->httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

		curl_close($ch);

		if(!$this->buffer)
		{
			return $this;
		}

		switch($this->format)
		{
			case 'json':
				$this->result = json_decode($this->buffer);
				break;
			case 'xml':
				$xml = simplexml_load_string($this->buffer);
				$this->result = $xml->children();
				break;
			case 'csv':
				break;
		}

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