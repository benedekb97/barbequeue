# Log levels

Short little convention doc on when to use which levels

## Emergency

Use this if there is a massive failure, and the whole codebase is unusable as a result of it. It is likely that you will
never need to use this command, as there isn't much you can do in PHP to cause such a massive failure.

## Alert

Use this if you think something is unsecure and could result in a leak of private information or code. This is also a 
very unlikely scenario if you adhere to coding standards and use basic common sense

## Critical

Use this if something unexpected makes you stop processing a request entirely, e.g. an unknown exception is caught

## Error

Use this if something expected makes you stop processing a request and could introduce some instabilities into later 
processing e.g. a missing argument that could be required by the application at a later stage.

## Warning

Use this when someone is doing something weird in your code, e.g. trying to brute-force and endpoint. It serves as a 
_warning_ to anyone looking at the logs that something funny could be going on

## Notice

Use this when you want to signify the relevance of a specific log entry that would otherwise only be info.

## Info

For use for statistics, logging the normal operation of the application

## Debug

Use this if it makes no difference to the normal operation of the application if the entry is dropped or not, e.g. while
debugging your code. If you need to log something to debug a piece of code you are writing, it is likely that someone 
else will also need to, so leave these in!
