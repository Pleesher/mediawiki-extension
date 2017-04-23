<?php
class Pleesher_GetNotificationsAction extends Pleesher_Action
{
	protected function doExecute()
	{
		$pleesher_notifications = PleesherExtension::$pleesher->getNotifications($this->getUser()->getId());
		$this->getResult()->addValue(null, 'notifications', $pleesher_notifications);
	}
}
