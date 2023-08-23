<?php
namespace App\Modules\Publications\Helpers\Parsers;

use Exception;

/**
 * Structures_BibTex
 *
 * A class which provides common methods to access and
 * create Strings in BibTex format.
 * Example 1: Parsing a BibTex File and returning the number of entries
 *
 * <code>
 * $bibtex = new Structures_BibTex();
 * $ret    = $bibtex->loadFile('foo.bib');
 * $bibtex->parse();
 * print "There are ".$bibtex->amount()." entries";
 * </code>
 *
 * Example 2: Parsing a BibTex File and getting all Titles
 * <code>
 * $bibtex = new Structures_BibTex();
 * $bibtex->loadFile('bibtex.bib');
 * $bibtex->parse();
 * foreach ($bibtex->data as $entry)
 * {
 *     print $entry['title']."<br />";
 * }
 * </code>
 *
 * Example 3: Adding an entry and printing it in BibTex Format
 * <code>
 * $bibtex                         = new Structures_BibTex();
 * $addarray                       = array();
 * $addarray['type']               = 'Article';
 * $addarray['cite']               = 'art2';
 * $addarray['title']              = 'Titel2';
 * $addarray['author'][0]['first'] = 'John';
 * $addarray['author'][0]['last']  = 'Doe';
 * $addarray['author'][1]['first'] = 'Jane';
 * $addarray['author'][1]['last']  = 'Doe';
 * $bibtex->addEntry($addarray);
 * print nl2br($bibtex->bibTex());
 * </code>
 *
 * @category  Structures
 * @package   Structures_BibTex
 * @author    Elmar Pitschke <elmar.pitschke@gmx.de>
 * @copyright 1997-2005 The PHP Group
 * @license   http://www.php.net/license/3_0.txt PHP License 3.0
 * @version   Release: @package_version@
 * @link      http://pear.php.net/Structures/Structure_BibTex
 */
class BibTex
{
	/**
	 * Array with the BibTex Data
	 *
	 * @var    array<int,array{string,string}>
	 */
	public $data = array();

	/**
	 * String with the BibTex content
	 *
	 * @var    string
	 */
	public $content = '';

	/**
	 * Array with possible Delimiters for the entries
	 *
	 * @var    array<string,string>
	 */
	private $_delimiters = array(
		'"' => '"',
		'{' => '}'
	);

	/**
	 * Array to store warnings
	 *
	 * @var    array<int,array{string,string}>
	 */
	public $warnings = array();

	/**
	 * Run-time configuration options
	 *
	 * @var    array<string,mixed>
	 */
	private $_options = array(
		'stripDelimiter'    => true,
		'validate'          => true,
		'unwrap'            => false,
		'wordWrapWidth'     => false,
		'wordWrapBreak'     => "\n",
		'wordWrapCut'       => 0,
		'removeCurlyBraces' => true,
		'extractAuthors'    => true,
	);

	/**
	 * RTF Format String
	 *
	 * @var    string
	 */
	public $rtfstring = 'AUTHORS, "{\b TITLE}", {\i JOURNAL}, YEAR';

	/**
	 * HTML Format String
	 *
	 * @var    string
	 */
	public $htmlstring = '<tr><td>AUTHORS, "<strong>TITLE</strong>", <em>JOURNAL</em>, VOLUME, PAGES, PUBLISHER, YEAR</td></tr>';

	/**
	 * Array with the "allowed" types
	 *
	 * @var    array<int,string>
	 */
	public $allowedTypes = array(
		'article',
		'book',
		'booklet',
		'conference',
		'inbook',
		'incollection',
		'inproceedings',
		'manual',
		'masterthesis',
		'misc',
		'phdthesis',
		'proceedings',
		'techreport',
		'unpublished',
		'xarchive',
		'magazine',
		'patent appl',
		'book',
		'chapter',
		'notes',
		'letter',
		'manuscript'
	);

	/**
	 * Author Format Strings
	 *
	 * @var    string
	 */
	public $authorstring = 'VON LAST JR, FIRST';

	/**
	 * Constructor
	 *
	 * @param  array<string,mixed>  $options
	 * @return void
	 */
	public function __construct($options = array())
	{
		foreach ($options as $option => $value)
		{
			$test = $this->setOption($option, $value);
			if ($test instanceof Exception)
			{
				//Currently nothing is done here, but it could for example raise an warning
			}
		}
	}

	/**
	 * Sets run-time configuration options
	 *
	 * @param  string $option option name
	 * @param  mixed  $value  value for the option
	 * @return true|Exception  true on success Exception on failure
	 */
	public function setOption($option, $value)
	{
		$ret = true;
		if (array_key_exists($option, $this->_options))
		{
			$this->_options[$option] = $value;
		}
		else
		{
			$ret = new Exception('Unknown option ' . $option);
		}
		return $ret;
	}

