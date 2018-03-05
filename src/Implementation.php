<?php
abstract class PleesherImplementation
{
	public function isExtensionEnabled(User $user = null)
	{
		if (is_null($user) && isset($GLOBALS['wgUser']))
			$user = $GLOBALS['wgUser'];

		if (isset($GLOBALS['wgPleesherAccessChecker']) && is_callable($GLOBALS['wgPleesherAccessChecker']))
			return $GLOBALS['wgPleesherAccessChecker']($user);

		return true;
	}

	public abstract function getGoalData();

	public abstract function getI18nPrefix();

	public function getGoalCategories()
	{
		return [];
	}

	public function getLogger()
	{
		$logger = new \Monolog\Logger('pleesher');
		$logger->pushHandler(new \Monolog\Handler\RotatingFileHandler(__DIR__ . '/logs/warning.log', 0, \Monolog\Logger::WARNING));
		$logger->pushHandler(new \Monolog\Handler\RotatingFileHandler(__DIR__ . '/logs/error.log', 0, \Monolog\Logger::ERROR));

		return $logger;
	}

	public function getGoalCheckingContext()
	{
		return [];
	}

	public function fillUser(User $user)
	{
		return $user;
	}

	public function fillGoal($goal)
	{
		return $goal;
	}

	public function getUserPageData(User $user)
	{
		return [];
	}

	public function getViewsFolder()
	{
		return null;
	}
}