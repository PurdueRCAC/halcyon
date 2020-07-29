---
title: Adding a new page
tags:
 - internal
---

# Adding a new page

Adding a new page is easy. First, decide where you want the page to show up in the directory structure. 

### Existing directory

If you're adding a new page in an existing directory, this is easy. Create a new file name, with the extension ".md". The filename will show up in the final URL, so choose the name carefully.

### New directory

If you wish to add a whole new directory, there is an additional step. The directory needs to have a README.md file. This file contains the text that is displayed on the index of the new directory (section) or at the top of the new section (in expanded view). This file also lets you set the the order in which pages are listed.

The README.md file should have the following contents:

    ---
    title: Editing the User Guide
    order:
    - file1
    - file8
    - file3
    ---
    
    This section describes how to the edit the User Guide.

The title field names this section for links and section headers. The order section is a list of files in the order in which they should be displayed. File extensions are omitted. If a file listed does not exist, or does not apply to the tag being displayed it will be ignored.

Once the README.md file is created, you may add new files in the directory.

*NOTE*: A directory with a single file, or a directory in which only a single file applies to any given tag the README.md file will be bypassed and the file will be displayed directly. There are very few circumstances where this behavior should be exploited. A section should typically have at least several files - if it doesn't, it probably shouldn't be its own section (or the single file broken up).

A good use case for this is the overview directory. In here is a file for each resource, and is aptly named for each resource. Here only one file applies to any resource, and so the contents of that file is displayed directly. For example, the `rice` tag then displays the `rice.md` file as `/knowledge/rice/overview`. In this case, exploiting this behavior is very helpful because creating a single overview file is very impractical with the number of logic statements needed.

### The actual file

At the top of the file, include this bit of in-line YAML:

    ---
    title: Adding a new page
    tags:
    - internal
    ---
    
The title field sets the display text for links and breadcrumbs in the display. The tags field set what [tags the page supports](../tags).

Below this include the page text. See the other pages in this guide outlining formatting, variables, logic, etc. NOTE: For consistency, every page should start with a header line `# Adding a new page` (for example). Typically you want this to be the same as the link display, but isn't strictly necessary. Without this header, the page will appear somewhat naked in certain displays.
