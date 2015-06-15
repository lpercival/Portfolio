package cs496.finalproject;

import android.app.Activity;
import android.app.ProgressDialog;
import android.content.Context;
import android.content.Intent;
import android.database.Cursor;
import android.database.sqlite.SQLiteDatabase;
import android.net.ConnectivityManager;
import android.net.NetworkInfo;
import android.os.AsyncTask;
import android.support.v7.app.ActionBarActivity;
import android.os.Bundle;
import android.view.Menu;
import android.view.MenuItem;
import android.view.View;
import android.widget.TextView;
import android.widget.Toast;

import org.json.JSONArray;
import org.json.JSONException;
import org.json.JSONObject;

import java.io.IOException;
import java.io.InputStream;
import java.io.PrintWriter;
import java.net.HttpURLConnection;
import java.net.URL;
import java.net.URLEncoder;
import java.util.HashMap;


public class ViewReviewActivity extends Activity {

    // For telling whether someone's logged in
    SessionManager session;

    // JSON Node names
    private static final String TAG_TITLE = "title";
    private static final String TAG_DESCR = "description";
    private static final String TAG_DATE = "date";
    private static final String TAG_SCHOOL = "school";
    private static final String TAG_RANK = "ranking";

    private ProgressDialog pDialog;
    String reviewID;
    String title;
    String description;
    String date;
    String school;
    String schoolName;
    String ranking;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_view_review);

        // set up Session class instance
        session = new SessionManager(getApplicationContext());
        // make sure someone's logged in and if not they'll be redirected
        session.checkLogin();

        // Get the review ID from the intent
        Intent intent = getIntent();
        reviewID = intent.getStringExtra("reviewID");

        // Make sure there's a network connection available
        ConnectivityManager connMgr = (ConnectivityManager) getSystemService(Context.CONNECTIVITY_SERVICE);
        NetworkInfo networkInfo = connMgr.getActiveNetworkInfo();
        if (networkInfo != null && networkInfo.isConnected())
        {
            // get review data
            new GetReviewAPI().execute();
        }
        else
        {
            // use toast to notify of issue
            Context context = getApplicationContext();
            CharSequence text = "No internet connection available, cannot load data.";
            int duration = Toast.LENGTH_LONG;
            Toast.makeText(context, text, duration).show();
        }
    }


    @Override
    public boolean onCreateOptionsMenu(Menu menu) {
        // Inflate the menu; this adds items to the action bar if it is present.
        getMenuInflater().inflate(R.menu.menu_view_review, menu);
        return true;
    }

    @Override
    public boolean onOptionsItemSelected(MenuItem item) {
        // Handle action bar item clicks here. The action bar will
        // automatically handle clicks on the Home/Up button, so long
        // as you specify a parent activity in AndroidManifest.xml.
        int id = item.getItemId();

        //If they chose to add a Review, then start that activity
        if (id == R.id.add_review) {
            // Make an intent
            Intent intent = new Intent(this, AddReviewActivity.class);
            // Pass the schoolList to be used to populate the schools dropdown
            //intent.putExtra("schools", schoolList);
            startActivity(intent);
            return true;
        }
        //If they chose to add a school, then start that activity
        if (id == R.id.add_school) {
            // Make an intent
            Intent intent = new Intent(this, AddSchoolActivity.class);
            startActivity(intent);
            return true;
        }
        //If they chose to log out, do so
        if (id == R.id.logout) {
            session.logoutUser();
        }

        // default
        return super.onOptionsItemSelected(item);
    }

    /** Called when user clicks button to edit the review*/
    public void EditReview(View view) {
        // call the Edit Review activity, but pass along all of the review's data to save GET
        Intent intent = new Intent(this, EditReviewActivity.class);
        intent.putExtra("reviewID", reviewID);
        intent.putExtra("title", title);
        intent.putExtra("description", description);
        intent.putExtra("date", date);
        intent.putExtra("school", school);  // ID
        intent.putExtra("schoolName", schoolName);
        intent.putExtra("ranking", ranking);
        startActivity(intent);
    }

    /** Called when user clicks button to delete the review*/
    public void DeleteReview(View view) {
        // Make sure there's a network connection available
        ConnectivityManager connMgr = (ConnectivityManager) getSystemService(Context.CONNECTIVITY_SERVICE);
        NetworkInfo networkInfo = connMgr.getActiveNetworkInfo();
        if (networkInfo != null && networkInfo.isConnected())
        {
            // delete the review through the API
            new DeleteReviewAPI().execute();
        }
        else
        {
            // use toast to notify of issue
            Context context = getApplicationContext();
            CharSequence text = "No internet connection available, cannot delete review.";
            int duration = Toast.LENGTH_LONG;
            Toast.makeText(context, text, duration).show();
        }
    }


    /** Subclass uses AsyncTask to create a task away from the main UI thread. It takes the URL and
     * uses it to create an HttpUrlConnection. Once the connection's established, it downloads the
     * contents of the webpage as an InputStream. Then the pieces are parsed and displayed.
     */
    private class GetReviewAPI extends AsyncTask<Void, Void, Void> {
        // help from http://www.androidhive.info/2012/01/android-json-parsing-tutorial/
        // and developer.android.com/training/basics/network-ops/connecting.html

        // Show a progress dialog at first so it's not just a blank screen
        @Override
        protected void onPreExecute() {
            super.onPreExecute();
            // Showing progress dialog
            pDialog = new ProgressDialog(ViewReviewActivity.this);
            pDialog.setMessage("Please wait...");
            pDialog.setCancelable(false);
            pDialog.show();

        }

        @Override
        protected Void doInBackground(Void... arg0) {
            // build the desired URL from the base and including the logged in user and review ID
            String loggedInUser = session.getUserID();
            String baseUrl = "http://stately-list-96223.appspot.com/users/";
            String theUrl = baseUrl + loggedInUser + "/reviews/" + reviewID;

            try {
                // Get the information
                downloadUrl(theUrl);
                //return null;
            } catch (IOException e) {
               pDialog.setMessage("Unable to retrieve web page. URL may be invalid");
            }

            return null;
        }

        //onPostExecute displays the results of the AsyncTask
        @Override
        protected void onPostExecute(Void result) {
            super.onPostExecute(result);
            // Dismiss the progress dialog
            if (pDialog.isShowing()) {
                pDialog.dismiss();
            }

            // Fill in the textViews with the data
            // title
            TextView titleTxt = (TextView) findViewById(R.id.title);
            titleTxt.setText("Title: " + title);
            // description
            TextView descrTxt = (TextView) findViewById(R.id.description);
            descrTxt.setText("Description: " + description);
            // date
            TextView dateTxt = (TextView) findViewById(R.id.date);
            dateTxt.setText("Date: " + date);
            // school - have to convert from key to name 1st, use SQLite cache
            // make the helper
            SchoolsSaverHelper dbHelper = new SchoolsSaverHelper(ViewReviewActivity.this);
            // get the data repository in read mode
            SQLiteDatabase db = dbHelper.getReadableDatabase();
            // define a projection that specifies which columns will use after query
            String[] projection = {SchoolsSaverHelper.COL_NAME};
            // set up the WHERE clause
            String selection = SchoolsSaverHelper.COL_KEY + "=?";
            String[] selectionArgs = {school};
            // set the sort order
            String sortOrder = SchoolsSaverHelper.COL_NAME + " ASC";
            // make the query, whose results're returned in Cursor object
            Cursor c = db.query(SchoolsSaverHelper.TABLE, projection, selection, selectionArgs, null, null, sortOrder);
            c.moveToFirst();
            schoolName = c.getString(c.getColumnIndex(SchoolsSaverHelper.COL_NAME));
            TextView schoolTxt = (TextView) findViewById(R.id.school);
            schoolTxt.setText("School: " + schoolName);
            // ranking
            TextView rankTxt = (TextView) findViewById(R.id.ranking);
            rankTxt.setText("Ranking: " + ranking);
        }

        /** Given a URL, establishes a HttpUrlConnection and retrieves the content as an InputStream,
         * which it parses into variables that will be displayed.
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

            // Take the string that was returned and parse the JSON to get the data
            if (contentString != null)
            {
                // it should be a JSON object
                try {
                    JSONObject review = new JSONObject(contentString);

                    // title
                    title = review.getString(TAG_TITLE);
                    // description
                    description = review.getString(TAG_DESCR);
                    // date
                    date = review.getString(TAG_DATE);
                    // school
                    school = review.getString(TAG_SCHOOL);
                    // ranking
                    ranking = review.getString(TAG_RANK);
                } catch (JSONException e) {
                    e.printStackTrace();
                }
            }
        }
    }


    /** Subclass uses AsyncTask to create a task away from the main UI thread. It takes the URL and
     * uses it to create an HttpUrlConnection. Once the connection's established, it sends a DELETE
     * request for the given review.
     */
    private class DeleteReviewAPI extends AsyncTask<Void, Void, Void> {

        private int responseCode;
        private String response="";

        // do the actual work here
        @Override
        protected Void doInBackground(Void... arg0) {
            // build the desired URL from the base and including the logged in user and review ID
            String loggedInUser = session.getUserID();
            String baseUrl = "http://stately-list-96223.appspot.com/users/";
            String theUrl = baseUrl + loggedInUser + "/reviews/" + reviewID;

            try {
                URL url = new URL(theUrl);
                HttpURLConnection conn = (HttpURLConnection) url.openConnection();

                try {
                    conn.setReadTimeout(10000);    // milliseconds
                    conn.setConnectTimeout(15000);
                    conn.setRequestMethod("DELETE");
                    // make it accept JSON
                    conn.setRequestProperty("Accept", "application/json");
                    conn.setDoInput(true);
                    // Start the query
                    conn.connect();

                    // read the response
                    responseCode = conn.getResponseCode();
                    if (responseCode == 200) {
                        InputStream in = conn.getInputStream();
                        response = SchoolsActivity.IStoString(in);
                    } else {    // if a 400-type response code, have to read from error stream
                        InputStream in = conn.getErrorStream();
                        response = SchoolsActivity.IStoString(in);
                    }
                    // Make sure disconnect when done
                } finally {
                    conn.disconnect();
                }
            } catch (IOException e) {
                response = e.toString();
            }


            return null;
        }

        //onPostExecute happens after
        @Override
        protected void onPostExecute(Void result) {
            super.onPostExecute(result);

            // toast and/or redirect based on success/failure
            if (responseCode == 200) {

                // use toast to notify of completion
                Context context = getApplicationContext();
                CharSequence text = "Review deleted successfully.";
                int duration = Toast.LENGTH_SHORT;
                Toast.makeText(context, text, duration).show();

                // then redirect to My Reviews page
                Intent intent = new Intent(ViewReviewActivity.this, MyReviewsActivity.class);
                startActivity(intent);
            }
            else {
                // use toast to notify of issue
                Context context = getApplicationContext();
                CharSequence text = "Unable to delete review. API response was: " + response;
                int duration = Toast.LENGTH_LONG;
                Toast.makeText(context, text, duration).show();
            }
        }

    }
}
