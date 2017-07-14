<?php
class Pleesher_GetUserPageOutputAction extends Pleesher_Action
{
	public function getAllowedParams()
	{
		return array_merge(parent::getAllowedParams(), [
			'username' => null
		]);
	}

	protected function doExecute()
	{
		$user_name = $this->getParameter('username');

		$output = '';
		try {
			$achievement_count = count(PleesherExtension::getAchievements($user_name)) ?: 0;
			$showcased_achievement_count = count(PleesherExtension::getShowcasedAchievements($user_name));
			$goal_count = count(PleesherExtension::$goal_data);

			$user = PleesherExtension::getUser($user_name);
			if (!is_object($user))
				return;

			if (!empty($output))
				$output .= PHP_EOL . PHP_EOL;

			$output .= PleesherExtension::render('user', array_merge(PleesherExtension::$implementation->getUserPageData($user), [
				'user' => $user,
				'closest_achievements' => PleesherExtension::getClosestAchievements($user_name, 3),
				'achievement_count' => $achievement_count,
				'showcased_achievement_count' => $showcased_achievement_count,
				'goal_count' => $goal_count
			]));

		} catch (Exception $e)
		{
			if (!empty($output))
				$output .= PHP_EOL . PHP_EOL;

			if ($e instanceof PleesherDisabledException)
				$output .= PleesherExtension::render('disabled');
			else
				$output .= PleesherExtension::render('error', ['error_message' => PleesherExtension::$view_helper->text('pleesher.error.text.' . ($e->getErrorCode() ?: 'generic'), $e->getErrorParameters() ?: [])]);
		}

		$this->getResult()->addValue(null, 'output', $output);
	}
}