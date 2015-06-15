package cs496.finalproject;

import android.app.Activity;
import android.app.ProgressDialog;
import android.content.Context;
import android.content.Intent;
import android.net.ConnectivityManager;
import android.net.NetworkInfo;
import android.os.AsyncTask;
import android.support.v7.app.ActionBarActivity;
import android.os.Bundle;
import android.view.Menu;
import android.view.MenuItem;
import android.view.View;
import android.widget.Button;
import android.widget.EditText;
import android.widget.Toast;

import org.json.JSONArray;
import org.json.JSONException;
import org.json.JSONObject;

import java.io.IOException;
import java.io.InputStream;
import java.net.HttpURLConnection;
import java.net.URL;
import java.util.ArrayList;
import java.util.HashMap;
import java.util.Map;


public class LoginActivity extends Activity {

    private EditText usernameText;
    private EditText pwdText;

    // For logging in
    SessionManager session;

    private boolean validCredentials = false;

    // ArrayList of HashMaps for users
    ArrayList<HashMap<String, String>> userList;
    private ProgressDialog pDialog;

    private static final String theUrl = "http://stately-list-96223.appspot.com/users";

    // JSON Node names
    private static final String TAG_KEY = "key";
    private static final String TAG_USERNAME = "username";
    private static final String TAG_PWD = "password";

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_login);

        // get the Views accessible
        usernameText = (EditText) findViewById(R.id.username);
        pwdText = (EditText) findViewById(R.id.pwd);

        // make session manager for logging in
        session = new SessionManager(getApplicationContext());

        // initialize user list
        userList = new ArrayList<HashMap<String, String>>();
    }

    /** Called when user clicks button to log in */
    public void Login(View view) {
        // First step is to get the list of all users to check against so use AsyncTask class
        // The rest of the work (check if valid, login) must also occur there in the onPostExecute()
        // because it can only happen once the AsyncTask finishes
        // Make sure there's a network connection available
        ConnectivityManager connMgr = (ConnectivityManager) getSystemService(Context.CONNECTIVITY_SERVICE);
        NetworkInfo networkInfo = connMgr.getActiveNetworkInfo();
        if (networkInfo != null && networkInfo.isConnected())
        {
            // get user data
            new GetUsersAPI().execute();
        }
        else
        {
            // use toast to notify of issue
            Context context = getApplicationContext();
            CharSequence text = "No internet connection available, cannot log in.";
            int duration = Toast.LENGTH_LONG;
            Toast.makeText(context, text, duration).show();
        }

    }

    /** Called when user clicks button to sign up */
    public void SignUp(View view) {
        // Make an intent
        Intent intent = new Intent(this, SignUpActivity.class);
        // start it
        startActivity(intent);
    }


    /** Subclass uses AsyncTask to create a task away from the main UI thread. It takes the URL and
     * uses it to create an HttpUrlConnection. Once the connection's established, it downloads the
     * contents of the webpage as an InputStream. Then that's converted into an ArrayList of
     * HashMaps that are iterated over to determine whether the username and password are valid.
     */
    private class GetUsersAPI extends AsyncTask<Void, Void, Void> {
        // help from http://www.androidhive.info/2012/01/android-json-parsing-tutorial/
        // and developer.android.com/training/basics/network-ops/connecting.html

        // Show a progress dialog at first so it's not just a blank screen
        @Override
        protected void onPreExecute() {
            super.onPreExecute();
            // Showing progress dialog
            pDialog = new ProgressDialog(LoginActivity.this);
            pDialog.setMessage("Please wait...");
            pDialog.setCancelable(false);
            pDialog.show();

        }

        @Override
        protected Void doInBackground(Void... arg0) {
            try {
                // Get the information
                downloadUrl(theUrl);
            } catch (IOException e) {
                pDialog.setMessage("Unable to retrieve web page. URL may be invalid");
            }

            return null;
        }

        //onPostExecute handles the results of the AsyncTask
        @Override
        protected void onPostExecute(Void result) {
            super.onPostExecute(result);
            // Dismiss the progress dialog
            if (pDialog.isShowing()) {
                pDialog.dismiss();
            }

            // iterate over the userList to decide whether the credentials provided are valid
            String sentUsername;
            String sentPwd;
            String givenUsername = usernameText.getText().toString();
            String givenPwd = pwdText.getText().toString();
            String theUserID = "";
            for (HashMap<String, String> user : userList) {
                sentUsername = user.get(TAG_USERNAME);
                sentPwd = user.get(TAG_PWD);
                // if what was given matches the entry in the hash map, it's valid
                if (sentUsername.equals(givenUsername) && sentPwd.equals(givenPwd)) {
                    validCredentials = true;
                    theUserID = user.get(TAG_KEY);
                    break;  //end early
                }
            }
            // if the credentials were deemed valid, log in
            if (validCredentials) {
                session.createLoginSession(theUserID);

                // then redirect to Schools page
                Intent intent = new Intent(LoginActivity.this, SchoolsActivity.class);
                startActivity(intent);
            }
            else {
                // use toast to notify of issue
                Context context = getApplicationContext();
                CharSequence text = "Username or password was invalid. Try again.";
                int duration = Toast.LENGTH_LONG;
                Toast.makeText(context, text, duration).show();
            }
        }

        /** Given a URL, establishes a HttpUrlConnection and retrieves the content as an InputStream,
         * which it parses into the HashMap structure.
         */
        private void downloadUrl(String myUrl) throws IOException {
            InputStream is = null;
            String contentString = null;

            URL url = new URL(myUrl);
            HttpURLConnection conn = (HttpURLConnection) url.openConnection();

            try {
                conn.setReadTimeout(10000);    // milliseconds
                conn.setConnectTimeout(15000);
                conn.setRequestMethod("GET");
                conn.setDoInput(true);
                // Start the query
                conn.connect();
                int response = conn.getResponseCode();
                if (response == 200) // if got actual data
                {
                    is = conn.getInputStream();
                    // Convert the InputStream into a string
                    contentString = SchoolsActivity.IStoString(is);
                }
                // Makes sure InputStream's closed & connection's disconnected after app's finished using it
            } finally {
                if (is != null) {
                    is.close();
                }
                conn.disconnect();
            }

            // Take the string that was returned and parse the JSON to make the HashMap
            if (contentString != null)
            {
                // Make it a JSON array
                try {
                    JSONArray users = new JSONArray(contentString);

                    // looping through all users
                    for (int i = 0; i < users.length(); i++) {
                        JSONObject u = users.getJSONObject(i);

                        // get out the pieces
                        String key = u.getString(TAG_KEY);
                        String username = u.getString(TAG_USERNAME);
                        String pwd = u.getString(TAG_PWD);

                        // temp HashMap for single user
                        HashMap<String, String> user = new HashMap<String, String>();

                        // add each child node to HashMap key => value
                        user.put(TAG_KEY, key);
                        user.put(TAG_USERNAME, username);
                        user.put(TAG_PWD, pwd);

                        // then add the user to the user list
                        userList.add(user);
                    }
                } catch (JSONException e) {
                    e.printStackTrace();
                }
            }
        }
    }

}