	/**
	 * Reads a give BibTex File
	 *
	 * @param  string $filename Name of the file
	 * @return true|Exception  true on success Exception on failure
	 */
	public function loadFile($filename)
	{
		if (file_exists($filename))
		{
			if (($this->content = @file_get_contents($filename)) === false)
			{
				return new Exception('Could not open file ' . $filename);
			}
			/*else
			{
				$this->_pos    = 0;
				$this->_oldpos = 0;
				return true;
			}*/
		}

		return new Exception('Could not find file ' . $filename);
	}

	/**
	 * Adds to the content string
	 *
	 * @param   string  $bibstring Name of the file
	 * @return  void   True on success Exception on failure
	 */
	public function addContent($bibstring)
	{
		$this->content .= $bibstring;
	}

	/**
	 * Parses what is stored in content and clears the content if the parsing is successful.
	 *
	 * @return true|Exception true on success and Exception if there was a problem
	 */
	public function parse()
	{
		//The amount of opening braces is compared to the amount of closing braces
		//Braces inside comments are ignored
		$this->warnings = array();
		$this->data     = array();
		$valid          = true;
		$open           = 0;
		$entry          = false;
		$char           = '';
		$lastchar       = '';
		$buffer         = '';
		for ($i = 0; $i < strlen($this->content); $i++)
		{
			$char = substr($this->content, $i, 1);
			if ((0 != $open) && ('@' == $char))
			{
				if (!$this->_checkAt($buffer))
				{
					$this->_generateWarning('WARNING_MISSING_END_BRACE', '', $buffer);
					//To correct the data we need to insert a closing brace
					$char     = '}';
					$i--;
				}
			}
			if ((0 == $open) && ('@' == $char))
			{ //The beginning of an entry
				$entry = true;
			}
			elseif ($entry && ('{' == $char) && ('\\' != $lastchar))
			{ //Inside an entry and non quoted brace is opening
				$open++;
			}
			elseif ($entry && ('}' == $char) && ('\\' != $lastchar))
			{ //Inside an entry and non quoted brace is closing
				$open--;
				if ($open < 0)
				{ //More are closed than opened
					$valid = false;
				}
				if (0 == $open)
				{ //End of entry
					$entry     = false;
					$entrydata = $this->_parseEntry($buffer);
					if (!$entrydata)
					{
						/**
						 * This is not yet used.
						 * We are here if the Entry is either not correct or not supported.
						 * But this should already generate a warning.
						 * Therefore it should not be necessary to do anything here
						 */
					}
					else
					{
						$this->data[] = $entrydata;
					}
					$buffer = '';
				}
			}
			if ($entry)
			{ //Inside entry
				$buffer .= $char;
			}
			$lastchar = $char;
		}
		//If open is one it may be possible that the last ending brace is missing
		if (1 == $open)
		{
			$entrydata = $this->_parseEntry($buffer);
			if (!$entrydata)
			{
				$valid = false;
			}
			else
			{
				$this->data[] = $entrydata;
				$buffer = '';
				$open   = 0;
			}
		}
		//At this point the open should be zero
		if (0 != $open)
		{
			$valid = false;
		}
		//Are there Multiple entries with the same cite?
		if ($this->_options['validate'])
		{
			$cites = array();
			foreach ($this->data as $entry)
			{
				$cites[] = $entry['cite'];
			}
			$unique = array_unique($cites);
			if (count($cites) != count($unique))
			{ //Some values have not been unique!
				$notuniques = array();
				for ($i = 0; $i < count($cites); $i++)
				{
					if (isset($unique[$i]) && '' == $unique[$i])
					{
						$notuniques[] = $cites[$i];
					}
				}
				$this->_generateWarning('WARNING_MULTIPLE_ENTRIES', implode(',', $notuniques));
			}
		}

		if ($valid)
		{
			$this->content = '';
			return true;
		}

		return new Exception('Unbalanced parenthesis');
	}

