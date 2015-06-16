/**********************************************************
* Author: Lisa Percival
* Date Created: 8/24/14
* Last Modification Date: 8/27/14
* File name: assistant.cpp
* Overview: The implementation for the class Assistant, which is derived from
* Robot. Defines the functions associated with a personal assistant robot, including
* constructors, destructor, other public functions, and some private
* functions. Note that some of the functions are virtual.
* Two class-external functions are DoGCF() and GCF(), because DoGCF() is called from within the
* class function DoMenu(), but does not need a calling object. And GCF() is called
* by DoGCF(), also without the need for a calling object. (still in namespace though)
* Input: none
* Output: none
* *******************************************************/

#include <iostream>
#include <fstream>
#include <sstream>
#include <string>
#include <vector>
#include <iterator>
#include <cstdlib>
#include "assistant.h"
using std::cout;
using std::cin;
using std::endl;
using std::ifstream;
using std::ofstream;
using std::stringstream;
using std::getline;
using std::vector;

namespace PERCIVAL_ROBOTS
{
	/**************************************
	* Function: Assistant::Assistant()
	* Description: The default constructor for the Assistant class. Calls the
	* Robot constructor to set the shared variables, then uses AddOptions() to
	* adjust menuOptions to include the new Assistant capabilities. Doesn't do
	* anything with the task list so it starts out empty by default.
	* Parameters: none
	* Returns: nothing directly
	**************************************/
	//----------------------------------------------------------------------
	//	Requirement #21: Demonstrate a default constructor
	//----------------------------------------------------------------------
	Assistant::Assistant() : Robot()
	{
		AddOptions();
	}
	
	/**************************************
	* Function: Assistant::Assistant(int, int, int, string, string, string)
	* Description: The parametrized constructor for the Assistant class. Calls the
	* Robot constructor to set the shared variables, then uses AddOptions() to
	* adjust menuOptions to include the new Assistant capabilities. Also runs ReadTasks()
	* with the input file provided to set up an initial task list.
	* Parameters: 3 ints for the height, width, and wheels, and 3 strings for the
	* name, master, and task file
	* Returns: nothing directly
	**************************************/
	Assistant::Assistant(int h, int w, int wh, string n, string m, string tF)
				: Robot(h, w, wh, n, m)
	{
		AddOptions();
		ReadTasks(tF);
	}
	
	/**************************************
	* Function: Assistant::Assistant(const Assistant&)
	* Description: The copy constructor for the Assistant class. Calls the
	* Robot constructor to set the shared variables, then uses CopyTasks() to 
	* properly copy the vector of Tasks from the Assistant that is passed to
	* the one being created.
	* Parameters: an Assistant to copy, by const reference
	* Returns: nothing directly
	**************************************/
	//----------------------------------------------------------------------
	//	Requirement #21: Demonstrate a copy constructor
	//----------------------------------------------------------------------
	Assistant::Assistant(const Assistant& a)
	{
		CopyTasks(a);
	}
	
	/**************************************
	* Function: Assistant::~Assistant()
	* Description: The destructor for the Assistant class. Doesn't actually need
	* to do anything extra, because the class doesn't have any additional dynamically-
	* allocated variables. Just calls the Robot destructor automatically at the end.
	* Parameters: none
	* Returns: nothing directly
	**************************************/
	//----------------------------------------------------------------------
	//	Requirement #21: Demonstrate a destructor
	//----------------------------------------------------------------------
	Assistant::~Assistant()
	{
		//intentionally empty
	}
	
