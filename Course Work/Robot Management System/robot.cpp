/**********************************************************
* Author: Lisa Percival
* Date Created: 8/23/14
* Last Modification Date: 8/29/14
* File name: robot.cpp
* Overview: The implementation for the class Robot, which the Entertainer & Assistant
* classes will be derived from. Defines the functions associated with a generic robot, including
* constructors, destructor, overloaded << operator, other public functions, and some
* internal functions. It also defines the helper function GetInt() (but outside
* the namespace), so it can be used in this class, the classes that derive from it,
* and the application file that uses it.
* Input: none
* Output: none
* *******************************************************/

#include <iostream>
#include <string>
#include <vector>
#include <iterator>
#include "robot.h"
using std::cout;
using std::cin;
using std::endl;
using std::ostream;
using std::getline;
using std::vector;

namespace PERCIVAL_ROBOTS
{
	/**************************************
	* Function: Robot::Robot()
	* Description: The default constructor for the Robot class. Sets the robot's
	* features to default values, then runs CreateLooks() and CreateOptions().
	* Parameters: none
	* Returns: nothing directly
	**************************************/
	//----------------------------------------------------------------------
	//	Requirement #08: Demonstrate an overloaded function you define - constructors
	//----------------------------------------------------------------------
	Robot::Robot()
	{
		height = 17;		//make smallest possible robot
		width = 18;
		wheels = 0;			//yes, wheeled
		name = "HAL";
		master = "Dave";
		CreateLooks();		//set looks using height, width, wheels
		CreateOptions();	//build vector of menu options
	}

	/**************************************
	* Function: Robot::Robot(int, int, int, string, string)
	* Description: The parametrized constructor for the Robot class. Sets the robot's
	* features to passed values, then runs CreateLooks() and CreateOptions().
	* Parameters: 3 ints for the height, width, and whether it has wheels, plus
	* 2 strings for its name and its master's name
	* Returns: nothing directly
	**************************************/
	//----------------------------------------------------------------------
	//	Requirement #08: Demonstrate an overloaded function you define - constructors
	//----------------------------------------------------------------------
	Robot::Robot(int h, int w, int wh, string n, string m)
	{
		height = h;		
		width = w;
		wheels = wh;			
		name = n;
		master = m;
		CreateLooks();		//set looks using height, width, wheels
		CreateOptions();	//build vector of menu options
	}
	
	/**************************************
	* Function: Robot::Robot(const Robot&)
	* Description: The copy constructor for the Robot class. Sets the robot's
	* features to those of the argument, then runs CopyLooks() and CopyOptions().
	* Parameters: none
	* Returns: a Robot by const reference
	**************************************/
	//----------------------------------------------------------------------
	//	Requirement #08: Demonstrate an overloaded function you define - constructors
	//----------------------------------------------------------------------
	Robot::Robot(const Robot& r)
	{
		height = r.height;		
		width = r.width;
		wheels = r.wheels;			
		name = r.name;
		master = r.master;
		CopyLooks(r);		//properly copies 2-D array in looks
		CopyOptions(r);	//properly copies vector of menu options
	}

	/**************************************
	* Function: Robot::~Robot()
	* Description: The destructor for the Robot class. Deletes the 2-D array
	* in looks because it was dynamically allocated.
	* Parameters: none
	* Returns: nothing directly
	**************************************/
	Robot::~Robot()
	{
		//----------------------------------------------------------------------
		//	Requirement #14: Demonstrate multi-dimensional array & dynamically allocated array
		//		- combined, deletion here, allocation in CreateLooks()
		//----------------------------------------------------------------------
		for (int i = 0; i < height; i++)
			delete [] looks[i];			//delete each row
		delete [] looks;				//delete overall
	}
	