	/**
	 * Extracting the data of one content
	 *
	 * The parse function splits the content into its entries.
	 * Then every entry is parsed by this function.
	 * It parses the entry backwards.
	 * First the last '=' is searched and the value extracted from that.
	 * A copy is made of the entry if warnings should be generated. This takes quite
	 * some memory but it is needed to get good warnings. If nor warnings are generated
	 * then you don have to worry about memory.
	 * Then the last ',' is searched and the field extracted from that.
	 * Again the entry is shortened.
	 * Finally after all field=>value pairs the cite and type is extraced and the
	 * authors are splitted.
	 * If there is a problem false is returned.
	 *
	 * @param  string  $entry The entry
	 * @return array<string,mixed>   The representation of the entry or false if there is a problem
	 */
	private function _parseEntry($entry)
	{
		$entrycopy = '';
		if ($this->_options['validate'])
		{
			$entrycopy = $entry; //We need a copy for printing the warnings
		}
		$ret = array();
		if ('@string' ==  strtolower(substr($entry, 0, 7)))
		{
			//String are not yet supported!
			if ($this->_options['validate'])
			{
				$this->_generateWarning('STRING_ENTRY_NOT_YET_SUPPORTED', '', $entry.'}');
			}
		}
		elseif ('@preamble' ==  strtolower(substr($entry, 0, 9)))
		{
			//Preamble not yet supported!
			if ($this->_options['validate'])
			{
				$this->_generateWarning('PREAMBLE_ENTRY_NOT_YET_SUPPORTED', '', $entry.'}');
			}
		}
		else
		{
			//Parsing all fields
			while (strrpos($entry, '=') !== false)
			{
				$position = strrpos($entry, '=');
				//Checking that the equal sign is not quoted or is not inside a equation (For example in an abstract)
				$proceed  = true;
				if (substr($entry, $position-1, 1) == '\\')
				{
					$proceed = false;
				}
				if ($proceed)
				{
					$proceed = $this->_checkEqualSign($entry, $position);
				}
				while (!$proceed)
				{
					$substring = substr($entry, 0, $position);
					$position  = strrpos($substring, '=');
					$proceed   = true;
					if (substr($entry, $position-1, 1) == '\\')
					{
						$proceed = false;
					}
					if ($proceed)
					{
						$proceed = $this->_checkEqualSign($entry, $position);
					}
				}

				$value = trim(substr($entry, $position+1));
				$entry = substr($entry, 0, $position);

				if (',' == substr($value, strlen($value)-1, 1))
				{
					$value = substr($value, 0, -1);
				}
				if ($this->_options['validate'])
				{
					$this->_validateValue($value, $entrycopy);
				}
				if ($this->_options['stripDelimiter'])
				{
					$value = $this->_stripDelimiter($value);
				}
				if ($this->_options['unwrap'])
				{
					$value = $this->_unwrap($value);
				}
				if ($this->_options['removeCurlyBraces'])
				{
					$value = $this->_removeCurlyBraces($value);
				}
				$position    = strrpos($entry, ',');
				$field       = strtolower(trim(substr($entry, $position+1)));
				$ret[$field] = $value;
				$entry       = substr($entry, 0, $position);
			}
			//Parsing cite and type
			$arr = preg_split('#{#', $entry);
			$ret['cite'] = trim($arr[1]);
			$ret['type'] = strtolower(trim($arr[0]));
			if ('@' == $ret['type'][0])
			{
				$ret['type'] = substr($ret['type'], 1);
			}
			if ($this->_options['validate'])
			{
				if (!$this->_checkAllowedType($ret['type']))
				{
					$this->_generateWarning('WARNING_NOT_ALLOWED_TYPE', $ret['type'], $entry.'}');
				}
			}
			//Handling the authors
			if (in_array('author', array_keys($ret)) && $this->_options['extractAuthors'])
			{
				$ret['author'] = $this->_extractAuthors($ret['author']);
			}
		}

		return $ret;
	}

	/**
	 * Checking whether the position of the '=' is correct
	 *
	 * Sometimes there is a problem if a '=' is used inside an entry (for example abstract).
	 * This method checks if the '=' is outside braces then the '=' is correct and true is returned.
	 * If the '=' is inside braces it contains to a equation and therefore false is returned.
	 *
	 * @param  string  $entry The text of the whole remaining entry
	 * @param  int     $position current used place of the '='
	 * @return bool    true if the '=' is correct, false if it contains to an equation
	 */
	private function _checkEqualSign($entry, $position)
	{
		$ret = true;
		//This is getting tricky
		//We check the string backwards until the position and count the closing an opening braces
		//If we reach the position the amount of opening and closing braces should be equal
		$length = strlen($entry);
		$open   = 0;
		for ($i = $length-1; $i >= $position; $i--)
		{
			$precedingchar = substr($entry, $i-1, 1);
			$char          = substr($entry, $i, 1);
			if (('{' == $char) && ('\\' != $precedingchar))
			{
				$open++;
			}
			if (('}' == $char) && ('\\' != $precedingchar))
			{
				$open--;
			}
		}
		if (0 != $open)
		{
			$ret = false;
		}
		//There is still the posibility that the entry is delimited by double quotes.
		//Then it is possible that the braces are equal even if the '=' is in an equation.
		if ($ret)
		{
			$entrycopy = trim($entry);
			$lastchar  = $entrycopy[strlen($entrycopy)-1];
			if (',' == $lastchar)
			{
				$lastchar = $entrycopy[strlen($entrycopy)-2];
			}
			if ('"' == $lastchar)
			{
				//The return value is set to false
				//If we find the closing " before the '=' it is set to true again.
				//Remember we begin to search the entry backwards so the " has to show up twice - ending and beginning delimiter
				$ret = false;
				$found = 0;
				for ($i = $length; $i >= $position; $i--)
				{
					$precedingchar = substr($entry, $i-1, 1);
					$char          = substr($entry, $i, 1);
					if (('"' == $char) && ('\\' != $precedingchar))
					{
						$found++;
					}
					if (2 == $found)
					{
						$ret = true;
						break;
					}
				}
			}
		}
		return $ret;
	}

