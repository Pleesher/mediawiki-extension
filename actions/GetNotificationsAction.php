<?php
class Pleesher_GetNotificationsAction extends ApiBase
{
	public function execute()
	{
		$pleesher_notifications = PleesherExtension::$pleesher->getNotifications($this->getUser()->getId());
		$this->getResult()->addValue(null, 'notifications', $pleesher_notifications);
	}
}
