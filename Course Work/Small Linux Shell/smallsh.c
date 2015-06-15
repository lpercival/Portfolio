//CS344 Program 3: smallsh
//Author: Lisa Percival
//Date Crated: 2/7/15
//Description: Implement a small shell that can run command line instructions
//and return the results. It has 3 built-in commands: exit, cd, and status. It
//can also run existing Linux commands, with options for redirecting 
//standard input and output and running processes in either the foreground
//or background.

#include <stdio.h>
#include <stdlib.h>
#include <limits.h>
#include <string.h>
#include <unistd.h>
#include <sys/wait.h>
#include <sys/types.h>
#include <sys/stat.h>
#include <fcntl.h>
#include <signal.h>

#define MAX_ARGS 512	//have room for 512 arguments in Command struct

//structure to hold the information about an entered command, once parsed
struct Command {
	char *cmd;		//the actual command itself
    char *args[MAX_ARGS];	//array of arguments to command, includes cmd & null
    int numArgs;		//number of arguments to command- array size - 2
    int inputRedirect;		//1 if command has input redirection
    char *inFile;		//input file, NULL if no input redirection
    int outputRedirect;		//1 if has output redirection
    char *outFile;		//output file, NULL if no output redirection
    int isBackground;		//1 if command should be run in the background
};

//global variables for status functionality
//whether last foreground process had exit status or terminating signal
int lastForegroundTerminated = 0;	//0 means use exit status, 1 => use signal
//the exit status of the last foreground process, assuming exited normally
int lastForegroundStatus = 0;
//the terminating signal of the last foreground process terminated by signal
int lastForegroundSignal = 0;

struct Command parseCommand(char *input);
void startForeground(struct Command command);
void startBackground(struct Command command);
void checkForBackgroundCompletion();
void smallStatus();
void smallCd(char *newDir);

int main()
{
	struct Command theCommand;	//stores the parsed commands
    char aLine[LINE_MAX];		//captures the commands in raw form
	
	//don't want Ctrl-C to terminate shell so use sigaction to ignore SIGINT
	struct sigaction ignoreIt;
	ignoreIt.sa_handler = SIG_IGN;
	ignoreIt.sa_flags = 0;
	sigfillset(&(ignoreIt.sa_mask));	//block all signals while executing
	sigaction(SIGINT, &ignoreIt, NULL);

    printf(":");		//show the prompt
	fflush(stdout);		//flush the buffer for readability
	fgets(aLine, LINE_MAX, stdin);	//capture command input
	//keep prompting for input and doing commands until they say to exit
    while (strcmp(aLine, "exit\n") != 0)
	{
		//turn the line into the necessary components for the command
		theCommand = parseCommand(aLine);
		if (strcmp(theCommand.cmd, "") == 0)
		{
			//blank or comment both set to "", do nothing
		}
		else if (strcmp(theCommand.cmd, "status") == 0)	//built-in status cmd
		{
			smallStatus();
		}
		else if (strcmp(theCommand.cmd, "cd") == 0)	//if built-in cd command
		{
			if (theCommand.numArgs == 0)	//just typed "cd" so do default
			{
				smallCd(getenv("HOME"));	//go to HOME environment variable
			}
			else
			{
				smallCd(theCommand.args[1]);	//call with what was after cd
			}
		}
		else		//it's some other Linux command (or invalid)
		{
			if (theCommand.isBackground == 1)	//call in background if was &
			{
				startBackground(theCommand);
			}
			else		//otherwise call in foreground like normal
			{
				startForeground(theCommand);
			}
		}

		//prep for the next iteration: look for completed backgrounds, prompt
		checkForBackgroundCompletion();
		printf(":");
		fflush(stdout);		//flush buffer for readability
		if(!(fgets(aLine, LINE_MAX, stdin))) //capture command input, if exists
		{
			break;	//if fgets returns NULL there's no more input, leave loop
		}
	}

	exit(0); //they typed "exit" (the built-in command) or ran out of input
}

