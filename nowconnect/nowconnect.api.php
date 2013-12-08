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
			$uid = sha1(md5(session_id()));
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

		// XE 1.7.4 beta 2부터 포인트 레벨 애드온이 작동하지 않아서 별도로 레벨 아이콘을 처리하도록 하였습니다.
		if(version_compare(__XE_VERSION__, '1.7.4-beta.2', '>='))
		{
			// addonAdminModel 객체 생성
			$oAddonModel = getAdminModel('addon');

			// 포인트 레벨 애드온이 활성화 되어 있으면 레벨 아이콘을 추가합니다
			if($oAddonModel->isActivatedAddon('point_level_icon'))
			{
				require_once(_XE_PATH_ . 'addons/point_level_icon/point_level_icon.lib.php');

				$temp_output = preg_replace_callback('!<(div|span|a)([^\>]*)member_([0-9\-]+)([^\>]*)>(.*?)\<\/(div|span|a)\>!is', 'pointLevelIconTrans', $html);
				if($temp_output)
				{
					$html = $temp_output;
				}
				unset($temp_output);
			}
		}

		$oModule->add('html', $html);
	}
}