<?php
class Pleesher_RefreshCacheAction extends Pleesher_AdminAction
{
	public function getAllowedParams()
	{
		return array_merge(parent::getAllowedParams(), [
			'user_name' => null,
			'refresh' => null
		]);
	}

	protected function doExecute()
	{
		// FIXME: handle non-global cache refresh
		// FIXME: check parameters
		$user_name = $this->getParameter('user_name');
		$refresh_type = $this->getParameter('refresh');

		switch ($refresh_type)
		{
			case 'goals':
				$keys = ['goal'];
				break;
			case 'achievements':
				$keys = ['goal_relative_to_user', 'user', 'achievers_of_*', 'participations_*'];
				break;
			case 'users':
				$keys = ['user'];
				break;
			case 'all':
				$keys = null;
				break;
			default:
				$this->getResult()->addValue(null, 'success', 0);
				return;
		}

		if (isset($user_name))
		{
			PleesherExtension::$pleesher->refreshAllCache($user_name, $keys);
			if (is_null($keys))
			{
				PleesherExtension::$pleesher->refreshCache(null, 'user', $user_name);
				PleesherExtension::$pleesher->getGoals(['user_id' => $user_name, 'auto_award' => true, 'auto_revoke' => true]);
			}
		}
		else
			PleesherExtension::$pleesher->refreshCacheGlobally($keys);

		if (is_null($keys) || in_array('goal_relative_to_user', $keys))
		{
			foreach (PleesherExtension::getUsers() as $user)
				PleesherExtension::$pleesher->getGoals(['user_id' => $user->getName(), 'auto_award' => true, 'auto_revoke' => true]);
		}

		$this->getResult()->addValue(null, 'success', 1);
	}
}
