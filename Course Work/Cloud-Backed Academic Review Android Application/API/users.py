import os
import urllib
from google.appengine.api import users
from google.appengine.ext import ndb # data modeling API
from datetime import datetime
import webapp2
import objects
import json

USER_KEY = 'users'

class User(webapp2.RequestHandler):
	def get(self):		
		if 'application/json' not in self.request.accept:
			# can't handle the JSON response so give up
			self.response.status = 406
			self.response.status_message = "Not acceptable, only supports JSON"
			return
		# Get all Users
		users_query = objects.User.query(
			ancestor=ndb.Key(objects.User, USER_KEY)).order(objects.User.username)
		users = users_query.fetch()
		user_data = [user.to_dict() for user in users]
		self.response.write(json.dumps(user_data))		
		
	def post(self):
		if 'application/json' not in self.request.accept:
			# can't handle the JSON response so give up
			self.response.status = 406
			self.response.status_message = "Not acceptable, only supports JSON"
			return
		# Create a new User object and save it to the datastore
		k = ndb.Key(objects.User, USER_KEY)
		user = objects.User(parent=k)
		theUsername = self.request.get('username')
		thePwd = self.request.get('pwd')
		# Both fields required
		if theUsername:
			user.username = theUsername
			# Make sure it's a new unique username, not already taken
			users_query = objects.User.query(
				objects.User.username == theUsername, ancestor=ndb.Key(objects.User, USER_KEY))
			users = users_query.fetch()
			user_data = [user.to_dict() for user in users]
			if len(user_data) != 0:		# nonempty result so username exists
				self.response.status = 400
				self.response.status_message = "Invalid request, username must be unique"
				self.response.write("Sorry, that username is already taken. Try again.")
				return
		else:	
			self.response.status = 400
			self.response.status_message = "Invalid request, username is required"
			self.response.write("You must provide a username. Try again.")
			return
		if thePwd:	
			user.password = thePwd
		else:	
			self.response.status = 400
			self.response.status_message = "Invalid request, password is required"
			self.response.write("You must provide a password. Try again.")
			return
		user.put()
		# Return what just made
		self.response.write(json.dumps(user.to_dict()))
		return

app = webapp2.WSGIApplication([
	('/users', User),
], debug=True)
