package cs496.finalproject;

import android.app.Activity;
import android.app.DatePickerDialog;
import android.app.Dialog;
import android.app.DialogFragment;
import android.os.Bundle;
import android.widget.DatePicker;

import java.util.Calendar;

/**
 * Per developer.android.com/guide/topics/ui/controls/pickers.html
 */
public class DatePickerFragment extends DialogFragment implements DatePickerDialog.OnDateSetListener {
    @Override
    public Dialog onCreateDialog(Bundle savedInstanceState) {
        // Use the current date as the default date in the picker
        final Calendar c = Calendar.getInstance();
        int year = c.get(Calendar.YEAR);
        int month = c.get(Calendar.MONTH);
        int day = c.get(Calendar.DAY_OF_MONTH);

        // Create a new instance of DatePickerDialog and return it
        return new DatePickerDialog(getActivity(), this, year, month, day);
    }

    public void onDateSet(DatePicker view, int year, int month, int day) {
        // Have the Activity class update the date picker element, since it can do findViewById
        //AddReviewActivity ara = (AddReviewActivity) getActivity();
        //ara.saveDate(year, month, day);
        Activity a = getActivity();
        // but have to cast to correct specific Activity class to access its saveDate() function
        if (a instanceof AddReviewActivity) {
            AddReviewActivity ara = (AddReviewActivity) a;
            ara.saveDate(year, month, day);
        }
        else if (a instanceof EditReviewActivity) {
            EditReviewActivity era = (EditReviewActivity) a;
            era.saveDate(year, month, day);
        }
    }
}
