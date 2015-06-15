The purpose of this project was to use socket programming to implement a client
and server that could interact as a simple file transfer program. The client can
only make one request each time it runs, which is to either view a directory
listing or obtain a particular file. There are 2 connections involved, a control
connection and a data connection. The server was written in C++ and the client
was written in Python.
-------------------------------------------------------------------------------


Project 2 README
Lisa Percival

To get started:
Open 2 sessions to the flip servers (access.engr.oregonstate.edu). When I did this it
ended up being both on flip1, but that could change.

To compile:
In one session, run "make". This will create an executable called ftserver.
Python doesn't need to be compiled.

To start and use:
First, in the session that will be used as the server, run "ftserver port_#" 
where port_# is the number of the port you want the server to run on. It will
start and show a message about the server being open, then just wait.

Then, in the other session, start the client by running either "python ftclient.py
server_hostname server_port_# -l data_port_#" to see a listing of directory contents
or "python ftclient.py server_hostname server_port_# -g data_port_# filename" to
have the contents of the file called filename sent from ftserver to ftclient and
saved in the same directory as ftclient. The server_hostname will depend on exactly
which flip server the server session is on (in my case I used localhost). The 
server_port_# should be the same port number provided to ftserver. The parameters
-l and -g are the 2 valid commands for the system. The data_port_# is the port
where you want ftserver to initiate a 2nd connection to ftclient and send either
the directory or file contents (must be different from server_port_#). The filename 
given, if the command is -g, is assumed to be a file in the same directory as ftserver.

That is the only user interaction required. After starting the client you should
see a sequence of messages displayed in both sessions that let you know what's
going on. When the one command is complete, ftclient will end but ftserver will
remain waiting. You can run ftclient again or end ftserver with a Ctrl-C.