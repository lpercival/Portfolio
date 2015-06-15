package cs496.finalproject;

import android.app.Activity;
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


public class AddSchoolActivity extends Activity implements AdapterView.OnItemSelectedListener {

    private EditText nameText;
    private EditText cityText;
    private Spinner stateSpinner;
    private String selectedState;

    // For checking whether someone's logged in (only affects action bar menu)
    SessionManager session;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_add_school);

        // make session manager for checking login
        session = new SessionManager(getApplicationContext());

        // get the Views accessible
        nameText = (EditText) findViewById(R.id.name);
        cityText = (EditText) findViewById(R.id.city);

        // Set up the state dropdown
        stateSpinner = (Spinner) findViewById(R.id.state);
        // Create ArrayAdapter using string array and default spinner layout
        ArrayAdapter<CharSequence> adapter = ArrayAdapter.createFromResource(this,
                R.array.state_options, android.R.layout.simple_spinner_item);
        // Specify layout to use when list of choices appears
        adapter.setDropDownViewResource(android.R.layout.simple_spinner_dropdown_item);
        // Apply adapter to spinner
        stateSpinner.setAdapter(adapter);
        stateSpinner.setOnItemSelectedListener(this);
    }


    @Override
    public boolean onCreateOptionsMenu(Menu menu) {
        // Inflate the menu; this adds items to the action bar if it is present.
        // will look different depending on whether someone's logged in
        if (session.isLoggedIn()) {
            getMenuInflater().inflate(R.menu.menu_add_school_logged_in, menu);
        }
        else {
            getMenuInflater().inflate(R.menu.menu_add_school, menu);
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
            // Pass the schoolList to be used to populate the schools dropdown
            //intent.putExtra("schools", schoolList);
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

    /** These two functions are used (required) to implement the spinner dropdown. */
    public void onItemSelected(AdapterView<?> parent, View view, int pos, long id) {
        // just get the selection into the variable
        selectedState = (String)parent.getItemAtPosition(pos);
    }

    public void onNothingSelected(AdapterView<?> parent) {

    }

    /** Called when user clicks button to submit/ add school */
    public void AddSchool(View view) {
        // Make sure there's a network connection available
        ConnectivityManager connMgr = (ConnectivityManager) getSystemService(Context.CONNECTIVITY_SERVICE);
        NetworkInfo networkInfo = connMgr.getActiveNetworkInfo();
        if (networkInfo != null && networkInfo.isConnected())
        {
            // submit the new school, call the AsyncTask class
            new MakeSchool().execute();
        }
        else
        {
            // use toast to notify of issue
            Context context = getApplicationContext();
            CharSequence text = "No network connection available, cannot submit school.";
            int duration = Toast.LENGTH_LONG;
            Toast.makeText(context, text, duration).show();
        }
    }


    /** Subclass uses AsyncTask to create a task away from the main UI thread. It creates an
     *  HttpUrlConnection, tells it to use POST, sets the header to accept JSON, and provides the
     *  data to POST (based on the form fields) to the school creation URL.
     */
    private class MakeSchool extends AsyncTask<Void, Void, Void> {
        // help from: http://digitallibraryworld.com/?p=189

        private int responseCode;
        private String response="";
        private String data;

        /** This is where the actual work is done.
         */
        @Override
        protected Void doInBackground(Void... arg0) {
            String myUrl = "http://stately-list-96223.appspot.com/schools";

            try {
                // build the data parameters to send
                data = "name=" + URLEncoder.encode(nameText.getText().toString(), "UTF-8") +
                        "&city=" + URLEncoder.encode(cityText.getText().toString(), "UTF-8") +
                        "&state=" + URLEncoder.encode(selectedState, "UTF-8");

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

            // make a toast depending on whether or not the school was added
            if (responseCode == 200) {
                Context context = getApplicationContext();
                CharSequence text = "School added successfully.";
                int duration = Toast.LENGTH_SHORT;
                Toast.makeText(context, text, duration).show();
                // Go to Ranked Schools page
                Intent intent = new Intent(AddSchoolActivity.this, SchoolsActivity.class);
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
