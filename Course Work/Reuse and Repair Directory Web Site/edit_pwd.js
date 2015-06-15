/* Author: Lisa Percival
*  Date Created: 5/22/15
*  Description: JavaScript file for changing a user's password. Makes a request to PHP
*  to validate the input and update the AuthorizedUsers table.
*/

/* Make a request to PHP to check the form input and set if it's OK.
*/
function editPwd() {
  //make the AJAX call to PHP
  var request = new XMLHttpRequest();
  if (!request) {
    document.getElementById('forErrors').textContent =
      'Could not create XMLHttpRequest';
  document.getElementById('forErrors').className = 'alert alert-danger';
  }
  var URL = 'update_pwd.php';
  var params = {
  thePwd: document.getElementById('pwd').value,
  theID: document.getElementById('ID').value,
  };
  request.open('POST', URL);
  //per developer.mozilla.org Getting Started Guide
  request.setRequestHeader('Content-Type',
    'application/x-www-form-urlencoded');
  //include the Parameters as data to send to server
  request.send(myURLStringify(params));
  //check out the response to see whether the credentials were valid
  request.onreadystatechange = function checkEdit() {
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