	/**
	 * Checking if the type is allowed
	 *
	 * @param  string  $entry The entry to check
	 * @return bool    true if allowed, false otherwise
	 */
	private function _checkAllowedType($entry)
	{
		return in_array($entry, $this->allowedTypes);
	}

	/**
	 * Checking whether an at is outside an entry
	 *
	 * Sometimes an entry misses an entry brace. Then the at of the next entry seems to be
	 * inside an entry. This is checked here. When it is most likely that the at is an opening
	 * at of the next entry this method returns true.
	 *
	 * @param  string  $entry The text of the entry until the at
	 * @return bool    true if the at is correct, false if the at is likely to begin the next entry.
	 */
	private function _checkAt($entry)
	{
		$ret     = false;
		$opening = array_keys($this->_delimiters);
		$closing = array_values($this->_delimiters);
		//Getting the value (at is only allowd in values)
		if (strrpos($entry, '=') !== false)
		{
			$position = strrpos($entry, '=');
			$proceed  = true;
			if (substr($entry, $position-1, 1) == '\\')
			{
				$proceed = false;
			}
			while (!$proceed)
			{
				$substring = substr($entry, 0, $position);
				$position  = strrpos($substring, '=');
				$proceed   = true;
				if (substr($entry, $position-1, 1) == '\\')
				{
					$proceed = false;
				}
			}
			$value    = trim(substr($entry, $position+1));
			$open     = 0;
			$char     = '';
			$lastchar = '';
			for ($i = 0; $i < strlen($value); $i++)
			{
				$char = substr($this->content, $i, 1);
				if (in_array($char, $opening) && ('\\' != $lastchar))
				{
					$open++;
				}
				elseif (in_array($char, $closing) && ('\\' != $lastchar))
				{
					$open--;
				}
				$lastchar = $char;
			}
			//if open is grater zero were are inside an entry
			if ($open>0)
			{
				$ret = true;
			}
		}

		return $ret;
	}

	/**
	 * Stripping Delimiter
	 *
	 * @param  string  $entry The entry where the Delimiter should be stripped from
	 * @return string  Stripped entry
	 */
	private function _stripDelimiter($entry)
	{
		$beginningdels = array_keys($this->_delimiters);
		$length        = strlen($entry);
		$firstchar     = substr($entry, 0, 1);
		$lastchar      = substr($entry, -1, 1);

		while (in_array($firstchar, $beginningdels))
		{ //The first character is an opening delimiter
			if ($lastchar == $this->_delimiters[$firstchar])
			{ //Matches to closing Delimiter
				$entry = substr($entry, 1, -1);
			}
			else
			{
				break;
			}
			$firstchar = substr($entry, 0, 1);
			$lastchar  = substr($entry, -1, 1);
		}

		return $entry;
	}

	/**
	 * Unwrapping entry
	 *
	 * @param  string  $entry The entry to unwrap
	 * @return string  unwrapped entry
	 */
	private function _unwrap($entry)
	{
		$entry = preg_replace('/\s+/', ' ', $entry);
		return trim($entry);
	}

	/**
	 * Wordwrap an entry
	 *
	 * @param  string  $entry The entry to wrap
	 * @return string  wrapped entry
	 */
	private function _wordwrap($entry)
	{
		if (('' != $entry) && (is_string($entry)))
		{
			$entry = wordwrap($entry, $this->_options['wordWrapWidth'], $this->_options['wordWrapBreak'], $this->_options['wordWrapCut']);
		}
		return $entry;
	}

