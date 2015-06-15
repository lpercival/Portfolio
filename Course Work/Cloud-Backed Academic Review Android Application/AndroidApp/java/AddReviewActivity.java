package cs496.finalproject;

import android.app.Activity;
import android.app.DialogFragment;
import android.content.ContentValues;
import android.content.Context;
import android.content.Intent;
import android.database.Cursor;
import android.database.sqlite.SQLiteDatabase;
import android.net.ConnectivityManager;
import android.net.NetworkInfo;
import android.os.AsyncTask;
import android.os.Bundle;
import android.view.Menu;
import android.view.MenuItem;
import android.view.View;
import android.widget.AdapterView;
import android.widget.ArrayAdapter;
import android.widget.Button;
import android.widget.EditText;
import android.widget.Spinner;
import android.widget.Toast;

import java.io.IOException;
import java.io.InputStream;
import java.io.PrintWriter;
import java.net.HttpURLConnection;
import java.net.URL;
import java.net.URLEncoder;
import java.util.ArrayList;
import java.util.HashMap;


public class AddReviewActivity extends Activity implements AdapterView.OnItemSelectedListener {

    private EditText titleText;
    private EditText descrText;
    private Button picker;
    private Spinner schoolSpinner;
    private Spinner rankSpinner;
    //ArrayList<HashMap<String, String>> schoolList;

    private String selectedSchool;
    private String selectedSchoolID;
    private String selectedRanking;

