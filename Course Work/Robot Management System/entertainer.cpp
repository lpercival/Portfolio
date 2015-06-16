/**********************************************************
* Author: Lisa Percival
* Date Created: 8/24/14
* Last Modification Date: 8/29/14
* File name: entertainer.cpp
* Overview: The implementation for the class Entertainer, which is derived from
* Robot. Defines the functions associated with an entertainer robot, including
* constructors, destructor, other public functions, and some private
* functions. Note that some of the functions are virtual.
* One class-external function is DoBitWise(), because it's called from within the
* class function DoMenu(), but does not need a calling object.
* Input: none
* Output: none
* *******************************************************/

#include <iostream>
#include <fstream>
#include <string>
#include <vector>
#include <iterator>
#include <cstdlib>
#include "entertainer.h"
using std::cout;
using std::cin;
using std::endl;
using std::ifstream;
using std::getline;
using std::vector;

namespace PERCIVAL_ROBOTS
{
	/**************************************
	* Function: Entertainer::Entertainer()
	* Description: The default constructor for the Entertainer class. Calls the
	* Robot constructor to set the shared variables, then uses AddOptions() to
	* adjust menuOptions to include the new Entertainer capabilities. Also tries
	* to run ReadJokes() to fill the jokes vector from an input file, and catches
	* a string exception that is thrown if the file can't be opened.
	* NOTE: The default jokes file is called defaultJokeFile.txt, and should be included
	* with the package of program files.
	* Parameters: none
	* Returns: nothing directly
	**************************************/
	Entertainer::Entertainer() : Robot()
	{
		AddOptions();
		//----------------------------------------------------------------------
		//	Requirement #25: Demonstrate exceptions
		//----------------------------------------------------------------------
		try
		{
			ReadJokes("defaultJokeFile.txt");
		}
		catch (string e)		//if the file open fails
		{
			cout << "Exception: " << e << endl;
		}
	}
	
	/**************************************
	* Function: Entertainer::Entertainer(int, int, int, string, string, string)
	* Description: The parametrized constructor for the Entertainer class. Calls the
	* Robot constructor to set the shared variables, then uses AddOptions() to
	* adjust menuOptions to include the new Entertainer capabilities. Also tries
	* to run ReadJokes() to fill the jokes vector from the given input file, and catches
	* a string exception that is thrown if the file can't be opened.
	* Parameters: 3 ints for the height, width, and wheels value, 3 strings for
	* the name, master, and joke file to use
	* Returns: nothing directly
	**************************************/
	Entertainer::Entertainer(int h, int w, int wh, string n, string m, string jF)
				: Robot(h, w, wh, n, m)
	{
		AddOptions();
		//----------------------------------------------------------------------
		//	Requirement #25: Demonstrate exceptions
		//----------------------------------------------------------------------
		try
		{
			ReadJokes(jF);
		}
		catch (string e)		//if the file open fails
		{
			cout << "Exception: " << e << endl;
		}
	}
	
	/**************************************
	* Function: Entertainer::Entertainer(const Entertainer&)
	* Description: The copy constructor for the Entertainer class. Calls the
	* Robot constructor to set the shared variables, then uses CopyJokes() to
	* properly copy the vector of jokes from the passed Entertainer to the one
	* being constructed.
	* Parameters: an Entertainer by const reference
	* Returns: nothing directly
	**************************************/
	Entertainer::Entertainer(const Entertainer& e) : Robot(e)
	{
		CopyJokes(e);
	}
	
	/**************************************
	* Function: Entertainer::~Entertainer()
	* Description: The destructor for the Entertainer class. Deletes all of the
	* Jokes in jokes because they were dynamically allocated.
	* Also calls the Robot destructor automatically at the end.
	* Parameters: none
	* Returns: nothing directly
	**************************************/
	Entertainer::~Entertainer()
	{
		for (vector<Joke*>::iterator i = jokes.begin(); i != jokes.end(); i++)
		{
			delete *i;
		}
	}
	
