package cs496.finalproject;

import android.content.Context;
import android.content.Intent;
import android.content.SharedPreferences;

import cs496.finalproject.LoginActivity;
import cs496.finalproject.SchoolsActivity;

/**
 * Created by percy on 5/31/2015.
 * Help from: www.androidhive.info/2012/08/android-session-management-using-shared-preferences/
 */
public class SessionManager {
    // Shared Preferences
    SharedPreferences pref;
    // Editor for shared preferences
    SharedPreferences.Editor editor;
    // Context
    Context _context;

    // Shared preferences mode
    int PRIVATE_MODE = 0;
    // Shared preferences file name
    private static final String PREF_NAME = "LoginPref";
    // All Shared preferences keys
    private static final String IS_LOGIN = "IsLoggedIn";    // whether logged in
    public static final String KEY_ID = "userID";           // logged in user's ID

    // Constructor
    public SessionManager(Context context) {
        this._context = context;
        pref = _context.getSharedPreferences(PREF_NAME, PRIVATE_MODE);
        editor = pref.edit();
    }

    // Create a login session
    public void createLoginSession(String ID) {
        // Storing logged in value as True
        editor.putBoolean(IS_LOGIN, true);

        // Storing user ID in pref
        editor.putString(KEY_ID, ID);

        // commit changes
        editor.commit();
    }

    // Get stored session data on who's logged in
    public String getUserID() {
        return pref.getString(KEY_ID, null);  //2nd param is default if not there
    }

    // Call from other activities to determine whether someone's logged in and redirect to login if not
    public void checkLogin() {
        // Check login status
        if (!this.isLoggedIn()) {
            // redirect to login activity
            Intent i = new Intent(_context, LoginActivity.class);
            // close all the activities
            i.addFlags(Intent.FLAG_ACTIVITY_CLEAR_TOP);
            // add new flag to start new activity
            i.setFlags(Intent.FLAG_ACTIVITY_NEW_TASK);
            // start activity
            _context.startActivity(i);
        }
    }

    // Simply check whether or not someone is logged in, no redirection logic
    public boolean isLoggedIn() {
        return pref.getBoolean(IS_LOGIN, false);
    }

    // Log out by clearing all session data and redirecting to schools activity
    public void logoutUser() {
        // clear all data from Shared Preferences
        editor.clear();
        editor.commit();

        // then redirect to schools activity
        Intent i = new Intent(_context, SchoolsActivity.class);
        // close all the activities
        i.addFlags(Intent.FLAG_ACTIVITY_CLEAR_TOP);
        // add new flag to start new activity
        i.setFlags(Intent.FLAG_ACTIVITY_NEW_TASK);
        // start activity
        _context.startActivity(i);
    }

}
