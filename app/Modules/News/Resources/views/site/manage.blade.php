@extends('layouts.master')

@section('styles')
<link rel="stylesheet" type="text/css" media="all" href="{{ asset('modules/News/css/news.css') }}" />
@stop

@section('scripts')
<script type="text/javascript" src="{{ asset('modules/News/js/site.js') }}"></script>
@stop

@section('content')

<div class="sidenav col-lg-3 col-md-3 col-sm-12 col-xs-12">
	@include('news::site.menu', ['types' => $types, 'active' => 'manage'])
</div>

<div class="contentInner col-lg-9 col-md-9 col-sm-12 col-xs-12">
	<div id="everything">
		<ul class="nav nav-tabs">
			<li data-toggle="tab"><a id="TAB_search" class="tab activeTab" href="#search">Search</a></li>
			<li data-toggle="tab"><a id="TAB_add" class="tab" href="#add">Add New</a></li>
		</ul>
		<div class="tabMain" id="tabMain">
			<div id="DIV_news">

				<form method="get" action="/news/manage">
					<fieldset>
						<legend><span id="SPAN_header" data-search="Search News" data-add="Add New News" data-edit="Edit News">Search News</span></legend>

						<div class="form-group row tab-search tab-add tab-edit" id="TR_date">
							<label for="datestartshort" class="col-sm-2 col-form-label">Date from</label>
							<div class="col-sm-4">
								<?php
								$startdate = '';
								$starttime = '';
								if ($value = request('start'))
								{
									$value = explode('!', $value);
									$startdate = $value[0];
									if (isset($value[1]))
									{
										$starttime = $value[1];
										// Convert to human readable form
										$values = explode(':', $starttime);
										if ($values[0] > 12)
										{
											$values[0] -= 12;
											$starttime = $values[0] . ':' . $values[1] . ' PM';
										}
										else if ($values[0] == 12)
										{
											$starttime = $values[0] . ':' . $values[1] . ' PM';
										}
										else if ($values[0] == 0)
										{
											$values[0] += 12;
											$starttime = $values[0] . ':' . $values[1] . ' AM';
										}
										else
										{
											$starttime = $values[0] . ':' . $values[1] . ' AM';
										}
										$starttime = preg_replace('/^0/', '', $starttime);
									}
								}

								$stopdate = '';
								$stoptime = '';

								$value = request('stop');
								if ($value && $value != '0000-00-00 00:00:00')
								{
									$value = explode('!', $value);
									$stopdate = $value[0];
									if (isset($value[1]) && $value[1] != '00:00:00')
									{
										$stoptime = $value[1];
										// Convert to human readable form
										$values = explode(':', $stoptime);
										if ($values[0] > 12)
										{
											$values[0] -= 12;
											$stoptime = $values[0] . ':' . $values[1] . ' PM';
										}
										else if ($values[0] == 12)
										{
											$stoptime = $values[0] . ':' . $values[1] . ' PM';
										}
										else if ($values[0] == 0)
										{
											$values[0] += 12;
											$stoptime = $values[0] . ':' . $values[1] . ' AM';
										}
										else
										{
											$stoptime = $values[0] . ':' . $values[1] . ' AM';
										}
										$stoptime = preg_replace('/^0/', '', $stoptime);
									}
								}

								if ($starttime == '12:00 AM' && $stoptime == '12:00 AM')
								{
									$starttime = $stoptime;
								}
								?>
								<div class="input-group">
									<span class="input-group-addon"><span class="input-group-text fa fa-calendar" aria-hidden="true"></span></span>
									<input id="datestartshort" type="text" class="date-pick form-control" name="start" placeholder="YYYY-MM-DD" data-start="{{ $startdate }}" value="{{ $startdate }}" />
								</div>
								<div class="input-group input-time tab-add tab-edit hide">
									<span class="input-group-addon"><span class="input-group-text fa fa-clock-o" aria-hidden="true"></span></span>
									<input id="timestartshort" type="text" class="time-pick form-control" name="starttime" placeholder="h:mm AM/PM" value="{{ $starttime }}" />
								</div>
							</div>

							<label for="datestopshort" class="col-sm-2 col-form-label align-right">Date to</label>
							<div class="col-sm-4">
								<div class="input-group" id="enddate">
									<span class="input-group-addon"><span class="input-group-text fa fa-calendar" aria-hidden="true"></span></span>
									<input id="datestopshort" type="text" class="date-pick form-control" name="stop" placeholder="YYYY-MM-DD" data-stop="{{ $stopdate }}" value="{{ $stopdate }}">
								</div>
								<div class="input-group input-time tab-add tab-edit hide">
									<span class="input-group-addon"><span class="input-group-text fa fa-clock-o" aria-hidden="true"></span></span>
									<input id="timestopshort" type="text" class="time-pick form-control" name="stoptime" placeholder="h:mm AM/PM" value="{{ $stoptime }}" />
								</div>
							</div>
						</div>

						<div class="form-group row tab-search tab-add tab-edit" id="TR_newstype">
							<label for="newstype" class="col-sm-2 col-form-label">News Type</label>
							<div class="col-sm-10">
								<select id="newstype" name="newstype" class="form-control">
									<option id="OPTION_all" value="-1">All</option>
									<?php
									$value = request('newstype');
									//$newstypes = $ws->get(ROOT_URI . "newstype/");

									foreach ($types as $newstype)
									{
										//$type = preg_replace('/^' . str_replace('/', '\/', ROOT_URI) . 'newstype\//', '', $newstype->id, 1, $count);

										$selected = '';
										if ($newstype == $value)
										{
											$selected = ' selected="selected"';
										}
										?>
										<option value="<?php echo $newstype->id; ?>" data-tagresources="<?php echo $newstype->tagresources; ?>" data-taglocation="<?php echo $newstype->location; ?>" data-tagusers="<?php echo $newstype->tagusers; ?>" data-tagurl="<?php echo $newstype->url; ?>"<?php echo $selected; ?>><?php echo $newstype->name; ?></option>
										<?php
									}
									?>
								</select>
							</div>
						</div>
						<div class="form-group row tab-search" id="TR_keywords">
							<label for="keywords" class="col-sm-2 col-form-label">Keywords</label>
							<div class="col-sm-10">
								<input type="text" name="keyword" id="keywords" size="45" class="form-control" value="{{ request('keywords') }}" />
							</div>
						</div>
						<div class="form-group row tab-search tab-add tab-edit" id="TR_resource">
							<label for="newsresource" class="col-sm-2 col-form-label">Resource</label>
							<div class="col-sm-10">
								<?php
								$resources = array();
								if ($res = request('resource'))
								{
									foreach (explode(',',$res) as $r)
									{
										if (trim($r))
										{
											$resource = App\Modules\Resources\Models\Asset::findOrFail($r);//$ws->get(ROOT_URI . 'resource/' . $r);
											$resources[] = $resource->name . ':' . $r . '';
										}
									}
								}
								?>
								<input name="resource" id="newsresource" size="45" class="form-control" value="{{ implode(',', $resources) }}" data-uri="<?php echo url('/'); ?>/api/resources/%s" />
							</div>
						</div>
						<div class="form-group row tab-add tab-edit" id="TR_user">
							<label for="newsuser" class="col-sm-2 col-form-label">User</label>
							<div class="col-sm-10">
								<?php
								$usrs = array();
								if ($users = request('user'))
								{
									foreach (explode(',', $users) as $u)
									{
										if (trim($u))
										{
											$usr = $ws->get(ROOT_URI . 'user/' . $u);
											$usrs[] = $usr->name . ':' . $u . '';
										}
									}
								}
								?>
								<input name="user" id="newsuser" size="45" class="form-control" value="{{ implode(',', $usrs) }}" data-uri="<?php echo url('/'); ?>/api/users/%s" />
							</div>
						</div>
						<div class="form-group row tab-add tab-edit<?php echo isset($_GET['edit']) ? '' : ' stash'; ?>" id="TR_published">
							<label for="published" class="col-sm-2 col-form-label">
								Published
								<a href="#help2" class="help icn tip" title="Help">
									<i class="fa fa-question-circle" aria-hidden="true"></i> Help
								</a>
							</label>
							<div class="col-sm-10">
								<input type="checkbox" id="published" />
							</div>
						</div>
						<div class="form-group row tab-search" id="TR_template">
							<label for="template" class="col-sm-2 col-form-label">Template</label>
							<div class="col-sm-10">
								<input type="checkbox" id="template" />
							</div>
						</div>
						<div class="form-group row tab-add tab-edit stash" id="TR_use_template">
							<label for="template_select" class="col-sm-2 col-form-label">
								Template
								<a href="#help4" class="help icn tip" title="Help">
									<i class="fa fa-question-circle" aria-hidden="true"></i> Help
								</a>
							</label>
							<div class="col-sm-10">
								<select id="template_select" name="template_select" class="form-control">
									<option value="0">(No Template)</option>
									<option value="savetemplate">(Save as New Template)</option>
									<?php
									//$templates = $ws->get(ROOT_URI . 'news/template:1');

									foreach ($templates as $template)
									{
										echo '<option value="' . $template['id'] . '">' . e($template['headline']) . '</option>';
									}
									?>
								</select>
							</div>
						</div>
						<div class="form-group row tab-add tab-edit stash" id="TR_headline">
							<label for="Headline" class="col-sm-2 col-form-label">Headline</label>
							<div class="col-sm-10">
								<input id="Headline" name="headline" type="text" size="78" maxlength="255" class="form-control" />
							</div>
						</div>
						<div class="form-group row tab-search tab-add tab-edit" id="TR_location">
							<label for="location" class="col-sm-2 col-form-label">Location</label>
							<div class="col-sm-10">
								<input name="location" id="location" type="text" size="45" maxlength="32" class="form-control" />
							</div>
						</div>
						<div class="form-group row tab-add tab-edit stash" id="TR_url">
							<label for="url" class="col-sm-2 col-form-label">URL</label>
							<div class="col-sm-10">
								<input name="url" id="url" type="text" size="45" class="form-control" />
							</div>
						</div>
						<div class="form-group row tab-add tab-edit stash" id="TR_notes">
							<label for="NotesText" class="col-sm-2 col-form-label">
								News Text
								<a href="#help1" class="help icn tip" title="Help">
									<i class="fa fa-question-circle" aria-hidden="true"></i> Help
								</a>
							</label>
							<div class="col-sm-10">
								<textarea id="NotesText" rows="15" cols="77" class="form-control"></textarea>
							</div>
						</div>
						<div class="form-group row tab-search" id="TR_id">
							<label for="id" class="col-sm-2 col-form-label">NEWS#</label>
							<div class="col-sm-10">
								<input name="id" type="text" id="id" size="45" class="form-control" value="{{ request('id') }}" />
							</div>
						</div>
						<div class="form-group row tab-search" id="TR_search">
							<div class="col-sm-2">
							</div>
							<div class="col-sm-10 offset-sm-10">
								<input type="submit" class="btn btn-primary" value="Search" id="INPUT_search" />
								<input type="reset" class="btn btn-default" value="Clear" id="INPUT_clearsearch" />
							</div>
						</div>
						<div class="form-group row tab-add stash" id="TR_create">
							<div class="col-sm-2">
							</div>
							<div class="col-sm-10 offset-sm-10">
								<input id="INPUT_add" type="submit" class="btn btn-primary" data-add="Add News" data-edit="Save Changes" value="Add News" disabled="true" />
								<input id="INPUT_preview" type="button" class="btn btn-default" value="Preview" data-id="{{ request('id') }}" />
								<input id="INPUT_clear" type="reset" class="btn btn-default" data-add="Add News" data-edit="Save Changes" value="Clear" />
							</div>
						</div>

						<span id="TAB_search_action"></span>
						<span id="TAB_add_action"></span>
					</fieldset>
				</form>

				<?php
				$params = request()->input();
				if (count($params) > 0)
				{
					$valid_args = array('start', 'stop', 'newstype', 'id', 'resource');
					$string = '';

					foreach ($params as $key => $value)
					{
						if ($key != 'keywords' && $value != '')
						{
							if (in_array($key, $valid_args))
							{
								$string .= $key . ':' . $value . ' ';
							}
						}
						else
						{
							// Try to sanitize a little. We target quotes to prevent XSS attmepts
							$string .= str_replace('"', '', $value);
						}
					}
				}
				else
				{
					$string = 'start:0000-00-00';
				}

				if ($string == '')
				{
					$string = 'start:0000-00-00';
				}
				?>
				<p><strong id="matchingnews">Matching News</strong></p>
				<div id="news" data-query="<?php echo $string; ?>">
					News stories are loading...
				</div>
			</div>
		</div>
	</div>

