/**********************************************************
* Author: Lisa Percival
* Date Created: 8/23/14
* Last Modification Date: 8/29/14
* File name: RMS.cpp
* Overview: The primary file for my final project, and the application file that
* uses the three robot classes in a Robot Management System. Contains a vector of
* pointers to Robots (which can hold the sub-types Entertainer & Assistant as well).
* Displays a menu of options to the user so that they can create, interact with,
* and destroy robots. The user of pointers to Robots allows the virtual functions
* in the Robot class definitions to use the proper function definition according
* to the true type of each Robot. The key functionality consists of using a robot,
* which calls the appropriate DoMenu() function defined in each class.
* Input: menu selections, instructions for building a new robot, which robot to
* use or destroy, subsequent menu selections and interaction according to the
* pertinent DoMenu() function of a robot
*	*An optional command line argument may be passed, which should be an integer.
*	*The number that is passed will be used as the number of generic Robots to 
*	*initialize the vector with. Otherwise, it will start out empty.
* Output: lots of requests and print statements, possibly creation of a file if
* the correct robot type and task is selected
* *******************************************************/

#include <iostream>
#include <string>
#include <vector>
#include <iterator>
#include <cstdlib>
#include <ctime>
//----------------------------------------------------------------------
//	Requirement #19: Demonstrate a header file
//----------------------------------------------------------------------
#include "robot.h"
#include "entertainer.h"
#include "assistant.h"
//----------------------------------------------------------------------
//	Requirement #18: Demonstrate a custom namespace
//----------------------------------------------------------------------
using PERCIVAL_ROBOTS::Robot;
using PERCIVAL_ROBOTS::Entertainer;
using PERCIVAL_ROBOTS::Assistant;
using std::cout;
using std::cin;
using std::endl;
using std::getline;
using std::string;
using std::vector;


void PrintList(vector<Robot*> bots);
void BuildRobot(vector<Robot*>& bots);
void CallRobot(vector<Robot*> bots);
void RemoveRobot(vector<Robot*>& bots);

