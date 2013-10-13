<?php

if(!defined('__ZBXE__') && defined('__XE__')) exit();

/**
* @file nowconnect_user_count.addon.php
* @author 퍼니엑스이 (admin@funnyxe.com)
* @brief 현재 접속자 수 출력 애드온
**/

// HTML 요청 시에만 출력할 수 있도록 함
if(Context::getResponseMethod()!='HTML') return;

// 관리자 페이지 제외
if(Context::get('module') == 'admin') return;

if($called_position != 'before_display_content') return;

$oNowconnectModel = getModel('nowconnect');
if($oNowconnectModel)
{
	if(strpos($output, '[#CONNECT_USER#]') !== FALSE)
	{
		$args = new stdClass;
		$userCount = $oNowconnectModel->getNowconnectedUserCount($args);
		$output = str_replace('[#CONNECT_USER#]', $userCount, $output);
	}

	if(strpos($output, '[#CONNECT_MEMBER#]') !== FALSE)
	{
		$args = new stdClass;
		$args->nowconnect_target = 'member';
		$userCount = $oNowconnectModel->getNowconnectedUserCount($args);
		$output = str_replace('[#CONNECT_MEMBER#]', $userCount, $output);
	}

	if(strpos($output, '[#CONNECT_USER_EXCEPT_ADMIN#]') !== FALSE)
	{
		$args = new stdClass;
		$args->excludeAdmin = 'Y';
		$userCount = $oNowconnectModel->getNowconnectedUserCount($args);
		$output = str_replace('[#CONNECT_USER_EXCEPT_ADMIN#]', $userCount, $output);
	}

	$use_realtime = ($addon_info->use_realtime == 'Y') && FALSE;
	if($use_realtime)
	{
		$selector = $addon_info->css_selector = addslashes(trim($addon_info->css_selector));
		$duration = (int)$addon_info->duration;
		if($duration >= 1000)
		{
			Context::addHtmlFooter(sprintf('<script type="text/javascript">(function($){ $(document).ready(function(){ refresh_nowconnect_user_count(\'%s\', %s); }); })(jQuery);', $selector, $duration));
		}
	}
}