	/**************************************
	* Function: Robot::CreateLooks()
	* Description: Used by the constructors to dynamically create and fill the
	* 2-D char array stored in looks, which represents the robot's appearance
	* and can be used to show its physical representation. The only part of the
	* robot that will actually change with height and width (besides appropriate
	* spacing) is the size of its torso portion. The bottom row will vary
	* depending on whether or not the robot has wheels.
	* Parameters: none
	* Returns: nothing directly
	**************************************/
	void Robot::CreateLooks()
	{
		string line = "";			//used to build each line and insert it into a row
		int div3 = width/3;			//used to determine # of spaces in some places
		int div6 = width/6;			//used to determine # of characters in some places
		int div2 = width/2;			//used to determine # of characters sometimes
		int div9 = width/9;			//used to determine # of characters
		
		//----------------------------------------------------------------------
		//	Requirement #14: Demonstrate multi-dimensional array & dynamically allocated array
		//		- combined, allocation and initialization here, deletion in destructor
		//----------------------------------------------------------------------
		//create the 2-D char array
		looks = new char*[height];		//array of pointers to char arrays
		for (int i = 0; i < height; i++)
		{
			looks[i] = new char[width];	//each row is a char array
		}
		//first set to all spaces to avoid any junk in elements that don't get set below
		for (int i = 0; i < height; i++)
		{
			for (int j = 0; j < width; j++)
			{
				looks[i][j] = ' ';
			}
		}
		
		//fill the char array according to the specifications, starting with the head
		//build line 1
		line.insert(line.end(), div3, ' ');		//http://www.cplusplus.com/reference/string/string/insert/
		line += "()   ()";    
		//put line 1 in first row of looks
		for (int i = 0; i < line.length(); i++)
		{
			looks[0][i] = line.at(i);
		}
		//----------------------------------------------------------------------
		//	Requirement #10: Demonstrate scope of variables
		//		-Notice how I re-use the variable i in a whole series of for loops.
		//		This does not cause problems or interference because they are all
		//		actually different variables, since their scope is limited to 
		//		the for loop in which they are declared. Clearly they can be
		//		accessed inside the loop, but they could not be accessed here.
		//----------------------------------------------------------------------
		//line 2
		line = "";
		line.insert(line.end(), div3, ' ');
		line += "__\\ /__";					//extra backslash for escape char
		for (int i = 0; i < line.length(); i++)
		{
			looks[1][i] = line.at(i);
		}
		//line 3
		line = "";
		line.insert(line.end(), (div3-1), ' ');
		line += "/       \\";						//extra backslash because escape character
		for (int i = 0; i < line.length(); i++)
		{
			looks[2][i] = line.at(i);
		}
		//line 4
		line = "";
		line.insert(line.end(), (div3-2), ' ');
		line += "|/ \\  / \\|";				//extra backslashes for escape char
		for (int i = 0; i < line.length(); i++)
		{
			looks[3][i] = line.at(i);
		}
		//line 5
		line = "";
		line.insert(line.end(), (div3-2), ' ');
		line += "|| H||H ||";
		for (int i = 0; i < line.length(); i++)
		{
			looks[4][i] = line.at(i);
		}
		//line 6
		line = "";
		line.insert(line.end(), (div3-2), ' ');
		line += "|\\_/  \\_/|";				//extra backslashes for escape char
		for (int i = 0; i < line.length(); i++)
		{
			looks[5][i] = line.at(i);
		}
		//line 7
		line = "";
		line.insert(line.end(), (div3-2), ' ');
		line += "| \\____/ |";				//extra backslash for escape char
		for (int i = 0; i < line.length(); i++)
		{
			looks[6][i] = line.at(i);
		}
		//line 8- bottom of head
		line = "";
		line.insert(line.end(), (div3-1), ' ');
		line += "\\_______/";				//extra backslash for escape char
		for (int i = 0; i < line.length(); i++)
		{
			looks[7][i] = line.at(i);
		}
		//line 9- neck and start of body
		line = "";
		line.insert(line.end(), div6, ' ');
		line.insert(line.end(), (div6+1), '_');
		line += "|__|";
		line.insert(line.end(), (div6+1), '_');
		for (int i = 0; i < line.length(); i++)
		{
			looks[8][i] = line.at(i);
		}
		//line 10- body and start of arms
		line = " / |";
		line.insert(line.end(), (div2+1), ' ');
		line += "| \\";				//extra backslash because escape character
		for (int i = 0; i < line.length(); i++)
		{
			looks[9][i] = line.at(i);
		}
		//line 11
		line = " | |";
		line.insert(line.end(), (div6+1), ' ');
		if (width > 21)			//add extra spaces to make things work
		{
			line.insert(line.end(), (div9), ' ');
		}
		else if (width > 19)
		{
			line += " ";
		}
		//line += "O O O | |";
		line += "O O O";
		line.insert(line.end(), (div9-1), ' ');
		line += "| |";
		for (int i = 0; i < line.length(); i++)
		{
			looks[10][i] = line.at(i);
		}
		//line 12
		line = " / |";
		line.insert(line.end(), (div6+1), ' ');
		if (width > 21)			//add extra spaces to make things work
		{
			line.insert(line.end(), (div9), ' ');
		}
		else if (width > 19)
		{
			line += " ";
		}
		//line += "O O O | \\";				
		line += "O O O";
		line.insert(line.end(), (div9-1), ' ');
		line += "| \\";				//extra backslash because escape char
		for (int i = 0; i < line.length(); i++)
		{
			looks[11][i] = line.at(i);
		}
		//line 13
		line = "| /|";
		line.insert(line.end(), (div2+1), ' ');
		line += "|\\ |";					//extra backslash for escape char
		for (int i = 0; i < line.length(); i++)
		{
			looks[12][i] = line.at(i);
		}
		//line 14 to height-4 - depends on height
		if (height == 17)         //smallest robot arms end at bottom, body done
		{
			line = " \\||";   				//extra backslash for escape char
			line.insert(line.end(), (div2+1), '_');
			line += "||/";
			for (int i = 0; i < line.length(); i++)
			{
					looks[13][i] = line.at(i);
			}
		}
		else    //other robots end arms and continue body depending on height
		{
			//line 14- end arms
			line = " \\||";   				//extra backslash for escape char
			line.insert(line.end(), (div2+1), ' ');
			line += "||/";
			for (int i = 0; i < line.length(); i++)
			{
				looks[13][i] = line.at(i);
			}
			//make empty additional body, except bottom, up to line height-5
			line = "   |";
			line.insert(line.end(), (div2+1), ' ');
			line += "|";
			for (int j = 14; j <= (height-5); j++)
			{
				for (int i = 0; i < line.length(); i++)
				{
					looks[j][i] = line.at(i);
				}
			}
			//add bottom, leaving room for legs so at height-4
			line = "   |";
			line.insert(line.end(), (div2+1), '_');
			line += "|";
			for (int i = 0; i < line.length(); i++)
			{
				looks[height-4][i] = line.at(i);
			}
		}
		//legs, starting at line height-3
		line = "";
		line.insert(line.end(), (div6+1), ' ');
		line += "|__|";
		line.insert(line.end(), div9, ' ');
		line += "|__|";
		for (int i = 0; i < line.length(); i++)
		{
			looks[height-3][i] = line.at(i);
		}
		//line height-2
		line = "";
		line.insert(line.end(), div6, ' ');
		line += "/ __ \\";				//extra backslash for escape char
		line.insert(line.end(), (div9-2), ' ');
		line += "/ __ \\";				//extra backslash for escape char
		for (int i = 0; i < line.length(); i++)
		{
			looks[height-2][i] = line.at(i);
		}
		//last line, at height-1 - depends on whether or not has wheels
		if (wheels == 0)        //draw wheels
		{
			line = "";
			line.insert(line.end(), div9, ' ');
			line += "OOOOOO";
			line += line;		//same sequence of <div9 spaces> then OOOOOO repeats
			for (int i = 0; i < line.length(); i++)
			{
					looks[height-1][i] = line.at(i);
			}
		}
		else        //no wheels
		{
			line = "";
			line.insert(line.end(), div9, ' ');
			line += "|-----|";
			line.insert(line.end(), (div9-2), ' ');
			line += "|-----|";
			for (int i = 0; i < line.length(); i++)
			{
				looks[height-1][i] = line.at(i);
			}
		}

	}