//Function: parseCommand
//Description: Used to take the string command line given and divide it up into
//parts while determining what aspects were included. It sets the actual 
//command, any arguments, an input file if applicable, an output file if 
//applicable, and determines whether a & was written to request running in
//the background.
//Parameters: the string (char *) of input
//Returns: the parsed command in a Command struct
struct Command parseCommand(char *input)
{
	int i;		//loop counter
	char *token;		//used with strtok to pull out pieces of input
	struct Command aCommand;
	//initialize the command - prep all char *s for strcpy
	aCommand.cmd = (char *)malloc(512*sizeof(char));
	for (i = 0; i < MAX_ARGS; i++)
	{
		aCommand.args[i] = (char *)malloc(512*sizeof(char));
	}
	aCommand.numArgs = 0;			//start at 0- only counts real args
	aCommand.inputRedirect = 0;		//start false
	aCommand.inFile = (char *)malloc(512*sizeof(char));
	aCommand.outputRedirect = 0;	//start false
	aCommand.outFile = (char *)malloc(512*sizeof(char));
	aCommand.isBackground = 0;		//start false

	//before do anything get rid of the \n at the end of the read line
	input[strlen(input)-1] = '\0';
	
	//a blank line should have cmd=""
	if (strcmp(input, "") == 0)
	{
		strcpy(aCommand.cmd, "");
		return aCommand;		//skip the rest
	}
	//for a comment starting with # also set cmd="" so can do 1 check in main
	if (input[0] == '#')	//check for # in first character
	{
		strcpy(aCommand.cmd, "");
		return aCommand;	//skip the rest
	}

	//get the first thing, which should be the command
	token = strtok(input, " ");
	//set cmd field and first argument
	strcpy(aCommand.cmd, token);
	strcpy(aCommand.args[0], token);
	
	//get the rest of the tokens and handle them accordingly
	while (token = strtok(NULL, " "))
	{
		//if there's input redirection set the indicator and get the file
		if (strcmp(token, "<") == 0)
		{
			aCommand.inputRedirect = 1;
			token = strtok(NULL, " ");		//next token is the input file
			strcpy(aCommand.inFile, token);
			break;	//because otherwise it goes in the else with new token
		}
		//if there's output redirection set the indicator and get the file
		if (strcmp(token, ">") == 0)
		{
			aCommand.outputRedirect = 1;
			token = strtok(NULL, " ");		//next token is output file
			strcpy(aCommand.outFile, token);
			break;	//because otherwise it goes in the else with new token
		}
		//if there's a & set the indicator for being a background process
		if (strcmp(token, "&") == 0)
		{
			aCommand.isBackground = 1;
		}
		//everything else must just be an argument
		else
		{
			aCommand.numArgs++;		//increment 1st to accommodate 0 used above
			strcpy(aCommand.args[aCommand.numArgs], token);
		}
	}
	//now that it's done args has cmd and any args, need to add NULL at end
	aCommand.args[aCommand.numArgs+1] = NULL;
	
	return aCommand;
}

