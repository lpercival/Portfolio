package cs496.finalproject;

import android.app.Activity;
import android.app.ListActivity;
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
import android.widget.ListView;
import android.widget.SimpleAdapter;
import android.widget.TextView;
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

import android.widget.AdapterView;
import android.widget.AdapterView.OnItemClickListener;
import android.view.View;


public class MyReviewsActivity extends ListActivity {

    private ProgressDialog pDialog;
    private TextView textView;
    private SimpleAdapter adapter;

    // For telling whether someone's logged in
    SessionManager session;

    // ArrayList of HashMaps for reviews
    ArrayList<HashMap<String, String>> reviewList;

    // JSON Node names
    private static final String TAG_KEY = "key";
    private static final String TAG_TITLE = "title";
    private static final String TAG_SCHOOL = "school";
    private static final String TAG_RANKING="ranking";

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_my_reviews);

        // get the textView that shows when empty and set its text
        textView = (TextView) findViewById(android.R.id.empty);
        textView.setText("No reviews yet...");

        // set up Session class instance
        session = new SessionManager(getApplicationContext());
        // make sure someone's logged in and if not they'll be redirected
        session.checkLogin();

        // Make sure there's a network connection available
        ConnectivityManager connMgr = (ConnectivityManager) getSystemService(Context.CONNECTIVITY_SERVICE);
        NetworkInfo networkInfo = connMgr.getActiveNetworkInfo();
        if (networkInfo != null && networkInfo.isConnected())
        {
            // get review data
            new GetReviewsAPI().execute();
        }
        else
        {
            // use toast to notify of issue
            Context context = getApplicationContext();
            CharSequence text = "No internet connection available, cannot load data.";
            int duration = Toast.LENGTH_LONG;
            Toast.makeText(context, text, duration).show();
        }

        // set the ListView to use the review list
        reviewList = new ArrayList<HashMap<String, String>>();
        ListView lv = getListView();
        adapter = new SimpleAdapter(
                this, reviewList,
                R.layout.review_list_item, new String[] { TAG_TITLE, TAG_SCHOOL,
                TAG_RANKING }, new int[] { R.id.title,
                R.id.school, R.id.ranking });
        setListAdapter(adapter);

        // set it up to listen for clicks on an item and go to the appropriate View Review page
        // help from: http://www.androidhive.info/2011/10/android-listview-tutorial/
        // and http://www.androidhive.info/2012/01/android-json-parsing-tutorial/
        lv.setOnItemClickListener(new OnItemClickListener() {
            @Override
            public void onItemClick(AdapterView<?> parent, View view, int position, long id) {
                // get ID of selected review
                String reviewID = reviewList.get(position).get(TAG_KEY);

                // Launching new Activity on selecting single List Item
                Intent i = new Intent(getApplicationContext(), ViewReviewActivity.class);
                // sending data to new activity
                i.putExtra("reviewID", reviewID);
                startActivity(i);

            }
        });
    }


    @Override
    public boolean onCreateOptionsMenu(Menu menu) {
        // Inflate the menu; this adds items to the action bar if it is present.
        getMenuInflater().inflate(R.menu.menu_my_reviews, menu);
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


    /** Subclass uses AsyncTask to create a task away from the main UI thread. It takes the URL and
     * uses it to create an HttpUrlConnection. Once the connection's established, it downloads the
     * contents of the webpage as an InputStream. Then that's converted into an ArrayList of
     * HashMaps that's used to build the ListView.
     */
    private class GetReviewsAPI extends AsyncTask<Void, Void, Void> {
        // help from http://www.androidhive.info/2012/01/android-json-parsing-tutorial/
        // and developer.android.com/training/basics/network-ops/connecting.html

        // Show a progress dialog at first so it's not just a blank screen
        @Override
        protected void onPreExecute() {
            super.onPreExecute();
            // Showing progress dialog
            pDialog = new ProgressDialog(MyReviewsActivity.this);
            pDialog.setMessage("Please wait...");
            pDialog.setCancelable(false);
            pDialog.show();

        }

        @Override
        protected Void doInBackground(Void... arg0) {
            // build the desired URL from the base and including the logged in user
            String loggedInUser = session.getUserID();
            String baseUrl = "http://stately-list-96223.appspot.com/users/";
            String theUrl = baseUrl + loggedInUser + "/reviews";

            try {
                // Get the information
                downloadUrl(theUrl);
                //return null;
            } catch (IOException e) {
                textView.setText("Unable to retrieve web page. URL may be invalid");
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

            adapter.notifyDataSetChanged();
        }

        /** Given a URL, establishes a HttpUrlConnection and retrieves the content as an InputStream,
         * which it parses into the HashMap structure used to fill the ListView.
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
                    JSONArray reviews = new JSONArray(contentString);

                    // looping through all schools
                    for (int i = 0; i < reviews.length(); i++) {
                        JSONObject r = reviews.getJSONObject(i);

                        // get out the pieces
                        String key = r.getString(TAG_KEY);
                        String title = r.getString(TAG_TITLE);
                        String schoolKey = r.getString(TAG_SCHOOL);
                        String ranking = r.getString(TAG_RANKING);

                        // convert school key to its name before add to HashMap
                        // make the helper
                        SchoolsSaverHelper dbHelper = new SchoolsSaverHelper(MyReviewsActivity.this);
                        // get the data repository in read mode
                        SQLiteDatabase db = dbHelper.getReadableDatabase();
                        // define a projection that specifies which columns will use after query
                        String[] projection = {SchoolsSaverHelper.COL_NAME};
                        // set up the WHERE clause
                        String selection = SchoolsSaverHelper.COL_KEY + "=?";
                        String[] selectionArgs = {schoolKey};
                        // set the sort order
                        String sortOrder = SchoolsSaverHelper.COL_NAME + " ASC";
                        // make the query, whose results're returned in Cursor object
                        Cursor c = db.query(SchoolsSaverHelper.TABLE, projection, selection, selectionArgs, null, null, sortOrder);
                        c.moveToFirst();
                        String school = c.getString(c.getColumnIndex(SchoolsSaverHelper.COL_NAME));

                        // temp HashMap for single review
                        HashMap<String, String> review = new HashMap<String, String>();

                        // add each child node to HashMap key => value
                        review.put(TAG_KEY, key);
                        review.put(TAG_TITLE, title);
                        review.put(TAG_SCHOOL, getString(R.string.school_label_view) + school);
                        review.put(TAG_RANKING, getString(R.string.ranking_label_view) + ranking);

                        // then add the review to the review list
                        reviewList.add(review);
                    }
                } catch (JSONException e) {
                    e.printStackTrace();
                }
            }
        }
    }
}
