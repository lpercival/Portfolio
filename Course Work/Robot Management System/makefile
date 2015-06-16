#Author: Lisa Percival
#Date Created: 8/24/14
#Last Modification Date: 8/27/14
#File name: makefile
#Overview: Build RMS (the Robot Management System) using all the necessary files.
# These include robot.cpp, entertainer.cpp, assistant.cpp, and RMS.cpp.
# Also take into account the header files robot.h, entertainer.h, and assistant.h.
# First builds an object file for each cpp, then links those objects together
# in the default rule, which makes the executable RMS.

#----------------------------------------------------------------------
#	Requirement #20: Demonstrate a makefile
#----------------------------------------------------------------------

RMS: robot.o entertainer.o assistant.o RMS.o
	g++ robot.o entertainer.o assistant.o RMS.o -o RMS
	
robot.o: robot.cpp robot.h
	g++ robot.cpp -c -o robot.o
	
entertainer.o: entertainer.cpp entertainer.h robot.h
	g++ entertainer.cpp -c -o entertainer.o
	
assistant.o: assistant.cpp assistant.h robot.h
	g++ assistant.cpp -c -o assistant.o
	
RMS.o: RMS.cpp robot.h entertainer.h assistant.h	
	g++ RMS.cpp -c -o RMS.o