	/**
	 * Extracting the authors
	 *
	 * @param  string  $entry The entry with the authors
	 * @return array<int,array{string,string}>   the extracted authors
	 */
	private function _extractAuthors($entry)
	{
		$entry       = $this->_unwrap($entry);
		$authorarray = array();
		$authorarray = preg_split('# and #', $entry);

		for ($i = 0; $i < count($authorarray); $i++)
		{
			$author = trim($authorarray[$i]);
			/*The first version of how an author could be written (First von Last)
			 has no commas in it*/
			$first    = '';
			$von      = '';
			$last     = '';
			$jr       = '';
			if (strpos($author, ',') === false)
			{
				$tmparray = array();
				//$tmparray = explode(' ', $author);
				$tmparray = preg_split('# |~#', $author);
				$size     = count($tmparray);
				if (1 == $size)
				{ //There is only a last
					$last = $tmparray[0];
				}
				elseif (2 == $size)
				{ //There is a first and a last
					$first = $tmparray[0];
					$last  = $tmparray[1];
				}
				else
				{
					$invon  = false;
					$inlast = false;
					for ($j=0; $j<($size-1); $j++)
					{
						if ($inlast)
						{
							$last .= ' ' . $tmparray[$j];
						}
						elseif ($invon)
						{
							$case = $this->_determineCase($tmparray[$j]);
							if ($case instanceof Exception)
							{
								// IGNORE?
							}
							elseif ((0 == $case) || (-1 == $case))
							{ //Change from von to last
								//You only change when there is no more lower case there
								$islast = true;
								for ($k=($j+1); $k<($size-1); $k++)
								{
									$futurecase = $this->_determineCase($tmparray[$k]);
									if ($futurecase instanceof Exception)
									{
										// IGNORE?
									}
									elseif (0 == $futurecase)
									{
										$islast = false;
									}
								}
								if ($islast)
								{
									$inlast = true;
									if (-1 == $case)
									{ //Caseless belongs to the last
										$last .= ' ' . $tmparray[$j];
									}
									else
									{
										$von .= ' ' . $tmparray[$j];
									}
								}
								else
								{
									$von .= ' ' . $tmparray[$j];
								}
							}
							else
							{
								$von .= ' ' . $tmparray[$j];
							}
						}
						else
						{
							$case = $this->_determineCase($tmparray[$j]);
							if ($case instanceof Exception)
							{
								// IGNORE?
							}
							elseif (0 == $case)
							{ //Change from first to von
								$invon = true;
								$von .= ' ' . $tmparray[$j];
							}
							else
							{
								$first .= ' ' . $tmparray[$j];
							}
						}
					}
					//The last entry is always the last!
					$last .= ' ' . $tmparray[$size-1];
				}
			}
			else
			{ //Version 2 and 3
				$tmparray     = array();
				$tmparray     = explode(',', $author);
				//The first entry must contain von and last
				$vonlastarray = array();
				$vonlastarray = explode(' ', $tmparray[0]);
				$size         = count($vonlastarray);
				if (1==$size)
				{ //Only one entry->got to be the last
					$last = $vonlastarray[0];
				}
				else
				{
					$inlast = false;
					for ($j=0; $j<($size-1); $j++)
					{
						if ($inlast)
						{
							$last .= ' '.$vonlastarray[$j];
						}
						else
						{
							$cs = $this->_determineCase($vonlastarray[$j]);
							if ($cs instanceof Exception || 0 != $cs)
							{ //Change from von to last
								$islast = true;
								for ($k=($j+1); $k<($size-1); $k++)
								{
									$this->_determineCase($vonlastarray[$k]);
									$case = $this->_determineCase($vonlastarray[$k]);
									if ($case instanceof Exception)
									{
										// IGNORE?
									}
									elseif (0 == $case)
									{
										$islast = false;
									}
								}
								if ($islast)
								{
									$inlast = true;
									$last .= ' ' . $vonlastarray[$j];
								}
								else
								{
									$von .= ' ' . $vonlastarray[$j];
								}
							}
							else
							{
								$von .= ' ' . $vonlastarray[$j];
							}
						}
					}
					$last .= ' '.$vonlastarray[$size-1];
				}
				//Now we check if it is version three (three entries in the array (two commas)
				if (3 == count($tmparray))
				{
					$jr = $tmparray[1];
				}
				//Everything in the last entry is first
				$first = $tmparray[count($tmparray)-1];
			}
			$authorarray[$i] = array(
				'first' => trim($first),
				'von'   => trim($von),
				'last'  => trim($last),
				'jr'    => trim($jr)
			);
		}
		return $authorarray;
	}