	/**************************************
	* Function: Entertainer::AddOptions()
	* Description: Adjusts the menuOptions vector variable, which is inherited from
	* the Robot class. Called by the constructors after the Robot constructor is
	* used to put the shared options in menuOptions. First removes the "Return to 
	* Main Menu" option that is at the end of the vector after the Robot construction,
	* so it can be moved to appear at the end of the Entertainer list. Then adds the
	* additional things an Entertainer can do that a Robot can't.
	* Makes it so DoMenu() shows an appropriate menu.
	* Parameters: none
	* Returns: nothing directly
	**************************************/
	void Entertainer::AddOptions()
	{
		//remove "Return to Main Menu" from the last position so it can be moved down
		menuOptions.pop_back();			//http://www.cplusplus.com/reference/vector/vector/pop_back/
		//add the new things
		menuOptions.push_back("Tell Joke");
		menuOptions.push_back("Learn Joke");
		menuOptions.push_back("Do Neat Bitwise Trick");
		//add back "Return to Main Menu" at the new end
		menuOptions.push_back("Return to Main Menu");
	}
	
	/**************************************
	* Function: Entertainer::ReadJokes()
	* Description: Reads in a starting set of jokes from the file provided. Called
	* by the constructors to initialize the jokes vector variable. The file must have
	* one joke every two lines, with the two parts of the joke each on their own line
	* (like line1: intro1, line2: punchline1, line3: intro2, line4: punchline2...).
	* If the attempt to open the file fails, it throws a string exception with a message
	* that is caught and printed in the constructor. However, it is not a major error,
	* the Entertainer will simply start out jokeless (with an empty joke vector).
	* Parameters: a string with the name of the input file to read
	* Returns: nothing, but it can throw a string if the file open fails
	**************************************/
	void Entertainer::ReadJokes(string file) throw (string)
	{
		//----------------------------------------------------------------------
		//	Requirement #17: Demonstrate a pointer to a struct
		//----------------------------------------------------------------------
		Joke *newJoke;		//holds the data for each new joke so it can be built & added to vector
		string input;			//used to capture a line from the file
		ifstream ifs;			//the input file stream, which will be tied to the file
		string msg;				//what the exception throws
		
		//try to open the file
		//----------------------------------------------------------------------
		//	Requirement #02: Demonstrate explicit type casting
		//----------------------------------------------------------------------
		ifs.open(file.c_str());
		//throw exception if fails
		if (ifs.fail())
		{
			msg = "Opening the joke file failed. The Entertainer will be jokeless.";
			//----------------------------------------------------------------------
			//	Requirement #25: Demonstrate exceptions
			//----------------------------------------------------------------------
			throw msg;			//end early, skip the rest, goes back to constructor
		}
		
		//if it opened fine, pull in each set of two lines to be the 2 parts of a new joke
		while (getline(ifs, input))			//each things on a new line, goes to end
		{
			//make an actually new joke
			newJoke = new Joke;
			//set the first line captured to be the new joke's intro1
			newJoke->intro = input;
			//capture the following line and assign it to the new joke's punchline
			if (getline(ifs, input))
			{
				newJoke->punchline = input;
			}
			else	//for some reason there's no matching punchline (odd # lines in file)
			{
				newJoke->punchline = "Joke over!";		//set to a default value
			}
			//add the new joke to the list
			jokes.push_back(newJoke);
		}
		
		//close the file
		ifs.close();
	}
	
	/**************************************
	* Function: Entertainer::CopyJokes(const Entertainer&)
	* Description: Used by the copy constructor to properly copy the vector of jokes
	* from the Entertainer that is passed to the one being constructed.
	* Parameters: an Entertainer by const reference
	* Returns: nothing directly
	**************************************/
	void Entertainer::CopyJokes(const Entertainer& e)
	{
		//use a const_iterator because the argument is passed as a const
		for (vector<Joke*>::const_iterator i = e.jokes.begin(); i != e.jokes.end(); i++)
		{
			jokes.push_back(*i);		//add every joke from e to the new one
		}
	}
	