	/**************************************
	* Function: Robot::CopyLooks(cont Robot&)
	* Description: Used by the copy constructor to properly copy the 2-D char
	* array looks from the Robot that is passed to the one being constructed.
	* Dynamically creates the new 2-D array (using height and width, which were
	* already copied from the argument in the copy constructor) and assigns
	* each value of the argument's array to the corresponding element of the 
	* new Robot's array.
	* Parameters: a Robot by const reference to copy from
	* Returns: nothing directly
	**************************************/
	void Robot::CopyLooks(const Robot& r)
	{
		//make the dynamic 2-D char array for looks
		looks = new char*[height];		//array of pointers to char arrays
		for (int i = 0; i < height; i++)
		{
			looks[i] = new char[width];	//each row is a char array
		}
		
		//fill the new looks with the values from r's looks
		for (int i = 0; i < height; i++)
		{
			for (int j = 0; j < width; j++)
			{
				looks[i][j] = r.looks[i][j];
			}
		}
	}
	
	/**************************************
	* Function: Robot::CreateOptions()
	* Description: Used by the constructors to fill the vector of menu options
	* that will be displayed when a robot is used.
	* Parameters: none
	* Returns: nothing directly
	**************************************/
	void Robot::CreateOptions()
	{
		menuOptions.push_back("Change Name");
		menuOptions.push_back("Change Master's Name");
		menuOptions.push_back("Show Name");
		menuOptions.push_back("Show Master's Name");
		menuOptions.push_back("Return to Main Menu");
	}
	
