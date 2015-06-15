import os
import urllib
from google.appengine.api import users
from google.appengine.ext import ndb # data modeling API
from datetime import datetime
import webapp2
import objects
import json

SCHOOL_KEY = 'schools'

class School(webapp2.RequestHandler):
	def get(self):		
		if 'application/json' not in self.request.accept:
			# can't handle the JSON response so give up
			self.response.status = 406
			self.response.status_message = "Not acceptable, only supports JSON"
			return
		# Get all Schools, sorted by avgRanking (highest first)
		schools_query = objects.School.query(
			ancestor=ndb.Key(objects.School, SCHOOL_KEY)).order(-objects.School.avgRanking, objects.School.name)
		schools = schools_query.fetch()
		school_data = [school.to_dict() for school in schools]
		self.response.write(json.dumps(school_data))		
		
	def post(self):
		if 'application/json' not in self.request.accept:
			# can't handle the JSON response so give up
			self.response.status = 406
			self.response.status_message = "Not acceptable, only supports JSON"
			return
		# Create a new School object and save it to the datastore
		k = ndb.Key(objects.School, SCHOOL_KEY)
		school = objects.School(parent=k)
		theName = self.request.get('name')
		theCity = self.request.get('city')
		theState = self.request.get('state')
		if theName:
			school.name = theName
		else:	# fails because name is required
			self.response.status = 400
			self.response.status_message = "Invalid request, name is required"
			self.response.write("Sorry, you must give a name for the school.")
			return
		if theCity:		#optional
			school.city = theCity
		if theState:	#optional
			school.state = theState
		# set the ranking to 0 to start, list of rankings should auto start empty
		school.avgRanking = 0
		school.put()
		# Return what just made
		self.response.write(json.dumps(school.to_dict()))
		return

app = webapp2.WSGIApplication([
	('/schools', School),
], debug=True)
