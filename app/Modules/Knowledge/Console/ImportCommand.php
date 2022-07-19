<?php

namespace App\Modules\Knowledge\Console;

use App\Modules\Knowledge\Models\Page;
use App\Modules\Knowledge\Models\Association;
use League\CommonMark\CommonMarkConverter;
use League\CommonMark\Extension\Table\TableExtension;
use League\CommonMark\Extension\Strikethrough\StrikethroughExtension;
use League\CommonMark\Extension\Autolink\AutolinkExtension;
use Illuminate\Console\Command;

class ImportCommand extends Command
{
	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'knowledge:import';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Import old KN docs';

	/**
	 * Execute the console command.
	 *
	 * @return void
	 */
	public function handle()
	{
		$this->info('Starting import...');

		$files = app('files');
		$path = dirname(__DIR__) . '/KB';

		if (!$files->isDirectory($path))
		{
			$this->warn('No import directory found');
			return;
		}

		require_once __DIR__ . '/sfYaml.php';
		require_once __DIR__ . '/sfYamlParser.php';
		$yamlParser = new \sfYamlParser();

		$mdParser = new CommonMarkConverter([
			'html_input' => 'allow',
		]);
		$mdParser->getEnvironment()->addExtension(new TableExtension());
		$mdParser->getEnvironment()->addExtension(new StrikethroughExtension());
		$mdParser->getEnvironment()->addExtension(new AutolinkExtension());

		$this->importGuides($path, $yamlParser, $mdParser, $files);
		//$this->importPages($path, $yamlParser, $mdParser, $files);

		$this->info('Import finished.');

		//print_r($tree);
	}

	public function importGuides($path, $yamlParser, $mdParser, $files)
	{
		$all = $files->allfiles($path . '/tags');

		$tree = array();

		foreach ($all as $file)
		{
			if (substr($file->getFilename(), -5) == '.yaml')
			{
				$contents = file_get_contents($file->getPathname());

				$yaml = $yamlParser->convertToHtml($contents);

				$vars = new \stdClass;
				$vars->tagtype = $yaml['tagtype'];
				if (isset($yaml['vars']))
				{
					$vars->variables = [$yaml['tagtype'] => $yaml['vars']];
				}
				if (isset($yaml['tags']))
				{
					$vars->tags = $yaml['tags'];
				}

				$row = new Page;
				$row->title = $yaml['name'];
				$row->alias = substr($file->getFilename(), 0, -5);
				$row->params = json_encode($vars);
				$row->state = isset($yaml['vars']['active']) && $yaml['vars']['active'] ? 1 : 0;
				$row->access = 1;
				if (in_array('internal', $row->options->get('tags', [])))
				{
					$row->access = 3;
				}
				$row->save();

				$this->info('Created guide "' . $row->title . '"');
			}
		}
	}

	/**
	 * Parse old MarkDown files and populate database
	 *
	 * @param  string $path
	 * @param  object $yamlParser
	 * @param  object $mdParser
	 * @param  array  $files
	 * @return void
	 */
	public function importPages($path, $yamlParser, $mdParser, $files)
	{
		$all = $files->allfiles($path);

		$tree = array();

		foreach ($all as $file)
		{
			if ($file->getFilename() == 'README.md')
			{
				$contents = file_get_contents($file->getPathname());

				// Strip out newlines
				if (preg_match("/^--- *\n(.+?)\n--- */s", $contents, $yaml))
				{
					$yaml = $yamlParser->parse($yaml[1]);
				}

				$contents = preg_replace("/^--- *\n(.+?)\n--- */s", '', $contents);

				// If we don't have a title, set one.
				if (!isset($yaml['title']))
				{
					$yaml['title'] = 'Index';
				}

				$p = str_replace($path, '', $file->getPath());

				if (!$p)
				{
					$p = '/';
				}

				$row = new Page;
				$row->title = str_replace('${resource.name}', $file->getFilename(), $yaml['title']);
				$row->alias = basename($file->getPath());
				$row->content = $mdParser->text($contents);
				$row->content = preg_replace("/<p>(.*)<\/p>\n<(table.*)\n/m", "<$2 <caption>$1</caption>\n", $row->content);
				$row->content = preg_replace("/<h2>(.*)<\/h2>/", "<h3 class=\"kb2\">$1</h3>", $row->content);
				$row->content = preg_replace("/<h1>(.*)<\/h1>/", "<h2 class=\"kb1\">$1</h2>", $row->content);
				$row->params = json_encode($yaml);
				$row->state = 1;
				$row->access = 1;
				$row->main = ($p == '/' ? 1 : 0);
				if (in_array('internal', $row->options()->get('tags', [])))
				{
					$row->access = 3;
				}

				$row->save();

				$this->info('Created index "' . $row->title . '"');

				$tree[$p] = $row->id;
			}
		}

		ksort($tree);

		foreach ($tree as $k => $branch)
		{
			$k = dirname($k);
			if (!$k)
			{
				continue;
			}

			if (isset($tree[$k]))
			{
				$assoc = new Association;
				$assoc->parent_id = $tree[$k];
				$assoc->child_id  = $branch;
				$assoc->save();

				$this->info('Created association - parent: ' . $assoc->parent_id . ', child: ' . $assoc->child_id);
				//echo $k . ' , parent: ' . $tree[$k] . "\n";
			}
		}
		//print_r($tree);
		//return;

		foreach ($all as $file)
		{
			if ($file->getFilename() != 'README.md' && substr($file->getFilename(), -3) == '.md')
			{
				$contents = file_get_contents($file->getPathname());

				// Strip out newlines
				if (preg_match("/^--- *\n(.+?)\n--- */s", $contents, $yaml))
				{
					$yaml = $yamlParser->parse($yaml[1]);
				}

				$contents = preg_replace("/^--- *\n(.+?)\n--- */s", '', $contents);

				// If we don't have a title, set one.
				if (!isset($yaml['title']))
				{
					$yaml['title'] = 'Index';
				}

				$p = str_replace($path, '', $file->getPath());

				if (!$p)
				{
					$p = '/';
				}

				$row = new Page;
				$row->title = str_replace('${resource.name}', $file->getFilename(), $yaml['title']);
				$row->alias = basename($file->getPath());
				$row->content = $mdParser->text($contents);
				$row->content = preg_replace("/<p>(.*)<\/p>\n<(table.*)\n/m", "<$2 <caption>$1</caption>\n", $row->content);
				$row->content = preg_replace("/<h2>(.*)<\/h2>/", "<h3 class=\"kb2\">$1</h3>", $row->content);
				$row->content = preg_replace("/<h1>(.*)<\/h1>/", "<h2 class=\"kb1\">$1</h2>", $row->content);
				$row->params = json_encode($yaml);
				$row->state = 1;
				$row->access = 1;
				if (in_array('internal', $row->options()->get('tags', [])))
				{
					$row->access = 3;
				}

				$row->save();

				$this->info('Created page "' . $row->title . '"');

				if (isset($tree[$p]))
				{
					//echo 'parent: ' . $tree[$p];

					$assoc = new Association;
					$assoc->parent_id = $tree[$p];
					$assoc->child_id  = $row->id;
					$assoc->save();

					$this->info('Created association - parent: ' . $assoc->parent_id . ', child: ' . $assoc->child_id);
				}
			}
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
			 ->addOverview('Import docs')
			 ->addTasks($this)
			 ->render();
	}
}
