# Project 2 ftclient
# Author: Lisa Percival
# Date Created: 5/14/15
# Description: Connects to the server at the hostname and port number provided on
# the command line to initiate the control connection. Sends the command provided
# on the command line to ftserver for processing. Handles the response from
# ftserver, either by displaying an error message for an invalid command or
# accepting a data connection on the data port given on the command line, where
# it is waiting like a server. With the data connection, it can capture and save the contents of
# a file from ftserver, or display the directory sent by ftserver. Errors from ftserver
# related to reading the file arrive on the control connection. Closes the
# control connection after the single command.
# Usage: python ftclient.py server_hostname server_port_# command data_port_# [filename]
# 	* The filename is only needed when the command is -g
# References Used: mostly the book and lectures, my Project 1, Python resources,
# some specific ones included in the code

from socket import *
import sys
import os.path

# Function: initiateContact
# Description: Performs the necessary steps to create a client socket that
# connects to the given server. Creates a TCP socket using the server name and
# port provided, then initiates contact with the connect() function.
# Parameters: none
# Returns: the client socket created
def initiateContact():
	# Make the socket as a client - control connection
	serverName = sys.argv[1]
	serverPort = int(sys.argv[2])
	cliSocket = socket(AF_INET, SOCK_STREAM)
	# Connect to the server
	cliSocket.connect((serverName, serverPort))
	
	return cliSocket

# Function: setupServerSocket
# Description: Performs the necessary steps to create a server socket that
# listens on the given port. Creates a TCP socket and binds it to the
# port provided, then listens.
# Parameters: none
# Returns: the client socket created
def setupServerSocket():
	# Make the socket as a server
	serverPort = int(sys.argv[4])
	try:
		servSocket = socket(AF_INET, SOCK_STREAM)
		# Bind to the port
		servSocket.bind(('', serverPort))
		# Listen with only 1 possible queued connection
		servSocket.listen(1)
	except socket.error, msg:
		sys.stderr.write("[ERROR] %s\n" % msg[1])
		
	return servSocket

# Function: makeRequest
# Description: Handles all of the logic of making a request to ftserver, starting
# with sending a command over the control connection. Makes a series of subsequent
# decisions based on responses from ftserver, including printing error messages,
# sending additional information, allowing a data connection, and handling
# actual content sent by ftserver.
# Parameters: the control socket to send the command on
# Returns: 	nothing
def makeRequest(ctrlSocket):
	# Send the provided command to ftserver over the control connection
	mySendAll(ctrlSocket, sys.argv[3])
	
	# Check the response from ftserver, which will validate the command
	# If it's valid server will request data port
	response = myRecvAll(ctrlSocket)
	if response != 'Data port please':
		# print the error and end request process
		print response
	else:		# Continue request process
		# Set up socket to act as server on data port to accept data connection from ftserver
		welcomeSocket = setupServerSocket()	# before send port so sure is ready
		
		# Send the data port to ftserver so it can initiate the connection
		mySendAll(ctrlSocket, sys.argv[4])
		
		# Accept the data connection from ftserver
		dataSocket, addr = welcomeSocket.accept()
		# Get the initial message that ftserver sends in response to command
		response = myRecvAll(dataSocket)
		# If the command was -l it will have just sent the directory contents
		# If it was -g it will send a request for the filename to send
		if response != 'Filename please':
			# Display directory contents and end request process
			print 'Received directory contents:'
			print response
		else:	# Send the filename to continue request process
			mySendAll(dataSocket, sys.argv[5])

			# Receive the file, or handle errors
			receiveFile(ctrlSocket, dataSocket)

