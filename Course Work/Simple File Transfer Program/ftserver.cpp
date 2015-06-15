/* Project 2 ftserver
*  Author: Lisa Percival
*  Date Created: 5/25/15
*  Description: Sets up a server socket at the port given on the command line and
*  waits for ftclient to connect to it. When a connection comes in, it handles the
*  request depending on what it is. First it validates that the sent command is
*  valid. If it is, it either gets the directory contents and sends them (-l) or
*  reads in a file and sends it (-g), after making a new socket for the data connection
*  where it acts as a client. There is an alternating exchange of messages
*  with ftclient and special functions are used to make sure whole messages get
*  sent and received.
*  Compile: g++ -o ftserver ftserver.cpp  (or use makefile)
*  Usage: ftserver port_#
*  Note: must be started before ftclient
*  References used: Beej's Guide, my Project 1, some specific things commented below
*/

#include <iostream>
#include <cstring>
#include <string>
#include <cstdlib>
#include <unistd.h>
#include <sys/types.h>
#include <sys/socket.h>
#include <netdb.h>
#include <dirent.h>
#include <fstream>
using std::cout;
using std::endl;
using std::cin;
using std::getline;
using std::string;
using std::ifstream;

const int NUM_SIZE = 10;	//all #s will be 10 bytes when converted to strings

int startup(int &servSocket, char *argv[]);
int startDataConn(int &dataSocket, string dataPort, struct sockaddr_in client);
int handleRequest(int ctrlSocket, struct sockaddr_in client);
int sendDir(int dataSocket);
int sendFile(int ctrlSocket, int dataSocket, string filename);
int socketSend(int sockfd, const char *msg, int length);
int socketSend(int sockfd, char *msg, int length);
int socketRead(int sockfd, string &readMsg, int length);
int sendAll(int sockfd, string msg);
int recvAll(int sockfd, string &message);

int main (int argc, char *argv[])
{
	int servSocket;		// socket file descriptor for control connection (server welcoming)
	int ctrlSocket;		// socket file descriptor for new control connection socket
	struct sockaddr_in client;	//the structure to capture client at connection
	socklen_t clientLength = sizeof(client);	//size of client, for accept
	int result;			// to capture and check results of functions
	
	// Check for the correct # of command line arguments (2)
	if (argc != 2)
	{
		cout << "Incorrect number of arguments. Need port number. Try again" << endl;
		return 1;
	}
	
	// Start up the control connection welcoming socket - acts as server and listens
	result = startup(servSocket, argv);
	if (result == -1)	// there was an error
	{
		return 1;
	}
	
	// Keep listening for new client connections
	while(1)
	{
		// Accept and make new socket that is actual control connection
		ctrlSocket = accept(servSocket, (struct sockaddr *) &client, &clientLength);
		if (ctrlSocket < 0)
		{
			cout << "Error with accept" << endl;
			return 1;
		}
		cout << endl << "Client connected" << endl;
		
		// Handle the request that comes through the control connection
		// Does everything needed for this request/ connection
		handleRequest(ctrlSocket, client);
	}
}

/* Function: startup
*  Description: Performs the necessary steps to create a server socket that is
*  ready for client connections, on the given port. Creates, binds, and listens.
*  Parameters: the socket being setup (int by reference), the command line
*  arguments that contain the desired port to use
*  Returns: 0 normally, -1 if an error is encountered
*/
int startup(int &servSocket, char *argv[])
{
	struct sockaddr_in server;	//the structure for the server socket setup
	int port;			// the port number given to run on
	
	// Make a server socket
	servSocket = socket(AF_INET, SOCK_STREAM, 0);
	if (servSocket < 0)		//verify worked
	{
		cout << "Error opening socket" << endl;
		return -1;
	}
	// Clear out the server address struct then fill it accordingly
	memset(&server, 0, sizeof(server));
	server.sin_family = AF_INET;
	port = atoi(argv[1]);		//convert string to integer
	server.sin_port = htons(port);	//handle byte ordering
	server.sin_addr.s_addr = INADDR_ANY;	//accept anything because is server
	//bind the server socket to the given address/port
	if (bind(servSocket, (struct sockaddr *) &server, sizeof(server)) < 0)
	{
		cout << "Error on binding" << endl;
		return -1;
	}
	// listen on the given port, with a queue size of 1
	listen(servSocket, 1);
	
	cout << "Server open on port " << port << endl;
	return 0;	//normal, successful end
}

