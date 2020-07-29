# Website Development

## Overview 

Anyone wishing to do work on the Research Computing website will be given a virtualhost to do this work on. These will be dedicated to their personal work, so they will not need to worry about any conflicts in editing files, and they can use these to view edits live while they work. All changes to these must then be committed through the git repo.

## Creating Personal VirtualHosts

Each site editor may create a distinct virtualhosts: https://myusername.dev.rcac.purdue.edu/ hosted on web-dev.rcac.purdue.edu

Staff in Support and Engineering should be able to complete most of these steps themselves. You will need sudo and Bluecat access: if you do not have these, contact Kevin Colby to help you out.

NOTE: Staff outside Support or Engineering will need to get the "wwwdev" Acmaint role to their account to log into web-dev. You may do this from Catbert or alongside your CAS request, or ask Dan or Kevin to do it from Catbert.

1) SSH into web-dev.rcac.purdue.edu
1) Create a directory under: /var/www/html/ named with the username of the editor.
1) Create a CNAME in Bluecat: myusername.dev.rcac.purdue.edu -> web-dev.rcac.purdue.edu. (Quick deploy seems unlikely here :().
1) Give yourself access to the RCAC-Staff/www repository on github. Any manager of RCAC-Staff org can do this themselves, otherwise, contact Dan or Kevin.
1) In your /var/www/html/myusername/ directory, clone the repository: `git clone git@github.rcac.purdue.edu:RCAC-Staff/www.git .`. (Note: It is highly recommended you set up an SSH key to Github, and required if you don't want to type your password a million times).
1) *Optional*: If you need to be able to log into your virtualhost to edit the portal portions of the site you will need to ask IAMO to authorize CAS for your URL. If you will just be editing the userguide or other static pages, this isn't necessary, you just won't be able log in. If emailing accounts@purdue.edu, you *must* use this boilerplate text:
```
Please authorize the following URL to use CAS: https://myusername.dev.rcac.purdue.edu . Use the existing SLA for web-dev.rcac.purdue.edu as this URL is served by web-dev.
```
If you get any grief or the request needs clarify, refer your ticket to Dan or Kevin.

7) *Optional*: Add yourself to the commit mailing list if you wish to receive gitdiff emails, from web.rcac: `mailinglist --list=svn-rcac-www --add-user myusername@purdue.edu`

After cloning the repository, you should be able to pull up the site in your web bowser. No further steps should be necessary. The server uses a wildcard certificate and Apache and mod_rewrite magic to make everything work just by creating the CNAME and the directory.


## Development Process

### Pre-requisites

This process assumes you have SSH Keys set up with Github (this is highly recommended) and that you have basic git knowledge. There are many git tutorials available online. 

It is also assumed you will be using a command-line based editor for editing files on the dev server. There are some graphical editors and ways to connect IDEs on your desktop over SSH, but those will not be documented here.

### Initializing your VM

* SSH into web-dev.rcac.purdue.edu
* `cd /var/www/html/myusername/`
* `git clone git@github.rcac.purdue.edu:RCAC-Staff/www.git .`

### Making changes

At the basic level, changes and commits should be made to the `master` branch of the repo. Once pushed to Github, your changes will appear shortly on dev.rcac.purdue.edu. It is updated every 5 minutes with the latest changes to the master branch. Use of git branches is highly encouraged for complex changes.

### Deploying changes to production

* `deploysite --prod`

This will rename existing `deployed` branch to `deployed-timestamp`, and duplicate the current `master` branch as the new deployed. The production server will update the deployed branch every 5 minutes.

### Making hot-fixes

If you need to make a fix live quickly, and don't want to pull potentially untested, unrelated, changes into production you can cherry-pick a commit into the deployed branch.

* Commit your change to master and push
* Check out the deployed branch
* Cherry-pick your commit into deployed branch: `git cherry-pick -x <commit id>`
* Repeat any other cherry-picks for other commits.

Changes will auto-update every 5 minutes on both www and dev. If you have a complex hot-fix to make it is highly encouraged to make use of branches and merging. Of course, if one is making complex hot-fixes, you should strongly consider whether such a complex change should be rushed out.

## Style Guide

Please read carefully and adhere to all the style standards below in all site documentation. If you have any questions, please contact Kevin Colby.

### Proper Formatting for User Guide Material

