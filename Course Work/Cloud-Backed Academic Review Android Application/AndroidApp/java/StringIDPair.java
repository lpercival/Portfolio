package cs496.finalproject;

/**
 * Used to allow the school spinner to display some information and store the ID in the background.
 * Help from: http://stackoverflow.com/questions/2942132/setting-values-and-display-text-in-android-spinner
 */
public class StringIDPair {
    private String text;
    private String ID;
    public StringIDPair(String text, String ID) {
        this.text = text;
        this.ID = ID;
    }
    public String getText() {
        return text;
    }
    public String getID() {
        return ID;
    }
    @Override
    public String toString() {
        return getText();
    }

    // Override equals so getPosition() in LoadReview works
    // help from: http://www.javaworld.com/article/2072762/java-app-dev/object-equality.html
    @Override
    public boolean equals(Object other) {
        if (other == this) return true;
        if (other == null) return false;
        if (getClass() != other.getClass()) return false;
        StringIDPair pair = (StringIDPair)other;
        return ((text == pair.text || (text != null && text.equals(pair.text))) &&
                (ID == pair.ID || (ID != null && ID.equals(pair.ID))));
    }

    // also supposed to override hashCode so matches with equals
    @Override
    public int hashCode() {
        return (text  == null ? 17 : text.hashCode()) ^ (ID == null ? 31 : ID.hashCode());
    }
}
