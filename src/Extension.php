<?php
use MediaWiki\Auth\AuthManager;
use Pleesher\Client\Cache\DatabaseStorage;
use Pleesher\Client\Cache\LocalStorage;
use Pleesher\Client\Exception\Exception;

class PleesherExtension
{
	const ADMIN_RIGHT = 'pleesher-admin';

	/**
	 * @var \Pleesher\Client\Client
	 */
	public static $pleesher;

	/**
	 * @var \PDO
	 */
	public static $pdo;
	public static $goal_data;
	public static $goal_categories;

	/**
	 * @var PleesherImplementation
	 */
	public static $implementation;

	/**
	 * @var Pleesher_ViewHelper
	 */
	public static $view_helper;

	/**
	 * Retrieve an extension's configuration value.
	 * @param string $key A config key
	 * @param string $default A value to return if the requested config value doesn't exist
	 * @return string|null The config value or $default
	 */
	public static function getConfigValue($key, $default = null)
	{
		return isset($GLOBALS['wg' . get_called_class() . $key]) ? $GLOBALS['wg' . get_called_class() . $key] : $default;
	}

	/**
	 * Retrieve a Pleesher extension's setting value
	 * @param string $key A setting key
	 * @param string $default A value to return if the requested setting value doesn't exist
	 * @return string|null The setting value or $default
	 */
	public static function getSettingValue($key, $default = null)
	{
		$sql = 'SELECT value FROM ' . self::pleesherPrefixTableName('pleesher_setting') . ' WHERE `key` = :key';
		$params = array(':key' => $key);

		$query = self::$pdo->prepare($sql);
		$query->execute($params);
		$result = $query->fetch(\PDO::FETCH_COLUMN);

		return $result;
	}

	/**
	 * Set a Pleesher extension's setting value
	 * @param string $key A setting key
	 * @param string $value The value to set this setting to
	 */
	public static function setSettingValue($key, $value)
	{
		$sql = 'REPLACE INTO ' . self::pleesherPrefixTableName('pleesher_setting') . ' (`key`, `value`) VALUES (:key, :value)';
		$params = array(':key' => $key, ':value' => $value);

		$query = self::$pdo->prepare($sql);

		return $query->execute($params);
	}

	/**
	 * Define a PleesherImplementation instance. Must be called by PleesherExtension's subclasses.
	 * @param \PleesherImplementation $implementation The PleesherImplementation instance
	 */
	public static function setImplementation(\PleesherImplementation $implementation)
	{
		self::$implementation = $implementation;
	}

