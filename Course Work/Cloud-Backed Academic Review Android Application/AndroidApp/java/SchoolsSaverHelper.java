package cs496.finalproject;

import android.content.Context;
import android.database.sqlite.SQLiteDatabase;
import android.database.sqlite.SQLiteOpenHelper;

/**
 * help from: https://developer.android.com/training/basics/data-storage/database.html
 * This table will just hold a series of ID-name pairs for schools, used to populate dropdowns.
 */
public class SchoolsSaverHelper extends SQLiteOpenHelper {

    // set up constants for the table name and columns
    public static final String TABLE = "schools";
    public static final String COL_ID = "id";
    public static final String COL_NAME = "name";
    public static final String COL_KEY = "myKey";

    // set up constants for methods
    private static final String CREATE_TABLE = "CREATE TABLE " + TABLE + " (" + COL_ID + " INTEGER PRIMARY KEY, " +
            COL_NAME + " TEXT, " + COL_KEY + " TEXT);";
    private static final String DELETE_TABLE = "DROP TABLE IF EXISTS " + TABLE;

    public static final int DB_VERSION = 1;
    public static final String DB_NAME = "SchoolList.db";

    public SchoolsSaverHelper(Context context) {
        super(context, DB_NAME, null, DB_VERSION);
    }

    @Override
    public void onCreate(SQLiteDatabase db) {
        db.execSQL(CREATE_TABLE);
    }

    @Override
    public void onUpgrade(SQLiteDatabase db, int oldVersion, int newVersion) {
        // I'm only using to cache, so just discard and re-create
        db.execSQL(DELETE_TABLE);
        onCreate(db);
    }
}
