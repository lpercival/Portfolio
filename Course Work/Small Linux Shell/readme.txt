This program was an assignment for my Operating Systems course, intended to implemented
some of the basic functionality of a Linux shell. It includes the ability to run
existing Linux programs, its own version of the "cd" command, exit, and a status
command that shows the exit value or termination signal of the last foreground process that
ended. For existing Linux programs it supports input and output redirection, as
well as running them in the background. It makes use of fork, exec, and signal
handling.
--------------------------------------------------------------------------------



To compile, simply use gcc -o, for example:
	gcc -o smallsh smallsh.c