/* Function: startDataConn
*  Description: Performs the necessary steps to set up the data connection with 
*  a client socket that connects to ftclient on the given data port. Creates and
*  connects. Makes use of the client struct from the earlier accept, which has
*  the address of ftclient.
*  Parameters: the socket being setup (int by reference), the data port as a string,
*  the sockaddr_in with ftclient's info
*  Returns: 0 normally, -1 if an error is encountered
*/
int startDataConn(int &dataSocket, string dataPort, struct sockaddr_in client)
{
	int port;							// the port number for connection
	
	// Make a client socket
	dataSocket = socket(AF_INET, SOCK_STREAM, 0);
	if (dataSocket < 0)		//verify worked
	{
		cout << "Error opening socket" << endl;
		return -1;
	}
	
	// The client passed in has data from the control connection accepted from ftclient
	// just use the client saved earlier, but change to the data port
	port = atoi(dataPort.c_str());	//convert string to integer
	client.sin_port = htons(port);		//handle byte ordering
	//try to connect to ftclient on given port
	if (connect(dataSocket, (struct sockaddr *) &client, sizeof(client)) < 0)
	{
		cout << "Error connecting data connection" << endl;
		perror("Error was");
		return -1;
	}
	
	return 0;	//normal, successful end
}

/* Function: handleRequest
*  Description: Goes through all the steps involved in taking care of a request
*  from ftclient. Gets the command and validates it. If the command is valid it
*  gets the data port and sets up the data connection for it. Then it either sends
*  the directory contents or the contents of a file, after getting the filename
*  and making sure it is valid.
*  Parameters: the control socket for communication, the client sockaddr_in struct
*  that contains the information for ftclient and is passed on to startDataConn()
*  Returns: 0 normally, -1 if an error is encountered, 1 when done early
*/
int handleRequest(int ctrlSocket, struct sockaddr_in client)
{
	int dataSocket;		// socket file descriptor for data connection (client)
	int result;			// to capture and check results of functions
	string cmd;			// the command sent by ftclient
	string port;		// the data port sent by ftclient
	string filename;	// the filename sent by ftclient
	string invalidCmd = "Sorry, the command provided is invalid. Try again.";
	string requestPort = "Data port please";
	string requestFile = "Filename please";
	
	// Read the command sent by ftclient
	result = recvAll(ctrlSocket, cmd);
	if (result == -1) 	// there was an error
	{
		cout << "Error reading on socket." << endl;
		return -1;
	}
	// Validate it to be -l or -g
	// If invalid, send error message and end request process
	if (cmd != "-l" && cmd != "-g")
	{
		cout << "Invalid command. Sending error message" << endl;
		result = sendAll(ctrlSocket, invalidCmd);
		if (result == -1) 	// error
		{
			cout << "Error sending on socket." << endl;
			return -1;
		}
		return 1;
	}
	// If OK, ask for data port
	result = sendAll(ctrlSocket, requestPort);
	if (result == -1) 	// error
	{
		cout << "Error sending on socket." << endl;
		return -1;
	}
	
	// Receive data port and use it to set up a client socket for data connection
	result = recvAll(ctrlSocket, port);
	if (result == -1) 	// there was an error
	{
		cout << "Error reading on socket." << endl;
		return -1;
	}
	result = startDataConn(dataSocket, port, client);
	if (result == -1)	// there was an error
	{
		return -1;
	}
	
	// If command was -l, get the directory and send it, done
	if (cmd == "-l")
	{
		cout << "Sending directory contents" << endl;
		sendDir(dataSocket);
	}
	// Otherwise -g, request filename
	else
	{
		result = sendAll(dataSocket, requestFile);
		if (result == -1) 	// error
		{
			cout << "Error sending on socket." << endl;
			return -1;
		}
		
		// Receive filename from ftclient
		result = recvAll(dataSocket, filename);
		if (result == -1) 	// there was an error
		{
			cout << "Error reading on socket." << endl;
			return -1;
		}
		
		cout << "File \"" << filename << "\" requested" << endl;
		// Then attempt to send it
		sendFile(ctrlSocket, dataSocket, filename);
	}
	
	// Close data connection socket
	close(dataSocket);	
	
	return 0;	//normal, successful end
}

