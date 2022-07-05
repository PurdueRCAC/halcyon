<?php
$help1a = "The news interface supports basic font formatting:

**Bold** _example_, or you can have **_both_**.

These examples are fully interactive. Just type in the top box and see the formatting below live.";

$help1b = "Unordered lists can be made using '-' or '*' to denote list items. Ordered lists can be made in a similar fashion.
- This
- Is
* A
* List

1. One
2. Two
3. Three";

$help1c = "Hyperlinks can be made in the following way.

http://www.example.edu

[Example University](http://www.example.edu)

By using [Title] notation immediately preceding a URL in parentheses, you can give it another title.

Email addresses will automatically be converted into mailto links: help@example.edu";

$help1d = "You can also mention and link another news article by referencing its news ID and the title of the article will be automatically retrieved:

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

| *Node*   | *Cores* | *Memory* |
|----------|--------:|---------:|
| Carter-A |      16 |     32GB |
| Carter-B |      16 |     64GB |
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

$help1i = 'Images can be included using a similar syntax as links but prefixing with an exclamation mark:

![Halcyon logo](/themes/admin/images/halcyon.svg)
';
?>
	<div id="markdown-help" data-api="{{ route('api.news.preview') }}" class="dialog dialog-help tabs" data-width="700" title="MarkDown Help">
		<ul>
			<li><a href="#help1a">Fonts</a></li>
			<li><a href="#help1b">Lists</a></li>
			<li><a href="#help1c">Links</a></li>
			<li><a href="#help1d">Other News</a></li>
			<li><a href="#help1e">Line Breaks</a></li>
			<li><a href="#help1f">Code</a></li>
			<li><a href="#help1g">Tables</a></li>
			<li><a href="#help1h">Variables</a></li>
			<li><a href="#help1i">Images</a></li>
		</ul>
		<div id="help1a">
			<?php
			$article = new App\Modules\News\Models\Article(['body' => $help1a]);
			?>
			<div class="form-group">
				<label for="help1ainput">Input text:</label>
				<textarea id="help1ainput" class="form-control samplebox" rows="5" data-sample="a"><?php echo $help1a; ?></textarea>
			</div>
			<p>Output text:<p>
			<div id="help1aoutput" class="sampleoutput"><?php echo $article->formattedbody; ?></div>
		</div>
		<div id="help1b">
			<?php
			$article = new App\Modules\News\Models\Article(['body' => $help1b]);
			?>
			<div class="form-group">
				<label for="help1binput">Input text:</label>
				<textarea id="help1binput" class="form-control samplebox" rows="5" data-sample="b"><?php echo $help1b; ?></textarea>
			</div>
			<p>Output text:</p>
			<div id="help1boutput" class="sampleoutput"><?php echo $article->formattedBody; ?></div>
		</div>
		<div id="help1c">
			<?php
			$article = new App\Modules\News\Models\Article(['body' => $help1c]);
			?>
			<div class="form-group">
				<label for="help1cinput">Input text:</label>
				<textarea id="help1cinput" class="form-control samplebox" rows="5" data-sample="c"><?php echo $help1c; ?></textarea>
			</div>
			<p>Output text:</p>
			<div id="help1coutput" class="sampleoutput"><?php echo $article->formattedBody; ?></div>
		</div>
		<div id="help1d">
			<?php
			$article = new App\Modules\News\Models\Article(['body' => $help1d]);
			?>
			<div class="form-group">
				<label for="help1dinput">Input text:</label>
				<textarea id="help1dinput" class="form-control samplebox" rows="5" data-sample="d"><?php echo $help1d; ?></textarea>
			</div>
			<p>Output text:</p>
			<div id="help1doutput" class="sampleoutput"><?php echo $article->formattedBody; ?></div>
		</div>
		<div id="help1e">
			<?php
			$article = new App\Modules\News\Models\Article(['body' => $help1e]);
			?>
			<div class="form-group">
				<label for="help1einput">Input text:</label>
				<textarea id="help1einput" class="form-control samplebox" rows="5" data-sample="e"><?php echo $help1e; ?></textarea>
			</div>
			<p>Output text:</p>
			<div id="help1eoutput" class="sampleoutput"><?php echo $article->formattedBody; ?></div>
		</div>
		<div id="help1f">
			<?php
			$article = new App\Modules\News\Models\Article(['body' => $help1f]);
			?>
			<div class="form-group">
				<label for="help1finput">Input text:</label>
				<textarea id="help1finput" class="form-control samplebox" rows="5" data-sample="f"><?php echo $help1f; ?></textarea>
			</div>
			<p>Output text:</p>
			<div id="help1foutput" class="sampleoutput"><?php echo $article->formattedBody; ?></div>
		</div>
		<div id="help1g">
			<?php
			$article = new App\Modules\News\Models\Article(['body' => $help1g]);
			?>
			<div class="form-group">
				<label for="help1ginput">Input text:</label>
				<textarea id="help1ginput" class="form-control samplebox" rows="5" data-sample="g"><?php echo $help1g; ?></textarea>
			</div>
			<p>Output text:</p>
			<div id="help1goutput" class="sampleoutput"><?php echo $article->formattedBody; ?></div>
		</div>
		<div id="help1h">
			<?php
			$article = new App\Modules\News\Models\Article(['body' => $help1h]);
			$article->datetimenews = Carbon\Carbon::now();
			$article->datetimenewsend = Carbon\Carbon::now()->modify('+3 hours');
			$article->location = '123 Some Street';
			?>
			<div class="form-group">
				<label for="help1hinput">Input text:</label>
				<textarea id="help1hinput" class="form-control samplebox" rows="5" data-sample="h"><?php echo $help1h; ?></textarea>
			</div>
			<p>Output text:</p>
			<div id="help1goutput" class="sampleoutput"><?php echo $article->formattedBody; ?></div>
		</div>
		<div id="help1i">
			<?php
			$article = new App\Modules\News\Models\Article(['body' => $help1i]);
			?>
			<div class="form-group">
				<label for="help1ginput">Input text:</label>
				<textarea id="help1ginput" class="form-control samplebox" rows="5" data-sample="g"><?php echo $help1i; ?></textarea>
			</div>
			<p>Output text:</p>
			<div id="help1goutput" class="sampleoutput"><?php echo $article->formattedBody; ?></div>
		</div>
	</div>
