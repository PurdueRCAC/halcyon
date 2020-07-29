---
title: Logic
tags:
 - internal
---

# Logic

The user guide supports some basic logic statements. These are simple `if ... else` statements operating on any of the [variables](../variables) available in the display, and standard logical operators (`==`, `!=`, `<`, `<=`, `>`, `>=`, `=~`). Operators are applied appropriately depending on the value type (integer vs string).

<pre>
&#123;::if resource.name == Carter&#125;
This is the Carter User Guide.
&#123;::elseif resource.nodecores > 24&#125;
This cluster has a lot of cores.
&#123;::else&#125;
We don't know much about this cluster.
&#123;::/&#125;
</pre>

`if` tags can be placed inline or by themselves on the line. The user guide will subtract the tags out of the text logically and leave behind the text inside the tags that meet the condition. If the tag appears on a line by itself, the entire line will be deleted as if it were never there (as you would naturally expect).

The `=~` operator means "matches" and expects a regular expression. In the example below, it will match any number of characters followed by "carter".

<pre>
// This will match
//    foocarter
//    bar-carter
// But not
//    carters
&#123;::if resource.name =~ .*carter$&#125;
This is the Carter User Guide.
&#123;::/&#125;
</pre>