//Function: startForeground
//Description: Used to start a requested Unix command (not one of the smallsh
//built-in commands) that was intended to run in the foreground. Uses fork and
//execvp to generate the new process and waitpid to wait for that process to
//complete before moving on. Sets up input and/or output redirection to files
//depending on the values in the parsed command.
//Parameters: the Command structure with information on what to run & how
//Returns: nothing, just runs the requested command
void startForeground(struct Command command)
{
	pid_t spawnpid;		//capture return value/ new pid generated by fork
	int status;			//capture the return value of waitpid
	int fdIn;			//file descriptor for input redirection
	int fdOut;			//file descriptor for output redirection
	int dupResult;		//to capture result of dup2 and verify worked

	//create a new process, which starts as a copy of the current process
	spawnpid = fork();	//returns 0 in child, new pid in parent
	//if you're in the child, use execvp to run the different program
	if (spawnpid == 0)
	{
		//set it to stop ignoring Ctrl-C, which was inherited from parent
		struct sigaction dontIgnore;
		dontIgnore.sa_handler = SIG_DFL;		//back to default
		dontIgnore.sa_flags = 0;
		sigfillset(&(dontIgnore.sa_mask));	//block all signals while executing
		sigaction(SIGINT, &dontIgnore, NULL);

		//if requested, redirect input to the file provided
		if (command.inputRedirect == 1)
		{
			//open file for reading only
			fdIn = open(command.inFile, O_RDONLY);
			if (fdIn == -1)		//verify worked
			{
				printf("Error: input file could not be opened.\n");
				fflush(stdout);
				exit(1);
			}
			//set it to close on exec for security reasons
			fcntl(fdIn, F_SETFD, 1);
			//make stdin point to the file
			dupResult = dup2(fdIn, 0);
			if (dupResult == -1)	//verify worked
			{
				printf("Error: dup2 failed to redirect input.\n");
				fflush(stdout);
				exit(1);
			}
	
		}
		//if requested, redirect output to the file provided
		if (command.outputRedirect == 1)
		{
			//open file for writing only, truncate if exists, create if not
			fdOut = open(command.outFile, O_WRONLY | O_TRUNC | O_CREAT, 0664);
			if (fdOut == -1)		//verify worked
			{
				printf("Error: output file could not be opened.\n");
				fflush(stdout);
				exit(1);
			}
			//set it to close on exec for security reasons
			fcntl(fdOut, F_SETFD, 1);
			//make stdout point to the file
			dupResult = dup2(fdOut, 1);
			if (dupResult == -1)	//verify worked
			{
				printf("Error: dup2 failed to redirect output.\n");
				fflush(stdout);
				exit(1);
			}
		}

		execvp(command.cmd, command.args);	//replaces with new process
		//will only do the following if fails (couldn't find command to run)
		printf("Error: could not run command.\n");
		fflush(stdout);
		//set exit status to 1
		exit(1);
	}
	//if in parent, use waitpid to see if the child has finished
	else
	{
		spawnpid = waitpid(spawnpid, &status, 0);
		//use macros to interpret what happened with status
		if (WIFEXITED(status))		//terminated normally
		{
			//set global status variables for exit status
			lastForegroundTerminated = 0;	//normal exit, not signal
			lastForegroundStatus = WEXITSTATUS(status);
		}
		else if (WIFSIGNALED(status))	//terminated by signal, print message
		{
			printf("terminated by signal %d\n", WTERMSIG(status));
			fflush(stdout);
			//set global status variables for exit status
			lastForegroundTerminated = 1;	//terminated by signal
			lastForegroundSignal = WTERMSIG(status);
		}
	}
}

//Function: startBackground
//Description: Used to start a requested Unix command (not one of the smallsh
//built-in commands) that was intended to run in the background. Uses fork and
//execvp to generate the new process. Sets up input and/or output redirection to
//files depending on the values in the parsed command.
//Parameters: the Command structure with information on what to run & how
//Returns: nothing, just runs the requested command
void startBackground(struct Command command)
{
	pid_t spawnpid;		//capture return value/ new pid generated by fork
	int fdIn;			//file descriptor for input redirection
	int fdOut;			//file descriptor for output redirection
	int dupResult;		//to capture result of dup2 and verify worked

	//create a new process, which starts as a copy of the current process
	spawnpid = fork();	//returns 0 in child, new pid in parent
	//if you're in the child, use execvp to run the different program
	if (spawnpid == 0)
	{
		//if requested, redirect input to the file provided
		if (command.inputRedirect == 1)
		{
			//open file for reading only
			fdIn = open(command.inFile, O_RDONLY);
			if (fdIn == -1)		//verify worked
			{
				printf("Error: input file could not be opened.\n");
				fflush(stdout);
				exit(1);
			}
			//set it to close on exec for security reasons
			fcntl(fdIn, F_SETFD, 1);
			//make stdin point to the file
			dupResult = dup2(fdIn, 0);
			if (dupResult == -1)	//verify worked
			{
				printf("Error: dup2 failed to redirect input.\n");
				fflush(stdout);
				exit(1);
			}
	
		}
		else	//if no input redirection, redirect stdin to /dev/null
		{
			//open /dev/null first
			fdIn = open("/dev/null", O_RDONLY);
			dupResult = dup2(fdIn, 0);
			if (dupResult == -1)	//verify worked
			{
				printf("Error: dup2 failed to redirect stdin to /dev/null.\n");
				fflush(stdout);
				exit(1);
			}
		}
		//if requested, redirect output to the file provided
		if (command.outputRedirect == 1)
		{
			//open file for writing only, truncate if exists, create if not
			fdOut = open(command.outFile, O_WRONLY | O_TRUNC | O_CREAT, 0664);
			if (fdOut == -1)		//verify worked
			{
				printf("Error: output file could not be opened.\n");
				fflush(stdout);
				exit(1);
			}
			//set it to close on exec for security reasons
			fcntl(fdOut, F_SETFD, 1);
			//make stdout point to the file
			dupResult = dup2(fdOut, 1);
			if (dupResult == -1)	//verify worked
			{
				printf("Error: dup2 failed to redirect output.\n");
				fflush(stdout);
				exit(1);
			}
		}
		
		//Ctrl-C shouldn't terminate background,  use sigaction to ignore SIGINT
		struct sigaction ignoreIt;
		ignoreIt.sa_handler = SIG_IGN;
		ignoreIt.sa_flags = 0;
		sigfillset(&(ignoreIt.sa_mask));	//block all signals while executing
		sigaction(SIGINT, &ignoreIt, NULL);	//should only apply to child process

		execvp(command.cmd, command.args);	//replaces with new process
		//will only do the following if fails (couldn't find command to run)
		printf("Error: could not run command.\n");
		fflush(stdout);
		//set exit status to 1
		exit(1);
	}
	//if in parent, print the pid of the command that started - don't wait
	else
	{
		printf("Background pid is %d\n", spawnpid);
		fflush(stdout);
	}
	
}

