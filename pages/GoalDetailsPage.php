<?php
class Pleesher_GoalDetailsPage extends Pleesher_SpecialPage
{
	public function __construct()
	{
		parent::__construct('AchievementDetails');
	}

	function getGroupName() {
		return 'pleesher';
	}

	public function execute($subPage)
	{
		if (empty($subPage))
			return $this->getOutput()->redirect(PleesherExtension::$view_helper->pageUrl('Special:Achievements'));

		$this->setHeaders();
		$this->checkPermissions();
		$this->checkReadOnly();
		$this->outputHeader();

		$goal_id_or_code = preg_replace('/^(\d+)_(.*)$/', '\1', $subPage);

		$user = $this->getUser();
		$user_name = $user->getName();

		$goal = PleesherExtension::getGoal($goal_id_or_code, ['user_id' => $user->isLoggedIn() ? $user_name : null]);
		if (!is_object($goal))
		{
			header('Location: ' . PleesherExtension::$view_helper->pageUrl('Special:AchievementsError', true));
			die;
		}

		$achievers = PleesherExtension::getAchievers($goal_id_or_code);
		$html = PleesherExtension::render('goal_details', [
			'user' => $user,
			'goal' => $goal,
			'achievers' => $achievers,
			'canonical_url' => PleesherExtension::$view_helper->pageUrl('Special:AchievementDetails/' . PleesherExtension::$view_helper->slugifyUrlId($goal->id, $goal->title))
		]);

		$this->getOutput()->setPageTitle(PleesherExtension::$view_helper->text('pleesher.goal_details.title', [$goal->title]));
		$this->getOutput()->addHTML($html);

		return null;
	}
}
