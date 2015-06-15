package cs496.finalproject;

import android.app.Activity;
import android.app.DialogFragment;
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


public class EditReviewActivity extends Activity implements AdapterView.OnItemSelectedListener {

    // view elements
    private EditText titleText;
    private EditText descrText;
    private Button picker;
    private Spinner schoolSpinner;
    private Spinner rankSpinner;

    // selected choices
    private String selectedSchool;
    private String selectedSchoolID;
    private String selectedRanking;

    private String reviewID;
    // previously-set values, used to auto-populate
    private String oldTitle;
    private String oldDescription;
    private String oldDate;
    private String oldSchool;   // ID
    private String oldSchoolName;
    private String oldRanking;

    // For checking login
    SessionManager session;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_edit_review);

        // make session manager for checking login
        session = new SessionManager(getApplicationContext());
        // make sure someone's logged in and if not they'll be redirected
        session.checkLogin();

        // Get the review ID and current field values from the intent
        Intent intent = getIntent();
        reviewID = intent.getStringExtra("reviewID");
        oldTitle = intent.getStringExtra("title");
        oldDescription = intent.getStringExtra("description");
        oldDate = intent.getStringExtra("date");
        oldSchool = intent.getStringExtra("school");
        oldSchoolName = intent.getStringExtra("schoolName");
        oldRanking = intent.getStringExtra("ranking");

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
        // Get the pertinent data from the SQLite cache table
        // make the helper
        SchoolsSaverHelper dbHelper = new SchoolsSaverHelper(this);
        // get the data repository in read mode
        SQLiteDatabase db = dbHelper.getReadableDatabase();
        // define a projection that specifies which columns will use after query
        String[] projection = {SchoolsSaverHelper.COL_NAME, SchoolsSaverHelper.COL_KEY};
        // set the sort order
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

        // auto-populate all the form fields with their current/old values
        titleText.setText(oldTitle);
        descrText.setText(oldDescription);
        StringIDPair school = new StringIDPair(oldSchoolName, oldSchool);
        // help from: http://stackoverflow.com/questions/11072576/set-selected-item-of-spinner-programmatically
        int schoolPos = ((ArrayAdapter<StringIDPair>) schoolSpinner.getAdapter()).getPosition(school);
        // just in case it can't find it for some reason, then default to showing the first option
        if (schoolPos < 0) {
            schoolPos = 0;
        }
        schoolSpinner.setSelection(schoolPos);
        picker.setText(oldDate);
        int rankPos = ((ArrayAdapter<String>) rankSpinner.getAdapter()).getPosition(oldRanking);
        // just in case it can't find it for some reason, then default to showing the first option
        if (rankPos < 0) {
            rankPos = 0;
        }
        rankSpinner.setSelection(rankPos);
    }


    @Override
    public boolean onCreateOptionsMenu(Menu menu) {
        // Inflate the menu; this adds items to the action bar if it is present.
        getMenuInflater().inflate(R.menu.menu_edit_review, menu);
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

    /** Called when user clicks button to save changes to review */
    public void SaveReview(View view) {
        // Make sure there's a network connection available
        ConnectivityManager connMgr = (ConnectivityManager) getSystemService(Context.CONNECTIVITY_SERVICE);
        NetworkInfo networkInfo = connMgr.getActiveNetworkInfo();
        if (networkInfo != null && networkInfo.isConnected())
        {
            // submit the new review, call the AsyncTask class
            new PutReview().execute();
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
     *  HttpUrlConnection, tells it to use PUT, sets the header to accept JSON, and provides the
     *  data to PUT (based on the form fields) to the review's URL.
     */
    private class PutReview extends AsyncTask<Void, Void, Void> {

        private int responseCode;
        private String response="";
        private String data;

        /** This is where the actual work is done.
         */
        @Override
        protected Void doInBackground(Void... arg0) {
            // build the desired URL from the base and including the logged in user and review ID
            String loggedInUser = session.getUserID();
            String baseUrl = "http://stately-list-96223.appspot.com/users/";
            String myUrl = baseUrl + loggedInUser + "/reviews/" + reviewID;

            try {
                // build the data parameters to send
                data = "title=" + URLEncoder.encode(titleText.getText().toString(), "UTF-8") +
                        "&description=" + URLEncoder.encode(descrText.getText().toString(), "UTF-8") +
                        "&date=" + URLEncoder.encode(picker.getText().toString(), "UTF-8") +
                        "&ranking=" + URLEncoder.encode(selectedRanking, "UTF-8") +
                        "&school=" + URLEncoder.encode(selectedSchoolID, "UTF-8") +
                        "&oldSchool=" + URLEncoder.encode(oldSchool, "UTF-8") +
                        "&oldRanking=" + URLEncoder.encode(oldRanking, "UTF-8");

                URL url = new URL(myUrl);
                HttpURLConnection conn = (HttpURLConnection) url.openConnection();

                try {
                    conn.setReadTimeout(10000);    // milliseconds
                    conn.setConnectTimeout(15000);
                    conn.setDoOutput(true);
                    conn.setRequestMethod("PUT");
                    // make it accept JSON
                    conn.setRequestProperty("Accept", "application/json");
                    // provide the data
                    conn.setFixedLengthStreamingMode(data.getBytes().length);
                    // send the PUT
                    PrintWriter out = new PrintWriter(conn.getOutputStream());
                    out.print(data);
                    out.close();

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

        @Override
        protected void onPostExecute(Void result) {
            super.onPostExecute(result);

            // make a toast depending on whether or not the review was added
            if (responseCode == 200) {
                Context context = getApplicationContext();
                CharSequence text = "Review changed successfully.";
                int duration = Toast.LENGTH_SHORT;
                Toast.makeText(context, text, duration).show();
                // Go to My Reviews page
                Intent intent = new Intent(EditReviewActivity.this, MyReviewsActivity.class);
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
}
