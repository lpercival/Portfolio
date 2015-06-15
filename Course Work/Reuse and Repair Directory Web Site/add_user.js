/* Author: Lisa Percival
*  Date Created: 5/10/15
*  Description: JavaScript file for creating a new user. Makes a request to PHP
*  to validate the input and add it to the AuthorizedUsers table.
*/

/* Make a request to PHP to check the form inputs and add if they're OK.
*/
function newUser() {
  //make the AJAX call to PHP
  var request = new XMLHttpRequest();
  if (!request) {
    document.getElementById('forErrors').textContent =
      'Could not create XMLHttpRequest';
  document.getElementById('forErrors').className = 'alert alert-danger';
  }
  var URL = 'make_user.php';
  var params = {
  theLogin: document.getElementById('username').value,
  theFname: document.getElementById('fname').value,
  theLname: document.getElementById('lname').value,
  thePwd: document.getElementById('pwd').value,
  };
  request.open('POST', URL);
  //per developer.mozilla.org Getting Started Guide
  request.setRequestHeader('Content-Type',
    'application/x-www-form-urlencoded');
  //include the Parameters as data to send to server
  request.send(myURLStringify(params));
  //check out the response to see whether the credentials were valid
  request.onreadystatechange = function checkCreation() {
    if (this.readyState === 4) {    //make sure it's done
      //make sure it worked
      if (this.status === 200) {
        if (this.responseText == 'Success') {
          window.location.replace('users.php');
        }
    else { //show errors
      document.getElementById('forErrors').textContent = this.responseText;
      document.getElementById('forErrors').className = 'alert alert-danger';
    }
      }
      else {
        document.getElementById('forErrors').textContent = 'PHP Request failed';
        document.getElementById('forErrors').className = 'alert alert-danger';
      }
    }
  };
}

/* Helper function for AJAX requests - converts a parameter object into a string
*  that will work as part of a URL. Normal JSON stringify wouldn't do that
*  @param theObj the object to be converted
*  @return a string representation of that object
*/
function myURLStringify(theObj) {
    var theString = [];        //start with an array to fill
    //convert every property of theObj into a URL-friendly string, add to array
    for (var property in theObj) {
        var str = encodeURIComponent(property) + '=' +
            encodeURIComponent(theObj[property]);
        theString.push(str);
    }
    //combine all the properties from the array into one big string
    return theString.join('&');
}
