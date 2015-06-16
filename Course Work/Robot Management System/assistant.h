/**********************************************************
* Author: Lisa Percival
* Date Created: 8/24/14
* Last Modification Date: 8/27/14
* File name: assistant.h
* Overview: The interface for the class Assistant, which is derived from
* Robot. Describes a personal assistant robot, including its one additional feature (tasks list)
* constructors, destructor, other public functions, and some private
* functions. Note that some of the functions are virtual.
* Two class-external functions are DoGCF() and GCF(), because DoGCF() is called from within the
* class function DoMenu(), but does not need a calling object. And GCF() is called
* by DoGCF(), also without the need for a calling object. (still in namespace though)
* It also has the definition of a Task struct, which is used for the list of tasks.
* Input: none
* Output: none
* *******************************************************/

#ifndef PERCIVAL_ASSISTANT_H
#define PERCIVAL_ASSISTANT_H

#include <string>
#include <vector>
#include "robot.h"
using std::string;
using std::vector;

namespace PERCIVAL_ROBOTS
{
	//----------------------------------------------------------------------
	//	Requirement #16: Define a struct
	//----------------------------------------------------------------------
	struct Task
	{	
		string name;				//a name for the task
		string priority;			//a description of the priority for the task
		string label;				//a label to assign to the task
	};
	
	class Assistant : public Robot
	{
		public:
			Assistant();
			Assistant(int h, int w, int wh, string n, string m, string tF);
			Assistant(const Assistant& a);
			virtual ~Assistant();
			virtual void DoMenu();
			virtual string GetType();
		private:
			//----------------------------------------------------------------------
			//	Requirement #16: Use a struct by declaring a vector
			//----------------------------------------------------------------------
			vector<Task> tasks;		//stores the list of tasks
			void ReadTasks(string file);
			void CopyTasks(const Assistant& a);
			void AddTask();
			void ShowTasks();
			void PrintTasks();
			void RemoveTask();
			void AddOptions();
	};
	
	void DoGCF ();
	int GCF(int x, int y);
} //end of PERCIVAL_ROBOTS

#endif	//PERCIVAL_ASSISTANT_H