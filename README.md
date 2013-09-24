dogu
====

Tools for Orange™ Cloud Infrastructures.


Introduction
------------

Dogu is a modular application that exposes a REST API (_core_) and a Web 
application (_ui_). Each of them are using the HTTP protocol and are accessed
from a unique entry point (one for the _ui_, one fore the _core_):

  - http://api.dogu-demo.com - REST API
  - http://www.dogu-demo.com - Web application

This application contains several modules. The application core is divided into
two modules: _dogu-core-api_ and _dogu-core-ui_. These two modules expose the
HTTP services but are empty - they are just containers for _plugin_ modules.


Following the same pattern,
each plugin is divided into two modules: one for the _api_, one for the _ui_.
For instance the plugin _history_ contains two modules:

  - _dogu-plugin-history-api_ - Additions to the REST API
  - _dogu-plugin-history-ui_ - Additions to the Web Application


Installation
------------

TODO Installation instructions


Contributing
------------

TODO Contribution guide


License
-------

TODO Mention license


dogu-core-api
-------------

dogu-core-ui
-------------

dogu-plugin-history
-------------------

### dogu-plugin-history-api ###

### dogu-plugin-history-ui ###