	/**************************************
	* Function: Robot::CopyOptions(const Robot&)
	* Description: Used by the copy constructor to properly copy the vector of
	* menu options from the Robot that is passed to the one being constructed.
	* Parameters: a Robot by const reference to copy from
	* Returns: nothing directly
	**************************************/
	void Robot::CopyOptions(const Robot& r)
	{
		//----------------------------------------------------------------------
		//	Requirement #06: Demonstrate understanding of syntax errors
		//		-original code for the for loop was as below:
		//			for (vector<string>::iterator i = r.menuOptions.begin();.....
		//		I got a compiler error because the language syntax requires the
		//		use of a const iterator for iterating over a vector that was 
		//		passed with a const argument
		//----------------------------------------------------------------------
		//at first I had a regular iterator, and was getting a compiler error, but
		//https://www.daniweb.com/software-development/cpp/threads/125172/vector-iterator helped
		for (vector<string>::const_iterator i = r.menuOptions.begin(); i != r.menuOptions.end(); i++)
		{
			menuOptions.push_back(*i);
		}
	}
	
	/**************************************
	* Function: Robot::DoMenu()
	* Description: Introduces the robot and then displays a list of possible tasks
	* for the user to choose from. Validates the chosen task and then performs
	* the requested actions. Continues until the user chooses to return to the Main Menu.
	* Used to work with a robot when they are selected in the Robot Management System.
	* Declared as virtual so different versions can be used for the derived classes.
	* Parameters: none
	* Returns: nothing directly
	**************************************/
	void Robot::DoMenu()
	{
		int choice = -1;		//the user's selection from the menu, starts invalid
		string again = "yes";		//whether or not to continue using the menu or exit
		
		//have the robot introduce itself, by printing it
		cout << *this;
		
		//keep using the menu until the user says they want to stop
		do
		{
			choice = -1;		//re-set for repetitions
			//keep displaying the menu and asking for a choice until it's valid
			while ((choice < 1) || (choice > menuOptions.size()))
			{
				cout << endl;
				//show options
				for (vector<string>::iterator i = menuOptions.begin(); i != menuOptions.end(); i++)
				{
					cout << "[" << (distance(menuOptions.begin(), i)+1) << "] "
						 << *i << endl;			//ex: [1] Change Name
				}
				GetInt(choice, "Please enter your selection as an integer (ex: 1): ");
				if ((choice < 1) || (choice > menuOptions.size()))
				{
					cout << "I'm sorry " << master << ", I'm afraid I can't do that. Not a valid "
						 << "selection so I will ask you to try again." << endl;
				}
			}
			
			//do what they asked for
			switch (choice)
			{
				case 1:
					ChangeName();
					break;
				case 2:
					ChangeMaster();
					break;
				case 3:
					cout << "My name is " << name << endl;
					break;
				case 4:
					cout << "My master's name is " << master << endl;
					break;
				case 5:
					again = "no";
					break;
				default:
					cout << "Should never get here" << endl;
			}
		} while(again != "no");
	
	}
	
