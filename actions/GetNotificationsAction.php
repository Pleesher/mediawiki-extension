<?php
class Pleesher_GetNotificationsAction extends Pleesher_Action
{
	protected function doExecute()
	{
		// FIXME: filter out notifications associated with goals that no longer exist (not in data/goals.php) -> add a PleesherExtension::getNotifications probably
		$pleesher_notifications = PleesherExtension::$pleesher->getNotifications($this->getUser()->getName());
		$this->getResult()->addValue(null, 'notifications', $pleesher_notifications);
	}
}
