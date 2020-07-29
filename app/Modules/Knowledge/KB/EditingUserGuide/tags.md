---
title: Tags
tags:
 - internal
---

# Tags

The User Guide system is built on the concept of tags. Every page/file in the system advertises a set of tags that applies to the page. For example, a page about PBS jobs may advertise that it supports both whole node scheduling and shared job scheduling. This tells the userguide that the page can support either concept. The page may look the same for either, or may have logic to display different things in certain places (but the page is largely the same either way).

Each tag then can be defined to request its own subset of tags. These are defined in the tags directory. The best way to explain this is to look at an example. The "rice" tag requests these tags:

    - faq
    - linuxcluster
    - communitycluster
    - wholenode
    
The User Guide is then displayed by requesting one of these tags - for example, the "rice" tag. THe URL format is "/knowledge/$tag". So the Rice user guide then displays all pages that advertise they support one of the above 4 tags. Each of these 4 tags could also have nested tags, and would display additional tags (none of these do, but they could).