	/**
	 * Case Determination according to the needs of BibTex
	 *
	 * To parse the Author(s) correctly a determination is needed
	 * to get the Case of a word. There are three possible values:
	 * - Upper Case (return value 1)
	 * - Lower Case (return value 0)
	 * - Caseless   (return value -1)
	 *
	 * @param  string  $word
	 * @return int|Exception The Case or Exception if there was a problem
	 */
	private function _determineCase($word)
	{
		$ret         = -1;
		$trimmedword = trim ($word);
		/*We need this variable. Without the next of would not work
		 (trim changes the variable automatically to a string!)*/
		if (is_string($word) && (strlen($trimmedword) > 0))
		{
			$i         = 0;
			$found     = false;
			$openbrace = 0;
			while (!$found && ($i <= strlen($word)))
			{
				$letter = substr($trimmedword, $i, 1);
				$ord    = ord($letter);
				if ($ord == 123)
				{ //Open brace
					$openbrace++;
				}
				if ($ord == 125)
				{ //Closing brace
					$openbrace--;
				}
				if (($ord>=65) && ($ord<=90) && (0==$openbrace))
				{ //The first character is uppercase
					$ret   = 1;
					$found = true;
				}
				elseif (($ord>=97) && ($ord<=122) && (0==$openbrace))
				{ //The first character is lowercase
					$ret   = 0;
					$found = true;
				}
				else
				{ //Not yet found
					$i++;
				}
			}
		}
		else
		{
			$ret = new Exception('Could not determine case on word: '.(string)$word);
		}

		return $ret;
	}

	/**
	 * Validation of a value
	 *
	 * There may be several problems with the value of a field.
	 * These problems exist but do not break the parsing.
	 * If a problem is detected a warning is appended to the array warnings.
	 *
	 * @param  string  $entry      The entry aka one line which which should be validated
	 * @param  string  $wholeentry The whole BibTex Entry which the one line is part of
	 * @return void
	 */
	private function _validateValue($entry, $wholeentry)
	{
		//There is no @ allowed if the entry is enclosed by braces
		if (preg_match('/^{.*@.*}$/', $entry))
		{
			$this->_generateWarning('WARNING_AT_IN_BRACES', $entry, $wholeentry);
		}
		//No escaped " allowed if the entry is enclosed by double quotes
		if (preg_match('/^\".*\\".*\"$/', $entry))
		{
			$this->_generateWarning('WARNING_ESCAPED_DOUBLE_QUOTE_INSIDE_DOUBLE_QUOTES', $entry, $wholeentry);
		}
		//Amount of Braces is not correct
		$open     = 0;
		$lastchar = '';
		$char     = '';
		for ($i = 0; $i < strlen($entry); $i++)
		{
			$char = substr($entry, $i, 1);
			if (('{' == $char) && ('\\' != $lastchar))
			{
				$open++;
			}
			if (('}' == $char) && ('\\' != $lastchar))
			{
				$open--;
			}
			$lastchar = $char;
		}
		if (0 != $open)
		{
			$this->_generateWarning('WARNING_UNBALANCED_AMOUNT_OF_BRACES', $entry, $wholeentry);
		}
	}

	/**
	 * Remove curly braces from entry
	 *
	 * @param  string  $value The value in which curly braces to be removed
	 * @return string
	 */
	private function _removeCurlyBraces($value)
	{
		//First we save the delimiters
		$beginningdels = array_keys($this->_delimiters);
		$firstchar     = substr($value, 0, 1);
		$lastchar      = substr($value, -1, 1);
		$begin         = '';
		$end           = '';
		while (in_array($firstchar, $beginningdels))
		{ //The first character is an opening delimiter
			if ($lastchar == $this->_delimiters[$firstchar])
			{ //Matches to closing Delimiter
				$begin .= $firstchar;
				$end   .= $lastchar;
				$value  = substr($value, 1, -1);
			}
			else
			{
				break;
			}
			$firstchar = substr($value, 0, 1);
			$lastchar  = substr($value, -1, 1);
		}
		//Now we get rid of the curly braces
		$pattern     = '/([^\\\\]|^)?\{(.*?[^\\\\])\}/';
		$replacement = '$1$2';
		$value       = preg_replace($pattern, $replacement, $value);
		//Reattach delimiters
		$value       = $begin.$value.$end;
		return $value;
	}

	/**
	 * Generates a warning
	 *
	 * @param  string  $type       The type of the warning
	 * @param  string  $entry      The line of the entry where the warning occurred
	 * @param  string  $wholeentry OPTIONAL The whole entry where the warning occurred
	 * @return void
	 */
	private function _generateWarning($type, $entry, $wholeentry='')
	{
		$warning['warning']    = $type;
		$warning['entry']      = $entry;
		$warning['wholeentry'] = $wholeentry;
		$this->warnings[]      = $warning;
	}

