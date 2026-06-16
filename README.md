# Competency Grid #

The Moodle Competency Grid plugin is an activity module which allows learners to track their progress in specific competencies.

Instructors can upload their competence grid as a csv file.
The csv file must have the following structure:
TODO

An exemplary file to show the needed structure is supplied in this repository, and may be provided to instructors. 

## Features

### Flexible upload
* **Upload as csv file** <br> Creating a new competencygrid activity is as easy as uploading a properly formatted ```.csv``` file. ```.csv``` files can be exported by most spreadsheet programs such as Excel, Google Sheets or Libre Office Calc. An exemplary file is provided with the repository.
* **flexible number of skills + skill levels** <br> A competency grid can have any number of skills/subskills/skill levels. However, the number of skill levels per competency grid is fixed, and cannot vary between skills. In that case you can split the competency grid in seperate activities that have differing amounts of skill levels

### Intuitive filling out of competence grid

- shows input from last time
- only needs to update what changed
- 

### Historic tracking

### Analysis page 
The analysis page shows a range of different chart types with the current fill-out status, as well different historic charts to visualize the learning progression

![img.png](docs/analysis.png)

Charts showing the current learning state, will automatically use the provided level titles as axis annotations, while the historic charts will use the maximum possible amount of points achievable in a skill to set as the y-axis.

### Course references

Instructors can associate other moodle courses with a subskill. The associated courses appear as clickable links when filling out the grid.

![img.png](docs/coursereferences.png)
Intuitive and performant adding of courses to a skill, that uses the inbuild moodle course-picker component. 

![coursereferences2.png](docs/coursereferences2.png)
Associated courses appear during filling, appearing as clickable links.

### Fit page


## Dependencies

[//]: # (TODO : Maybe include example csv structure and show during creation of activity)

This plugin is not related to the [Competence]() functionality, but rather stands for itself. It does **not have any external dependencies** and can be used out of the box.

It does use some dependencies internally, but these are included here. See the [Third Party Notice](thirdpartylibs.xml) for more info.

## Installing via uploaded ZIP file ##

1. Log in to your Moodle site as an admin and go to _Site administration >
   Plugins > Install plugins_.
2. Upload the ZIP file with the plugin code. You should only be prompted to add
   extra details if your plugin type is not automatically detected.
3. Check the plugin validation report and finish the installation.

## Installing manually ##

The plugin can be also installed by putting the contents of this directory to

    {your/moodle/dirroot}/mod/competencygrid

Afterwards, log in to your Moodle site as an admin and go to _Site administration >
Notifications_ to complete the installation.

Alternatively, you can run

    $ php admin/cli/upgrade.php

to complete the installation from the command line.

## Issues and Contributing

TODO

## License ##

2026 MITZ <mitz.software@ukdd.de>

This program is free software: you can redistribute it and/or modify it under
the terms of the GNU General Public License as published by the Free Software
Foundation, either version 3 of the License, or (at your option) any later
version.

This program is distributed in the hope that it will be useful, but WITHOUT ANY
WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A
PARTICULAR PURPOSE.  See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with
this program.  If not, see <https://www.gnu.org/licenses/>.
