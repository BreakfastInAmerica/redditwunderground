redditwunderground
==================

A Reddit Weather Underground Script

This script ran as a cron will pull your city weather information from Weather Underground and sumbit it to a subreddit.

Requirements:
Pear HTTP_Request2

Bugs:
Reddit returns a cookie that HTTP_Request2 sees as invalid. You can modify HTTP_Request2 to remove the exception or edit the regex.