	/**************************************
	* Function: Assistant::AddOptions()
	* Description: Adjusts the menuOptions vector variable, which is inherited from
	* the Robot class. Called by the constructors after the Robot constructor is
	* used to put the shared options in menuOptions. First removes the "Return to 
	* Main Menu" option that is at the end of the vector after the Robot construction,
	* so it can be moved to appear at the end of the Assistant list. Then adds the
	* additional things an Assistant can do that a Robot can't.
	* Makes it so DoMenu() shows an appropriate menu.
	* Parameters: none
	* Returns: nothing directly
	**************************************/
	void Assistant::AddOptions()
	{
		//remove "Return to Main Menu" from the last position so it can be moved down
		menuOptions.pop_back();			//http://www.cplusplus.com/reference/vector/vector/pop_back/
		//add the new things
		menuOptions.push_back("See Task List");
		menuOptions.push_back("Print Task List to File");
		menuOptions.push_back("Add Task");
		menuOptions.push_back("Complete Task");
		menuOptions.push_back("Calculate a Greatest Common Factor");
		//add back "Return to Main Menu" at the new end
		menuOptions.push_back("Return to Main Menu");
	}
	
	/**************************************
	* Function: Assistant::ReadJokes()
	* Description: Reads in a starting set of tasks from the file provided. Called
	* by the parametrized constructor to initialize the tasks vector variable. The
	* file must have one task per line, with the 3 components of the task separated
	* by commas (like name, priority, label).
	* If the attempt to open the file fails, it prints a message and
	* the Assistant will simply start out taskless (with an empty tasks vector).
	* Parameters: a string with the name of the input file to read
	* Returns: nothing directly
	**************************************/
	void Assistant::ReadTasks(string file)
	{
		Task newTask;				//stores the values of the new task, gets added to the vector
		string input, part;			//used to parse input from the file with getline
		ifstream ifs;				//the input file stream, will be pointed at the file
		
		//----------------------------------------------------------------------
		//	Requirement #23: Demonstrate file IO - input
		//----------------------------------------------------------------------
		//try to open the file
		ifs.open(file.c_str());
		//if it failed, just print a message and end the function
		//----------------------------------------------------------------------
		//	Requirement #07: Demonstrate debugging trick
		//			- check for bad conditions, in this case make sure that you
		//				don't try to do anything with a file that didn't open properly
		//----------------------------------------------------------------------
		if (ifs.fail())
		{
			cout << "Opening the task file failed. The Assistant will begin with "
				 << "an empty task list." << endl;
			return;					//end early, skip the rest of the function
		}
		
		//pull in each line, which is a task, and break it into components, add to list
		while (getline(ifs, input))
		{
			//put the line in the stringstream for processing
			stringstream ss;		//make a new one for every use to avoid issues
			ss << input;
			//the line should be structured like name, priority, label so use comma delimiter
			//if the getlines don't work properly (improper structure), fill with defaults
			if (getline(ss, part, ','))
			{
				newTask.name = part;
			}
			else			//default for name
			{
				newTask.name = "No name";
			}
			if (getline(ss, part, ','))
			{
				newTask.priority = part;
			}
			else			//default for priority
			{
				newTask.priority = "No priority";
			}
			//no comma after label (3rd item) so use normal >>
			if (ss >> part)
			{
				newTask.label = part;
			}
			else		//default for label
			{
				newTask.label = "No label";
			}
			
			//add that line's task to the vector task list
			tasks.push_back(newTask);
		}
		
		//close the file
		ifs.close();			
	}
	
	/**************************************
	* Function: Assistant::CopyTasks(const Assistant&)
	* Description: Used by the copy constructor to properly copy the vector of tasks
	* from the Assistant that is passed to the one being constructed.
	* Parameters: an Assistant by const reference
	* Returns: nothing directly
	**************************************/
	void Assistant::CopyTasks(const Assistant& a)
	{
		//use a const_iterator because the argument is passed as a const
		for (vector<Task>::const_iterator i = a.tasks.begin(); i != a.tasks.end(); i++)
		{
			tasks.push_back(*i);		//add every task from a to the new one
		}
	}
	