<?php
$help1a = "The news interface supports basic font formatting:

*Bold* _example_, or you can have *_both_*.

These examples are fully interactive. Just type in the top box and see the formatting below live.";

$help1b = "Unordered lists can be made using '-' or '*' to denote list items. Ordered lists can be made in a similar fashion.
- This
- Is
* A
* List

1) One
2) Two
3. Three";

$help1c = "Hyperlinks can be made in the following way.

http://www.purdue.edu

[ITaP Research Computing](http://www.rcac.purdue.edu)

By using [Title] notation immediately preceding a URL in parentheses, you can give it another title.

Email addresses will automatically be converted into mailto links: rcac-help@purdue.edu";

$help1d = "You can also mention and link another news article by referencing it's news ID and the title of the article will be automatically retrieved:

NEWS#658

or you can replace the title of the article in the same way as hyperlinks.

NEWS#658{Give it another title}";

$help1e = "      The news interface will ignore any artificial
line breaking or   extra spaces .
A full empty line is required to
get a line break to display.



As well, extra line breaks are
ignored.";

$help1f = "Inline code can be created with single back-ticks to mark the beginning and end. Example: `this is inline code`. Code blocks can be created using triple back-ticks to mark the beginning and end of a code block. Text inside the code block will be exempt from other formatting rules and will display exactly as typed.

```
// This is an example of some code

int main (int argc, char * argv[]) {
	printf(\"hello world!\\n\");
	return 0;
}
```
";

$help1g = "Tables can be created using \"|\" to start a line to mark the beginning and end of a table row. Cell divisions in the table are marked by a single \"|\". The other formatting rules apply within the cells.

| *Node* | *Cores* | *Memory* |
| Carter-A | 16 | 32GB |
| Carter-B | 16 | 64GB |
";

$help1h = "Several variables are available to automatically fill in certain fields for a news articles. These include dates, resources, and location. Variables are denoted such as '%date%'. Below is a listing of possible variables.

Templates may also be written to contain placeholder text intended to be replaced by the author. These are variables that cannot be filled in automatically. They are denoted such as '%%insert text here%%'.

List of possible variables:
- %date%
- %datetime%
- %time%
- %startdatetime%
- %startdate%
- %starttime%
- %enddatetime%
- %enddate%
- %endtime%
- %resources%
- %location%

Additionally these variables are available inside updates and will be filled with appropriate values for each update:

- %updatetime%
- %updatedate%
- %updatedatetime%
";
?>
	<div id="help1" class="dialog dialog-help" title="Text Formatting">
		<ul>
			<li><a href="#help1a">Fonts</a></li>
			<li><a href="#help1b">Lists</a></li>
			<li><a href="#help1c">Links</a></li>
			<li><a href="#help1d">Other News</a></li>
			<li><a href="#help1e">Line Breaks</a></li>
			<li><a href="#help1f">Code</a></li>
			<li><a href="#help1g">Tables</a></li>
			<li><a href="#help1h">Variables</a></li>
		</ul>
		<div id="help1a">
			<?php
			$article = new App\Modules\News\Models\Article(['body' => $help1a]);
			?>
			<p>Input text: <textarea id="help1ainput" class="samplebox" data-sample="a"><?php echo $help1a; ?></textarea></p>
			<p>Output text: <br/><div id="help1aoutput" class="sampleoutput"><?php echo $article->body; ?></div></p>
		</div>
		<div id="help1b">
			<?php
			$article = new App\Modules\News\Models\Article(['body' => $help1b]);
			?>
			<p>Input text: <textarea id="help1binput" class="samplebox" data-sample="b"><?php echo $help1b; ?></textarea></p>
			<p>Output text: <br/><div id="help1boutput" class="sampleoutput"><?php echo $article->body; ?></div></p>
		</div>
		<div id="help1c">
			<?php
			$article = new App\Modules\News\Models\Article(['body' => $help1c]);
			?>
			<p>Input text: <textarea id="help1cinput" class="samplebox" data-sample="c"><?php echo $help1c; ?></textarea></p>
			<p>Output text: <br/><div id="help1coutput" class="sampleoutput"><?php echo $article->body; ?></div></p>
		</div>
		<div id="help1d">
			<?php
			$article = new App\Modules\News\Models\Article(['body' => $help1d]);
			?>
			<p>Input text: <textarea id="help1dinput" class="samplebox" data-sample="d"><?php echo $help1d; ?></textarea></p>
			<p>Output text: <br/><div id="help1doutput" class="sampleoutput"><?php echo $article->body; ?></div></p>
		</div>
		<div id="help1e">
			<?php
			$article = new App\Modules\News\Models\Article(['body' => $help1e]);
			?>
			<p>Input text: <textarea id="help1einput" class="samplebox" data-sample="e"><?php echo $help1e; ?></textarea></p>
			<p>Output text: <br/><div id="help1eoutput" class="sampleoutput"><?php echo $article->body; ?></div></p>
		</div>
		<div id="help1f">
			<?php
			$article = new App\Modules\News\Models\Article(['body' => $help1f]);
			?>
			<p>Input text: <textarea id="help1finput" class="samplebox" data-sample="f"><?php echo $help1f; ?></textarea></p>
			<p>Output text: <br/><div id="help1foutput" class="sampleoutput"><?php echo $article->body; ?></div></p>
		</div>
		<div id="help1g">
			<?php
			$article = new App\Modules\News\Models\Article(['body' => $help1g]);
			?>
			<p>Input text: <textarea id="help1ginput" class="samplebox" data-sample="g"><?php echo $help1g; ?></textarea></p>
			<p>Output text: <br/><div id="help1goutput" class="sampleoutput"><?php echo $article->body; ?></div></p>
		</div>
		<div id="help1h">
			<p>Input text: <textarea id="help1hinput" class="samplebox" data-sample="h"><?php echo $help1h; ?></textarea></p>
			<p>Output text: <br/><div id="help1houtput" class="sampleoutput"></div></p>
		</div>
	</div>

	<div id="help2" class="dialog dialog-help" title="Published">
		<p>Check this box if you wish to publish this new article to the website for the public to see. Leaving this box unchecked will create the article in draft mode where only other news editors can read it.</p>
		<p>To publish this article later you can click the 'newspaper' icon in the news article header within the management interface.</p>
	</div>

	<div id="help3" class="dialog dialog-help" title="Update">
		<p>Check this box if you wish to publically flag this news article as being updated. Articles will be flagged as being updated with the current timestamp.</p>
		<p>Typically this box is used when adding new information to an article. Minor corrections to articles, such as fixing typos, do not need to be publicized as being updated.</p>
	</div>

	<div id="help4" class="dialog dialog-help" title="Template">
		<p>From this drop down, you may choose to create a new template, populate article from a template, or leave the selection alone and create an article from scratch.</p>
	</div>

	<div id="preview" class="dialog" title="News Preview">
	</div>

	<div id="mailpreview" class="dialog" title="Mail Preview">
	</div>

	<div id="dialog-confirm" class="dialog" title="Unsaved Changes">
		<p>You have unsaved changes that need to be saved before mailing news item.</p>
		<p>Would you like to save the changes?</p>
	</div>

	<?php
	if ($id = request('id'))
	{
		$news = App\Modules\News\Models\Article::findOrFail($id);

		if ($news->authorized)
		{
			$value = explode(' ', $news->newsdate);
			$startdate = $value[0];
			$starttime = $value[1];
			// Convert to human readable form
			$values = explode(':', $starttime);
			if ($values[0] > 12)
			{
				$values[0] -= 12;
				$starttime = $values[0] . ':' . $values[1] . ' PM';
			}
			else if ($values[0] == 12)
			{
				$starttime = $values[0] . ':' . $values[1] . ' PM';
			}
			else if ($values[0] == 0)
			{
				$values[0] += 12;
				$starttime = $values[0] . ':' . $values[1] . ' AM';
			}
			else
			{
				$starttime = $values[0] . ':' . $values[1] . ' AM';
			}
			$starttime = preg_replace('/^0/', '', $starttime);

			$value = explode(' ', $news->newsdateend);
			$stopdate = $value[0];
			$stoptime = $value[1];
			// Convert to human readable form
			$values = explode(':', $stoptime);
			if ($values[0] > 12)
			{
				$values[0] -= 12;
				$stoptime = $values[0] . ':' . $values[1] . ' PM';
			}
			else if ($values[0] == 12)
			{
				$stoptime = $values[0] . ':' . $values[1] . ' PM';
			}
			else if ($values[0] == 0)
			{
				$values[0] += 12;
				$stoptime = $values[0] . ':' . $values[1] . ' AM';
			}
			else
			{
				$stoptime = $values[0] . ':' . $values[1] . ' AM';
			}
			$stoptime = preg_replace('/^0/', '', $stoptime);

			// Only display 12:00AM in certain circumstances
			if ($starttime == '12:00 AM' && $stoptime == '12:00 AM')
			{
				$starttime = $stoptime = '';
			}
			if ($stopdate == '0000-00-00')
			{
				$stoptime = '';
			}
			if ($stopdate == '0000-00-00' && $starttime == '12:00 AM')
			{
				$starttime = '';
			}
			?>
			<script type="application/json" id="news-data">
				{
					"id": "<?php echo $news->id; ?>",
					"newsdate": "<?php echo $news->newsdate; ?>",
					"newsdateend": "<?php echo $news->newsdateend; ?>",
					"news": "<?php echo preg_replace('/\n/', '\\n', $news->news); ?>",
					"headline": "<?php echo e($news->headline); ?>",
					"newstype": "<?php echo $news->newstypeid; ?>",
					"published": "<?php echo $news->published; ?>",
					"lastedit": "<?php echo $news->editdate; ?>",
					"location": "<?php echo e($news->location); ?>",
					"url": "<?php echo $news->url; ?>",
					"startdate": "<?php echo $startdate; ?>",
					"stopdate": "<?php echo $stopdate; ?>",
					"starttime": "<?php echo $starttime; ?>",
					"stoptime": "<?php echo $stoptime; ?>",
					"resources": <?php echo json_encode($news->resources); ?>,
					"associations": <?php echo json_encode($news->associations); ?>
				}
			</script>
			<?php
		}
	}
	?>
</div>
@stop