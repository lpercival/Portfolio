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
import android.widget.EditText;
import android.widget.TextView;
import android.widget.Toast;

import org.json.JSONException;
import org.json.JSONObject;

import java.io.IOException;
import java.io.InputStream;
import java.io.PrintWriter;
import java.net.HttpURLConnection;
import java.net.URL;
import java.net.URLEncoder;

import cs496.finalproject.SessionManager;


public class SignUpActivity extends Activity {

    private EditText usernameText;
    private EditText pwdText;

    // For logging in
    SessionManager session;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_sign_up);

        // get the Views accessible
        usernameText = (EditText) findViewById(R.id.username);
        pwdText = (EditText) findViewById(R.id.pwd);

        // make session manager for logging in
        session = new SessionManager(getApplicationContext());
    }

    /** Called when user clicks button to create the new account*/
    public void CreateAccount(View view) {
        // Make sure there's a network connection available
        ConnectivityManager connMgr = (ConnectivityManager) getSystemService(Context.CONNECTIVITY_SERVICE);
        NetworkInfo networkInfo = connMgr.getActiveNetworkInfo();
        if (networkInfo != null && networkInfo.isConnected())
        {
            // create the new account, call the AsyncTask class
            new MakeAccount().execute();
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
    private class MakeAccount extends AsyncTask<Void, Void, Void> {
        // help from: http://digitallibraryworld.com/?p=189

        private int responseCode;
        private String responseMsg;
        private String response="";
        private String data;

        /** This is where the actual work is done.
         */
        @Override
        protected Void doInBackground(Void... arg0) {
            String myUrl = "http://stately-list-96223.appspot.com/users";

            try {
                // build the data parameters to send
                data = "username=" + URLEncoder.encode(usernameText.getText().toString(), "UTF-8") +
                        "&pwd=" + URLEncoder.encode(pwdText.getText().toString(), "UTF-8");

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

            // toast and/or login/ redirect based on success/failure
            if (responseCode == 200) {

                // use toast to notify of completion
                Context context = getApplicationContext();
                CharSequence text = "New account added successfully.";
                int duration = Toast.LENGTH_SHORT;
                Toast.makeText(context, text, duration).show();

                // log them in - get the ID out of response and save it in shared preferences
                String newUserID = "";
                try {
                    JSONObject user = new JSONObject(response);
                    newUserID = user.getString("key");
                } catch (JSONException e) {
                    e.printStackTrace();
                }
                // save it
                session.createLoginSession(newUserID);

                // then redirect to Schools page
                Intent intent = new Intent(SignUpActivity.this, SchoolsActivity.class);
                startActivity(intent);
            }
            else {
                // use toast to notify of issue
                Context context = getApplicationContext();
                CharSequence text = response;
                int duration = Toast.LENGTH_LONG;
                Toast.makeText(context, text, duration).show();
            }
        }
    }


}