int main(int argc, char *argv[])
{
	int numInit = 0;			//stores the number of Robots to initialize, if there was a command line argument
	int choice = -1;			//stores the user's menu selection, starts invalid
	int numOptions = 5;			//the number of items in the options array
	//the list of menu options to display
	string options[] = {"See List of Robots", "Build Robot", "Use Robot", "Remove Robot", "Quit"};
	//----------------------------------------------------------------------
	//	Requirement #12: Demonstrate std::string variable
	//----------------------------------------------------------------------
	string again = "yes";		//indicates whether to keep doing the menu or end
	
	srand(time(0));				//to improve randomness (used in Entertainer::TellJoke())
	
	//----------------------------------------------------------------------
	//	Requirement #01: Demonstrate simple output - cout
	//----------------------------------------------------------------------
	cout << "---------------------------------------" << endl;
	cout << "Welcome to the Robot Management System!" << endl;
	cout << "---------------------------------------" << endl << endl;
	
	//initialize empty vector of pointers to Robots
	//----------------------------------------------------------------------
	//	Requirement #17: Demonstrate a pointer to an object
	//----------------------------------------------------------------------
	vector<Robot*> robots;
	//if there is a command line argument, initialize that many generic Robots
	//----------------------------------------------------------------------
	//	Requirement #03: Demonstrate logical operators - ==
	//----------------------------------------------------------------------
	if (argc == 2)
	{
		//----------------------------------------------------------------------
		//	Requirement #12: Demonstrate C-style string variable
		//----------------------------------------------------------------------
		numInit = atoi(argv[1]);
		//----------------------------------------------------------------------
		//	Requirement #15: Demonstrate command line argument
		//		- print usage statements, initialize robots if there's a valid
		//		integer argument
		//----------------------------------------------------------------------
		if (numInit <= 0)			//if it's bad input, act is if there's no argument
		{
			cout << "Usage: A command line argument can be provided to say how many "
				 << "generic robots to initialize." << endl;
			cout << "You provided an argument, but it was not valid so we will start "
				 << "with an empty list of robots." << endl;
			cout << "The command line argument must be an integer greater than 0." << endl;
		}
		else						//valid input, so initialize that many
		{
			cout << "You provided the command line argument " << numInit 
				 << ", so the list will begin with " << numInit << " generic robots." << endl;
			//make and add that many robots
			for (int i = 0; i < numInit; i++)
			{
				robots.push_back(new Robot());
			}
		}
	}
	else			//no arguments, just use the empty vector
	{
		cout << "Usage: A command line argument can be provided to say how many "
			 << "generic robots to initialize." << endl;
		cout << "It must be an integer greater than 0." << endl;
		cout << "You did not provide a command line argument, so we will start "
			 << "with an empty list of robots." << endl;
	}
		
	//keep showing the menu, getting an option, and doing it until they don't want to anymore
	//----------------------------------------------------------------------
	//	Requirement #04: Demonstrate loops - do-while
	//----------------------------------------------------------------------
	do
	{
		choice = -1;			//re-set for repetitions
		//show the menu and get a choice until it's valid
		//----------------------------------------------------------------------
		//	Requirement #03: Demonstrate logical operators - ||
		//----------------------------------------------------------------------
		while ((choice < 1) || (choice > numOptions))
		{
			cout << endl;
			cout << "In the RMS, you can choose from one of the following tasks!" << endl;
			for (int i = 0; i < numOptions; i++)
			{
				cout << "[" << (i+1) << "] " << options[i] << endl;		//ex: [2] Build Robot
			}
			//----------------------------------------------------------------------
			//	Requirement #07: Demonstrate debugging trick
			//		- the use of numbers instead of strings for menu input makes
			//			validation easier and reduces accidental errors
			//----------------------------------------------------------------------
			GetInt(choice, "Please enter an integer choice (ex: 1): ");
			if ((choice < 1) || (choice > numOptions))
			{
				cout << "Sorry, that's not a valid options. I'll ask you to try again." << endl;
			}
		}
		//do what they selected
		//----------------------------------------------------------------------
		//	Requirement #09: Demonstrate functional decomposition
		//		-each option goes to its own function, to limit the amount of code
		//		complicating this section and make the flow obvious
		//----------------------------------------------------------------------
		switch (choice)
		{
			case 1:
				//----------------------------------------------------------------------
				//	Requirement #10: Demonstrate scope of variables
				//		-Notice that I have to pass the variable robots to each of
				//		the functions below. This is because robots is declared here
				// 		in the main function so that's where its scope is limited
				//		to. It would not be available to the functions if I did 
				//		not pass it.
				//----------------------------------------------------------------------
				PrintList(robots);
				break;
			case 2:
				BuildRobot(robots);
				break;
			case 3:
				CallRobot(robots);
				break;
			case 4:
				RemoveRobot(robots);
				break;
			case 5:
				again = "no";
				break;
			default:
				cout << "Should never get here." << endl;
		}
	//----------------------------------------------------------------------
	//	Requirement #03: Demonstrate logical operators - !
	//----------------------------------------------------------------------		
	} while (again != "no");
	
	cout << endl << "Thanks for using the Robot Management System!" << endl;
	
	//free the dynamic memory in robots, since they were all declared dynamically with new
	for (vector<Robot*>::iterator i = robots.begin(); i != robots.end(); i++)
	{
		delete *i;			
	}

	return 0;
}

/**************************************
* Function: PrintList(vector<Robot*>)
* Description: Shows the name and type of each robot in the current list (the
* vector of the Robot Management System)
* Parameters: a vector of pointers to Robots
* Returns: nothing directly, just prints
**************************************/
//----------------------------------------------------------------------
//	Requirement #08: Demonstrate a function you define
//----------------------------------------------------------------------
void PrintList(vector<Robot*> bots)
{
	//----------------------------------------------------------------------
	//	Requirement #06: Demonstrate understanding of run-time errors
	//		-If I had instead had the code below (leaving out "i != bots.end()"),
	//		the loop would try to continue beyond the end of the vector and cause
	//		a segmentation fault when it runs:
	//			for (vector<Robot*>::iterator i = bots.begin();; i++)
	//----------------------------------------------------------------------
	for (vector<Robot*>::iterator i = bots.begin(); i != bots.end(); i++)
	{
		//i is basically a pointer to a pointer, so dereference and still use ->
		cout << "[" << (distance(bots.begin(), i)+1) << "] " << (*i)->GetName() << ", " << (*i)->GetType() << endl;
	}
	if (bots.size() == 0)
	{
		cout << "The RMS is currently empty." << endl;
	}
}

