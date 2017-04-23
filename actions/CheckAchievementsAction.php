<?php
class Pleesher_CheckAchievementsAction extends Pleesher_Action
{
	protected function doExecute()
	{
		PleesherExtension::$pleesher->checkAchievementsQueued($this->getUser()->getId());
		$this->getResult()->addValue(null, 'success', 1);
	}
}