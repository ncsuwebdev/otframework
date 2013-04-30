#OT Framework#

OT Framework is an application framework built on top of Zend Framework 1.  The purpose of Zend Framework is to allow a rapid application framework that takes care of certain tasks that are the same for every app.

Features include:
* Authentication via local account and NC State's WRAP service (http://ncsu.edu/wrap)
* Dynamic and editable access control lists
* User management with multi-role assignment
* CSS and Javascript autoloading
* Cronjob management and execution
* Built-in API interface with key-based authentication
* Application trigger interface for sending customized emails, texts, notifications, etc.
* Custom fields for model objects
* Navigation editor
* Application configuration system
* Centralized caching
* Centralized logging
* Database migration system for versioned databases
* Database backup system
* Utility library for common tasks

##Creating an OTF App##

There is a base skeleton app available for you to get started.  It is available at http://github.com/ncsuwebdev/base-otf-app and can be installed in one step via composer.

**You should not start building your app from this git repo!!** Using the skeleton app is the preferred method to starting a project built on OTF.  Composer will also be the avenue for future OT Framework updates.

