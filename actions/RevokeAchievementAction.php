<?php
class Pleesher_RevokeAchievementAction extends ApiBase
{
	public function getAllowedParams()
	{
		return array_merge(parent::getAllowedParams(), [
			'user_id' => null,
			'goal_id' => null,
			'duration' => 'this_time'
		]);
	}

	public function execute()
	{
		if (!$this->getUser()->isAllowed(PleesherExtension::ADMIN_RIGHT))
		{
			$this->getResult()->addValue(null, 'success', 0);
			return;
		}

		// FIXME: check parameters
		$user_id = (int)$this->getParameter('user_id');
		$goal_id = (int)$this->getParameter('goal_id');
		$duration = $this->getParameter('duration');

		$result = PleesherExtension::$pleesher->revoke($user_id, $goal_id, ['duration' => $duration]);

		// TODO: maybe we could remove all goal data and not just 'showcased', but maybe we also want to keep some of the future data, so not sure
		if ($result)
			PleesherExtension::$pleesher->deleteObjectData('goal', $goal_id, $this->getUser()->getId(), 'showcased');

		$this->getResult()->addValue(null, 'success', $result ? 1 : 0);
	}
}
