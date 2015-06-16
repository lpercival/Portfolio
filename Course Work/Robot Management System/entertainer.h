/**********************************************************
* Author: Lisa Percival
* Date Created: 8/24/14
* Last Modification Date: 8/27/14
* File name: entertainer.h
* Overview: The interface for the class Entertainer, which is derived from
* Robot. Describes an entertainer robot, including its one additional feature (jokes list)
* constructors, destructor, other public functions, and some private
* functions. Note that some of the functions are virtual.
* One class-external function is DoBitWise(), because it's called from within the
* class function DoMenu(), but does not need a calling object. (still in namespace)
* It also has the definition of a Joke struct, which is used for the list of jokes.
* Input: none
* Output: none
* *******************************************************/

#ifndef PERCIVAL_ENTERTAINER_H
#define PERCIVAL_ENTERTAINER_H

#include <string>
#include <vector>
#include "robot.h"
using std::string;
using std::vector;

namespace PERCIVAL_ROBOTS
{
	struct Joke
	{	
		string intro;				//the first part of a joke
		string punchline;			//the second part of a joke, comes after a pause
	};
	
	//----------------------------------------------------------------------
	//	Requirement #24: Demonstrate inheritance
	//----------------------------------------------------------------------
	class Entertainer : public Robot
	{
		public:
			Entertainer();
			Entertainer(int h, int w, int wh, string n, string m, string jF);
			Entertainer(const Entertainer& e);
			virtual ~Entertainer();
			virtual void DoMenu();
			virtual string GetType();
		private:
			//----------------------------------------------------------------------
			//	Requirement #17: Demonstrate a pointer to a struct
			//----------------------------------------------------------------------
			vector<Joke*> jokes;		//stores pointers to Jokes, for the joke list
			void ReadJokes(string file) throw (string);
			void CopyJokes(const Entertainer& e);
			void TellJoke();
			void AddJoke();
			void AddOptions();
	};
	
	void DoBitwise ();
} //end of PERCIVAL_ROBOTS

#endif	//PERCIVAL_ENTERTAINER_H