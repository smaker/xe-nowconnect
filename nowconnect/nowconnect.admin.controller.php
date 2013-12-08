<?php
/**
 * @class nowconnectAdminController
 * @author 퍼니엑스이 (admin@funnyxe.com)
 * @brief nowconnect 모듈의 admin controller class
 **/

class nowconnectAdminController extends nowconnect
{
	/**
	 * @brief 초기화
	 */
	function init()
	{
	}

	/**
	 * 현재 접속자 모듈 생성
	 */
	function procNowconnectAdminCreate()
	{
		$output = executeQuery('nowconnect.getNowconnect');
		$module_info = $output->data;
		if($module_info)
		{
			return new Object(-1,'msg_invalid_request');
		}

		$oModuleController = getController('module');

		/**
		 * @TODO : 모듈 생성 전에 API 키를 확인할 것
		 */
		$api_key = Context::get('api_key');
		if(!$api_key)
		{
			return new Object(-1, 'msg_api_key_required');
		}

		$args->module = 'nowconnect';
		$args->mid = Context::get('nowconnect_name');
		$args->module_category_srl = Context::get('module_category_srl');
		$args->layout_srl = Context::get('layout_srl');
		$args->site_srl = 0;
		$args->skin = 'default';
		$args->browser_title = Context::get('browser_title');
		$args->header_text = Context::get('header_text');
		$output = $oModuleController->insertModule($args);
		if(!$output->toBool()) return $output;

		$returnUrl = Context::get('success_return_url') ? Context::get('success_return_url') : getNotEncodedUrl('', 'module', 'admin', 'act','dispNowconnectAdminModuleConfig');
		$this->setRedirectUrl($returnUrl);
	}


	function procNowconnectAdminUpdate()
	{
		$oModuleController = &getController('module');

		$output = executeQuery('nowconnect.getNowconnect');
		$module_info = $output->data;
		if(!$module_info) return new Object(-1,'msg_invalid_request');

		$args = Context::getRequestVars();
		$args->mid = $args->nowconnect_name;
		$args->module = 'nowconnect';
		$args->site_srl = 0;
		unset($args->act);
		unset($args->ruleset);
		unset($args->nowconnect_name);

		$output = $oModuleController->updateModule($args);
		if(!$output->toBool()) return $output;

		$returnUrl = Context::get('success_return_url') ? Context::get('success_return_url') : getNotEncodedUrl('', 'module', 'admin', 'act','dispNowconnectAdminModuleConfig');
		$this->setRedirectUrl($returnUrl);
	}

	function procNowconnectAdminInsertConfig()
	{
		$config = Context::gets('exclude_admin');
		$oModuleController = &getController('module');
		$oModuleController->insertModuleConfig('nowconnect', $config);

		$this->setMessage('success_saved');

		$this->setRedirectUrl(Context::get('success_return_url') ? Context::get('success_return_url') : getNotEncodedUrl('act', 'dispNowconnectAdminGlobalConfig'));
	}
}

/* End of file : nowconnect.admin.controller.php */
/* Location : ./modules/nowconnect/nowconnect.admin.controller.php */