	/**************************************
	* Function: Entertainer::DoMenu()
	* Description: Introduces the entertainer robot and then displays a list of possible tasks
	* for the user to choose from. Validates the chosen task and then performs
	* the requested actions. Continues until the user chooses to return to the Main Menu.
	* Used to work with a robot when they are selected in the Robot Management System.
	* Declared as virtual so different versions can be used for the derived classes.
	* Parameters: none
	* Returns: nothing directly
	**************************************/
	void Entertainer::DoMenu()
	{
		int choice = -1;		//the user's selection from the menu, starts invalid
		string again = "yes";		//whether or not to continue using the menu or exit
		
		//have the entertainer robot introduce itself, by printing it (uses the inherited Robot class <<)
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
					ChangeName();				//uses the inherited Robot function
					break;
				case 2:
					ChangeMaster();				//uses the inherited Robot function
					break;
				case 3:
					//----------------------------------------------------------------------
					//	Requirement #24: Demonstrate inheritance
					//			-can access Robot's protected member variables
					//----------------------------------------------------------------------
					cout << "My name is " << name << endl;
					break;
				case 4:
					cout << "My master's name is " << master << endl;
					break;
				case 5:
					TellJoke();
					break;
				case 6:
					AddJoke();
					break;
				case 7:
					DoBitwise();
					break;
				case 8:
					again = "no";
					break;
				default:
					cout << "Should never get here" << endl;
			}
		} while(again != "no");
	}
	
	/**************************************
	* Function: Entertainer::TellJoke()
	* Description: Randomly selects an item in the jokes list, then tells that
	* joke using its intro and punchline.
	* Parameters: none
	* Returns: nothing directly, just prints
	**************************************/
	void Entertainer::TellJoke()
	{
		int index;				//to store the random number for which joke to tell
		
		//if jokes is empty can't tell a joke, so say so
		if (jokes.size() == 0)
		{
			cout << "Sorry, I don't have any jokes to tell right now. Maybe teach me one?" << endl;
		}
		//otherwise, generate a random number and tell that joke
		else
		{
			//----------------------------------------------------------------------
			//	Requirement #05: Demonstrate random number
			//----------------------------------------------------------------------
			index = rand() % jokes.size();		//gives # 0 to (# of jokes - 1)
			cout << endl << "Ready?" << endl << endl;
			cout << jokes[index]->intro << endl;
			//one limitation of robots is their comedic timing, but hopefully this helps
			cout << "......................................................" << endl;
			cout << jokes[index]->punchline << endl;
		}
	}
	
	/**************************************
	* Function: Entertainer::AddJoke()
	* Description: Lets the user teach the entertainer robot a new joke by adding
	* it to the jokes list. Gets both parts, adds them to a new Joke pointer, then
	* adds that to the vector.
	* Parameters: none
	* Returns: nothing directly
	**************************************/
	void Entertainer::AddJoke()
	{
		Joke *newJoke = new Joke;				//holds the elements of the new joke, gets added to vector
		
		//both parts of the joke can contain any characters, so no input validation just getline
		cout << "Please enter the intro to the joke for me to learn: ";
		getline(cin, newJoke->intro);
		cout << "Please enter the punchline of the joke for me to learn: ";
		getline(cin, newJoke->punchline);
		
		//add to the vector
		jokes.push_back(newJoke);
	}
	
	/**************************************
	* Function: Entertainer::GetType()
	* Description: Returns the type (class) of a robot. Used external to the classes
	* (in the Robot Management System) to show the types of robots.
	* Declared virtual so it will use the version of the appropriate class when 
	* called on a Robot pointer
	* Parameters: none
	* Returns: the type/ a description of the robot, depending on what class
	**************************************/
	string Entertainer::GetType()
	{
		return "entertainer robot";
	}
	
	/**************************************
	* Function: DoBitwise()
	* Description: Does the neat bitwise binary trick example described in the Final
	* Project assignment document. Sets x and y to 10 and 25, respectively. Then
	* sets x to the bitwise xor of x and y, then y to the bitwise xor of x and y, and
	* then x to the bitwise xor of x and y. In the end, x and y basically trade values.
	* Parameters: none
	* Returns: nothing, just prints
	**************************************/
	void DoBitwise ()
	{
		int x = 10;			//one variable to play with bitwise or on
		int y = 25;			//second variable to play with bitwise or on
		
		//do a series of bitwise exclusive or operations and print each result
		//----------------------------------------------------------------------
		//	Requirement #03: Demonstrate bitwise operators - ^
		//----------------------------------------------------------------------
		x = x ^ y;
		cout << "After the first x ^ y, x is " << x << endl;
		y = x ^ y;
		cout << "Then x ^ y is assigned to y, and it becomes " << y << endl;
		x = x ^ y;
		cout << "When x is once again set to x ^ y, it is " << x << endl;
	}	
}	//end of PERCIVAL_ROBOTS