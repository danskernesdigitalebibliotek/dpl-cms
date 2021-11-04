# Purpose of the documentation directory

This directory is self explanatory. It is containing documentation of features and decisions made around the dpl-cms.

# architecture
Contains Architectural Decision Records that describes choices, and context and consequences connected to them.

# diagrams
Contains diagram files like draw.io or PlantUML and rendered diagrams in png/svg format.

# images
This is just plain images used by documentation files.

# Taskfile
Taskfile is a configuration/definition file used by the cli tool (go-task)[https://taskfile.dev].
Commands in time of this writing:

If go-task is installed locally you can run following commands:
* `task render:plantuml`: This is a command that creates images from.puml files. The files needs to reside inside of documentation/diagrams.
* `task render:drawio`: This is a command that creates images from.drawio files. The files needs to reside inside of documentation/diagrams.
