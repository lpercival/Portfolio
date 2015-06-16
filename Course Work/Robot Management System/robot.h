/**********************************************************
* Author: Lisa Percival
* Date Created: 8/23/14
* Last Modification Date: 8/27/14
* File name: robot.h
* Overview: The interface for the class Robot, which the Entertainer & Assistant
* classes will be derived from. Describes a generic robot, including its features,
* constructors, destructor, overloaded << operator, other public functions, and some
* protected and private functions. Note that some of the functions are virtual.
* It also has the helper function GetInt(), so that it can be used in this class,
* the classes derived from it, and the application file that uses this class.
* Input: none
* Output: none
* *******************************************************/

//----------------------------------------------------------------------
//	Requirement #19: Demonstrate a header file
//----------------------------------------------------------------------
#ifndef PERCIVAL_ROBOT_H
#define PERCIVAL_ROBOT_H

#include <iostream>
#include <string>
#include <vector>
using std::string;
using std::ostream;
using std::vector;

//----------------------------------------------------------------------
//	Requirement #18: Demonstrate a custom namespace
//----------------------------------------------------------------------
namespace PERCIVAL_ROBOTS
{
	//----------------------------------------------------------------------
	//	Requirement #16: Define a class
	//----------------------------------------------------------------------
	class Robot
	{
		public:
			Robot();
			Robot(int h, int w, int wh, string n, string m);
			Robot(const Robot& r);
			virtual ~Robot();
			//----------------------------------------------------------------------
			//	Requirement #24: Demonstrate polymorphism
			//----------------------------------------------------------------------
			virtual void DoMenu();
			friend ostream& operator <<(ostream& outs, const Robot& r);
			string GetName();
			virtual string GetType();
			friend int RequestHeight();
			friend int RequestWidth();
			friend int RequestWheels();
		protected:
			int height;		//the height of the physical representation (# rows)
			int width;		//the width of the physical representation (# columns)
			int wheels;		//whether or not it has wheels- 0 is yes, 1 is no
			//----------------------------------------------------------------------
			//	Requirement #14: Demonstrate multi-dimensional array & dynamically allocated array
			//		- combined, declaration here, use in robot.cpp
			//----------------------------------------------------------------------
			//----------------------------------------------------------------------
			//	Requirement #17: Demonstrate a pointer to an array
			//----------------------------------------------------------------------
			char **looks;	//points to a 2-D dynamic array with the physical representation
			string name;	//robot's name
			string master;	//name of robot's master
			vector<string> menuOptions;		//list of options in the robot's menu
			void ChangeName();
			void ChangeMaster();
		private:
			void CreateLooks();
			void CreateOptions();
			void CopyLooks(const Robot& r);
			void CopyOptions(const Robot& r);
	};
	int RequestHeight();		//http://stackoverflow.com/questions/1749311/friend-function-within-a-namespace
	int RequestWidth();			//these have to be declared again here or they're not accessible in RMS
	int RequestWheels();		//http://stackoverflow.com/questions/8207633/whats-the-scope-of-inline-friend-functions
} //end of PERCIVAL_ROBOTS

void GetInt (int& in, string msg);

#endif	//PERCIVAL_ROBOT_H