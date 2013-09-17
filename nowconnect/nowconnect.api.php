<?php
/**
 * @class  nowconnectAPI
 * @author 퍼니엑스이(admin@funnyxe.com)
 * @brief  nowconnect module View Action에 대한 API 처리
 **/

class nowconnectAPI extends nowconnect
{

	/**
	 * @brief notice list
	 **/
	function dispNowconnect(&$oModule)
	{
		$module_info = $oModule->module_info;
		if($module_info->skin)
		{
			$templatePath = (sprintf($this->module_path.'skins/%s', $module_info->skin));
		}
		else
		{
			$templatePath = ($this->module_path.'skins/default');
		}

		if(!$module_info->list_count)
		{
			$module_info->list_count = 30;
		}

		if(!$module_info->page_count)
		{
			$module_info->page_count = 10;
		}


		// 중복 접속자 처리
		if($module_info->include_duplicated_user == 'Y')
		{
			$uid = session_id();
		}
		else
		{
			$uid = sha1(md5($_SERVER['REMOTE_ADDR']));
		}

		Context::set('myUID', $uid);
		Context::set('module_info', $module_info);
		Context::set('from_ajax', true);
		Context::set('myUID', $uid);

		$oTemplateHandler = TemplateHandler::getInstance();

		$html = $oTemplateHandler->compile($templatePath, '_nowconnect.list');

		$oModule->add('html', $html);
	}
}