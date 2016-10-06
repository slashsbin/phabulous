PHABulous
=========

Simple Dashboard-style Charts for [Phabricator](https://www.phacility.com/phabricator/), with Focus on Gantt Charts.

Features
--------

- [X] Gantt Chart for Tasks
- [X] Colorize Task bars using it's Priority
- [X] Identify Closed Tasks on Gantt
- [X] Show Task Progress on Gantt
- [ ] Group Tasks by Custom Projects List
- [ ] Link Task Dependencies on Gantt
- [X] Drag-n-Drop/ReSize Tasks on Gantt to Change it's Start-Date/Duration and Save it at Phabricator
- [X] Mark UnScheduled Tasks
- [X] Export Gantt Chart to PDF/iCal/Excel/MS-Project
- [ ] Dashboard for Gantt Status: Overdue(using Phrequent), Tasks Count, ...
- [X] Simple HTTP-Digest Authentication
- [X] Simple In-Memory Users, with Separate Access-Level for Admins
- [ ] Respect Phabricator Task Edit Policies
- [X] Cache Responses from Phabricator to Speed up Data Lookups.
- [X] Thanks to New Conduit API, We can Dynamically Choose Gantt Data from Phabricator itself using Saved Queries.

* * *

Requirements
------------

- PHP 7
- Phabricator [2016.36](https://secure.phabricator.com/w/changelog/2016.36/)

Setup
-----

### Configure Phabricator

1. Create a Bot User

2. Custom Fields for Maniphest

    ```
    {
      "phabulous-header": {
        "name": "Phabulous!",
        "type": "header"
      },
      "phabulous-start-date": {
        "name": "Start Date",
        "caption": "Scheduled Start Date",
        "type": "date",
        "required": false,
        "search": true
      },
      "phabulous-estimated-duration": {
        "name": "Estimated Hours",
        "caption": "Estimated number of hours this will take",
        "type": "int",
        "required": false,
        "search": true
      },
      "phabulous-end-date": {
        "name": "End Date",
        "caption": "Scheduled End Date",
        "type": "date",
        "required": false,
        "search": true
      },
      "phabulous-progress": {
        "name": "Progress",
        "caption": "",
        "type": "select",
        "options": {
          "0": "0%",
          "10": "10%",
          "20": "20%",
          "30": "30%",
          "40": "40%",
          "50": "50%",
          "60": "60%",
          "70": "70%",
          "80": "80%",
          "90": "90%",
          "100": "100%"
        },
        "required": false,
        "search": true
      }
    }
    ```

3. Custom Query for People

4. Custom Query for Projects

5. Custom Query for Maniphest

### Install Phabulous

These steps assume you already have completed the [Configure Phabricator](#configure-phabricator) section.

1. [Install the Symfony App](https://symfony.com/doc/current/setup.html#installing-an-existing-symfony-application)

    ```
    $ composer install
    $ bower install
    ```
2. Configure Web-Server

    There is a sample NginX Virtual-Server Configuration at [etc/nginx/sites-available/phabulous.conf](etc/nginx/sites-available/phabulous.conf).

3. Configure Phabulous:

    Open `app/config/parameters.yml` with text-editor:
    
    1. Set `phacility_url` Parameter to your Phabricator URL(with-out trailing slash).
    
    2. Set `phacility_phabulous_bot_token` to Bot User's Token.
    
    3. Set People/Projects/Maniphest Query Keys.
    
    Open `app/config/security.yml` with text-editor:
    
    4. Configure Users under `security.providers.in_memory.memory.users` key.

### Configure Locale

- Change Symfony App Default Locale

- Custom File `gantt_locale_XX.js`

- Set Gantt WorkTime

* * *

Credits
-------

- [kennyeni/phabricatorGantt](https://github.com/kennyeni/phabricatorGantt) - Inspired by
- [BootStrap](https://getbootstrap.com/)
- [DHTMLX Gantt](http://dhtmlx.com/docs/products/dhtmlxGantt/)
- [jQuery](https://jquery.com/)
- [Phabricator](https://www.phacility.com/phabricator/)
- [Symfony](https://symfony.com/)

License
-------
PHABulous is licensed under the [MIT License](http://slashsbin.mit-license.org/).