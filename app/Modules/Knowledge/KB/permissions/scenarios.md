---
title: Common Access Permission Scenarios
tags:
 - depot
---

# Common Access Permission Scenarios

Depending on your research group's specific needs and preferred way of sharing, there are various permission models your ${resource.name} can be designed to reflect.  Here are some common scenarios for access:

<ul>
 <li>
 <strong>"We have privately shared data within the group and some software for use only by us and a few collaborators."</strong><br />
 <em>Suggested implementation:</em><br />
 <div style="padding-left: 1.8em;">
 Keep data in the <kbd>data/</kbd> subdirectory and limit read and write access to select approved researchers.<br />
 Keep applications (if any) in the <kbd>apps/</kbd> subdirectory and limit write access to your developers and/or application stewards.<br />
 Allow read/execute to <kbd>apps/</kbd> by anyone in the larger research group with cluster queue access and approved collaborators.
 </div>
 </li>
 <li>
 <strong>"We have privately shared data within the group and some software which is needed by all cluster users (not just our group or known collaborators)."</strong><br />
 <em>Suggested implementation:</em><br />
 <div style="padding-left: 1.8em;">
 Keep data in the <kbd>data/</kbd> subdirectory and limit read and write access to select approved researchers.<br />
 Keep applications (if any) in the <kbd>apps/</kbd> subdirectory and limit write access to your developers and/or application stewards.<br />
 Allow read/execute to <kbd>apps/</kbd> by anyone at all by opening read/execute permissions on your base ${resource.name} directory.
 </div>
 </li>
 <li>
 <strong>"We have a few different projects and only the PI and respective project members should have any access to files for each project."</strong><br />
 <em>Suggested implementation:</em><br />
 <div style="padding-left: 1.8em;">
 Create distinct subdirectories within your ${resource.name} base directory for each project and corresponding Unix groups for read/write access to each.<br />
 Approve specific researchers for read and write access to only the projects they are working on.
 </div>
 </li>
</ul>
 <p>
 Many variants and combinations of the above are also possible covering the range from "very restrictive" to "mostly open" in terms of both read and write access to each subdirectory within your ${resource.name} space.  Your lab can sit down with our staff and explain your specific needs in human terms, and then we can help you implement those requirements in actual permissions and groups.  Once the initial configuration is done, you will then be able to easily add or remove access for your people.  If your needs change, just let us know and we can accommodate your new requirements as well.
