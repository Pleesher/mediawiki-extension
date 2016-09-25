<?php
class Pleesher_RevokeAchievementAction extends ApiBase
{
	public function getAllowedParams()
	{
		return array_merge(parent::getAllowedParams(), [
			'user_id' => null,
			'goal_id' => null
		]);
	}

	public function execute()
	{
		// FIXME: check and shit
		$user_id = (int)$this->getParameter('user_id');
		$goal_id = (int)$this->getParameter('goal_id');

		$pleesher_notifications = PleesherExtension::$pleesher->revoke($user_id, $goal_id);
		$this->getResult()->addValue(null, 'success', 1);

		return true;
	}
}