	/**
	 * Cleares all warnings
	 *
	 * @return void
	 */
	public function clearWarnings()
	{
		$this->warnings = array();
	}

	/**
	 * Is there a warning?
	 *
	 * @return bool  if there is, false otherwise
	 */
	public function hasWarning()
	{
		if (count($this->warnings) > 0)
		{
			return true;
		}

		return false;
	}

	/**
	 * Returns the amount of available BibTex entries
	 *
	 * @return int    The amount of available BibTex entries
	 */
	public function amount()
	{
		return count($this->data);
	}

	/**
	 * Returns the author formatted
	 *
	 * The Author is formatted as setted in the authorstring
	 *
	 * @param  array<string,string>   $array Author array
	 * @return string  the formatted author string
	 */
	private function _formatAuthor($array)
	{
		if (!array_key_exists('von', $array))
		{
			$array['von'] = '';
		}
		else
		{
			$array['von'] = trim($array['von']);
		}
		if (!array_key_exists('last', $array))
		{
			$array['last'] = '';
		}
		else
		{
			$array['last'] = trim($array['last']);
		}
		if (!array_key_exists('jr', $array))
		{
			$array['jr'] = '';
		}
		else
		{
			$array['jr'] = trim($array['jr']);
		}
		if (!array_key_exists('first', $array))
		{
			$array['first'] = '';
		}
		else
		{
			$array['first'] = trim($array['first']);
		}
		$ret = $this->authorstring;
		$ret = str_replace("VON", $array['von'], $ret);
		$ret = str_replace("LAST", $array['last'], $ret);
		$ret = str_replace("JR", $array['jr'], $ret);
		$ret = str_replace("FIRST", $array['first'], $ret);

		return trim($ret);
	}

	/**
	 * Converts the stored BibTex entries to a BibTex String
	 *
	 * In the field list, the author is the last field.
	 *
	 * @return string The BibTex string
	 */
	public function bibTex()
	{
		$bibtex = '';
		foreach ($this->data as $entry)
		{
			//Intro
			$bibtex .= '@'.strtolower($entry['type']).' { '.$entry['cite'].",\n";
			//Other fields except author
			foreach ($entry as $key => $val)
			{
				if ($this->_options['wordWrapWidth']>0)
				{
					$val = $this->_wordWrap($val);
				}
				if (!in_array($key, array('cite', 'type', 'author')))
				{
					$bibtex .= "\t".$key.' = {'.$val."},\n";
				}
			}
			//Author
			if (array_key_exists('author', $entry))
			{
				if ($this->_options['extractAuthors'])
				{
					$tmparray = array(); //In this array the authors are saved and the joind with an and
					foreach ($entry['author'] as $authorentry)
					{
						$tmparray[] = $this->_formatAuthor($authorentry);
					}
					$author = join(' and ', $tmparray);
				}
				else
				{
					$author = $entry['author'];
				}
			}
			else
			{
				$author = '';
			}
			$bibtex .= "\tauthor = {".$author."}\n";
			$bibtex .= "}\n\n";
		}
		return $bibtex;
	}

	/**
	 * Adds a new BibTex entry to the data
	 *
	 * @param  array<string,mixed>  $newentry The new data to add
	 * @return void
	 */
	public function addEntry($newentry)
	{
		$this->data[] = $newentry;
	}

	/**
	 * Returns statistic
	 *
	 * This functions returns a hash table. The keys are the different
	 * entry types and the values are the amount of these entries.
	 *
	 * @return array<string,int>  Hash Table with the data
	 */
	public function getStatistic()
	{
		$ret = array();
		foreach ($this->data as $entry)
		{
			if (array_key_exists($entry['type'], $ret))
			{
				$ret[$entry['type']]++;
			}
			else
			{
				$ret[$entry['type']] = 1;
			}
		}
		return $ret;
	}