	/**************************************
	* Function: Assistant::DoMenu()
	* Description: Introduces the assistant robot and then displays a list of possible tasks
	* for the user to choose from. Validates the chosen task and then performs
	* the requested actions. Continues until the user chooses to return to the Main Menu.
	* Used to work with a robot when they are selected in the Robot Management System.
	* Declared as virtual so different versions can be used for the derived classes.
	* Parameters: none
	* Returns: nothing directly
	**************************************/
	void Assistant::DoMenu()
	{
		int choice = -1;		//the user's selection from the menu, starts invalid
		string again = "yes";		//whether or not to continue using the menu or exit
		
		//have the assistant robot introduce itself, by printing it (uses the inherited Robot class <<)
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
					cout << "My name is " << name << endl;
					break;
				case 4:
					cout << "My master's name is " << master << endl;
					break;
				case 5:
					ShowTasks();
					break;
				case 6:
					PrintTasks();
					break;
				case 7:
					AddTask();
					break;
				case 8:
					RemoveTask();
					break;
				case 9:
					DoGCF();
					break;
				case 10:
					again = "no";
					break;
				default:
					cout << "Should never get here" << endl;
			}
		} while(again != "no");
	}
	
	/**************************************
	* Function: Assistant::ShowTasks()
	* Description: Displays the current list of tasks to the screen, by iterating
	* over the vector and printing the name, priority, and label of each task.
	* If the task list is empty, it says so.
	* Parameters: none
	* Returns: nothing directly, just prints
	**************************************/
	void Assistant::ShowTasks()
	{
		//print the elements of each task, if there are any
		for (vector<Task>::iterator i = tasks.begin(); i != tasks.end(); i++)
		{
			cout << endl;
			//----------------------------------------------------------------------
			//	Requirement #16: Really use a struct- access variables etc.
			//----------------------------------------------------------------------
			cout << "Task Name: " << (*i).name << endl;
			cout << "    Priority: " << (*i).priority << endl;
			cout << "    Label: " << (*i).label << endl;
		}
		
		//if the task list is empty, print a statement
		if (tasks.size() == 0)
		{
			cout << "The task list is currently empty. Try adding one!" << endl;
		}
	}
	
	/**************************************
	* Function: Assistant::PrintsTasks()
	* Description: Prints the current list of tasks to an output file, by iterating
	* over the vector and printing the name, priority, and label of each task to
	* the file. The user is first asked to provide the name of the file to use.
	* If the task list is empty, it says so.
	* Parameters: none
	* Returns: nothing directly, just prints
	**************************************/
	void Assistant::PrintTasks()
	{
		string outFile;			//stores user input for the name of the output file
		ofstream ofs;			//the output file stream that's associated with the file
		
		//get the name of the output file to use
		cout << "Please enter the name of the output file (ex: file.txt) where "
			 << "you would like the list of tasks to be printed: ";
		getline(cin, outFile);
		
		//----------------------------------------------------------------------
		//	Requirement #23: Demonstrate file IO - output
		//----------------------------------------------------------------------
		//open the file, verify whether it worked
		ofs.open(outFile.c_str());
		if (ofs.fail())
		{
			cout << "Opening the output file failed. Exiting the function so you can try again." << endl;
			return;				//end function early so they can research issue and try again
		}
		
		//print the elements of each task to the file, if there are any
		for (vector<Task>::iterator i = tasks.begin(); i != tasks.end(); i++)
		{
			ofs << endl;
			ofs << "Task Name: " << (*i).name << endl;
			ofs << "    Priority: " << (*i).priority << endl;
			ofs << "    Label: " << (*i).label << endl;
		}
		
		//if the task list is empty, print a statement to the file
		if (tasks.size() == 0)
		{
			ofs << "The task list is currently empty. Try adding one!" << endl;
		}
		
		cout << "To see the results, finish up with the RMS and open the file "
			 << "with the name you gave. It should be in the same directory." << endl;
		
		//close the file
		ofs.close();
	}
	
	/**************************************
	* Function: Assistant::AddTask()
	* Description: Lets the user add a new task to the personal assistant robot's
	* task list. Gets the 3 parts, adds them to a new Task, then
	* adds that to the vector.
	* Parameters: none
	* Returns: nothing directly
	**************************************/
	void Assistant::AddTask()
	{
		Task newTask;			//holds the elements of the new Task, gets added to the vector
		
		//the parts of the task can contain any characters, so no input validation just getline
		cout << "Please enter the name of the task for me to add: ";
		getline(cin, newTask.name);
		cout << "Please enter a priority for the new task (suggestions include high, med, low): ";
		getline(cin, newTask.priority);
		cout << "Please enter a label for the new task (perhaps school or work): ";
		getline(cin, newTask.label);
		
		//add the Task to the vector
		tasks.push_back(newTask);
	}
	
	/**************************************
	* Function: Assistant::RemoveTask()
	* Description: Lets the user choose a task to say is complete so it gets removed
	* from the personal assistant robot's task list. They enter a name, so it's worth noting
	* that if their are duplicate names this will remove the first instance of that name
	* (not expected to be an issue). If the task isn't in the list, just prints
	* a statement.
	* Parameters: none
	* Returns: nothing directly
	**************************************/
	void Assistant::RemoveTask()
	{
		string theTask;				//stores user input for the name of the task to remove
		
		//get the name of the task
		cout << "Please enter the name of the completed task that should be removed: ";
		getline(cin, theTask);
		
		//iterate over the task list, and if find the task then erase it
		for (vector<Task>::iterator i = tasks.begin(); i != tasks.end(); i++)
		{
			if ((*i).name == theTask)		//it's a match
			{
				tasks.erase(i);				//remove
				cout << "Requested task has been erased from my list!" << endl;
				return;				//end early
			}
		}
		
		//if it wasn't found, let them know it wasn't in the list
		cout << "Sorry, that task isn't in my list. Couldn't complete a non-existent "
			 << "task. Try printing the task list to remember what you did." << endl;
	}
	
	/**************************************
	* Function: Assistant::GetType()
	* Description: Returns the type (class) of a robot. Used external to the classes
	* (in the Robot Management System) to show the types of robots.
	* Declared virtual so it will use the version of the appropriate class when 
	* called on a Robot pointer
	* Parameters: none
	* Returns: the type/ a description of the robot, depending on what class
	**************************************/
	string Assistant::GetType()
	{
		return "personal assistant robot";
	}
	
	/**************************************
	* Function: DoGCF()
	* Description: Finds the Greatest Common Factor of two integers that the
	* user enters. Captures user input, runs the recursive function GCF() to 
	* calculate the Greatest Common Factor, and prints the result.
	* Parameters: none
	* Returns: nothing, just prints
	**************************************/
	void DoGCF ()
	{
		int x, y;			//store user input for the 2 integers to find the GCF of
		int result;			//captures the GCF calculated by GCF() to be displayed
		
		cout << endl << "Let me help you with that pesky math homework!" << endl;
		GetInt(x, "Enter integer for first number to find the GCF of: ");
		GetInt(y, "Enter integer for the second number to find the GCF of: ");
		
		//actually calculate the GCF
		result = GCF(x, y);
		//show the result
		cout << "The calculated Greatest Common Factor of " << x << " and " 
			 << y << " is " << result << ". You're welcome." << endl;
	}
	
	/**************************************
	* Function: GCF(int, int)
	* Description: Recursively calculates the Greatest Common Factor of the two 
	* integers that are passed to it, using a common algorithm, involving repeated
	* modulo of the numbers (idea from http://www.informit.com/articles/article.aspx?p=1708657&seqNum=4).
	* Returns the integer that is the Greatest Common Factor.
	* Parameters: 2 ints that are the numbers to calculate the GCF of
	* Returns: an int that is the GCF of the 2 numbers that were passed
	**************************************/
	//----------------------------------------------------------------------
	//	Requirement #13: Demonstrate recursion
	//----------------------------------------------------------------------
	int GCF(int x, int y)
	{
		//base case when y starts at 0 or the previous x was divisible by the previous y
		if (y == 0)		
		{
			return abs(x);		//then x is the GCF- added abs because negative answers seem silly
		}
		//basically keep shrinking y until it becomes 0
		else
		{
			return GCF(y, x % y);
		}
	}
}	//end of PERCIVAL_ROBOTS