    // For checking login
    SessionManager session;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_add_review);

        // make session manager for checking login
        session = new SessionManager(getApplicationContext());

        // make sure someone's logged in and if not they'll be redirected
        session.checkLogin();

        // get the Views accessible
        picker = (Button) findViewById(R.id.date);
        titleText = (EditText) findViewById(R.id.title);
        descrText = (EditText) findViewById(R.id.description);

        // Set up the ranking dropdown
        rankSpinner = (Spinner) findViewById(R.id.ranking);
        // Create ArrayAdapter using string array and default spinner layout
        ArrayAdapter<CharSequence> adapter = ArrayAdapter.createFromResource(this,
                R.array.ranking_options, android.R.layout.simple_spinner_item);
        // Specify layout to use when list of choices appears
        adapter.setDropDownViewResource(android.R.layout.simple_spinner_dropdown_item);
        // Apply adapter to spinner
        rankSpinner.setAdapter(adapter);
        rankSpinner.setOnItemSelectedListener(this);


        // Set up the school dropdown
        schoolSpinner = (Spinner) findViewById(R.id.school);
        // Get the pertinent data from what was passed with the intent, shape it
        //Intent intent = getIntent();
        //schoolList = (ArrayList<HashMap<String, String>>) intent.getSerializableExtra("schools");
        // Get the pertinent data from the SQLite cache table
        // make the helper
        SchoolsSaverHelper dbHelper = new SchoolsSaverHelper(this);
        // get the data repository in read mode
        SQLiteDatabase db = dbHelper.getReadableDatabase();
        // define a projection that specifies which columns will use after query
        String[] projection = {SchoolsSaverHelper.COL_NAME, SchoolsSaverHelper.COL_KEY};
        // set the sort order - case-insensitive
        String sortOrder = SchoolsSaverHelper.COL_NAME + " COLLATE NOCASE ASC";
        // make the query, whose results're returned in Cursor object
        Cursor c = db.query(SchoolsSaverHelper.TABLE, projection, null, null, null, null, sortOrder);
        // get the data out of the cursor and fill in an array of type StringIDPair that will be
        // used in spinner
        String savedKey;
        String savedName;
        StringIDPair[] schoolData = new StringIDPair[c.getCount()];
        int i = 0;
        c.moveToFirst();
        while (c.isAfterLast() == false)
        {
            savedKey = c.getString(c.getColumnIndex(SchoolsSaverHelper.COL_KEY));
            savedName = c.getString(c.getColumnIndex(SchoolsSaverHelper.COL_NAME));
            schoolData[i] = new StringIDPair(savedName, savedKey);
            c.moveToNext();
            i++;
        }
        // Create Array Adapter and apply it to spinner
        ArrayAdapter<StringIDPair> adapter2 = new ArrayAdapter<StringIDPair>(this, android.R.layout.simple_spinner_item, schoolData);
        adapter2.setDropDownViewResource(android.R.layout.simple_spinner_dropdown_item);
        schoolSpinner.setAdapter(adapter2);
        schoolSpinner.setOnItemSelectedListener(this);

    }

    @Override
    public boolean onCreateOptionsMenu(Menu menu) {
        // Inflate the menu; this adds items to the action bar if it is present.
        getMenuInflater().inflate(R.menu.menu_add_review, menu);
        return true;
    }

    @Override
    public boolean onOptionsItemSelected(MenuItem item) {
        // Handle action bar item clicks here. The action bar will
        // automatically handle clicks on the Home/Up button, so long
        // as you specify a parent activity in AndroidManifest.xml.
        int id = item.getItemId();

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
        //If they chose to log out, do so
        if (id == R.id.logout) {
            session.logoutUser();
        }

        // default
        return super.onOptionsItemSelected(item);
    }

    /** To make the date picker work */
    public void showDatePickerDialog(View v) {
        DialogFragment newFragment = new DatePickerFragment();
        newFragment.show(getFragmentManager(), "datePicker");
    }

    /** Called once the date is set in the date picker to store the values */
    public void saveDate(int year, int month, int day) {
        // Update the date picker's text to be the selected date
        month += 1;     //stored counting from 0
        picker.setText(year + "-" + month + "-" + day);
    }

    /** These two functions are used (required) to implement the spinner dropdowns. */
    public void onItemSelected(AdapterView<?> parent, View view, int pos, long id) {
        // behavior depends on which spinner
        switch(parent.getId()) {
            case R.id.school:
                selectedSchool = ((StringIDPair)parent.getItemAtPosition(pos)).toString();
                selectedSchoolID = ((StringIDPair)parent.getItemAtPosition(pos)).getID();
                break;
            case R.id.ranking:
                selectedRanking = (String)parent.getItemAtPosition(pos);
                break;
        }
    }

    public void onNothingSelected(AdapterView<?> parent) {

    }

    /** Called when user clicks button to save local draft */
    public void saveDraft(View view) {
        // call the AsyncTask class
        new SaveReview().execute();
    }

    /** Called when user clicks button to load local draft */
    public void loadDraft(View view) {
        // call the AsyncTask class
        new LoadReview().execute();
    }

    /** Called when user clicks button to submit review */
    public void submitReview(View view) {
        // Make sure there's a network connection available
        ConnectivityManager connMgr = (ConnectivityManager) getSystemService(Context.CONNECTIVITY_SERVICE);
        NetworkInfo networkInfo = connMgr.getActiveNetworkInfo();
        if (networkInfo != null && networkInfo.isConnected())
        {
            // submit the new review, call the AsyncTask class
            new MakeReview().execute();
        }
        else
        {
            // use toast to notify of issue
            Context context = getApplicationContext();
            CharSequence text = "No network connection available, cannot submit review.";
            int duration = Toast.LENGTH_LONG;
            Toast.makeText(context, text, duration).show();
        }

    }

    /** Subclass uses AsyncTask to create a task away from the main UI thread. It creates an
     *  HttpUrlConnection, tells it to use POST, sets the header to accept JSON, and provides the
     *  data to POST (based on the form fields) to the review creation URL.
     */
    private class MakeReview extends AsyncTask<Void, Void, Void> {
        // help from: http://digitallibraryworld.com/?p=189

        private int responseCode;
        private String responseMsg;
        private String response="";
        private String data;

        /** This is where the actual work is done.
         */
        @Override
        protected Void doInBackground(Void... arg0) {
            // build the desired URL from the base and including the logged in user
            String loggedInUser = session.getUserID();
            String baseUrl = "http://stately-list-96223.appspot.com/users/";
            //String baseUrl = "http://data-sunlight-93619.appspot.com/schools/";
            String myUrl = baseUrl + loggedInUser + "/reviews";

            try {
                // build the data parameters to send
                data = "title=" + URLEncoder.encode(titleText.getText().toString(), "UTF-8") +
                        "&description=" + URLEncoder.encode(descrText.getText().toString(), "UTF-8") +
                        "&date=" + URLEncoder.encode(picker.getText().toString(), "UTF-8") +
                        "&ranking=" + URLEncoder.encode(selectedRanking, "UTF-8") +
                        "&school=" + URLEncoder.encode(selectedSchoolID, "UTF-8");

                URL url = new URL(myUrl);
                HttpURLConnection conn = (HttpURLConnection) url.openConnection();

                try {
                    conn.setReadTimeout(10000);    // milliseconds
                    conn.setConnectTimeout(15000);
                    conn.setRequestMethod("POST");
                    conn.setDoOutput(true);
                    // make it accept JSON
                    conn.setRequestProperty("Accept", "application/json");
                    // provide the data
                    conn.setFixedLengthStreamingMode(data.getBytes().length);
                    // send the POST
                    PrintWriter out = new PrintWriter(conn.getOutputStream());
                    out.print(data);
                    out.close();

                    // read the response
                    responseCode = conn.getResponseCode();
                    responseMsg = conn.getResponseMessage();
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

        @Override
        protected void onPostExecute(Void result) {
            super.onPostExecute(result);

            // make a toast depending on whether or not the review was added
            if (responseCode == 200) {
                Context context = getApplicationContext();
                CharSequence text = "Review added successfully.";
                int duration = Toast.LENGTH_SHORT;
                Toast.makeText(context, text, duration).show();
                // Go to My Reviews page
                Intent intent = new Intent(AddReviewActivity.this, MyReviewsActivity.class);
                startActivity(intent);
            }
            else {
                // notify of issue
                Context context = getApplicationContext();
                CharSequence text = response;
                int duration = Toast.LENGTH_LONG;
                Toast.makeText(context, text, duration).show();
            }
        }
    }

    /** Subclass uses AsyncTask to work with the database outside the main thread. Uses a
     * DraftSaverHelper object to get a writable database and insert values.
     */
    private class SaveReview extends AsyncTask<Void, Void, Void> {
        /** This is where the actual work is done.
         */
        @Override
        protected Void doInBackground(Void... arg0) {
            String loggedInUser = session.getUserID();
            // make the helper
            DraftSaverHelper dbHelper = new DraftSaverHelper(AddReviewActivity.this);
            // get the data repository in write mode
            SQLiteDatabase db = dbHelper.getWritableDatabase();

            // make a map of values, where column names are the keys
            ContentValues values = new ContentValues();
            values.put(DraftSaverHelper.COL_ID, 1);
            values.put(DraftSaverHelper.COL_TITLE, titleText.getText().toString());
            values.put(DraftSaverHelper.COL_DESCR, descrText.getText().toString());
            values.put(DraftSaverHelper.COL_SCHOOL, selectedSchool);
            values.put(DraftSaverHelper.COL_SCHOOL_ID, selectedSchoolID);
            values.put(DraftSaverHelper.COL_DATE, picker.getText().toString());
            values.put(DraftSaverHelper.COL_RANK, selectedRanking);
            values.put(DraftSaverHelper.COL_USER, loggedInUser);

            // clear out anything that might be there, since only storing 1 draft
            db.delete(DraftSaverHelper.TABLE, null, null);
            // insert the new row, returning its primary key value
            long newRowId;
            newRowId = db.insert(DraftSaverHelper.TABLE, null, values);

            return null;
        }

        /** Indicate the save is complete with a toast. */
        @Override
        protected void onPostExecute(Void result) {
            super.onPostExecute(result);

            Context context = getApplicationContext();
            CharSequence text = "Draft review saved locally.";
            int duration = Toast.LENGTH_LONG;
            Toast.makeText(context, text, duration).show();
        }
    }

    /** Subclass uses AsyncTask to work with the database outside the main thread. Uses a
     * DraftSaverHelper object to get a readable database and query the values, then uses them
     * to populate the form.
     */
    private class LoadReview extends AsyncTask<Void, Void, Void> {
        // saved variables
        private String savedTitle;
        private String savedDescr;
        private String savedSchool;
        private String savedSchoolID;
        private String savedDate;
        private String savedRank;

        //to mark whether the query returns an empty result
        private boolean emptyResult = false;

        /** This is where the data is actually obtained.
         */
        @Override
        protected Void doInBackground(Void... arg0) {
            String loggedInUser = session.getUserID();
            // make the helper
            DraftSaverHelper dbHelper = new DraftSaverHelper(AddReviewActivity.this);
            // get the data repository in read mode
            SQLiteDatabase db = dbHelper.getReadableDatabase();

            // define a projection that specifies which columns will use after query
            String[] projection = {DraftSaverHelper.COL_TITLE, DraftSaverHelper.COL_DESCR,
                    DraftSaverHelper.COL_SCHOOL, DraftSaverHelper.COL_SCHOOL_ID,
                    DraftSaverHelper.COL_DATE, DraftSaverHelper.COL_RANK};
            // set up the WHERE clause - can only see the current draft if it's yours
            String selection = DraftSaverHelper.COL_USER + "=?";
            String[] selectionArgs = {loggedInUser};
            // set the sort order
            String sortOrder = DraftSaverHelper.COL_ID + " DESC";

            // make the query, whose results're returned in Cursor object
            Cursor c = db.query( DraftSaverHelper.TABLE, projection, selection, selectionArgs, null, null, sortOrder);

            // get the data out of the cursor, but only if there was a result to look at
            if (c.getCount() > 0) {
                c.moveToFirst();
                savedTitle = c.getString(c.getColumnIndex(DraftSaverHelper.COL_TITLE));
                savedDescr = c.getString(c.getColumnIndex(DraftSaverHelper.COL_DESCR));
                savedSchool = c.getString(c.getColumnIndex(DraftSaverHelper.COL_SCHOOL));
                savedSchoolID = c.getString(c.getColumnIndex(DraftSaverHelper.COL_SCHOOL_ID));
                savedDate = c.getString(c.getColumnIndex(DraftSaverHelper.COL_DATE));
                savedRank = c.getString(c.getColumnIndex(DraftSaverHelper.COL_RANK));
            }
            else {
                emptyResult = true;
            }

            return null;
        }

        /** Populate the form with the data obtained. Plus do a toast for good measure. */
        @Override
        protected void onPostExecute(Void result) {
            super.onPostExecute(result);

            // fill in the form values - if the query returned data
            if (!emptyResult) {
                titleText.setText(savedTitle);
                descrText.setText(savedDescr);
                StringIDPair school = new StringIDPair(savedSchool, savedSchoolID);
                // help from: http://stackoverflow.com/questions/11072576/set-selected-item-of-spinner-programmatically
                int schoolPos = 3;
                schoolPos = ((ArrayAdapter<StringIDPair>) schoolSpinner.getAdapter()).getPosition(school);
                // just in case it can't find it for some reason, then default to showing the first option
                if (schoolPos < 0) {
                    schoolPos = 0;
                }
                schoolSpinner.setSelection(schoolPos);
                picker.setText(savedDate);
                int rankPos = ((ArrayAdapter<String>) rankSpinner.getAdapter()).getPosition(savedRank);
                // just in case it can't find it for some reason, then default to showing the first option
                if (rankPos < 0) {
                    rankPos = 0;
                }
                rankSpinner.setSelection(rankPos);

                // do the toast so they know it's done
                Context context = getApplicationContext();
                CharSequence text = "Draft review loaded.";
                int duration = Toast.LENGTH_LONG;
                Toast.makeText(context, text, duration).show();
            }
            else {
                // do a toast saying there was no draft to load
                Context context = getApplicationContext();
                CharSequence text = "You don't have a draft saved.";
                int duration = Toast.LENGTH_LONG;
                Toast.makeText(context, text, duration).show();
            }
        }
    }


}
