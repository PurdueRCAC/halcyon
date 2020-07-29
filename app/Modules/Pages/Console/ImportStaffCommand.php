<?php
/**
 * @package    framework
 * @copyright  Copyright 2020 Purdue University.
 * @license    http://opensource.org/licenses/MIT MIT
 */

namespace App\Modules\Pages\Console;

use Illuminate\Console\Command;
use App\Modules\Pages\Models\Page;
use Carbon\Carbon;

class ImportStaffCommand extends Command
{
	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'pages:importstaff';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Process auto-renewable orders';

	/**
	 * Execute the console command.
	 *
	 * @return void
	 */
	public function handle()
	{
		$this->info('Importing staff...');

		$all = app('files')->directories(dirname(__DIR__) . '/staff');

		$staff = array(
			'psmith',
			'colbykd',
			'ehillery',
			'ayounts',
			'acc',
			'cxsong',
			'zhu472',
			'ewa',
			'brazil',
			'cnovak1',
			'ltheadem',
			'gtakahas',
			'hopkin25',
			'sharrell',
			'gandino',
			'glentner',
			'mroute',
			'lev',
			'kelley',
			'amaji',
			'wu979',
			'goughes',
			'spiperov',
			'rices',
			'schwarz6',
			'jstjohn',
			'phill219',
			'jmbottum',
			'zweidner',
			'smithnp',
			'brewer36',
			'rcampbel',
			'thompscs',
			'rkalyana',
			'wooj',
			'zhao4',
			'ccarlson',
			'finnegpt',
		);

		foreach ($all as $path)
		{
			if (!is_file($path . '/config.php'))
			{
				continue;
			}

			$name      = '';
			$title     = '';
			$office    = '';
			$email     = '';
			$specialty = '';

			$profile = array();
			$education = array();
			$grants = array();
			$publications = array();
			$presentations = array();
			$engagement = array();
			$other = array();

			include_once $path . '/config.php';

			$staff_path = $path . '/';
			$alias = basename($path);

			$content = '';
			$pic_path = '';

			if (file_exists($staff_path . 'photo.jpg'))
			{
				$pic_path = 'photo.jpg';
			}
			elseif (file_exists($staff_path . 'photo.png'))
			{
				$pic_path = 'photo.png';
			}
			elseif (file_exists($staff_path . 'photo.gif'))
			{
				$pic_path = 'photo.gif';
			}

			if ($pic_path)
			{
				$content .= '<img class="profile_photo" src="users/' . $alias . '/' . $pic_path . '" alt="Profile Photo" />' . "\n";
			}

			$content .= '<p class="profile_title">' . preg_replace('/<br *\/?>/i', ' ', $title) . '</p>' . "\n";
			$content .= '<p class="profile_info">' . "\n";
			if (!empty($office))
			{
				$content .= $office . "<br />\n";
			}
			if (!empty($phone))
			{
				$content .= $phone . "<br />\n";
			}
			if (!empty($email))
			{
				$content .= $email . "<br />\n";
			}
			$content .= '</p>' . "\n";

			$content .= '<div class="profile">' . "\n";

			if (!empty($profile))
			{
				foreach ($profile as $item)
				{
					$content .= "<p>" . $item . "</p>\n";
				}
			}

			if (!empty($education)) {
				$content .= '<h3>Education</h3>' . "\n";
				$content .= '<ul>' . "\n";
					foreach ($education as $item) {
						$content .= '<li>' . $item . '</li>' . "\n";
					}
				$content .= '</ul>' . "\n";
			}

			if (!empty($grants)) {
				$content .= '<h3>Grants and Awards</h3>' . "\n";
				$content .= '<ul>' . "\n";
					foreach ($grants as $item) {
						$content .= '<li>' . $item . '</li>' . "\n";
					}
				$content .= '</ul>' . "\n";
			}

			if (!empty($engagement)) {
				$content .= '<h3>Engagement</h3>' . "\n";
				$content .= '<ul>' . "\n";
					foreach ($engagement as $item) {
						$content .= '<li>' . $item . '</li>' . "\n";
					}
				$content .= '</ul>' . "\n";
			}

			if (!empty($publications)) {
				$content .= '<h3>Selected Publications</h3>' . "\n";
				$content .= '<ul>' . "\n";
					foreach ($publications as $item) {
						$content .= '<li>' . $item . '</li>' . "\n";
					}
				$content .= '</ul>' . "\n";
			}

			if (!empty($presentations)) {
				$content .= '<h3>Presentations</h3>' . "\n";
				$content .= '<ul>' . "\n";
					foreach ($presentations as $item) {
						$content .= '<li>' . $item . '</li>' . "\n";
					}
				$content .= '</ul>' . "\n";
			}

			if (!empty($projects)) {
				$content .= '<h3>Projects</h3>' . "\n";
				$content .= '<ul>' . "\n";
					foreach ($projects as $item) {
						$content .= '<li>' . $item . '</li>' . "\n";
					}
				$content .= '</ul>' . "\n";
			}

			if (!empty($other)) {
				$content .= '<h3>Other Activities and Fun Facts</h3>' . "\n";
				$content .= '<ul>' . "\n";
					foreach ($other as $item) {
						$content .= '<li>' . $item . '</li>' . "\n";
					}
				$content .= '</ul>' . "\n";
			}
			$content .= '</div>' . "\n";

			$row = new Page;
			$row->title = $name;
			$row->alias = $alias;
			$row->path = 'about/staff/' . $row->alias;
			$row->state = in_array($alias, $staff) ? 1: 0;
			$row->access = 1;
			$row->content = $content;
			$row->parent_id = 69;
			$row->created_by = 61344;
			$row->save();

			$this->info('Created page "' . $row->title . ':' . $row->alias . '"');
		}
	}

	/**
	 * Output help documentation
	 *
	 * @return  void
	 **/
	public function help()
	{
		$this->output
			 ->getHelpOutput()
			 ->addOverview('Import Staff pages')
			 ->addTasks($this)
			 ->render();
	}
}
