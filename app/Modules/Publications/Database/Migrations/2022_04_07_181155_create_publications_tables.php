<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreatePublicationsTables extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		if (!Schema::hasTable('publications'))
		{
			Schema::create('publications', function (Blueprint $table)
			{
				$table->increments('id');
				$table->string('title', 500);
				//$table->string('type', 50);
				$table->integer('type_id')->unsigned()->default(0)->comment('FK to publication_types.id');
				$table->string('author', 3000)->nullable();
				$table->string('editor', 3000)->nullable();
				$table->string('url', 2083)->nullable();
				$table->string('series', 255)->nullable();
				$table->string('booktitle', 1000)->nullable();
				$table->string('edition', 100)->nullable();
				$table->string('chapter', 40)->nullable();
				$table->string('issuetitle', 255)->nullable();
				$table->string('journal', 255)->nullable();
				$table->string('issue', 40)->nullable();
				$table->string('volume', 40)->nullable();
				$table->string('number', 40)->nullable();
				$table->string('pages', 40)->nullable();
				$table->string('publisher', 500)->nullable();
				$table->string('address', 300)->nullable();
				$table->string('institution', 500)->nullable();
				$table->string('organization', 500)->nullable();
				$table->string('school', 200)->nullable();
				$table->string('crossref', 100)->nullable();
				$table->string('isbn', 50)->nullable();
				$table->string('doi', 255)->nullable();
				$table->string('note', 2000)->nullable();
				$table->tinyInteger('state')->unsigned()->default(0);
				$table->dateTime('published_at')->nullable();
				$table->dateTime('created_at')->nullable();
				$table->dateTime('updated_at')->nullable();
				$table->dateTime('deleted_at')->nullable();
				$table->index('type_id');
				$table->index('state');
				$table->index('published_at');
			});
		}

		if (!Schema::hasTable('publication_types'))
		{
			Schema::create('publication_types', function (Blueprint $table)
			{
				$table->increments('id');
				$table->string('alias', 50);
				$table->string('name', 50);
			});

			$types = [
				'unknown' => 'N/A',
				'journal' => 'Journal',
				'proceedings' => 'Proceedings',
				'inbook' => 'In Book',
				'phdthesis' => 'Phd Thesis',
				'masterstheis' => 'Masters Thesis',
				'conference' => 'Conference',
				'techreport' => 'Report',
				'magazine' => 'Magazine',
				'article' => 'Article',
				'preprint' => 'Preprint',
				'xarchive' => 'XArchive',
				'patent' => 'Patent',
				'notes' => 'Notes',
				'letter' => 'Letter',
				'syllabus' => 'Syllabus',
				'tutorial' => 'Tutorial',
				'arxiv' => 'arXiv',
				'inproceedings' => 'In Proceedings',
				'misc' => 'Misc',
				'techbrief' => 'Technical Briefing',
				'invited_conference' => 'Invited Conference',
				'technical_review' => 'Technical Review',
				'invited_seminar' => 'Invited Seminar',
				'articles_citing_nemo' => 'Citation of Nemo',
			];

			foreach ($types as $type => $name)
			{
				DB::table('publication_types')->insert([
					'alias' => $type,
					'name' => $name,
				]);
			}
		}

		/*if (!Schema::hasTable('publication_authors'))
		{
			Schema::create('publication_authors', function (Blueprint $table)
			{
				$table->increments('id');
				$table->integer('user_id')->unsigned()->default(0);
				$table->string('given_name', 255);
				$table->string('surname', 255);
				$table->string('organization', 500);
				$table->dateTime('created_at')->nullable();
				$table->dateTime('updated_at')->nullable();
				$table->dateTime('deleted_at')->nullable();
				$table->index('user_id');
			});
		}
		
		if (!Schema::hasTable('publication_author_map'))
		{
			Schema::create('publication_author_map', function (Blueprint $table)
			{
				$table->increments('id');
				$table->integer('publication_id')->unsigned()->default(0)->comment('FK to publications.id');
				$table->integer('author_id')->unsigned()->default(0)->comment('FK to publication_authors.id');
				$table->integer('ordering')->unsigned()->default(0);
				$table->index(['publication_id', 'author_id'], 'publication_author');
			});
		}

		if (!Schema::hasTable('publication_formats'))
		{
			Schema::create('publication_formats', function (Blueprint $table)
			{
				$table->increments('id');
				$table->string('style', 50);
				$table->string('template', 500);
				$table->tinyInteger('is_default')->unsigned()->default(0);
			});

			$formats = array(
				array(
					'is_default' => 1,
					'style' => 'IEEE',
					'template' => '{AUTHORS}, {EDITORS} ({YEAR}), {TITLE/CHAPTER}, <em>{JOURNAL}</em>, <em>{BOOK TITLE}</em>, {EDITION}, {CHAPTER}, {SERIES}, {PUBLISHER}, {ADDRESS}, <strong>{VOLUME}</strong>, <strong>{ISSUE/NUMBER}</strong>: pp. {PAGES}, {ORGANIZATION}, {INSTITUTION}, {SCHOOL}, {LOCATION}, {MONTH}, {ISBN/ISSN}, (DOI: {DOI})',
				),
				array(
					'is_default' => 0,
					'style' => 'APA',
					'template' => '{AUTHORS}, {EDITORS} ({YEAR}), {TITLE/CHAPTER}, <em>{JOURNAL}</em>, <em>{BOOK TITLE}</em>, {EDITION}, {CHAPTER}, {SERIES}, {PUBLISHER}, {ADDRESS}, <strong>{VOLUME}</strong>, <strong>{ISSUE/NUMBER}</strong>: {PAGES}, {ORGANIZATION}, {INSTITUTION}, {SCHOOL}, {LOCATION}, {MONTH}, {ISBN/ISSN}, (DOI: {DOI}).',
				),
			);

			foreach ($formats as $format)
			{
				DB::table('publication_formats')->insert($format);
			}
		}*/
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('publications');
		Schema::dropIfExists('publication_types');
		Schema::dropIfExists('publication_authors');
		Schema::dropIfExists('publication_author_map');
	}
}
