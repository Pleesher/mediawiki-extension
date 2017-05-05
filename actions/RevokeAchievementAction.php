<?php
class Pleesher_RevokeAchievementAction extends Pleesher_AdminAction
{
	public function getAllowedParams()
	{
		return array_merge(parent::getAllowedParams(), [
			'user_name' => null,
			'goal_id' => null,
			'duration' => 'this_time'
		]);
	}

	protected function doExecute()
	{
		// FIXME: check parameters
		$user_name = $this->getParameter('user_name');
		$goal_id = (int)$this->getParameter('goal_id');
		$duration = $this->getParameter('duration');

		$result = PleesherExtension::$pleesher->revoke($user_name, $goal_id, ['duration' => $duration]);

		// TODO: maybe we could remove all goal data and not just 'showcased', but maybe we also want to keep some of the future data, so not sure
		if ($result)
			PleesherExtension::$pleesher->deleteObjectData('goal', $goal_id, $this->getUser()->getName(), 'showcased');

		$this->getResult()->addValue(null, 'success', $result ? 1 : 0);
	}
}
