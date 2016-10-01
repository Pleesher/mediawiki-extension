<?php
use Pleesher\Client\Client;
use Pleesher\Client\Cache\LocalStorage;
use Pleesher\Client\Cache\DatabaseStorage;
use MediaWiki\Auth\AuthManager;

class PleesherExtension
{
	/**
	 * @var \Pleesher\Client\Client
	 */
	public static $pdo;
	public static $pleesher;
	public static $goal_data;
	public static $implementation;

	public static function getConfigValue($key, $default = null)
	{
		return isset($GLOBALS['wg' . get_called_class() . $key]) ? $GLOBALS['wg' . get_called_class() . $key] : $default;
	}

	public static function setImplementation(\PleesherImplementation $implementation)
	{
		self::$implementation = $implementation;
	}

	public static function initialize()
	{
		if (!isset(self::$implementation))
			throw new \Exception('PleesherExtension::setImplementation must be called before PleesherExtension is loaded');

		require_once __DIR__ . '/vendor/autoload.php';
		self::$pleesher = new Client($GLOBALS['wgPleesherClientId'], $GLOBALS['wgPleesherClientSecret']);

		self::$pdo = new \PDO($GLOBALS['wgDBtype'] . ':host=' . $GLOBALS['wgDBserver'] . ';dbname=' . $GLOBALS['wgDBname'], $GLOBALS['wgDBuser'], $GLOBALS['wgDBpassword']);
		self::$pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
		self::$pleesher->setCacheStorage(new LocalStorage(new DatabaseStorage(self::$pdo, 'pleesher_cache')));

		$logger = self::$implementation->getLogger();
		if (!is_null($logger))
			self::$pleesher->setLogger($logger);

		self::$goal_data = array_map(function($goal) { return (object)$goal; }, self::$implementation->getGoalData());
		foreach (self::$goal_data as $key => $goal)
			$goal->key = $key;

		foreach (self::$goal_data as $goal_code => $goal) {
			self::$pleesher->bindGoalChecker($goal_code, $goal->checker, array_merge(self::$implementation->getGoalCheckingContext(), [
				'pdo' => self::$pdo
			]));
		}
	}

	public static function initializeParser(Parser $parser)
	{
		$parser->setHook('Goal', 'PleesherExtension::viewGoal');
		$parser->setHook('AchievementList', 'PleesherExtension::viewAchievements');
		$parser->setHook('UserKudos', 'PleesherExtension::viewUserKudos');
	}

	public static function beforePageDisplay(OutputPage &$out, Skin &$skin)
	{
		$out->addModules('pleesher');
		$out->addModules('pleesher.notifications');

		// using $out->addModules('toastr') fails, for some reason
		$out->addModuleScripts('toastr');
		$out->addModuleStyles('toastr');
	}

	public static function parserBeforeStrip(&$parser, &$text, &$strip_state)
	{
		$title = $parser->getTitle();

		if ($title->getNamespace() == NS_USER) {
			$article = WikiPage::factory($title);

			if ($article->getText(Revision::FOR_PUBLIC) == $text)
			{
				$user_id = User::idFromName($title->getText());
				$user = PleesherExtension::getUser($user_id);

				$text .= PHP_EOL . PHP_EOL . self::render('user.wiki', array_merge(self::$implementation->getUserPageData($user), [
					'user' => $user
				]));
			}
		}

		return true;
	}

	public static function pageContentSaveComplete($article, $user, $content, $summary, $isMinor, $isWatch, $section, $flags, $revision, $status, $baseRevId)
	{
		if ($user->isLoggedIn()) {
			self::$pleesher->checkAchievements($user->getId());
		}
	}

	public static function viewGoal($input, array $args, Parser $parser, PPFrame $frame)
	{
		$goal_code = $args['code'];
		$user_name = isset($args['perspective']) ? $args['perspective'] : null;
		$user_id = !is_null($user_name) ? User::idFromName($user_name) : null;

		$goal = self::$pleesher->getGoal($goal_code, ['user_id' => $user_id]);
		if (!is_object($goal))
			return '';

		return self::render('goal', [
			'goal' => self::$implementation->fillGoal($goal)
		]);
	}

	public static function viewUserKudos($input, array $args, Parser $parser, PPFrame $frame)
	{
		$username = $args['user'];
		$user_id = User::idFromName($username);

		if ($user_id == 0)
		{
			self::$pleesher->logger->error(sprintf('No such wiki user: %s (%s)', $username, $_SERVER['REQUEST_URI']));
			return 0;
		}

		$user = self::$pleesher->getUser($user_id);
		return $user->kudos;
	}

	public static function viewAchievements($input, array $args, Parser $parser, PPFrame $frame)
	{
		$user_name = $args['user'];
		$user_id = User::idFromName($user_name);

		$user = self::getUser($user_id);
		$achievements = self::getAchievements($user_id);

		$actions = ['revoke'];

		return self::render('goals', [
			'user' => $user,
			'goals' => $achievements,
			'actions' => $actions
		]);
	}

	public static function getUsers(array $options = [])
	{
		$pleesher_users = self::$pleesher->getUsers($options);

		$users = array_map(function($pleesher_user) {
			$wiki_user = User::newFromId($pleesher_user->id);
			if (!AuthManager::singleton()->userExists($wiki_user->getName()))
				return null;
			$wiki_user->kudos = $pleesher_user->kudos;
			return $wiki_user;
		}, $pleesher_users);

		$users = array_filter($users, function(User $user = null) {
			return !is_null($user);
		});

		return array_map([self::$implementation, 'fillUser'], $users);
	}

	public static function getUser($user_id)
	{
		$pleesher_user = self::$pleesher->getUser($user_id);

		$wiki_user = User::newFromId($pleesher_user->id);
		if (!AuthManager::singleton()->userExists($wiki_user->getName()))
			return null;

		$wiki_user->kudos = $pleesher_user->kudos;

		return self::$implementation->fillUser($wiki_user);
	}

	public static function getGoals(array $options = [])
	{
		$goals = self::$pleesher->getGoals($options);
		return array_map([self::$implementation, 'fillGoal'], $goals);
	}

	public static function getAchievements($user_id)
	{
		$achieved_goals = self::$pleesher->getAchievements($user_id);
		return array_map([self::$implementation, 'fillGoal'], $achieved_goals);
	}

	public static function getClosestAchievements($user_id, $max = null)
	{
		$goals = self::$pleesher->getGoals(['user_id' => $user_id]);

		$goals = array_filter($goals, function($goal) {
			return !$goal->achieved && isset($goal->progress) && $goal->progress->current > 0;
		});

		$advancements = [];
		foreach ($goals as $goal)
			$advancements[$goal->id] = (float)$goal->progress->current / $goal->progress->target;

		uasort($goals, function($goal1, $goal2) use($advancements) {
			return $advancements[$goal2->id] - $advancements[$goal1->id];
		});

		if (isset($max))
			$goals = array_slice($goals, 0, $max);

		return array_map([self::$implementation, 'fillGoal'], $goals);
	}

	public static function render($view_path, array $params = [])
	{
		extract($params);

		$h = new Pleesher_ViewHelper();

		ob_start();
		require self::getAbsoluteViewPath($view_path);
		return ob_get_clean();
	}

	protected static function getAbsoluteViewPath($view_path)
	{
		if (!is_null($implementation_folder = self::$implementation->getViewsFolder()))
		{
			$implementation_path = rtrim($implementation_folder, '/') . '/' . $view_path . '.php';
			if (file_exists($implementation_path))
				return $implementation_path;
		}

		return __DIR__ . '/view/' . $view_path . '.php';
	}
}
