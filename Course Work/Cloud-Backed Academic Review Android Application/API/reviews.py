import os
import urllib
from google.appengine.api import users
from google.appengine.ext import ndb # data modeling API
from datetime import datetime
import webapp2
import objects
import json
	
USER_KEY = 'users'
SCHOOL_KEY = 'schools'

class Review(webapp2.RequestHandler):
	def get(self, **kwargs):
		if 'application/json' not in self.request.accept:
			# can't handle the JSON response so give up
			self.response.status = 406
			self.response.status_message = "Not acceptable, only supports JSON"
			return
		if 'rid' in kwargs: 
			# Get a specific review
			k = ndb.Key(objects.User, USER_KEY)
			user = objects.User.get_by_id(int(kwargs['uid']), parent=k)
			if not user:		# given user doesn't exist so can't view review
				self.response.status = 400
				self.response.status_message = "Invalid request, user doesn't exist"
				self.response.write(self.response.status)
				return
			review = objects.Review.get_by_id(int(kwargs['rid']), parent=user.key)
			if not review:
				self.response.status = 404
				self.response.status_message = "Review doesn't appear to exist"
				self.response.write(self.response.status)
				return
			self.response.write(json.dumps(review.to_dict()))
		else:		# Get all reviews for the user
			k = ndb.Key(objects.User, USER_KEY)
			user = objects.User.get_by_id(int(kwargs['uid']), parent=k)
			if not user:		# given user doesn't exist so can't see reviews
				self.response.status = 400
				self.response.status_message = "Invalid request, user doesn't exist"
				self.response.write(self.response.status)
				return
			reviews_query = objects.Review.query(ancestor=user.key)
			reviews = reviews_query.fetch()
			review_data = [review.to_dict() for review in reviews]
			self.response.write(json.dumps(review_data))
		
	def post(self, **kwargs):
		if 'application/json' not in self.request.accept:
			# can't handle the JSON response so give up
			self.response.status = 406
			self.response.status_message = "Not acceptable, only supports JSON"
			return
		# Create a new Review object and save it to the datastore
		k = ndb.Key(objects.User, USER_KEY)
		user = objects.User.get_by_id(int(kwargs['uid']), parent=k)
		if not user:		# given user doesn't exist so can't make review
			self.response.status = 400
			self.response.status_message = "Invalid request, user doesn't exist"
			self.response.write("Sorry, a review can't be added for a nonexistent user.")
			return
		review = objects.Review(parent=user.key)
		theTitle = self.request.get('title')
		theDescription = self.request.get('description')
		theDate = self.request.get('date')
		theRanking = self.request.get('ranking')
		theSchool = self.request.get('school')
		# All fields are required
		if theTitle:
			review.title = theTitle
		else:
			self.response.status = 400
			self.response.status_message = "Invalid request, title is required"
			self.response.write("Sorry, you must give a title for the review.")
			return
		if theDescription:
			review.description = theDescription
		else:
			self.response.status = 400
			self.response.status_message = "Invalid request, description is required"
			self.response.write("Sorry, you must give a description for the review.")
			return
		if theDate:
			try:	# handle it if they give a poorly-formatted string
				review.date = datetime.strptime(theDate, '%Y-%m-%d').date()
			except ValueError:
				self.response.status = 400
				self.response.status_message = "Invalid request, date format should be YYYY-MM-DD"
				self.response.write("Sorry, you must provide a valid date for the review.")
				return
		else:
			self.response.status = 400
			self.response.status_message = "Invalid request, date is required"
			self.response.write("Sorry, you must provide a date for the review.")
			return
		if theRanking and theRanking.isdigit():	# also needs to be an integer
			review.ranking = int(theRanking)
		else:
			self.response.status = 400
			self.response.status_message = "Invalid request, integer ranking is required"
			self.response.write("Sorry, you must provide an integer ranking for the review.")
			return
		if theSchool:
			# also make sure it's a valid school
			k = ndb.Key(objects.School, SCHOOL_KEY)
			school = objects.School.get_by_id(int(theSchool), parent=k)
			if not school:		# given school doesn't exist so can't add review
				self.response.status = 400
				self.response.status_message = "Invalid request, school doesn't exist"
				self.response.write("Sorry, you must give a valid school ID for the review.")
				return
				
			review.school = school.key
		else:
			self.response.status = 400
			self.response.status_message = "Invalid request, school is required"
			self.response.write("Sorry, you must give a school for the review.")
			return
		review.put()
		
		# Also update the associated school's list of rankings and then average ranking
		school.rankings.append(int(theRanking))
		rankSum = 0
		rankNum = 0
		for rank in school.rankings:
			rankSum += rank
			rankNum += 1
		avg = float(rankSum) / float(rankNum) # just added so not / 0
		school.avgRanking = avg		
		school.put()		# have to save the changes
		
		# Return what just made
		self.response.write(json.dumps(review.to_dict()))
		return
		
	def delete(self, **kwargs):
		if 'application/json' not in self.request.accept:
			# can't handle the JSON response so give up
			self.response.status = 406
			self.response.status_message = "Not acceptable, only supports JSON"
			return
		if 'rid' not in kwargs: 		# need ID of specific review to delete
			self.response.status = 400
			self.response.status_message = "Invalid request, need ID to delete"
			self.response.write(self.response.status)
			return
		# Delete the review with the provided ID
		k = ndb.Key(objects.User, USER_KEY)
		user = objects.User.get_by_id(int(kwargs['uid']), parent=k)
		if not user:		# given user doesn't exist so can't find review
			self.response.status = 400
			self.response.status_message = "Invalid request, user doesn't exist"
			self.response.write("Sorry, a review can't be deleted for a nonexistent user.")
			return
		review = objects.Review.get_by_id(int(kwargs['rid']), parent=user.key)
		if not review:
			self.response.status = 404
			self.response.status_message = "Review doesn't appear to exist"
			self.response.write("Sorry, can't delete a nonexistent review.")
			return
		out = review.to_dict()
		
		# Also update the associated school's list of rankings and then average rankings
		k = ndb.Key(objects.School, SCHOOL_KEY)
		school = objects.School.get_by_id(review.school.id(), parent=k)
		school.rankings.remove(review.ranking)	# removes 1st instance of value
		rankSum = 0
		rankNum = 0
		for rank in school.rankings:
			rankSum += rank
			rankNum += 1
		if rankNum == 0:	# so don't / 0
			avg = 0
		else:
			avg = float(rankSum) / float(rankNum) 
		school.avgRanking = avg
		school.put()		# have to save the changes
		
		# Delete review
		review.key.delete()
		
		self.response.write("Deleted: ")
		self.response.write(json.dumps(out))
		
	def put(self, **kwargs):
		if 'application/json' not in self.request.accept:
			# can't handle the JSON response so give up
			self.response.status = 406
			self.response.status_message = "Not acceptable, only supports JSON"
			return
		if 'rid' not in kwargs: 		# need ID of specific review to update
			self.response.status = 400
			self.response.status_message = "Invalid request, need ID to edit"
			self.response.write(self.response.status)
			return
		# Update the review with the provided ID
		k = ndb.Key(objects.User, USER_KEY)
		user = objects.User.get_by_id(int(kwargs['uid']), parent=k)
		if not user:		# given user doesn't exist so can't find review
			self.response.status = 400
			self.response.status_message = "Invalid request, user doesn't exist"
			self.response.write("Sorry, a review can't be edited for a nonexistent user.")
			return
		review = objects.Review.get_by_id(int(kwargs['rid']), parent=user.key)
		if not review:
			self.response.status = 404
			self.response.status_message = "Review doesn't appear to exist"
			self.response.write("Sorry, can't edit a nonexistent review.")
			return
		theTitle = self.request.get('title')
		theDescription = self.request.get('description')
		theDate = self.request.get('date')
		theRanking = self.request.get('ranking')
		theSchool = self.request.get('school')
		oldSchool = self.request.get('oldSchool')
		oldRanking = self.request.get('oldRanking')
		# All fields are required
		if theTitle:
			review.title = theTitle
		else:
			self.response.status = 400
			self.response.status_message = "Invalid request, title is required"
			self.response.write("Sorry, you must give a title for the review.")
			return
		if theDescription:
			review.description = theDescription
		else:
			self.response.status = 400
			self.response.status_message = "Invalid request, description is required"
			self.response.write("Sorry, you must give a description for the review.")
			return
		if theDate:
			try:	# handle it if they give a poorly-formatted string
				review.date = datetime.strptime(theDate, '%Y-%m-%d').date()
			except ValueError:
				self.response.status = 400
				self.response.status_message = "Invalid request, date format should be YYYY-MM-DD"
				self.response.write("Sorry, you must provide a valid date for the review.")
				return
		else:
			self.response.status = 400
			self.response.status_message = "Invalid request, date is required"
			self.response.write("Sorry, you must provide a date for the review.")
			return
		if theRanking and theRanking.isdigit():	# also needs to be an integer
			review.ranking = int(theRanking)
		else:
			self.response.status = 400
			self.response.status_message = "Invalid request, integer ranking is required"
			self.response.write("Sorry, you must provide an integer ranking for the review.")
			return
		if theSchool:
			# also make sure it's a valid school
			k = ndb.Key(objects.School, SCHOOL_KEY)
			school = objects.School.get_by_id(int(theSchool), parent=k)
			if not school:		# given school doesn't exist so can't add review
				self.response.status = 400
				self.response.status_message = "Invalid request, school doesn't exist"
				self.response.write("Sorry, you must give a valid school ID for the review.")
				return	
			review.school = school.key
			
			# Also update the associated school's list of rankings and then average ranking
			school.rankings.append(int(theRanking))
			rankSum = 0
			rankNum = 0
			for rank in school.rankings:
				rankSum += rank
				rankNum += 1
			avg = float(rankSum) / float(rankNum) # just added so not / 0
			school.avgRanking = avg
			school.put()		# have to save the changes
			
			# And then do the same with the old school (but remove instead of add)
			k = ndb.Key(objects.School, SCHOOL_KEY)
			school = objects.School.get_by_id(int(oldSchool), parent=k)
			school.rankings.remove(int(oldRanking))	# removes 1st instance of value
			rankSum = 0
			rankNum = 0
			for rank in school.rankings:
				rankSum += rank
				rankNum += 1
			if rankNum == 0:	# so don't / 0
				avg = 0
			else:
				avg = float(rankSum) / float(rankNum)
			school.avgRanking = avg
			school.put()		# have to save the changes
		else:
			self.response.status = 400
			self.response.status_message = "Invalid request, school is required"
			self.response.write("Sorry, you must give a school for the review.")
			return			
			
		review.put()
		# Return what just changed
		self.response.write(json.dumps(review.to_dict()))
		return

app = webapp2.WSGIApplication([
	webapp2.Route('/users/<uid:[0-9]+>/reviews', handler=Review),
	webapp2.Route('/users/<uid:[0-9]+>/reviews/<rid:[0-9]+>', handler=Review),
], debug=True)
