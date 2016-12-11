<?php
use Pleesher\Client\Client;
use Pleesher\Client\Cache\LocalStorage;
use Pleesher\Client\Cache\DatabaseStorage;
use MediaWiki\Auth\AuthManager;
use Pleesher\Client\Exception\Exception;

class PleesherExtension
{
	/**
	 * @var \Pleesher\Client\Client
	 */
	public static $pdo;
	public static $pleesher;
	public static $goal_data;
	public static $implementation;
	public static $view_helper;

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

		self::$view_helper = new Pleesher_ViewHelper();

		self::$pleesher->setExceptionHandler(function(Exception $e) {
			$_SESSION[__CLASS__]['exception'] = $e;
			header('Location: ' . self::$view_helper->pageUrl('Special:AchievementsError', true));
			die;
		});

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
		$parser->setHook('Showcase', 'PleesherExtension::viewShowcase');
		$parser->setHook('AchievementList', 'PleesherExtension::viewAchievements');
		$parser->setHook('AchievementFeed', 'PleesherExtension::viewAchievementFeed');
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
				self::$pleesher->setExceptionHandler(self::$pleesher->getDefaultExceptionHandler());

				try {
					$user_id = User::idFromName($title->getText());
					if (is_null($user_id))
						return null;
					$user = PleesherExtension::getUser($user_id);
					if (!is_object($user))
						return null;
					$achievement_count = count(self::getAchievements($user_id)) ?: 0;
					$showcased_achievement_count = count(self::$pleesher->getObjectData('goal', null, $user_id, 'showcased'));
					$goal_count = count(self::$goal_data);

					if (!empty($text))
						$text .= PHP_EOL . PHP_EOL;

					$text .= self::render('user.wiki', array_merge(self::$implementation->getUserPageData($user), [
						'user' => $user,
						'closest_achievements' => self::getClosestAchievements($user->getId(), 3),
						'achievement_count' => $achievement_count,
						'showcased_achievement_count' => $showcased_achievement_count,
						'goal_count' => $goal_count
					]));

				} catch (Exception $e) {
					if (!empty($text))
						$text .= PHP_EOL . PHP_EOL;
					$text .= self::render('error', ['error_message' => self::$view_helper->text('pleesher.error.text.' . ($e->getErrorCode() ?: 'generic'), $e->getErrorParameters() ?: [])]);
				}

				self::$pleesher->restoreExceptionHandler();
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

		$goal = self::getGoal($goal_code, ['user_id' => $user_id]);
		if (!is_object($goal))
			return '';

