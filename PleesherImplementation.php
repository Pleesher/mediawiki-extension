<?php
abstract class PleesherImplementation
{
	public abstract function getGoalData();

	public function getLogger()
	{
		$logger = new \Monolog\Logger('debug');
		$logger->pushHandler(new \Monolog\Handler\RotatingFileHandler(__DIR__ . '/logs/debug.log', \Monolog\Logger::DEBUG));

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