/**************************************
* Function: BuildRobot(vector<Robot*>&)
* Description: Gives the user options for building a new robot, creates one as
* designated, and adds it to the list (vector of the Robot Management System).
* They can choose any of the 3 types of robots, and to either use default
* construction or provide the parameters for how to build it. Uses functions from
* the Robot class where applicable to capture parameters and ensure they're 
* appropriate.
* Parameters: a vector of pointers to Robots, by reference
* Returns: nothing directly
**************************************/
//----------------------------------------------------------------------
//	Requirement #11: Demonstrate different passing mechanisms - pass-by-reference
//----------------------------------------------------------------------
void BuildRobot(vector<Robot*>& bots)
{
	//----------------------------------------------------------------------
	//	Requirement #17: Demonstrate a pointer to an object
	//----------------------------------------------------------------------
	Robot *newRobot;		//where the new robot is made before being added to the vector
	int choice = -1;		//stores the user's menu selection, starts invalid
	int numOptions = 6;		//the size of the menu
	//the list of options for the menu
	string options[] = {"Generic Robot, Default Constructed", "Generic Robot, Provide Parameters",
						"Entertainer Robot, Default Constructed", "Entertainer Robot, Provide Parameters",
						"Personal Assistant Robot, Default Constructed", "Personal Assistant Robot, Provide Parameters"};
	int theHeight, theWidth, theWheels;		//capture the inputs for height, width, wheels
	string theName, theMaster;				//capture the inputs for name and master's name
	string theJokeFile, theTaskFile;		//capture filenames for jokes or tasks, where applicable
	
	//show the menu options and get a validated choice
	//----------------------------------------------------------------------
	//	Requirement #04: Demonstrate loops - while
	//----------------------------------------------------------------------
	while ((choice < 1) || (choice > numOptions))
	{
		cout << endl;
		cout << "Here are your options for the type of robot you can build:" << endl;
		//----------------------------------------------------------------------
		//	Requirement #04: Demonstrate loops - for
		//----------------------------------------------------------------------
		for (int i = 0; i < numOptions; i++)
		{
			cout << "[" << (i+1) << "] " << options[i] << endl;		//ex: [1] Generic Robot, Default Constructed
		}
		GetInt(choice, "Please enter the type you choose as an integer (ex: 1): ");
		if ((choice < 1) || (choice > numOptions))
		{
			cout << "Sorry, that's not a valid robot type. I'll ask you to try again." << endl;
		}
	}
	
	//ask the shared questions for the non-default ones, so don't have to repeat them
	if ((choice == 2) || (choice ==4) || (choice ==6))
	{
		cout << endl;
		//use the class friend functions to validate the input, because shouldn't need to know requirements here
		theHeight = PERCIVAL_ROBOTS::RequestHeight();
		theWidth = PERCIVAL_ROBOTS::RequestWidth();
		theWheels = PERCIVAL_ROBOTS::RequestWheels();
		//these can be anything, so can ask here and use getline
		cout << "Please enter a name for your new robot: ";
		//----------------------------------------------------------------------
		//	Requirement #01: Demonstrate simple input - getline
		//----------------------------------------------------------------------
		getline(cin, theName);
		cout << "Please enter a name for the master of your new robot (perhaps yours?): ";
		getline(cin, theMaster);
	}
	
	//ask any additional questions, call the appropriate constructors
	switch (choice)
	{
		case 1:			//Generic, Default
			//----------------------------------------------------------------------
			//	Requirement #16: Use a class/object
			//----------------------------------------------------------------------
			newRobot = new Robot();	
			cout << "Robot constructed!" << endl;
			break;
		case 2:			//Generic, Parameters
			newRobot = new Robot(theHeight, theWidth, theWheels, theName, theMaster);
			cout << "Robot constructed!" << endl;
			break;
		case 3:			//Entertainer, Default
			newRobot = new Entertainer();
			cout << "Entertainer constructed!" << endl;
			break;
		case 4:			//Entertainer, Parameters
			//need to also get an input file for jokes to start with
			cout << "The entertainer robot needs a file with some jokes to start with." << endl;
			cout << "Each joke should consist of two parts (an intro and a punchline), "
				 << "and each part should be on a separate line. The next joke just "
				 << "starts on the next line." << endl;
			cout << "Hint: If you don't want to provide your own joke file, you can enter "
				 << "defaultJokeFile.txt to use the default file that was provided." << endl;
			cout << "Enter your joke filename here: ";
			getline(cin, theJokeFile);				
			newRobot = new Entertainer(theHeight, theWidth, theWheels, theName, theMaster, theJokeFile);
			cout << "Entertainer constructed!" << endl;
			break;
		case 5:			//Assistant, Default
			newRobot = new Assistant();
			cout << "Assistant constructed!" << endl;
			break;
		case 6:			//Assistant, Parameters
			//need to also get an input file for tasks to start with
			cout << "The personal assistant robot requests a file with some tasks to start with." << endl;
			cout << "The file should have one task per line, in the format 'name, priority, label'." << endl;
			cout << "Hint: If you would rather start with an empty list, enter 'a' or other junk "
				 << "instead of a valid file." << endl;
			cout << "Enter your task filename here: ";
			getline(cin, theTaskFile);
			newRobot = new Assistant(theHeight, theWidth, theWheels, theName, theMaster, theTaskFile);
			cout << "Assistant constructed!" << endl;
			break;
		default:
			cout << "Should never get here" << endl;
	}
	
	//add the new robot to the vector
	bots.push_back(newRobot);	
}

