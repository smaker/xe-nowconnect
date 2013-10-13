<?php
/**
 * @class nowconnect_users 
 * @author 퍼니엑스이 (admin@funnyxe.com)
 * @brief 사이트에 접속한 사용자의 목록을 출력합니다.
 * @version 0.1
 **/

class nowconnect_users
{

	/**
	 * @brief 위젯의 실행 부분
	 *
	 * ./widgets/위젯/conf/info.xml 에 선언한 extra_vars를 args로 받는다
	 * 결과를 만든후 print가 아니라 return 해주어야 한다
	 **/
	function proc($args)
	{
		$list_count = (int)$args->list_count;
		if(!$list_count) $list_count = 20;

		$page_count = (int)$args->page_count;
		if(!$page_count) $page_count = 10;

		$exclude_admin = $args->exclude_admin;
		if(!$exclude_admin) $exclude_admin = 'N';

		$show_user_count = $args->show_user_count;
		$show_user_count_list = array('Y' => 1, 'N' => 1);
		if(!isset($show_user_count_list[$show_user_count]))
		{
			$show_user_count = 'N';
		}

		$only_member = $args->only_member;
		$only_member_list = array('Y' => 1, 'N' => 1);
		if(!isset($only_member_list[$only_member]))
		{
			$only_member = 'N';
		}

		$obj->list_count = $list_count * $page_count;
		$obj->mid = $mid;
		$obj->exclude_admin = $exclude_admin;

		if($only_member == 'Y')
		{
			$obj->nowconnect_target = 'member';
		}

		// nowconnectModel 객체 생성
		$oNowconnectModel = getModel('nowconnect');
		$output = $oNowconnectModel->getConnectedUsers($obj, FALSE);
		$widget_info->user_list = $output->result->users;
		$widget_info->user_count = $output->result->totalCount;
		$widget_info->list_count = $list_count;
		$widget_info->page_count = $page_count;
		$widget_info->show_user_count = $show_user_count;
		$widget_info->only_member = $only_member;
		Context::set('widget_info', $widget_info);

		// 템플릿의 스킨 경로를 지정 (skin, colorset에 따른 값을 설정)
		$tpl_path = sprintf('%sskins/%s', $this->widget_path, $args->skin);
		Context::set('colorset', $args->colorset);

		// 템플릿 컴파일
		$oTemplate = TemplateHandler::getInstance();
		return $oTemplate->compile($tpl_path, 'list');
	}
}