		return self::render('goal', [
			'goal' => $goal
		]);
	}

	public static function viewShowcase($input, array $args, Parser $parser, PPFrame $frame)
	{
		$user_name = isset($args['user']) ? $args['user'] : null;
		$user_id = !is_null($user_name) ? User::idFromName($user_name) : null;

		if (is_null($user_id))
		{
			self::$pleesher->logger->error(sprintf('No such wiki user: %s (%s)', $user_name, $_SERVER['REQUEST_URI']));
			return '';
		}

		$showcased_goal_ids = PleesherExtension::$pleesher->getObjectData('goal', null, $user_id, 'showcased');
		$showcased_goals = [];
		foreach ($showcased_goal_ids as $goal_id => $showcased) {
			if ($showcased)
				$showcased_goals[] = self::getGoal($goal_id);
		}

		$removable = is_object($GLOBALS['wgUser']) && $user_id == $GLOBALS['wgUser']->getId();

		return self::render('showcase', [
			'goals' => $showcased_goals,
			'removable' => $removable
		]);
	}

	public static function viewUserKudos($input, array $args, Parser $parser, PPFrame $frame)
	{
		$user_name = $args['user'];
		$user_id = User::idFromName($user_name);

		if (is_null($user_id))
		{
			self::$pleesher->logger->error(sprintf('No such wiki user: %s (%s)', $user_name, $_SERVER['REQUEST_URI']));
			return 0;
		}

		$user = self::$pleesher->getUser($user_id);
		return $user->kudos;
	}

	public static function viewAchievements($input, array $args, Parser $parser, PPFrame $frame)
	{
		if (!isset($args['user']))
			return '';

		$user_name = $args['user'];
		$user_id = User::idFromName($user_name);

		if (is_null($user_id))
		{
			self::$pleesher->logger->error(sprintf('No such wiki user: %s (%s)', $user_name, $_SERVER['REQUEST_URI']));
			return '';
		}

		$user = self::getUser($user_id);
		$achievements = self::getAchievements($user_id);
		$showcased_goal_ids = PleesherExtension::$pleesher->getObjectData('goal', null, $user_id, 'showcased');
		foreach ($achievements as $achievement)
			$achievement->showcased = !empty($showcased_goal_ids[$achievement->id]);

		$actions = is_object($GLOBALS['wgUser']) && $user_id == $GLOBALS['wgUser']->getId() ? ['showcase'] : [];

		return self::render('goals', [
			'user' => $user,
			'goals' => $achievements,
			'actions' => $actions
		]);
	}

	public static function viewAchievementFeed($input, array $args, Parser $parser, PPFrame $frame)
	{
		$max_age = isset($args['max_age']) ? $args['max_age'] : 30;
		$max_entries = isset($args['max_entries']) ? $args['max_entries'] : null;

		$achievements = self::getParticipations(['status' => Client::PARTICIPATION_STATUS_ACHIEVED, 'max_age' => $max_age]);
		uasort($achievements, function($achievement1, $achievement2) {
			return $achievement2->datetime->getTimestamp() - $achievement1->datetime->getTimestamp();
		});

		if (isset($max_entries))
			$achievements = array_slice($achievements, 0, $max_entries);

		return self::render('feed', [
			'achievements' => $achievements
		]);
	}

	public static function getUsers(array $options = [])
	{
		$pleesher_users = self::$pleesher->getUsers($options);

		$users = array_map(function($pleesher_user) {
			return self::pleesherUserToWikiUser($pleesher_user);
		}, $pleesher_users);

		$users = array_filter($users, function(User $user = null) {
			return !is_null($user);
		});

		return array_map([self::$implementation, 'fillUser'], $users);
	}

	public static function getUser($user_id)
	{
		$pleesher_user = self::$pleesher->getUser($user_id);

		$wiki_user = self::pleesherUserToWikiUser($pleesher_user);
		if (is_null($wiki_user))
			return null;

		return self::$implementation->fillUser($wiki_user);
	}

	public static function getGoals(array $options = [])
	{
		$goals = self::$pleesher->getGoals(array_merge($options, ['index_by' => 'code']));

		$_goals = [];
		foreach (self::$goal_data as $code => $goal_data)
		{
			if (isset($goals[$code]))
				$_goals[$code] = $goals[$code];
		}
		$goals = $_goals;

		$goals = array_filter($goals, function($goal) {
			return isset(self::$goal_data[$goal->code]);
		});

		return array_map([self::$implementation, 'fillGoal'], $goals);
	}

	public static function getGoal($goal_id_or_code, array $options = [])
	{
		$goal = self::$pleesher->getGoal($goal_id_or_code, $options);
		if (is_null($goal) || !isset(self::$goal_data[$goal->code]))
		{
			header('Location: ' . self::$view_helper->pageUrl('Special:AchievementsError', true));
			die;
		}

		return self::$implementation->fillGoal($goal);
	}

	public static function getAchievements($user_id)
	{
		$achieved_goals = self::$pleesher->getAchievements($user_id);
		return array_map([self::$implementation, 'fillGoal'], $achieved_goals);
	}

	public static function getParticipations(array $filters = [])
	{
		$participations = self::$pleesher->getParticipations($filters);

		$participations = array_map(function($participation) {
			$participation->author = self::pleesherUserToWikiUser($participation->author);
			if (isset($participation->author))
				$participation->author = self::$implementation->fillUser($participation->author);
			return $participation;
		}, $participations);
		$participations = array_filter($participations, function($participation) {
			return !is_null($participation->author);
		});

		return $participations;
	}

	public static function getAchievers($goal_id_or_code, array $options = [])
	{
		$achievers = self::$pleesher->getAchievers($goal_id_or_code);

		$achievers = array_map(function($pleesher_user) {
			return self::pleesherUserToWikiUser($pleesher_user);
		}, $achievers);

		$achievers = array_filter($achievers, function(User $user = null) {
			return !is_null($user);
		});

		return array_map([self::$implementation, 'fillUser'], $achievers);
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

		$h = self::$view_helper;

		ob_start();
		require self::getAbsoluteViewPath($view_path);
		return ob_get_clean();
	}

	protected static function pleesherUserToWikiUser($pleesher_user)
	{
		$wiki_user = User::newFromId($pleesher_user->id);
		if (!AuthManager::singleton()->userExists($wiki_user->getName()))
			return null;

		$wiki_user->kudos = $pleesher_user->kudos;

		return $wiki_user;
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
