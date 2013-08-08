<?php
/**
 * @brief API 서버와 통신하기 위한 API 클래스
 */
class ApiBase
{
	var $api_server; // << API 서버 주소
	var $api_key; // < API 키

	/**
	 * @brief API 서버 지정
	 */
	function setServer($server_name)
	{
		$this->api_server = $server_name;
	}

	/**
	 * @brief API 키 지정
	 */
	function setApiKey($api_key)
	{
		$this->api_key = $api_key;
	}

	function getServer()
	{
		return $this->api_server;
	}
}