	/**************************************
	* Function: Robot::ChangeName()
	* Description: Asks the user for a new name and assigns it to the robot. Names
	* can consist of any characters. Called by DoMenu().
	* Parameters: none
	* Returns: nothing directly
	**************************************/
	void Robot::ChangeName()
	{
		string newName;				//to capture the name provided by the user
		
		cout << "What would you like to change my name to? Please enter here: ";
		//capture using getline because it can be anything
		getline(cin, newName);
		
		//assign to robot's name
		name = newName;
	}
	
	/**************************************
	* Function: Robot::ChangeMaster()
	* Description: Asks the user for a new master and assigns it to the robot. Masters
	* can consist of any characters. Called by DoMenu().
	* Parameters: none
	* Returns: nothing directly
	**************************************/
	void Robot::ChangeMaster()
	{
		string newMaster;				//to capture the master provided by the user
		
		cout << "What would you like to change my master's name to? Please enter here: ";
		//capture using getline because it can be anything
		getline(cin, newMaster);
		
		//assign to robot's master variable
		master = newMaster;
	}
	
	/**************************************
	* Function: Robot::GetName()
	* Description: Returns the name of a robot. Used external to this class and its
	* derived ones (in the Robot Management System) to show the names of robots.
	* Parameters: none
	* Returns: the name of the robot
	**************************************/
	string Robot::GetName()
	{
		return name;
	}
	
	/**************************************
	* Function: Robot::GetType()
	* Description: Returns the type (class) of a robot. Used external to this class and its
	* derived ones (in the Robot Management System) to show the types of robots.
	* Declared virtual so it will use the version of the appropriate class when 
	* called on a Robot pointer
	* Parameters: none
	* Returns: the type/ a description of the robot, depending on what class
	**************************************/
	string Robot::GetType()
	{
		return "generic robot";
	}
	
	/**************************************
	* Function: RequestHeight()
	* Description: Asks the user for a height for a robot and validates that the
	* input is valid and meets parameters. Used outside of the class and its derived
	* ones (in the Robot Management System) to get a height from the user
	* without the external program needing to know the details of the requirements
	* for a valid height. (friend function because doesn't have a calling object)
	* Parameters: none
	* Returns: the validated height entered by the user for the robot
	**************************************/
	int RequestHeight()
	{
		int theHeight = -1;				//to store the height provided by the user
		
		//height must be integer between 17 and 22 (to work with looks)
		while ((theHeight < 17) || (theHeight > 22))
		{
			GetInt(theHeight, "Please enter an integer height between 17 and 22: ");
			if ((theHeight < 17) || (theHeight > 22))
			{
				cout << "That number is outside the valid range 17-22. I will ask you to try again." << endl;
			}
		}
		
		//return once validated
		return theHeight;		
	}
	
