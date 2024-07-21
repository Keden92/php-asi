# Asynchronous Multithread Interface for PHP (PURE PHP!)
asi (ASync Interface) is a class written in pure PHP that adds multithreading support to a single PHP script.

The goal of this project is to execute long-running tasks like database updates or file downloads (for example, with cURL) in a second, third, ..., process while performing other tasks in the "main thread" simultaneously.

## Requirements:
-  PHP 7 or greater (incl. php-cli) (maybe also the old 5* versions but untested)
-  eval and proc_* functions MUST NOT be disabled!
-  underlying Linux (untested on other operating systems, maybe working, try and report)

Run the requirements.php file to check. If it shows "All checks passed! - Should be working :)", then you're good to go.

## Installation:
Copy the stand-alone file asi.class.php to your project and include it.
```
include_once("asi.class.php");
```
## How to use

### 1. To start a child-thread, use the "new" function call and store the return value.
This function takes two arguments:
   - The first argument is a string used only for thread identification and debugging functions. Name it whatever you want. (required)
   - The second one is optional and takes an array of files to include in the child-thread.

     ```
     /* include the extension */
     include_once("asi.class.php");
    
     /* spawn a thread with the name "Example Thread 1", without any additional includes */
     $child = asi\async::new("Example Thread 1");
    
     /* or spawn a thread with, for example, the example.helpers included */
     /* by including additional files, take care not to build a recursion!! */
     $child = asi\async::new("Example Thread 2", [__DIR__."/asi_example.helper.php"]);
    
     /* the $child variable now holds all the necessary information and is the "master-object" of the child-thread */
     ```
     
     ![grafik](https://github.com/user-attachments/assets/a8a61fe6-379d-43e2-abdb-fdb6b353fff6)

### 2. Work with the child-thread
After spawning a child thread, give it some work.
   - Assign variables
   - Execute some functions
   - Whatever you need
     
     ```
     /* assign variable to child */
     $child->example_string = "Example";
     ```

   - By default, 99% of interactions with the child do not need async execution order. Passing variables or configuring objects is fast enough that interactions with the child are automatically "awaited". To execute some work on the child, you must tell the asi-extension not to autowait. The autowait returns automatically to true after each interaction with the child. In case autowait is set to false, the extension returns a "promise". This can be checked later in the main-thread by calling the asi\ready() function or, if you need to wait for the result, call asi\wait() with the affected variable. (Don't use the child-main-object (named $child in this examples) use the specific child-variable you want!)
   - Calling functions on the child thread needs a special syntax. Based on PHP execution order, a function is executed first, and at last, the return value is assigned to the variable. To prevent this behavior, we must tell PHP not to execute it but to pass this function for execution to the child process. This is done by prepending the asi namespace identifier. Of course, if you want to execute it on the child, the return value must also be assigned to a variable on the child.

     ```
     /* turn off the autowait function for the next call */
     asi\async::autowait($child, false);
    
     /* execute a function on the child and assign to a child variable */
     $child->example_sleep_result = asi\sleep(5);

     /* this will NOT work!! */
     // $whatever = asi\sleep(5);
    
     /* do some work on the main thread */
     sleep(2); // wait 2 seconds
    
     /* check if variable is ready */
     $bool = asi\ready($child->example_sleep_result); // returns true or false -> false in this case
    
     /* do some other work on the main thread */
     sleep(2); // wait 2 seconds
    
     /* wait for the result */
     $result = asi\async::wait($child->example_sleep_result);
    
     $bool = asi\ready($child->example_sleep_result); // will now return true
    
     /* after asi\ready returns true on a variable or it is waited for, it can be accessed like a default variable in PHP */
     echo $child->example_sleep_result; // will print out "0"
     ```
### 3. Finally, close the child thread if the work is done and it is not needed anymore.
This is a very important step you need to take care of! If your main thread crashes (e.g., syntax error), the child is never terminated and will run until you kill it manually or reboot the system.
   - You can check if the child is doing some work by calling the asi\inuse() function with the "child-main-object"
   - Finally, you can shut down the child by unsetting the "child-main-object"
   - If you unset a child while it is in use and running, the main thread will be delayed until the child completes its work!

     ```
     /* check if child-thread is in use */
     $bool = asi\inuse($child);

     /* if not, unset it to initiate the shutdown */
     unset($child);
    
     /* that's all */
     ```
> [!IMPORTANT]
> Take a look at the zombies.php and **use it** to detect zombie-threads!
     
> [!TIP]
> Take a look at the examples folder to check out how to work with classes and objects, for example.

## Known Limitations
-  You can't pass PHP types that are resources or objects include these (like file streams or network connections)
-  If you want to pass instantiated objects, they must conform to the Serializable interface. 
-  In these special cases, use functions or classes in separate files and include them on asi\async::new(). (Take a look at the examples folder)

## Features Not Implemented Yet
-  A detach functionality

## Summary
All in all, this extension is still in beta (v5.1). Take it, try it, test it, and report bugs.

Help me to get this thing production-ready.

If you want to support it, buy me a beer ;)

