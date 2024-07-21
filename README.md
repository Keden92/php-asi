# Asynchron Multithread Interface for PHP (PURE PHP!)
asi (ASync Interface) is a class written in pure PHP that adds multithreading support to a single PHP script.

The goal of this project is to execute long-running tasks like database updates or file downloads (for example, with cURL) in a second, third, ... process while performing other tasks in the "main thread" simultaneously.

# Requirements:
-  PHP 7 or greater (Incl. php-cli) (maybe also the old 5* versions but untested)
-  eval MUST NOT be disbaled
-  underlying linux (unteste on other operating systems)

Run the requirements.php file to check.
