# Web Composer Package Management
The sole purpose of this package is to port php composer library management form Command Line Interface (CLI) to an easily accessed web panel, from there developers can require, update or, remove packages normally as they would using composer's CLI application.

## Why?
Sometimes web hosting providers don't give access to secured shell so, we get little slowed down when a library is needed to be installed, updated or, removed in a project, so we run composer commands needed on a local copy of that project, then we upload the library's files back to server along with the updated composer's data.

Therefore, I made this small package that gives access to developers where they can manage their composer packages and libraries right from web without the need for shell access.

## Features
- Searching for packages installed or all packages from packagist.org.
- Installing new composer packages.
- Updating / downgrading existing composer packages.
- Removing existing composer packages.
  - **NOTE:** don't forget to refactor your source before removing to avoid errors on accessing the removed library.
- Showing list of dependencies of any package *(currently broken)*.
- Web console output showing the progress of all requested operations.
- Better support for Laravel framework version 5.0 and onwards.

## Credit
This library implements the original composer application commands into a web interface so, all credit really goes for the creators of composer.

### License
This library is provided freely at NO WARRANTY and, no permissions are required for modifying it in any way that suits its users. Collaborations and ideas are also welcome, for any suggestions you can send them to mo7y.66@gmail.com and, I'll look into them as soon as I can. 