/* Function: sendDir
*  Description: Reads the contents of the current directory and sends them to
*  ftclient over the data connection. 
*  Parameters: the socket for the data connection to send over
*  Returns: 0 normally, -1 if an error is encountered
*/
int sendDir(int dataSocket)
{
    DIR *dp;    //directory pointer
    struct dirent *ep;
	string dirToSend = "";	// directory information to be sent to ftclient
	int result = 0;			// captures results of functions to check for errors
	
	// First get the files in the directory
	// Modified from a function I did in Operating Systems
    // help from http://www.gnu.org/software/libc/manual/html_node/Simple-Directory-Lister.html#Simple-Directory-Lister
    // also http://stackoverflow.com/questions/4204666/how-to-list-files-in-a-directory-in-a-c-program
	dp = opendir(".");
    if (dp == NULL)        //verify it worked
    {
        cout << "Unable to open folder to read files. Exiting." << endl;
        return -1;
    }
    //iterate over the names of the files and add to string to send
    while (ep = readdir(dp))
    {
		dirToSend += ep->d_name;
		dirToSend += "\n";
    }
    //close the directory
    closedir(dp);
	
	// Then send the string to ftclient over data connection
	result = sendAll(dataSocket, dirToSend);
	if (result == -1) 	// error
	{
		cout << "Error sending on socket." << endl;
		return -1;
	}
	
	return 0;	//normal, successful end	
}

/* Function: sendFile
*  Description: Attempt to open the given file and send an error message to 
*  ftclient if it's not found. If it is, read in the file contents and send them
*  to ftclient over the data connection.
*  Parameters: the socket for the control connection to send over, the socket for
*  the data connection to send over, the name of the file to read and send
*  Returns: 0 normally, -1 if an error is encountered, 1 if end early
*/
int sendFile(int ctrlSocket, int dataSocket, string filename)
{
	string contents = "";		// file contents to be sent to ftclient
	int result = 0;			// captures results of functions to check for errors
	string notFound = "File not found.";
	string OKmsg = "OK";
	ifstream ifs;		//the input file stream
	string aLine;		//to hold lines from the input file
	
	// See if the file exists, and if not send error message on ctrlSocket and end
	ifs.open(filename.c_str());
	if (ifs.fail())
	{
		cout << "File not found. Sending error message" << endl;
		result = sendAll(ctrlSocket, notFound);
		if (result == -1) 	// error
		{
			cout << "Error sending on socket." << endl;
			return -1;
		}
		return 1;		// exit function early
	}	
	// If works, send OK message on ctrlSocket
	else 
	{
		result = sendAll(ctrlSocket, OKmsg);
		if (result == -1) 	// error
		{
			cout << "Error sending on socket." << endl;
			return -1;
		}
	}
	
	// Send contents of file on dataSocket, after reading it all in
	while (getline(ifs, aLine))
	{
		contents += aLine;
	}
	cout << "Sending \"" << filename << "\"" << endl;
	result = sendAll(dataSocket, contents);
	if (result == -1) 	// error
	{
		cout << "Error sending on socket." << endl;
		return -1;
	}
	
	return 0;	//normal, successful end
}

/* Function: sendAll
*  Description: Performs the whole message exchange used to ensure complete sending
*  of messages. Follows the established process of exchanging message length, followed
*  by a confirmation message, and then the actual message.
*  Parameters: the socket to send over, the message string to send
*  Returns: 0 normally, -1 if an error is encountered
*/
int sendAll(int sockfd, string msg)
{
	int length = 0;			// to hold the length of the message
	char *strLength = (char *) malloc(sizeof(char)*NUM_SIZE);	// string version of length
	int numSent = 0;		// number of bytes sent, to check for errors
	int numRecvd = 0;		// number of bytes received, to check for errors
	string message;			// the (hopefully confirmation) message received
	
	// Determine the length of the message being sent
	length = msg.length();
	
	// First send the message length so the recipient knows how much to expect
	// need to convert int to string before send, make all 10 chars/ bytes
	sprintf(strLength, "%010d", length);
	numSent = socketSend(sockfd, strLength, NUM_SIZE);
	if (numSent < 0)	//verify worked
	{
		return -1;
	}
	
	// Read the confirmation message, taking turns keeps messages separate
	numRecvd = socketRead(sockfd, message, 8);	
	if (numRecvd < 0)	//verify worked
	{
		return -1;
	}
	if (message != "Got size")
	{
		cout << "Issue with exchange for sending from server to client." << endl;
		return -1;
	}
	
	// Then send the actual message
	numSent = socketSend(sockfd, msg.c_str(), length);
	if (numSent < 0)	//verify worked
	{
		return -1;
	}
	
	// if no errors return 0 to indicate success
	return 0;
}

