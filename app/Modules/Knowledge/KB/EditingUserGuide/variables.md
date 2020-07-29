---
title: Variables
tags:
 - internal
---

# Variables

Variable subsitution is possible as it was in the old system. Most of the variable names are similar, however, they have been restructured. Each variable is made up of two components -a tag type, and the variable. For example, `${resource.name}`. Here `resource` is our tag type, and `name` is the variable. The variable should be denoted by `${class.var}`. This would print the name of the resource, e.g., Rice.

To see available variables for a guide, start with the guide tag (for URL "/knowledge/rice", rice is the guide tag). Look at the appropriate .yaml file in the top level tags directory. The `tagtype` (usually at the top) value is the name of the tag type, and the `vars` table lists all the variables and their values available for this tag type. Then look at each yaml file for each tag in the `tags` list and repeat the above process recursivley. These are the variables you have available to you. 

There is a special psudeo-tag type, `user`. At the moment, this has two variables:

* username: myusername or actual username if logged in
* staff: flag marking whether user is staff (value is 1 if user a news manager)
