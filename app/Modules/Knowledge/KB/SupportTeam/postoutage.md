---
title: Posting Outages 
tags:
 - internal
---

# Posting Outages 

News articles can be posted from the [Manage News](/news/manage/) interface. You can navigate from the RCAC website by logging in (upper right corner), mousing over News in the menu bar, and selecting Search and Manage News. If you do not have access to this, ask your manager to grant access. Select the Add New tab and fill out the fields:

**1)** Select an appropriate Outage template from the drop down. The "Unscheduled %%CLUSTER%% outage" is a good choice.

**2)** Wait a second and the "Outages and Maintenance" News Type should be selected, the Published box checked, and the headline and text boxes filled in.

**3)** Set the date. When posting the initial outage set the start date/time as when the outage is thought to have begun. Leave the end time blank.  Do not worry about entering a precise time and avoid putting an end time in. Any estimated RTS can be spelled out as such in the text. Right now you are just posting a singular event in time ("The cluster crashed at 3:30pm").

**4)** Tag the resources affected by the outage. Click where it says to click and search by name. Click results to tag them in the article. 

**5)** Edit the headline and fill in "%%CLUSTER%%" as appropriate. Typically it would be a list of clusters affected. But if the list is lengthy it may be more concise to omit the list and spell it out in the text.

**6)** Fill in any of the placeholder text (the double % sign). Leave any variables be (the single % sign, %resources%, %startime%, etc). These will automatically be filled in. In the template you just need to provide a few words with what is wrong, e.g.:

```
The %resources% cluster began experiencing issues with its scratch filesystem.
```

You do not need to give a detailed explaination of the situation. Just a few words of what is not working (scratch system, scheduler, etc). Some tips:


* The initial post of an outage should not be detailed. Just get something posted and sent out as quickly as possible.
* *DO NOT* replace variables with the literal text. The variables will be filled in at display time and formatted in a consistent way. It is important that dates are un-amibiguous to all readers and consistent between posters so please let the variables handle dates. Do not enter your own preferred date formatting.
* The sentence about job scheduling may also need to be removed or edited if not appropriate (it would be silly to say scheduling is paused when the power is out). 
* Try not to stray too far from the template text. This verbiage is the preferred wording for these things. Of course, do read and edit as necessary to ensure the text is intelligent and gramatically correct.

**7)** Click the Preview button. This will complete variables and show you how it will look. Anything highlighed in red either needs to be filled in a field or have placeholder text replaced.

**8)** Make sure Published button is checked and click Add News button. You should see something like this:

<img src="/knowledge/downloads/SupportTeam/images/postnews.png" alt="" />

**9)** You will be redirected to the posted news article. Double check everything looks good.

**10)** Click 'Edit News' button at the bottom to go back to the news editor.

**11)** Under the editing text box you will see the article again in the "search results". Under the Date and Resource header lines you will see an envelope icon. Click the icon to bring up the mail preview window. The button looks like this:

<img src="/knowledge/downloads/SupportTeam/images/newsmail.png" alt="" />

**12)** Double check again everything looks correct. This will show you exactly what the email will look like, including headers and footers. Click 'Send' to actually send the email.

[Updating Outage](../updateoutage) &gt;