/* Function: recvAll
*  Description: Performs the whole message exchange used to ensure receiving complete
*  messages. Follows the established process of exchanging message length, followed
*  by a confirmation message, and then the actual message.
*  Parameters: the socket to receive on, a string by reference where the message
*  is stored and "returned"
*  Returns: 0 normally, -1 if an error is encountered
*/
int recvAll(int sockfd, string &message)
{
	int length = 0;			// to hold the length of the message
	string lengthStr;		// string version of length
	int numSent = 0;		// number of bytes sent, to check for errors
	int numRecvd = 0;		// number of bytes received, to check for errors
	string confirmationMsg = "Got size";	// confirmation message to send
	
	// First read the length of the coming message, the number should be 10 bytes
	numRecvd = socketRead(sockfd, lengthStr, NUM_SIZE);
	if (numRecvd < 0)	//verify worked
	{
		return -1;
	}
	length = atoi(lengthStr.c_str());	// turn it into an int
	
	// Then send a confirmation message, taking turns to keep messages separate
	numSent = socketSend(sockfd, confirmationMsg.c_str(), confirmationMsg.length());
	if (numSent < 0)	//verify worked
	{
		return -1;
	}
	
	// Read the actual message from ftclient
	numRecvd = socketRead(sockfd, message, length);
	if (numRecvd < 0)	//verify worked
	{
		return -1;
	}
	
	// if no errors return 0 to indicate success
	return 0;
}

// Function: socketSend
// Description: Used to send an entire piece of data over the socket, with a
// while loop in case send sends less than requested. Keep sending until all
// intended bytes have been sent.
// Parameters: int sockfd the socket to write to, const char *msg the message to be 
// sent, int length the length of that message
// Returns: an int, which is the number of bytes sent, or -1 on failure
int socketSend(int sockfd, const char *msg, int length)
{
	// Modified from a function I wrote for an Operating Systems assignment
	int numSent = 0;	//to capture # bytes written over socket each write
	int totalSent = 0;	//add up all bytes written over socket until hit goal
	
	//need to use loop to ensure whole thing gets sent- may send chunks
	while (totalSent < length)
	{	//make string a pointer and keep track of where to (re)start
		numSent = send(sockfd, &msg[totalSent], length, 0);
		if (numSent < 0)	//verify worked
		{
			return -1;
		}
		totalSent += numSent;
	}
	
	//return number of bytes sent when finishes successfully
	return totalSent;
}

// Function: socketSend
// * 2nd version with non-const message - use both in different calls *
// Description: Used to send an entire piece of data over the socket, with a
// while loop in case send sends less than requested. Keep sending until all
// intended bytes have been sent.
// Parameters: int sockfd the socket to write to, char *msg the message to be 
// sent, int length the length of that message
// Returns: an int, which is the number of bytes sent, or -1 on failure
int socketSend(int sockfd, char *msg, int length)
{	
	// Modified from a function I wrote for an Operating Systems assignment
	int numSent = 0;	//to capture # bytes written over socket each write
	int totalSent = 0;	//add up all bytes written over socket until hit goal
	
	//need to use loop to ensure whole thing gets sent- may send chunks
	while (totalSent < length)
	{	//make string a pointer and keep track of where to (re)start
		numSent = send(sockfd, &msg[totalSent], length, 0);
		if (numSent < 0)	//verify worked
		{
			return -1;
		}
		totalSent += numSent;
	}
	
	//return number of bytes sent when finishes successfully
	return totalSent;
}

// Function: socketRead
// Description: Used to receive an entire piece of data over the socket, with a
// while loop in case recv gets less than requested. Keep recving until all
// intended bytes have been read.
// Parameters: int sockfd the socket to read from, string by reference readMsg where the 
// received message will be built and "returned", int length the expected
// length of that message
// Returns: an int, which is the number of bytes read, or -1 on failure
int socketRead(int sockfd, string &readMsg, int length)
{
	// Modified from a function I wrote for an Operating Systems assignment
	int numRead = 0;	//to capture # bytes read from socket each read
	int totalRead = 0;	//add up all bytes read from socket until hit goal
	char *aBuffer;			//used for reading
	aBuffer = (char *) malloc(sizeof(char)*(length+1));	//+1 for null byte
	bzero(aBuffer, length+1);	//clear out before use
	readMsg = "";				// prepare to concatenate
	
	//loop to ensure receive entire message, to full length- may receive chunks
	while (totalRead < length)
	{
		numRead = recv(sockfd, aBuffer, length, 0);
		if (numRead < 0)	//verify worked
		{
			return -1;
		}
		//concatenate whatever's in buffer to readMsg
		aBuffer[numRead] = '\0';	//make sure it's null-terminated
		readMsg += aBuffer;
		totalRead += numRead;
	}
	
	//clean up memory
	free(aBuffer);
	
	//return number of bytes read when finishes successfully
	return totalRead;
}
