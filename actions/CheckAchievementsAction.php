<?php
class Pleesher_CheckAchievementsAction extends Pleesher_Action
{
	protected function doExecute()
	{
		PleesherExtension::$pleesher->checkAchievementsQueued($this->getUser()->getName());
		$this->getResult()->addValue(null, 'success', 1);
	}
}