The user guides use Markdown for formatting.  Please use Markdown formatting. Use of raw HTML tags should be rare. Check [this quick reference guide](https://en.support.wordpress.com/markdown-quick-reference/) for a review of Markdown syntax.

* Use `# Heading 1`, `## Heading 2`, and `### Heading 3` to create section headers. You *must* follow proper heading hierarchy - start at Heading 1, then 2, then 3. Always start at Heading 1 at the start of the section. The first heading should  be the same text as the page title. You should never return to Heading 1 again in that section. If you need to return to Heading 1, you should break off into a new section/page. Failure to maintain hierarchy will cause a failure in accessiblity and will prevent screen readers from properly interpretting the guide.
* Use `_emphasis_` to denote new concepts or terms of art when they first appear. Example:

`_Condor_ allows users to run jobs...`

_Condor_ allows users to run jobs...

* Use `**bold**` sparingly, and only for very important points for the user. Do NOT use for commands, false headings, or other purposes.
* Use ``command`` (backticks) to denote commands when they appear in the middle of text. Example:

``To list the files in a directory, use the `ls` command.``

To list the files in a directory, use the `ls` command.

* Use triple backticks (`` ``` multiple lines of code ``` ``) to set off multi-line sections showing a user how to do something at a command prompt.
* Use `*` to make bulleted lists (`* Item 1 `)

### Proper HTML Tags for Non-User Guide Material

Anything outside the user guide uses normal HTML.

* Do not use `<br />`. It should never be needed. Use `<p></p>` and CSS to create line breaks.
* Ensure ALL paragraphs are wrapped with `<p></p>`. Failure to do so WILL break the page formatting.
* Do NOT include any header tags in any subsection files. These are managed centrally, as part of the automatic index building process for pages. If you need new subsections, or subsections changes or renamed, check with Kevin for details on how to do this.
* Use `<strong></strong>` or `<em></em>` and NOT `<b></b>` or `<i></i>`.
* Use `<strong></strong>` sparingly, and only for very important points for the user. Do NOT use for commands, false headings, or other purposes.
* Do NOT indent these sections at all using either tabs or spaces.
* Use `<ul></ul>` and `<li></li>` for bulleted lists and list items. Make sure all list items have closing `</li>` tags as well as opening tags.
* Do not use `<ol></ol>` unless describing an ordered sequence of instructions.

## Spelling and Grammar
* Always use the second person. That is, write as though speaking to a user: "You can..." "...in your..." Do NOT use the royal "we", such as "We must then..."
* Strive to avoid passive voice. That is, instead of saying "Using Phi coprocessors requires you to..." say "In order to use Phi coprocessors, you must..."
* In general, use the full name of any acronym or abbreviation for the first occurrence with the shortened version immediately following in parenthesis. Thereafter, the short version may be used in the text. Example:
"The Indiana Economic Development Corporation (IEDC) is..."
* "email" not "e-mail", and lowercase unless beginning a sentence.
* Use "email" for both singular and plural.
* "log in" not "login" if used as a verb--if used as a noun, then the one-word "login".
* "front-end" not "frontend" or "front end"
* "Gigabit" not "GigaBit" or "gigabit"
* "GHz" not "Ghz"
* "TeraFLOPS" not "TeraFlops" or "Teraflops"
* "GigE" (if second appearance) not "gige" or "Gige"
* "InfiniBand" not "Infiniband"
* "SSH" not "ssh" unless a quoted section of the command on Unix systems.

### Machine Names
* Use the Proper Case names for all machines unless describing how to log in to specific hosts. That is, when referring to the cluster use "Steele" and not "steele" or "steele.rcac.purdue.edu". Use the resource variables in the user guide instead.
* Use fully-qualified host names when referring giving instructions on logging in to specific hosts. That is, "SSH to steele.rcac.purdue.edu", not "steele" or "steele.rcac".

### Formatting, Filenames, and Commands
* When showing a command line prompt, use only the dollar sign ("$"). Do NOT use "%" or include a shell name or version in the prompt, even if normally present. This will be more easily understood by the user and avoids shell or version-specific documentation.
* When showing a file-in-the-blank sort of name in documenation, use something of the form "mysomething", such as "myfilename" or "myusername". Do NOT use "<something>" to denote substitution-required variables, as many users may not be familiar with this convention.
* When explaining command options, be explicit. Do NOT use conventions such as "this | that" to indicate one option or another. Do NOT use conventions such as "[optional]" to denote an optional argument. Users may not be familiar with these conventions.

### Linking
* All links should consist of text which names or describes the object to which the link points. **Never** link the word "here" or simply provide the actual URL in the text itself. This may require rephrasing the sentence. Example:
"Purdue faculty and staff can request access using the online <a href="">Research Computing Account Request Form</a>."
* When linking to any page off-site, add `"target=_blank" rel="noopener"` to the `<a>` tag so force a new tab.
