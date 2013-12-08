<?php
class ApiResult
{
	private $buffer;
	public $result;

	public function ApiResult($content)
	{
		if(!$content)
		{
			return FALSE;
		}

		$this->result = json_decode($content);
	}
}