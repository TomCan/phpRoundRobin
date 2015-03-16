# phpRoundRobin

phpRoundRobin is my attempt to create a library that can be used to store a round robin datasources, without relying on librrd and the PECL/rrd extension. 

## Why
The PECL/rrd extension is often not available, definitely not on your average shared hosting setup. Also, the extension is basically a wrapper around librrd/rrdtool, forcing you to use the same syntax as if you were to run rrdtool from the command line. By using only plain PHP functionality and a set of easy to use hierarchical classes, I hope to overcome both problems.

The library also aims to provide a flexible backend storage format, allowing you to save your round robin database to various different formats, allowing you to create custom integrations that tap directly into your database using the tools and libraries you are used to.

## Why not?
This project does not aim to be a full-fledged replacement for RRDTool. Things like graphing are not part of the scope. Also, since this is written in plain PHP, performance will probably not be able to compete with the native RRDTool or PECL extension.
