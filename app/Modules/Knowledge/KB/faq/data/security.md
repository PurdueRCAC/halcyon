---
title: How is my Data Secured on ${resource.name}?

tags:
 - faq
 - wholenode
 - sharednode
---

### How is my Data Secured on ${resource.name}?
{::if resource.name != Weber}
${resource.name} is operated in line with policies, standards, and best practices as described within <A href="https://www.purdue.edu/securepurdue">Secure Purdue</a>, and specific to <a href="/policies"> Research Computing Resources</a>.

Security controls for ${resource.name} are based on ones defined in NIST cybersecurity standards.

${resource.name} supports research at the L1 fundamental and L2 sensitive levels.
${resource.name} is not approved for storing data at the L3 restricted (covered by HIPAA) or L4 Export Controlled (ITAR), or any Controlled Unclassfied Information (CUI).


For resources designed to support research with heightened security requirements, please look for resources within the <A href="/services/reedplus/">REED+ Ecosystem</a>.
{::else}
${resource.name} is operated in line with policies, standards, and best practices as described within <A href="https://www.purdue.edu/securepurdue">Secure Purdue</a>, and specific to <a href="/policies"> Research Computing Resources</a>.  In addition, L4 Export Controlled (ITAR) or Controlled Unclassfied Information (CUI) stored within ${resource.name} are compliant with EAR, ITAR, or NIST SP 800-171 regulations.
{::/}

{::if user.username != myusername}
### High Level Data Security Diagram

<img src="/compute/${resource.hostname}/images/secdiagram.png" alt="System Security Diagram" />

### Notes on Data Security Configuration
<ul>
<li>Only research groups that have purchased access may access ${resource.name}.</li>
<li>All access to ${resource.name} is through <a href="https://www.purdue.edu/securepurdue/iamoServices/index.php">Purdue Career Accounts</a>, managed by Purdue's identity and access management office.</li>
<li>Scratch storage on ${resource.name} is private only to the individual user, using POSIX file permissions.</li>
<li>Scratch storage on ${resource.name} is not encrypted at rest or in flight.</li>
<li>Scratch storage on ${resource.name} is not backed up. We recommend using Fortress and the Data Depot as part of your lab's data management strategy.</li>
<li>Access to the PI's Data Depot space is only possible from HPC systems, or with the use of the Purdue VPN.</li>
<li>Access to the PI's Data Depot space is directly controlled by the PI via UNIX groups, POSIX file permissions and ACLs.</li>
<li><a href="https://transfer.rcac.purdue.edu">Globus</a> is provided as a tool for secure, high-performance file transfer and sharing.</li>
<li>All compute nodes on ${resource.name} are firewalled and accessible only from within the boundaries of research computing resources.</li>
<li>Access to a compute node is limited to the specific user assigned to the node via the job scheduler. No more than 1 user may access any one compute node at a time.</li>
<li>The Purdue research network is monitored with an intrustion detection system.</li>
<li>Purdue system administrators use two-factor authentication for administrative access to research systems.</li>
<li>All research systems are manged with version control, configuration management software and patched at regular intervals.</li>
<li>Usage, access, system, and application data is centrally logged and reviewed.</li>
<li>Physical access to data center facilties is restricted by swipe card access to data center and systems staff.</li>
</ul>
{::else}
### For additional information

<a href="/login">Log in</a> with your Purdue Career Account.
{::/}
