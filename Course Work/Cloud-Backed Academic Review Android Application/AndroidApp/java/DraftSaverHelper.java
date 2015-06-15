package cs496.finalproject;

import android.content.Context;
import android.database.sqlite.SQLiteDatabase;
import android.database.sqlite.SQLiteOpenHelper;

/**
 * help from: https://developer.android.com/training/basics/data-storage/database.html
 */
public class DraftSaverHelper extends SQLiteOpenHelper {

    // set up constants for the table name and columns
    public static final String TABLE = "draft";
    public static final String COL_ID = "id";
    public static final String COL_TITLE = "title";
    public static final String COL_DESCR = "description";
    public static final String COL_SCHOOL = "school";
    public static final String COL_SCHOOL_ID = "school_id";
    public static final String COL_DATE = "date";
    public static final String COL_RANK = "ranking";
    public static final String COL_USER = "author";

    // set up constants for methods
    private static final String CREATE_TABLE = "CREATE TABLE " + TABLE + " (" + COL_ID + " INTEGER PRIMARY KEY, " +
            COL_TITLE + " TEXT, " + COL_DESCR + " TEXT, " + COL_SCHOOL + " TEXT, " + COL_SCHOOL_ID + " TEXT, " +
            COL_DATE + " TEXT, " + COL_USER + " TEXT, " + COL_RANK + " TEXT);";
    private static final String DELETE_TABLE = "DROP TABLE IF EXISTS " + TABLE;

    public static final int DB_VERSION = 1;
    public static final String DB_NAME = "DraftReview.db";

    public DraftSaverHelper(Context context) {
        super(context, DB_NAME, null, DB_VERSION);
    }

    @Override
    public void onCreate(SQLiteDatabase db) {
        db.execSQL(CREATE_TABLE);
    }

    @Override
    public void onUpgrade(SQLiteDatabase db, int oldVersion, int newVersion) {
        // I'm only going to store 1 draft, so just discard and re-create
        db.execSQL(DELETE_TABLE);
        onCreate(db);
    }
}
