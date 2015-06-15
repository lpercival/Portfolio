from google.appengine.ext import ndb

class Review(ndb.Model):
	title = ndb.StringProperty(required=True)
	description = ndb.StringProperty(required=True)
	date = ndb.DateProperty(required=True)
	ranking = ndb.IntegerProperty(required=True)
	school = ndb.KeyProperty(required=True)
	# also has author but that's stored as its parent
	
	def to_dict(self):
		d = super(Review, self).to_dict()
		d['key'] = self.key.id()
		d['date'] = self.date.isoformat()
		d['school'] = self.school.id()
		return d
	
class School(ndb.Model):
	name = ndb.StringProperty(required=True)
	city = ndb.StringProperty()
	state = ndb.StringProperty()
	avgRanking = ndb.FloatProperty()
	rankings = ndb.IntegerProperty(repeated=True)
	
	def to_dict(self):
		d = super(School, self).to_dict()
		d['key'] = self.key.id()
		return d
		
class User(ndb.Model):
	username = ndb.StringProperty(required=True)
	password = ndb.StringProperty(required=True)
	
	def to_dict(self):
		d = super(User, self).to_dict()
		d['key'] = self.key.id()
		return d