# Function: receiveFile
# Description: Does the portion of making a request that receives a file, assuming
# the -g parameter was provided. Also checks for an error message from ftserver
# in case the file was not found or something. Handles duplicate file name if 
# necessary, saves file in current directory, and displays "transfer complete" message.
# Parameters: the control socket to check for errors, data socket to receive content on
# Returns: 	nothing			
def receiveFile(ctrlSocket, dataSocket):
	# Check ftserver's response for an error or pending file contents
	# Since the error has to come on control connection, check that first
	response = myRecvAll(ctrlSocket)
	if response != 'OK':	# Have to send extra control connection OK message if no error
		# Print error message
		print response
	else:	# ftserver will send file contents on data connection
		filename = sys.argv[5]
		print 'Receiving "%s"' % filename
		
		contents = myRecvAll(dataSocket)
		
		# Check for duplicate filename
		# help from: http://www.pythoncentral.io/check-file-exists-in-directory-python/
		originalFilename = filename
		version = 1
		while os.path.isfile(filename):		# already exists
			# Change name
			filename = '%s_%s' % (version, originalFilename)
			version += 1		# in case have to change more than once
		# Notify user if had to change
		if version > 1:
			print 'Duplicate file name encountered. Name adjusted accordingly.'
        
		# Save to file in current directory
		# help from: https://docs.python.org/2/tutorial/inputoutput.html
		file = open(filename, 'w')
		file.write(contents)
		file.close()
		
		# Print "transfer complete" message, done
		print 'Transfer complete. File saved at %s' % filename

# Function: mySendAll
# Description: Performs the whole message exchange used to ensure complete sending
# of messages. If it were just Python sendall would be sufficient, but ftserver 
# needs the exchange of length, confirmation message, then actual message so this
# must reflect that.
# Parameters: the socket to send on, the message to be sent
# Returns: 	-1 on error, otherwise nothing	
def mySendAll(socket, message):
	# Determine the length of the message
	length = len(message)
	
	# Send the length of the message so recipient knows how much to expect
	# Should be 10 long for consistency
	length = format(length, '010d')
	# sendall should take care of getting it all through, per python documentation
	socket.sendall(length);
	
	# Read the confirmation message, because taking turns keeps things separate
	response = recvAll(socket, 8)
	if response != 'Got size':
		print 'Issue with exchange for sending from client to server.'
		return -1
	
	# Then send the actual message
	socket.sendall(message)

# Function: myRecvAll
# Description: Performs the whole message exchange used to ensure receiving complete
# messages. Follows the established process of exchanging message length, followed
# by a confirmation message, and then the actual message.
# Parameters: the socket to receive on
# Returns:  the message receieved
def myRecvAll(socket):
	# Read the length of the coming message to expect, its size should be 10 bytes
	length = recvAll(socket, 10)
	
	# Send a confirmation message, because taking turns keeps things separate
	socket.sendall('Got size')
	
	# Then read the actual message
	message = recvAll(socket, length)
	
	return message
	
# Function: recvAll
# Description: My counterpart to Python's sendall. Ensures that a single piece of
# data is received in its entirety by using its length. Keeps calling recv until
# length number of bytes have been received.
# Parameters: the socket to receive on, the expected length of the data
# Returns:  the message received
def recvAll(socket, length):
	totalRead = 0			# add up all bytes read until reach goal
	message = ''			# to build up all the pieces read
	
	# Keep reading until we reach the desired length of message
	# help from: ubuntuforums.org/showthread.php?t=1306456
	while totalRead < int(length):	
		data = socket.recv(int(length) - totalRead)
		message += data		# append chunk of data to message
		totalRead += len(data)
	
	return message
	
	
# *** Main flow of program begins here ***

# Check the number of command line arguments is correct (5 or 6)
if len(sys.argv) != 5 and len(sys.argv) != 6:
	print 'Incorrect number of arguments. Need server hostname and port, command,'
	print ' data port and optional filename. Try again.'
	sys.exit(1)
# Further check that if the command is -g then the number of arguments is 6
if sys.argv[3] == '-g' and len(sys.argv) != 6:
	print 'Incorrect number of arguments. When the command is -g there must be a'
	print ' server hostname and port, command, data port, and filename. Try again.'
	sys.exit(1)
	
# Initiate contact with ftserver on control connection by making client socket
ctrlSocket = initiateContact()

# Make the request, starting by sending the command, then follow logic based on
# ftserver's response
makeRequest(ctrlSocket)

# Close the control connection, data connection closed by ftserver
ctrlSocket.close()

