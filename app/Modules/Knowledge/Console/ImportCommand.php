<?php

namespace App\Modules\Knowledge\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Process;
use App\Modules\Knowledge\Models\Page;
use App\Modules\Knowledge\Models\Associations;
use App\Modules\Users\Models\User;
use Gregwar\RST\Parser;

class ImportCommand extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'knowledge:import
		{repo : Git repository URL}
		{guide : Alias of the guide to populate}
		{--debug : Output what changes will be made without making them}';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Import RST files into the knowledge base.';

	/**
	 * Execute the console command.
	 */
	public function handle(): int
	{
		$debug = $this->option('debug') ? true : false;
		$repo = $this->argument('repo');
		$slug = $this->argument('guide');

		// Get the guide to populate
		$guide = Associations::query()
			->where('path', $slug ? $slug : '')
			->where('parent_id', '=', 1)
			->first();

		if (!$guide)
		{
			if ($debug || $this->output->isVerbose())
			{
				$this->error('Failed to find guide with alias: ' . $slug);
			}
			return Command::FAILURE;
		}

		// Clone the repo
		//$tm = time();
		$tm = 1681916171;
		$path = storage_path('app/temp/' . $tm);

		if (!is_dir($path))
		{
			//$result = Process::run('git clone ' . $repo . ' ' . $path);
			$output = array();
			$retval = null;
			$result = exec('git clone ' . $repo . ' ' . $path, $output, $retval);

			if ($retval) //$result->failed())
			{
				if ($debug || $this->output->isVerbose())
				{
					$this->error('Failed to clone repository: ' . $repo);
				}
				return Command::FAILURE;
			}

			if ($debug || $this->output->isVerbose())
			{
				$this->info('Finished cloning repo.');
			}
		}

		$parser = new Parser;
		$parser->registerDirective(new Warning);

		$files = app('files');
		$all = $files->files($path);

		foreach ($all as $file)
		{
			if (substr($file->getFilename(), -4) != '.rst')
			{
				continue;
			}

			if ($file->getFilename() == 'index.rst')
			{
				$title = $file->getFilename();
				$title = substr($title, 0, -4);

				$contents = file_get_contents($file->getPathname());
				$document = $parser->parse($contents);
				$document = $this->fixContent($document);

				$row = $guide->page;
				$row->content = $document;
				if (!$debug)
				{
					$row->save();
				}

				if ($debug || $this->output->isVerbose())
				{
					$this->info('Updated page: ' . $guide->path);
				}

				continue;
			}

			$this->importPage($debug, $parser, $files, $file, $guide, $path);
		}

		if ($debug || $this->output->isVerbose())
		{
			$this->comment('Cleaning up files...');
		}

		$files->deleteDirectory($path);

		if ($debug || $this->output->isVerbose())
		{
			$this->info('Done importing files.');
		}

		return Command::SUCCESS;
	}

	/**
	 * Fix bad HTML or unparsed bits
	 *
	 * @param string $document
	 * @return string
	 */
	private function fixContent($document): string
	{
		//$document = preg_replace('/((http|ftp|https):\/\/[\w-]+(\.[\w-]+)+([\w.,@?^=%&amp;:\/~+#-]*[\w@?^=%&amp;\/~+#-])?)/', '<a href="\1">\1</a>', $document);
		//$document = str_replace(['<h5', '<h4', '<h3', '<h2', '<h1'], ['<h6', '<h5', '<h4', '<h3', '<h2'], $document);
		//$document = str_replace(['h5>', 'h4>', 'h3>', 'h2>', 'h1>'], ['h6>', 'h5>', 'h4>', 'h3>', 'h2>'], $document);
		$document = str_replace('<a id="backbone-label"></a>', '', $document);
		$document = preg_replace('/(<a id="title\.1"><\/a><h2>\w+<\/h2>)/', '', $document);
		$document = str_replace([
			'<a id="title.1.1"></a><h3>',
			'<a id="title.1.2"></a><h3>',
			'<a id="title.1.3"></a><h3>',
			'<a id="title.1.4"></a><h3>',
			'<a id="title.1.5"></a><h3>'
		], '<h3>', $document);
		$document = str_replace('| For more information', 'For more information', $document);
		$document = str_replace('| Home page', 'Home page', $document);

		return $document;
	}

	/**
	 * Import an RST page
	 *
	 * @param bool $debug
	 * @param Parser $parser
	 * @param object $files
	 * @param object $file
	 * @param Associations $parent
	 * @param string $path
	 * @return bool
	 */
	private function importPage($debug, $parser, $files, $file, $parent, $path): bool
	{
		$contents = file_get_contents($file->getPathname());
		$document = $parser->parse($contents);
		$document = $this->fixContent($document);

		$title = $file->getFilename();
		$title = substr($title, 0, -4);

		// We do this for the model's alias-parsing
		$tmp = new Page;
		$tmp->alias = $title;

		$assoc = Associations::query()
			->where('parent_id', '=', $parent->id)
			->where('path', '=', $parent->path . '/' . $tmp->alias)
			->first();

		$row = $assoc ? $assoc->page : new Page;
		$row->title   = $title;
		$row->alias   = $title;

		if ($row->content == $document)
		{
			if ($debug || $this->output->isVerbose())
			{
				$this->comment('No changes detected for page: ' . $parent->path . '/' . $row->alias);
			}
			return false;
		}

		$row->content = $document;
		$row->state   = 1;
		$row->access  = 1;
		$id = $row->id;

		if (!$debug)
		{
			if (!$row->save())
			{
				if ($this->output->isVerbose())
				{
					$this->error('Failed to save page: ' . $title);
				}
				return false;
			}
		}

		if (!$assoc)
		{
			$assoc = new Associations;
			$assoc->parent_id = $parent->id;
			$assoc->page_id   = $row->id;
			$assoc->state     = 1;
			$assoc->access    = 1;
			$assoc->path = '';
			if ($assoc->parent)
			{
				$assoc->path = trim($assoc->parent->path . '/' . $row->alias, '/');
			}
			if (!$debug)
			{
				$assoc->save();
			}
		}

		if ($debug || $this->output->isVerbose())
		{
			if ($id)
			{
				$this->info('Updated page: ' . $assoc->path);
			}
			else
			{
				$this->info('Created page: ' . $assoc->path);
			}
		}

		if (is_dir($path . '/' . $row->title))
		{
			// Find files
			$sub = $files->files($path . '/' . $row->title);

			foreach ($sub as $subfile)
			{
				if (substr($subfile->getFilename(), -4) != '.rst')
				{
					continue;
				}

				$this->importPage($debug, $parser, $files, $subfile, $assoc, $path . '/' . $row->title);
			}

			// Find sub-directories
			$subd = $files->directories($path . '/' . $row->title);

			foreach ($subd as $subdir)
			{
				$sub = $files->files($subdir);

				$dirname = basename($subdir);

				foreach ($sub as $subfile)
				{
					if (substr($subfile->getFilename(), -4) != '.rst')
					{
						continue;
					}

					$this->importPage($debug, $parser, $files, $subfile, $assoc, $path . '/' . $row->title . '/' . $dirname);
				}
			}
		}

		//$assoc->rebuild($assoc->id, $assoc->lft, $assoc->level, $assoc->path);

		return true;
	}
}
