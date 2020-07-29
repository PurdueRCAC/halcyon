@component('mail::message')
Hello {$student->name},

You have been granted access to ITaP Research Computing resources.

---

You have been granted **access** to the following job submission queues, Unix groups, and other resources.

* {$resource}: '{$queue}' queue (account ready {$eta})

---

You have been granted **accounts** on the following resources.

* {$resource}: '{$queue}' queue (account ready {$eta})

@endcomponent

header
newroleheader
newrolerow
newrolefooteritar
newrolefooterdata
newrolefooter
newqueue
newgrouprow
newqueuerowshort
newqueuerow
datainfo
footer