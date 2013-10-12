<?php
class ApiResult
{
	private $format;
	private $buffer;
	public $result;

	function ApiResult($format, $content)
	{
		if(!$format || !$content)
		{
			return FALSE;
		}

		$this->format = $format;
		$this->buffer = $content;

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

		unset($this->buffer);
		unset($xml);
	}
}