//Function: checkForBackgroundCompletion
//Description: Runs before every new prompt to see if any background
//processes have completed, using a waitpid that checks for any pid and just
//returns 0 if nothing has finished. A while loop allows it to capture more
//than one completed process, and the WIFEXITED, WEXITSTATUS, WIFSIGNALED, and
//TERMSIG macros allow printing of the exit value or terminating signal.
//Parameters: none
//Returns: nothing, just prints information about any completed processes
void checkForBackgroundCompletion()
{
	pid_t completedPid;		//hold the pid of any completed background processes
	int status;		//capture information about completed process in waitpid	

	//use waitpid with -1 and WNOHANG to check if anything has completed
	//returns the pid of something that has, or 0 or -1 if none has
	while ((completedPid = waitpid(-1, &status, WNOHANG)) > 0)
	{
		//print pid of process that completed, with exit value or signal
		if (WIFEXITED(status))	//terminated normally, print exit value
		{
			printf("Background pid %d is done: exit value %d\n",
					completedPid, WEXITSTATUS(status));
			fflush(stdout);
		}
		else if (WIFSIGNALED(status))	//terminated by signal, print number
		{
			printf("Background pid %d is done: terminated by signal %d\n",
					completedPid, WTERMSIG(status));
			fflush(stdout);
		}
		else		//this should never happen
		{
			printf("Background pid %d somehow terminated without exit value"
					" or signal\n", completedPid);
			fflush(stdout);
		}
	}
}

//Function: smallStatus
//Description: One of the built-in functions for the small shell, it prints
//either the exit status or terminating signal of the last foreground
//process (depending on whether the process exited normally or by signal). It
//uses global variables to track these values. This command and the other
//built-in, smallCd, are considered to be foreground processes and therefore
//set the exit status to 0.
//Parameters: none
//Returns: nothing, just prints
void smallStatus()
{
	//if the last foreground process was terminated by a signal, print it
	if (lastForegroundTerminated == 1)
	{
		printf("terminated by signal %d\n", lastForegroundSignal);
		fflush(stdout);
	}
	//otherwise it exited normally so print the exit status
	else
	{
		printf("exit value %d\n", lastForegroundStatus);
		fflush(stdout);
	}

	//set global status variables to indicate successful normal exit
	lastForegroundTerminated = 0;		
	lastForegroundStatus = 0;
}

//Function: smallCd
//Description: One of the built-in functions for the small shell, it will
//change the working directory to the string that is passed (which may be
//a user-provided argument or HOME, as decided in main).
//Parameters: char *newDir the directory to change to
//Returns: nothing, just changes the current working directory
void smallCd(char *newDir)
{
	chdir(newDir);

	//set global status variables to indicate successful normal exit
	lastForegroundTerminated = 0;		
	lastForegroundStatus = 0;
}