	/**
	 * Returns the stored data in RTF format
	 *
	 * This method simply returns a RTF formatted string. This is done very
	 * simple and is not intended for heavy using and fine formatting. This
	 * should be done by BibTex! It is intended to give some kind of quick
	 * preview or to send someone a reference list as word/rtf format (even
	 * some people in the scientific field still use word). If you want to
	 * change the default format you have to override the class variable
	 * "rtfstring". This variable is used and the placeholders simply replaced.
	 * Lines with no data cause an warning!
	 *
	 * @return string the RTF Strings
	 */
	public function rtf()
	{
		$ret = "{\\rtf\n";
		foreach ($this->data as $entry)
		{
			$line    = $this->rtfstring;
			$title   = '';
			$journal = '';
			$year    = '';
			$authors = '';
			if (array_key_exists('title', $entry))
			{
				$title = $this->_unwrap($entry['title']);
			}
			if (array_key_exists('journal', $entry))
			{
				$journal = $this->_unwrap($entry['journal']);
			}
			if (array_key_exists('year', $entry))
			{
				$year = $this->_unwrap($entry['year']);
			}
			if (array_key_exists('author', $entry))
			{
				if ($this->_options['extractAuthors'])
				{
					$tmparray = array(); //In this array the authors are saved and the joind with an and
					foreach ($entry['author'] as $authorentry)
					{
						$tmparray[] = $this->_formatAuthor($authorentry);
					}
					$authors = join(', ', $tmparray);
				}
				else
				{
					$authors = $entry['author'];
				}
			}
			if ((''!=$title) || (''!=$journal) || (''!=$year) || (''!=$authors))
			{
				$line = str_replace("TITLE", $title, $line);
				$line = str_replace("JOURNAL", $journal, $line);
				$line = str_replace("YEAR", $year, $line);
				$line = str_replace("AUTHORS", $authors, $line);
				$line .= "\n\\par\n";
				$ret  .= $line;
			}
			else
			{
				$this->_generateWarning('WARNING_LINE_WAS_NOT_CONVERTED', '', implode(',', $entry));
			}
		}
		$ret .= '}';
		return $ret;
	}

	/**
	 * Returns the stored data in HTML format
	 *
	 * This method simply returns a HTML formatted string. This is done very
	 * simple and is not intended for heavy using and fine formatting. This
	 * should be done by BibTex! It is intended to give some kind of quick
	 * preview. If you want to change the default format you have to override
	 * the class variable "htmlstring". This variable is used and the placeholders
	 * simply replaced.
	 * Lines with no data cause an warning!
	 *
	 * @return string the HTML Strings
	 */
	public function html()
	{
		$ret = '<table>';
		foreach ($this->data as $entry)
		{
			$line    = $this->htmlstring;
			$title   = '';
			$journal = '';
			$year    = '';
			$volume  = '';
			$pages   = '';
			$publisher =  '';
			$authors = '';
			if (array_key_exists('title', $entry))
			{
				$title = $this->_unwrap($entry['title']);
			}
			if (array_key_exists('journal', $entry))
			{
				$journal = $this->_unwrap($entry['journal']);
			}
			if (array_key_exists('year', $entry))
			{
				$year = $this->_unwrap($entry['year']);
			}
			if (array_key_exists('volume', $entry))
			{
				$volume = $this->_unwrap($entry['volume']);
			}
			if (array_key_exists('pages', $entry))
			{
				$pages = $this->_unwrap($entry['pages']);
			}
			if (array_key_exists('publisher', $entry))
			{
				$publisher = $this->_unwrap($entry['publisher']);
			}
			if (array_key_exists('author', $entry))
			{
				if ($this->_options['extractAuthors'])
				{
					$tmparray = array(); //In this array the authors are saved and the joind with an and
					foreach ($entry['author'] as $authorentry)
					{
						$tmparray[] = $this->_formatAuthor($authorentry);
					}
					$authors = join(', ', $tmparray);
				}
				else
				{
					$authors = $entry['author'];
				}
			}
			if ((''!=$title) || (''!=$journal) || (''!=$year) || (''!=$authors))
			{
				$line = str_replace("TITLE", $title, $line);
				if (array_key_exists('journal', $entry))
				{
					$line = str_replace("JOURNAL", $journal, $line);
				}
				else
				{
					$line = str_replace("<em>JOURNAL</em>,", '', $line);
				}
				$line = str_replace("YEAR", $year, $line);
				$line = str_replace("AUTHORS", $authors, $line);
				if (array_key_exists('pages', $entry))
				{
					$line = str_replace("PAGES", $pages, $line);
				}
				else
				{
					$line = str_replace("PAGES,", '', $line);
				}
				if (array_key_exists('volume', $entry)){
					$line = str_replace("VOLUME", $volume, $line);
				}
				else
				{
					$line = str_replace("VOLUME,", '', $line);
				}
				if (array_key_exists('publisher', $entry))
				{
					$line = str_replace("PUBLISHER", $publisher, $line);
				}
				else
				{
					$line = str_replace("PUBLISHER,", '', $line);
				}
				$ret  .= $line;
			}
			else
			{
				$this->_generateWarning('WARNING_LINE_WAS_NOT_CONVERTED', '', implode(',', $entry));
			}
		}
		$ret  .= '</table>';

		return $ret;
	}
}
