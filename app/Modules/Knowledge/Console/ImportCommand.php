<?php

namespace App\Modules\Knowledge\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Process;
use League\CommonMark\CommonMarkConverter;
use League\CommonMark\Extension\Table\TableExtension;
use League\CommonMark\Extension\Strikethrough\StrikethroughExtension;
use League\CommonMark\Extension\Autolink\AutolinkExtension;
use App\Modules\Knowledge\Models\Page;
use App\Modules\Knowledge\Models\Associations;
use App\Modules\Users\Models\User;
//use Gregwar\RST\Parser;
use Doctrine\RST\Parser;
use Doctrine\RST\Configuration;
use Doctrine\RST\Kernel;
use Doctrine\RST\Builder\Documents;
use Doctrine\RST\ErrorManager;
use Doctrine\RST\Builder\ParseQueue;
use Doctrine\RST\Builder\ParseQueueProcessor;
use Doctrine\RST\Builder\Scanner;
use Doctrine\RST\Meta\Metas;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

class ImportCommand extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'knowledge:import
		{--debug : Output what changes will be made without making them}';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Sync a repo of RST or MD files to a path in the knowledge base.';

	/**
	 * Content parser
	 *
	 * @var CommonMarkConverter|Parser|string
	 */
	private $parser = null;

	/**
	 * File base path
	 *
	 * @var string
	 */
	private $basepath = '';

	/**
	 * Page path
	 *
	 * @var string
	 */
	private $slug = '';

	/**
	 * Execute the console command.
	 */
	public function handle(): int
	{
		$debug = $this->option('debug') ? true : false;
		$repos = config('module.knowledge.sync', []);

		if (empty($repos))
		{
			if ($debug || $this->output->isVerbose())
			{
				$this->info('No repos are configured for syncing');
			}
			return Command::SUCCESS;
		}

		foreach ($repos as $import)
		{
			$repo   = isset($import['repo']) ? (string)$import['repo'] : '';
			$slug   = isset($import['path']) ? (string)$import['path'] : '';
			$format = isset($import['format']) && $import['format'] ? strtolower($import['format']) : 'md';

			if (!$repo || !$slug)
			{
				if ($debug || $this->output->isVerbose())
				{
					$this->error('No repo or path specified. repo: "' . $repo . '", path: "' . $slug . '"');
				}
				continue;
			}

			$slug = trim($slug, '/');
			$this->slug = $slug;

			// Get the guide to populate
			$guide = Associations::query()
				->where('path', $slug ? $slug : '')
				//->where('parent_id', '!=', 0)
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
			$tm = time();
			$path = storage_path('app/temp/' . $tm);

			$this->basepath = $path;

			if (!is_dir($path))
			{
				$output = array();
				$retval = null;
				$result = exec('git clone ' . $repo . ' ' . $path, $output, $retval);

				if ($retval)
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

			$files = app('files');
			$all = $files->files($path);

			foreach ($all as $file)
			{
				if (substr($file->getFilename(), -(strlen($format) + 1)) != '.' . $format)
				{
					continue;
				}

				if ($file->getFilename() == 'index.' . $format)
				{
					$title = $file->getFilename();
					$title = substr($title, 0, -4);

					$document = $this->parse($file->getPathname(), $format);
					$document = str_replace('<h1>' . $title . '</h1>', '', $document);

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

				$this->importPage($debug, $format, $files, $file, $guide, $path);
			}

			if ($debug || $this->output->isVerbose())
			{
				$this->comment('Cleaning up files...');
			}

			$files->deleteDirectory($path);
		}

		if ($debug || $this->output->isVerbose())
		{
			$this->info('Done importing files.');
		}

		return Command::SUCCESS;
	}

	/**
	 * Get the content parser
	 *
	 * @param string $format
	 * @return CommonMarkConverter|Parser|string
	 */
	private function parser($format)
	{
		if (is_null($this->parser))
		{
			switch ($format)
			{
				case 'rst':
					$configuration = new Configuration();
					//$configuration->setInitialHeaderLevel(2);

					$kernel = new Kernel($configuration);
					$metas = new Metas();
					$filesystem = new Filesystem();
					$documents = new Documents(
						$filesystem,
						$metas
					);

					$scanner = new Scanner(
						$configuration->getSourceFileExtension(),
						$this->basepath,
						$metas,
						new Finder()
					);

					$parseQueue = $scanner->scan();

					$parseQueueProcessor = new ParseQueueProcessor(
						$kernel,
						new ErrorManager($configuration),
						$metas,
						$documents,
						$this->basepath,
						$this->basepath . '/rendered',
						$configuration->getFileExtension()
					);
					$parseQueueProcessor->process($parseQueue);

					$parser = $documents;
					//$parser = new Parser($kernel);
					//$parser->registerDirective(new Warning);
				break;

				case 'html':
					// No parser needed
					$parser = '';
				break;

				case 'md':
				default:
					$parser = new CommonMarkConverter([
						'html_input' => 'allow',
					]);
					$parser->getEnvironment()->addExtension(new TableExtension());
					$parser->getEnvironment()->addExtension(new StrikethroughExtension());
					$parser->getEnvironment()->addExtension(new AutolinkExtension());
				break;
			}
			$this->parser = $parser;
		}

		return $this->parser;
	}

	/**
	 * Convert the content
	 *
	 * @param string $file
	 * @param string $format
	 * @return string
	 */
	private function parse(string $file, string $format = 'md'): string
	{
		$contents = file_get_contents($file);
		$document = '';
		$parser = $this->parser($format);

		switch ($format)
		{
			case 'rst':
				//$parser->getEnvironment()->setCurrentDirectory(dirname($file));
				//$document = (string) $parser->parse($contents)->render();
				foreach ($parser->getAll() as $doc)
				{
					$current = $this->basepath . '/' . $doc->getEnvironment()->getCurrentFileName() . '.' . $format;

					if ($current == $file)
					{
						$document = (string) $doc->render();
						break;
					}
				}
			break;

			case 'html':
				if (preg_match('/<body>(.*?)<\/body>/i', $contents, $matches))
				{
					$document = (string) $matches[1];
				}
			break;

			case 'md':
			default:
				$document = (string) $parser->convertToHtml($contents);
			break;
		}

		return $this->fixContent($document);
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
		$document = str_replace(
			array('<h6', '</h6>', '<h5', '</h5>', '<h4', '</h4>', '<h3', '</h3>', '<h2', '</h2>'),
			array('<p',  '</p>',  '<h6', '</h6>', '<h5', '</h5>', '<h4', '</h4>', '<h3', '</h3>'),
			$document
		);

		$tags = array(
			'a'    => '/<a\s+([^>]*)>/i',
			'area' => '/<area\s+([^>]*)>/i'
		);
		foreach ($tags as $tag => $pattern)
		{
			$links = array();
			preg_match_all($pattern, $document, $links, PREG_SET_ORDER);

			foreach ($links as $link)
			{
				// Get attributes
				$pattern = "/(\w+)(\s*=\s*(?:\".*?\"|'.*?'|[^'\">\s]+))?/i";
				$attribs = array();
				preg_match_all($pattern, $link[1], $attribs, PREG_SET_ORDER);

				$list = array();
				foreach ($attribs as $attrib)
				{
					if (!isset($attrib[2]))
					{
						// something wrong, may be js in email cloaking plugin
						continue;
					}

					$att = strtolower(trim($attrib[1]));
					$list[$att] = preg_replace("/=\s*[\"']?([^'\"]*)[\"']?/", "$1", $attrib[2]);
					$list[$att] = trim($list[$att]);
				}

				// Skip if non http link or anchor
				if (!isset($list['href']))
				{
					continue;
				}

				if (stripos($list['href'], 'http') === 0)
				{
					continue;
				}

				$list['href'] = '/knowledge/' . $this->slug . '/' . ltrim($list['href'], '/');
				$list['href'] = strtolower($list['href']);
				$list['href'] = str_replace('.html', '', $list['href']);

				$ahref = "<$tag ";
				foreach ($list as $k => $v)
				{
					$ahref .= "{$k}=\"{$v}\" ";
				}
				$ahref .= '>';

				$document = str_replace($link[0], $ahref, $document);
			}
		}

		return $document;
	}

	/**
	 * Import a page
	 *
	 * @param bool $debug
	 * @param string $format
	 * @param object $files
	 * @param object $file
	 * @param Associations $parent
	 * @param string $path
	 * @return bool
	 */
	private function importPage($debug, $format, $files, $file, $parent, $path): bool
	{
		$title = $file->getFilename();
		$title = substr($title, 0, -4);

		//$contents = file_get_contents($file->getPathname());
		$document = $this->parse($file->getPathname(), $format);
		$document = str_replace('<h1>' . $title . '</h1>', '', $document);

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
		if (!$row->id)
		{
			$row->params->set('show_title', 1);
			$row->params->set('show_toc', 0);
		}

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
				if (substr($subfile->getFilename(), -(strlen($format) + 1)) != '.' . $format)
				{
					continue;
				}

				$this->importPage($debug, $format, $files, $subfile, $assoc, $path . '/' . $row->title);
			}

			// Find sub-directories
			$subd = $files->directories($path . '/' . $row->title);

			foreach ($subd as $subdir)
			{
				$sub = $files->files($subdir);

				$dirname = basename($subdir);

				foreach ($sub as $subfile)
				{
					if (substr($subfile->getFilename(), -(strlen($format) + 1)) != '.' . $format)
					{
						continue;
					}

					$this->importPage($debug, $format, $files, $subfile, $assoc, $path . '/' . $row->title . '/' . $dirname);
				}
			}
		}

		//$assoc->rebuild($assoc->id, $assoc->lft, $assoc->level, $assoc->path);

		return true;
	}
}
