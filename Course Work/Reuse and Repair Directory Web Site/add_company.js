/* Author: Lisa Percival
*  Date Created: 5/9/15
*  Description: JavaScript file for creating a new company. Makes a request to PHP
*  to validate the input and add it to the companies table, and also update the
*  CompanyCategories relationship table.
*/

/* Make a request to PHP to check the form inputs and add if they're OK.
*/
function newCompany() {
  //make the AJAX call to PHP
  var request = new XMLHttpRequest();
  if (!request) {
    document.getElementById('forErrors').textContent =
      'Could not create XMLHttpRequest';
  document.getElementById('forErrors').className = 'alert alert-danger';
  }
  var URL = 'make_company.php';
  //set up the array for categories
  // help from: http://stackoverflow.com/questions/10241759/get-multiple-values-from-dropdownlist-in-javascript
  var cats = document.getElementById('categories');
  var catValues = [];
  for (var i = 0; i < cats.options.length; i++) {
    if (cats.options[i].selected) {
      catValues.push(cats.options[i].value);
    }
  }
  // make the checkboxes into integers (0 or 1) to be booleans
  var isReuse = 0;	//start false
  var isRepair = 0;
  if (document.getElementById('reuseType').checked) {
	isReuse = 1;	// true
  }
  if (document.getElementById('repairType').checked) {
	isRepair = 1;	// true
  }
  var params = {
  theName: document.getElementById('name').value,
  theAddr1: document.getElementById('addr1').value,
  theAddr2: document.getElementById('addr2').value,
  theCity: document.getElementById('city').value,
  theState: document.getElementById('state').value,
  theZip: document.getElementById('zip').value,
  thePhone: document.getElementById('phone').value,
  theEmail: document.getElementById('email').value,
  theWebsite: document.getElementById('website').value,
  isReuseType: isReuse,
  isRepairType: isRepair,
  theCategories: catValues,
  theNotes: document.getElementById('notes').value,
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
          window.location.replace('companies.php');
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