	/**************************************
	* Function: RequestWidth()
	* Description: Asks the user for a width for a robot and validates that the
	* input is valid and meets parameters. Used outside of the class and its derived
	* ones (in the Robot Management System) to get a width from the user
	* without the external program needing to know the details of the requirements
	* for a valid width. (friend function because doesn't have a calling object)
	* Parameters: none
	* Returns: the validated width entered by the user for the robot
	**************************************/
	int RequestWidth()
	{
		int theWidth = -1;				//to store the width provided by the user
		
		//width must be integer between 18 and 28 (to work with looks)
		while ((theWidth < 18) || (theWidth > 28))
		{
			GetInt(theWidth, "Please enter an integer width between 18 and 28: ");
			if ((theWidth < 18) || (theWidth > 28))
			{
				cout << "That number is outside the valid range 18-28. I will ask you to try again." << endl;
			}
		}
		
		//return once validated
		return theWidth;		
	}
	
	/**************************************
	* Function: RequestWheels()
	* Description: Asks the user for wheels value for a robot and validates that the
	* input is valid and meets parameters. Used outside of this class and its derived
	* ones (in the Robot Management System) to get a wheels value from the user
	* without the external program needing to know the details of the requirements
	* for a valid wheels value. 
	* Parameters: none
	* Returns: the validated wheels value entered by the user for the robot
	**************************************/
	int RequestWheels()
	{
		int theWheels = -1;				//to store the wheels value provided by the user
		
		//the value for wheels must be either 0 (yes) or 1 (no)
		//----------------------------------------------------------------------
		//	Requirement #03: Demonstrate logical operators - &&
		//----------------------------------------------------------------------
		while ((theWheels != 0) && (theWheels != 1))
		{
			GetInt(theWheels, "Please enter either 0(yes) or 1(no) for wheels: ");
			if ((theWheels != 0) && (theWheels != 1))
			{
				cout << "That number is neither 0 nor 1. I will ask you to try again." << endl;
			}
		}
		
		//return once validated
		return theWheels;		
	}
	
	/**************************************
	* Function: operator <<(ostream&, const Robot&)
	* Description: Prints data about a robot (friend of the Robot class). Used
	* as an introduction in DoMenu(). Includes name, master, looks (physical 
	* representation), height, and width.
	* Parameters: an ostream by reference, a Robot to print by const reference
	* Returns: the ostream that was passed, by reference
	**************************************/
	//----------------------------------------------------------------------
	//	Requirement #22: Demonstrate an overloaded operator
	//----------------------------------------------------------------------
	ostream& operator <<(ostream& outs, const Robot& r)
	{
		outs << "Hi! My name is " << r.name << ", and my master's name is " << r.master << "." << endl;
		//print physical representation from looks
		for (int i = 0; i < r.height; i++)
		{
			for (int j = 0; j < r.width; j++)
			{
				outs << r.looks[i][j];
			}
			outs << endl;			//break for end of row
		}
		outs << endl;
		//----------------------------------------------------------------------
		//	Requirement #07: Demonstrate debugging trick
		//		- print statements show the values of variables and can help
		//			troubleshoot issues like when the robots look incorrect
		//----------------------------------------------------------------------
		outs << "I am " << r.height << " tall and " << r.width << " wide." << endl;
		outs << "What can I do for you today?" << endl;
		
		return outs;
	}
	

}	//end of PERCIVAL_ROBOTS

/**************************************
* Function: GetInt (int&, string)
* Description: Captures user input and verifies that it is an int using cin.fail() & cin.get()
* Parameters: an int by reference to store int in, a string message to request input from the user
* Returns: nothing directly
**************************************/
//----------------------------------------------------------------------
//	Requirement #07: Demonstrate debugging trick
//			- input validation to ensure that integers are really integers,
//				ensures input is appropriate and won't cause errors
//----------------------------------------------------------------------
void GetInt(int& in, string msg)
{
	cout << msg;
	//----------------------------------------------------------------------
	//	Requirement #01: Demonstrate simple input - cin
	//----------------------------------------------------------------------
	cin >> in;
	//keep requesting if cin failed (not an int) or there's leftovers (cin.get() != '\n')
	while(cin.fail() || cin.get() != '\n')
	{
		cout << "Sorry, that's not an int! Please try again: ";
		cin.clear();				//clear failbit, if set
		cin.ignore(1000, '\n');		//remove extra stuff
		cin >> in;
	}
}