/**************************************
* Function: CallRobot(vector<Robot*>)
* Description: Lets the user select a robot from the RMS to interact with, then
* calls its DoMenu() function. 
* Parameters: a vector of pointers to Robots
* Returns: nothing directly
**************************************/
//----------------------------------------------------------------------
//	Requirement #11: Demonstrate different passing mechanisms - pass-by-value
//----------------------------------------------------------------------
void CallRobot(vector<Robot*> bots)
{
	int theRobot = -1;			//to store user input for which robot to use, starts invalid
	
	//request and capture the number of the robot to call, validated to be an int
	GetInt(theRobot, "Please enter the number of the robot you would like to use, based on the print list: ");
	//----------------------------------------------------------------------
	//	Requirement #06: Demonstrate understanding of logic errors
	//		-If I had accidentally swapped the < and > signs in the if statement
	//		below, the program would not have behaved as intended. Bad code:
	//			if((theRobot > 1) || (theRobot < bots.size())
	//		In that case, it would have printed the message every time the integer
	//		given for theRobot was valid, as well as some times when it was invalid.
	//----------------------------------------------------------------------
	if ((theRobot < 1) || (theRobot > bots.size()))
	{
		cout << "Sorry, that robot isn't currently in the RMS. Try printing the list to see who is." << endl;
	}
	else
	{
		cout << endl;
		//use the robot in the matching position in the vector
		//----------------------------------------------------------------------
		//	Requirement #24: Demonstrate polymorphism
		//----------------------------------------------------------------------
		bots[theRobot-1]->DoMenu();			//uses correct menu for true class of robot (can last a while)
		//when done, returns to Main Menu
	}

	/* Old Version that requested the name instead of a number:
	string theRobot;			//to store user input for which robot to use
	
	//request and capture the name of the robot to call
	cout << "Please enter the name of the robot you would like to use: ";
	getline(cin, theRobot);
	cout << endl;
	//find the robot in the vector
	for (vector<Robot*>::iterator i = bots.begin(); i != bots.end(); i++)
	{
		//if it's the current one, call DoMenu() and end the function
		if ((*i)->GetName() == theRobot)
		{
			(*i)->DoMenu();			//uses correct menu for true class of robot (can last a while)
			return;					//end early to skip the rest- back to Main Menu
		}
	}
	//if it wasn't found, it couldn't be used
	cout << "Sorry, that robot isn't currently in the RMS. Try printing the list to see who is." << endl;
	*/
}

/**************************************
* Function: RemoveRobot(vector<Robot*>&)
* Description: Lets the user select a robot from the RMS to remove, then
* erases it from the vector.
* Parameters: a vector of pointers to Robots, by reference
* Returns: nothing directly
**************************************/
void RemoveRobot(vector<Robot*>& bots)
{
	int theRobot = -1;			//to store user input for which robot to remove, starts invalid
	
	//request and capture the number of the robot to remove, validated to be an int
	GetInt(theRobot, "Please enter the number of the robot you would like to remove, based on the print list: ");
	if ((theRobot < 1) || (theRobot > bots.size()))
	{
		cout << "Sorry, that robot isn't currently in the RMS. Try printing the list to see who is." << endl;
	}
	else
	{
		//erase the one in the matching position in the vector (position theRobot-1)
		bots.erase(bots.begin()+(theRobot-1));
		cout << "Requested robot erased!" << endl;
	}

	/* Old version requesting a name instead of a number:
	string theRobot;			//to store user input for which robot to remove
	
	//request and capture the name of the robot to remove
	cout << "Please enter the name of the robot you would like to remove: ";
	getline(cin, theRobot);
	//find the robot in the vector
	for (vector<Robot*>::iterator i = bots.begin(); i != bots.end(); i++)
	{
		//if it's the current one, erase it and end the function
		if ((*i)->GetName() == theRobot)
		{
			bots.erase(i);
			cout << "Requested robot erased!" << endl;
			return;					//end early to skip the rest
		}
	}
	//if it wasn't found, it couldn't be removed
	cout << "Sorry, that robot isn't currently in the RMS. Try printing the list to see who is." << endl;
	*/
}