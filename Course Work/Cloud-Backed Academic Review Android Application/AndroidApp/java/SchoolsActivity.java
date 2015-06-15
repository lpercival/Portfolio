package cs496.finalproject;

import android.app.ListActivity;
import android.app.ProgressDialog;
import android.content.ContentValues;
import android.content.Context;
import android.content.Intent;
import android.database.sqlite.SQLiteDatabase;
import android.net.ConnectivityManager;
import android.net.NetworkInfo;
import android.os.AsyncTask;
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

import java.io.BufferedReader;
import java.io.IOException;
import java.io.InputStream;
import java.io.InputStreamReader;
import java.net.HttpURLConnection;
import java.net.URL;
import java.util.ArrayList;
import java.util.HashMap;


public class SchoolsActivity extends ListActivity {

    private static final String theUrl = "http://stately-list-96223.appspot.com/schools";
    //private static final String theUrl = "http://data-sunlight-93619.appspot.com/schools";

    private ProgressDialog pDialog;
    private TextView textView;
    private SimpleAdapter adapter;

    // For telling whether someone's logged in
    SessionManager session;

    // ArrayList of HashMaps for schools
    ArrayList<HashMap<String, String>> schoolList;

    // JSON Node names
    private static final String TAG_KEY = "key";
    private static final String TAG_NAME = "name";
    private static final String TAG_CITY = "city";
    private static final String TAG_STATE = "state";
    private static final String TAG_CITY_STATE = "city_state";
    private static final String TAG_RANKING="avgRanking";

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_schools);
        // change title programmatically since it had to match app name in manifest
        this.setTitle(getString(R.string.schools_title));

        // get the textView that shows when empty and set its text
        textView = (TextView) findViewById(android.R.id.empty);
        textView.setText("No data yet...");

        // set up Session class instance
        session = new SessionManager(getApplicationContext());

        //Context context2 = getApplicationContext();
        //CharSequence text2;
        //if (session.isLoggedIn()) {
        //    text2 = "Someone's logged in:" + session.getUserID();
        //}
        //else {
        //    text2 = "Noone's logged in.";
        //}
        //int duration2 = Toast.LENGTH_LONG;
        //Toast.makeText(context2, text2, duration2).show();

        // Make sure there's a network connection available
        ConnectivityManager connMgr = (ConnectivityManager) getSystemService(Context.CONNECTIVITY_SERVICE);
        NetworkInfo networkInfo = connMgr.getActiveNetworkInfo();
        if (networkInfo != null && networkInfo.isConnected())
        {
            // get school data
            new GetSchoolsAPI().execute();
        }
        else
        {
            // use toast to notify of issue
            Context context = getApplicationContext();
            CharSequence text = "No internet connection available, cannot load data.";
            int duration = Toast.LENGTH_LONG;
            Toast.makeText(context, text, duration).show();
        }

        // set the ListView to use the school list
        schoolList = new ArrayList<HashMap<String, String>>();
        ListView lv = getListView();
        adapter = new SimpleAdapter(
                SchoolsActivity.this, schoolList,
                R.layout.school_list_item, new String[] { TAG_NAME, TAG_CITY_STATE,
                TAG_RANKING }, new int[] { R.id.name,
                R.id.city_state, R.id.ranking });
        setListAdapter(adapter);
    }

    @Override
    public boolean onCreateOptionsMenu(Menu menu) {
        // Inflate the menu; this adds items to the action bar if it is present.
        // will look different depending on whether someone's logged in
        if (session.isLoggedIn()) {
            getMenuInflater().inflate(R.menu.menu_schools_logged_in, menu);
        }
        else {
            getMenuInflater().inflate(R.menu.menu_schools, menu);
        }
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
            startActivity(intent);
            return true;
        }
        //If they chose to view their reviews, then start that activity
        if (id == R.id.my_reviews) {
            // Make an intent
            Intent intent = new Intent(this, MyReviewsActivity.class);
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
        //If they chose to log in, then start that activity
        if (id == R.id.login) {
            // Make an intent
            Intent intent = new Intent(this, LoginActivity.class);
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
    private class GetSchoolsAPI extends AsyncTask<Void, Void, Void> {
        // help from http://www.androidhive.info/2012/01/android-json-parsing-tutorial/
        // and developer.android.com/training/basics/network-ops/connecting.html

        // Show a progress dialog at first so it's not just a blank screen
        @Override
        protected void onPreExecute() {
            super.onPreExecute();
            // Showing progress dialog
            pDialog = new ProgressDialog(SchoolsActivity.this);
            pDialog.setMessage("Please wait...");
            pDialog.setCancelable(false);
            pDialog.show();

        }

        @Override
        protected Void doInBackground(Void... arg0) {
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

            // cache the returned set of reviews, at least their IDs and names, in SQLite
            new CacheSchools().execute();
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
                    contentString = IStoString(is);
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
                    JSONArray schools = new JSONArray(contentString);

                    // looping through all schools
                    for (int i = 0; i < schools.length(); i++) {
                        JSONObject s = schools.getJSONObject(i);

                        // get out the pieces
                        String key = s.getString(TAG_KEY);
                        String name = s.getString(TAG_NAME);
                        String city = s.getString(TAG_CITY);
                        String state = s.getString(TAG_STATE);
                        String ranking = s.getString(TAG_RANKING);

                        // temp HashMap for single contact
                        HashMap<String, String> school = new HashMap<String, String>();

                        // add each child node to HashMap key => value
                        school.put(TAG_KEY, key);
                        school.put(TAG_NAME, name);
                        if (city.equals("null")) {
                            school.put(TAG_CITY_STATE, state);
                        }
                        else {
                            school.put(TAG_CITY_STATE, city + ", " + state);
                        }
                        school.put(TAG_RANKING, getString(R.string.ranking_label_view) + ranking);

                        // then add the school to the school list
                        schoolList.add(school);
                    }
                } catch (JSONException e) {
                    e.printStackTrace();
                }
            }
        }
    }

    // Reads an InputStream and converts it to a String
    // from http://www.technotalkative.com/android-inputstream-to-string-conversion/
    public static String IStoString(InputStream is1)
    {
        BufferedReader rd = new BufferedReader(new InputStreamReader(is1), 4096);
        String line;
        StringBuilder sb =  new StringBuilder();
        try {
            while ((line = rd.readLine()) != null) {
                sb.append(line);
            }
            rd.close();

        } catch (IOException e) {
            e.printStackTrace();
        }
        String contentOfMyInputStream = sb.toString();
        return contentOfMyInputStream;
    }


    /** Subclass uses AsyncTask to work with the database outside the main thread. Uses a
     * SchoolsSaverHelper object to get a writable database and insert values. Stores a set of key-name
     * pairs to be used to populate school dropdowns.
     */
    private class CacheSchools extends AsyncTask<Void, Void, Void> {
        /** This is where the actual work is done.
         */
        @Override
        protected Void doInBackground(Void... arg0) {
            // make the helper
            SchoolsSaverHelper dbHelper = new SchoolsSaverHelper(SchoolsActivity.this);
            // get the data repository in write mode
            SQLiteDatabase db = dbHelper.getWritableDatabase();

            // clear out anything that might be there, to refresh cache
            db.delete(SchoolsSaverHelper.TABLE, null, null);

            // add the ID/key and name of each HashMap school in the schoolList as a row in SQLite
            long newRowId;
            for (HashMap<String, String> school : schoolList) {
                // make a map of values, where column names are the keys
                ContentValues values = new ContentValues();
                values.put(SchoolsSaverHelper.COL_NAME, school.get(TAG_NAME));
                values.put(SchoolsSaverHelper.COL_KEY, school.get(TAG_KEY));

                // insert the new row, returning its primary key value
                newRowId = db.insert(SchoolsSaverHelper.TABLE, null, values);
            }

            return null;
        }

        /** Indicate the save is complete with a toast. */
        @Override
        protected void onPostExecute(Void result) {
            super.onPostExecute(result);

            Context context = getApplicationContext();
            CharSequence text = "School list cached in SQLite.";
            int duration = Toast.LENGTH_SHORT;
            Toast.makeText(context, text, duration).show();
        }
    }
}
