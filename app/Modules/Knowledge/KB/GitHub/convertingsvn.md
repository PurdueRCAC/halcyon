---
title: Converting SVN repo to Github
tags:
  - internal
---

# Converting SVN repo to Github

Old SVN repos can be converted into GIT and imported into Github. These instructions are designed for svn.rcac repos, but could easily be modified to any random SVN repo. You just need HTTP access or access to the raw files. Just a checkout won't work, at least if you want full commit history.

* Make old repo read only, give yourself permissions
* Download docker and image: https://hub.docker.com/r/yukinagae/svn-to-git/
* Initialize docker environment:
<pre>
eval "$(docker-machine env default)"
</pre>

* Start docker image: 
<pre>
    docker run -it -p 80:80 yukinagae/svn-to-git /bin/bash
</pre>

* Checkout old repo and build list of authors
<pre>
cd /tmp
svn co https://svn.rcac.purdue.edu/svn/OLD/ OLD/
cd /tmp/OLD
svn log -q | awk -F '|' '/^r/ {sub("^ ", "", $2); sub(" $", "", $2); print $2" = "$2" &lt;"$2"&gt;"}' | sort -u > authors-transform.txt
</pre>


* Edit authors file to be $username@purdue.edu format:
<pre>
username = username &lt;username@purdue.edu&gt;
</pre>

* Do conversion of repo:
<pre>
git svn clone https://svn.rcac.purdue.edu/svn/OLD --no-metadata -A authors-transform.txt --stdlayout /tmp/new/
OR
git svn clone https://svn.rcac.purdue.edu/svn/OLD --no-metadata -A authors-transform.txt /tmp/new/
</pre>
*--stdlayout should only be used if the repo has proper trunk, tags, etc SVN layout*


* Include some metadata (if present)
<pre>
cd /tmp/new
git svn show-ignore > .gitignore
git add .gitignore
git commit -m 'Convert svn:ignore properties to .gitignore.'
</pre>

* Impersonate owner of org, make new repo on github, add yourself, then grab new repo
<pre>
cd /tmp/
git clone https://{username}@github.rcac.purdue.edu/NEWORG/NEW.git
cd NEW
git symbolic-ref HEAD refs/heads/trunk
</pre>

* Next step will expect a totally plain repo, but github does something and it is not quite plain, so tell it to ignore this fact by editing `.git/config` and adding:
<pre>
[receive]
    denyCurrentBranch = warn
</pre>

* Push converted repo into our empty repo:
<pre>
cd /tmp/new
git remote add bare /tmp/NEW
git config remote.bare.push 'refs/remotes/*:refs/heads/*'
git push bare
</pre>

* Rename branch
<pre>
cd /tmp/NEW
git branch -m trunk master
OR
git branch -m git-svn master
</pre>
*Use the latter if source did not have standard layout*

* Push branches and you're done!
<pre>
git push --all
</pre>