	/**
	 * Initialize the extension: OAuth2 client, logging, etc.
	 * @throws \Exception setImplementation must be called beforehand, otherwise this will throw an exception
	 */
	public static function initialize()
	{
		if (!isset(self::$implementation))
			throw new \Exception('PleesherExtension::setImplementation must be called before PleesherExtension is loaded');

		require_once __DIR__ . '/../vendor/autoload.php';
		self::$pleesher = new PleesherClient($GLOBALS['wgPleesherClientId'], $GLOBALS['wgPleesherClientSecret']);

		self::$pdo = new \PDO($GLOBALS['wgDBtype'] . ':host=' . $GLOBALS['wgDBserver'] . ';dbname=' . $GLOBALS['wgDBname'], $GLOBALS['wgDBuser'], $GLOBALS['wgDBpassword']);
		self::$pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
		self::$pleesher->setCacheStorage(new LocalStorage(new DatabaseStorage(self::$pdo, self::pleesherPrefixTableName('pleesher_cache'))));

		self::$view_helper = new Pleesher_ViewHelper(self::$implementation->getI18nPrefix());

		self::$pleesher->setExceptionHandler(function(Exception $e) {
			if ($e instanceof PleesherDisabledException)
			{
				header('Location: ' . self::$view_helper->pageUrl('Special:AchievementsDisabled', true));
				die;
			}

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

		self::$goal_categories = self::$implementation->getGoalCategories();
	}

	/**
	 * Initialize extension's parser hooks
	 */
	public static function initializeParser(Parser $parser)
	{
		if (!self::$implementation->isExtensionEnabled())
			return;

		$parser->setHook('Goal', 'PleesherExtension::viewGoal');
		$parser->setHook('Showcase', 'PleesherExtension::viewShowcase');
		$parser->setHook('AchievementList', 'PleesherExtension::viewAchievements');
		$parser->setHook('AchievementFeed', 'PleesherExtension::viewAchievementFeed');
		$parser->setHook('UserKudos', 'PleesherExtension::viewUserKudos');
	}

	/**
	 * Load modules (CSS/scripts/etc) before page display
	 */
	public static function beforePageDisplay(OutputPage &$out, Skin &$skin)
	{
		if (!self::$implementation->isExtensionEnabled())
			return;

		// Not too happy about this... but loading pleesher.js through a module happens too late. There's gotta be a cleaner way though.
		$out->addScript('<script type="text/javascript" src="https://code.jquery.com/jquery-3.2.1.min.js"></script>');
		$out->addInlineScript(file_get_contents(__DIR__ . '/../resources/js/pleesher-inline.js'));

		if (!self::isDisabled())
		{
			if ($out->getTitle()->getNamespace() == NS_USER)
				$out->addModules('pleesher-user-page');

			if ($out->getUser()->isLoggedIn())
			{
				$out->addModules('pleesher');

				// using $out->addModules('toastr') fails, for some reason
				$out->addModuleScripts('toastr');
				$out->addModuleStyles('toastr');
			}
		}
	}

	/**
	 * Alter text page "parts" display before it's displayed.
	 * Here, used for users' profile pages.
	 */
	public static function skinAfterContent( &$data, Skin $skin )
	{
		if (!self::$implementation->isExtensionEnabled())
			return;

		$title = $skin->getTitle();

		if ($title->getNamespace() == NS_USER && $skin->getRequest()->getVal('action') == 'view')
		{
			$user_name = $title->getText();
			$user = PleesherExtension::getUser($user_name);
			if (!is_object($user))
				return;

			$data = $skin->getOutput()->parseInline(
				self::render('user.wiki', [
					'user' => $user
				])
			) . $data;
		}
	
	}

	/**
	 * Called after a page was saved
	 */
	public static function pageContentSaveComplete($article, User $user, $content, $summary, $isMinor, $isWatch, $section, $flags, $revision, $status, $baseRevId)
	{
		if (!self::$implementation->isExtensionEnabled())
			return;

		if ($user->isLoggedIn())
			self::$pleesher->checkAchievementsLater($user->getName());
	}

	public static function extensionTypes(array &$extensionTypes)
	{
		$extensionTypes['pleesher'] = wfMessage( 'version-pleesher' )->text();

		return true;
	}

	/**
	 * Displays a Pleesher goal
	 * @return string A HTML template of it
	 */
	public static function viewGoal($input, array $args, Parser $parser = null, PPFrame $frame = null)
	{
		$goal_code = $args['code'];
		$user_name = isset($args['perspective']) ? $args['perspective'] : null;

		$goal = self::getGoal($goal_code, ['user_id' => $user_name]);
		if (!is_object($goal))
			return '';

		return self::render('goal', [
			'goal' => $goal
		]);
	}

	/**
	 * Displays a goal "showcase" (goals that a user chose to brag about)
	 * @return string A HTML template of it
	 */
	public static function viewShowcase($input, array $args, Parser $parser = null, PPFrame $frame = null)
	{
		$user_name = isset($args['user']) ? $args['user'] : null;
		$user_id = !is_null($user_name) ? User::idFromName($user_name) : null;

		if (is_null($user_id))
		{
			self::$pleesher->logger->error(sprintf('No such wiki user: %s (%s)', $user_name, $_SERVER['REQUEST_URI']));
			return '';
		}

		$showcased_goals = self::getShowcasedAchievements($user_name);
		$removable = is_object($GLOBALS['wgUser']) && $user_id == $GLOBALS['wgUser']->getId();

		return self::render('showcase', [
			'goals' => $showcased_goals,
			'removable' => $removable
		]);
	}

	/**
	 * Displays the user Kudos
	 * @return string The number of Kudos (pure text but might include HTML someday)
	 */
	public static function viewUserKudos($input, array $args, Parser $parser, PPFrame $frame)
	{
		$user_name = $args['user'];
		$user_id = User::idFromName($user_name);

		if (is_null($user_id))
		{
			self::$pleesher->logger->error(sprintf('No such wiki user: %s (%s)', $user_name, $_SERVER['REQUEST_URI']));
			return 0;
		}

		$user = self::$pleesher->getUser($user_name);
		return $user->kudos;
	}

	/**
	 * Displays the user's list of achievements
	 * @return string A HTML template of it
	 */
	public static function viewAchievements($input, array $args, Parser $parser = null, PPFrame $frame = null)
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

		$user = self::getUser($user_name);
		$achievements = self::getAchievements($user_name);
		$showcased_goal_ids = PleesherExtension::$pleesher->getObjectData('goal', null, $user_name, 'showcased');
		foreach ($achievements as $achievement)
			$achievement->showcased = !empty($showcased_goal_ids[$achievement->id]);

		$actions = [];
		if (is_object($GLOBALS['wgUser']))
		{
			if ($GLOBALS['wgUser']->getId() == $user_id)
				$actions[] = 'showcase';
			if ($GLOBALS['wgUser']->isAllowed(PleesherExtension::ADMIN_RIGHT))
				$actions[] = 'revoke';
		}

		return self::render('goals', [
			'user' => $user,
			'goals' => $achievements,
			'actions' => $actions
		]);
	}

	/**
	 * Displays a list of the last unlocked achievements
	 * @return string A HTML template of it
	 */
	public static function viewAchievementFeed($input, array $args, Parser $parser, PPFrame $frame)
	{
		$max_age = isset($args['max_age']) ? $args['max_age'] : 30;
		$max_entries = isset($args['max_entries']) ? $args['max_entries'] : null;

		$achievements = self::getParticipations(['status' => PleesherClient::PARTICIPATION_STATUS_ACHIEVED, 'max_age' => $max_age]);
		uasort($achievements, function($achievement1, $achievement2) {
			return $achievement2->datetime->getTimestamp() - $achievement1->datetime->getTimestamp();
		});

		if (isset($max_entries))
			$achievements = array_slice($achievements, 0, $max_entries);

		return self::render('feed', [
			'achievements' => $achievements
		]);
	}

	/**
	 * Retrieves a list of Pleesher users (as wiki users)
	 * @param array $options An optional array of options to be passed over to Client::getUsers
	 * @return User[] A list of Pleesher users
	 */
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

	public static function getUser($user_name)
	{
		if (!AuthManager::singleton()->userExists($user_name))
			return null;

		$pleesher_user = self::$pleesher->getUser($user_name);
		$wiki_user = self::pleesherUserToWikiUser($pleesher_user, false);
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

		return array_map([self::$implementation, 'fillGoal'], $goals);
	}

	public static function getGoal($goal_id_or_code, array $options = [])
	{
		$goal = self::$pleesher->getGoal($goal_id_or_code, $options);
		if (is_null($goal) || !isset(self::$goal_data[$goal->code]))
			return null;

		return self::$implementation->fillGoal($goal);
	}

	public static function getAchievements($user_name)
	{
		$achieved_goals = self::$pleesher->getAchievements($user_name);
		$achieved_goals = array_filter($achieved_goals, function($goal) {
			return isset(self::$goal_data[$goal->code]);
		});

		return array_map([self::$implementation, 'fillGoal'], $achieved_goals);
	}

	public static function getShowcasedAchievements($user_name)
	{
		$achieved_goals = self::$pleesher->getAchievements($user_name);

		$showcased_goal_ids = PleesherExtension::$pleesher->getObjectData('goal', null, $user_name, 'showcased');
		$showcased_goals = [];
		foreach ($showcased_goal_ids as $goal_id => $showcased)
		{
			if ($showcased && isset($achieved_goals[$goal_id]))
				$showcased_goals[] = $achieved_goals[$goal_id];
		}

		return $showcased_goals;
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

	public static function getClosestAchievements($user_name, $max = null)
	{
		$goals = self::$pleesher->getGoals(['user_id' => $user_name]);

		$goals = array_filter($goals, function($goal) {
			return isset(self::$goal_data[$goal->code]) && !$goal->achieved && isset($goal->progress) && $goal->progress->current > 0;
		});

		$advancements = [];
		foreach ($goals as $goal)
			$advancements[$goal->id] = (float)$goal->progress->current / $goal->progress->target;

		uasort($goals, function($goal1, $goal2) use($advancements) {
			$diff = $advancements[$goal2->id] - $advancements[$goal1->id];
			return $diff < 0 ? -1 : ($diff > 0 ? 1 : 0);
		});

		if (isset($max))
			$goals = array_slice($goals, 0, $max);

		return array_map([self::$implementation, 'fillGoal'], $goals);
	}

	public static function isDisabled()
	{
		static $disabled = null;

		if (is_null($disabled))
			$disabled = self::getSettingValue('disabled', '0') == '1';

		return $disabled;
	}

	public static function render($view_path, array $params = [])
	{
		extract($params);

		$h = self::$view_helper;

		ob_start();
		require self::getAbsoluteViewPath($view_path);
		return ob_get_clean();
	}

	public static function prefixTableName($name)
	{
		return isset($GLOBALS['wgDBprefix']) ? $GLOBALS['wgDBprefix'] . $name : $name;
	}

	protected static function pleesherPrefixTableName($name)
	{
		return isset($GLOBALS['wgPleesherDatabasePrefix']) ? $GLOBALS['wgPleesherDatabasePrefix'] . $name : $name;
	}

	protected static function pleesherUserToWikiUser($pleesher_user, $check_existence = true)
	{
		if ($check_existence && !AuthManager::singleton()->userExists($pleesher_user->id))
			return null;

		$wiki_user = User::newFromName($pleesher_user->id);
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

		return __DIR__ . '/../view/' . $view_path . '.php';
	}
}
