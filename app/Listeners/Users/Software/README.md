## Software Listener

This displays a list of software the user can request if they're apart of the required departments.

### Restricted Software

Most of the software installed on the clusters are either free or site-licensed for Purdue. However, some licenses have further restrictions such as by academic department or school.

* tecplot
* comsol

### Allowed Access

All of the above software currently have the same requirements for access. The user must be in one of the following departments:

* Aeronautics and Astronautics
* Agricultural and Biological Engineering
* Biomedical Engineering
* Chemical Engineering
* Civil and Mechanical Engineering
* Civil Engineering
* College of Engineering and Sciences
* College of Engr Admin and Engr Exp Sta
* Div of Construction Engineering and Mgmt
* Electrical and Computer Eng
* Electrical and Computer Engineering
* Engineering Computer Network
* Engineering Education
* Industrial Engineering
* Materials Engineering
* Mechanical and Civil Engineering
* Mechanical Engineering
* Network for Computational Nanotechnology
* Nuclear Engineering

### How It Works

Each software has a representative unix group under the ITaP group (ID #1). Access to the software is controlled by membership in the unix group. This plugin generates a menu item and page titled "Software Access Requests" on a user's account. When a user visits the "Software Access Requests" page, it first looks up the user's department (typically, via LDAP). That department is then checked against the allowed access list above. If the user's department is in the list, the available software is presented. If the user does not already have access, they will be presented with an option to request it. Upon request, a POST is made to the unix group members API (/api/unixgroups/members/) to create a new membership in the unix group.

### Dependencies

**Note:** This uses `modules/users/js/request.js`

### Events

This listens for the following events:

* `App\Modules\Users\Events\UserDisplay` - Generates the menu item and page for the user's account.
* `App\Modules\Groups\Events\UnixGroupMemberCreating` - Adds the HPSSUSER role to the user when adding them to the unix group.
