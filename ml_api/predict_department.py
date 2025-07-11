import sys
import joblib
from sentence_transformers import SentenceTransformer
import re
import nltk
from nltk.corpus import stopwords
import os

# Get the directory of the current script
script_dir = os.path.dirname(os.path.abspath(__file__))

# Download NLTK stopwords once (optional)
try:
    nltk.download('stopwords', quiet=True)
    stop_words = set(stopwords.words('english'))
except:
    stop_words = set()

# Load model components once with full path
try:
    model = joblib.load(os.path.join(script_dir, "svm_book_classifier.pkl"))
    le = joblib.load(os.path.join(script_dir, "label_encoder.pkl"))
    bert_model = SentenceTransformer('all-MiniLM-L6-v2')
except Exception as e:
    print(f"Error loading models: {e}")
    sys.exit(1)

# Clean text like you did during training
def clean_text(text):
    if not text:
        return ""
    text = text.lower()
    text = re.sub(r'[^a-zA-Z\s]', '', text)
    tokens = [w for w in text.split() if w not in stop_words and len(w) > 1]
    return " ".join(tokens)

# Entry point
if __name__ == "__main__":
    try:
        # Check if we have enough arguments
        if len(sys.argv) < 3:
            print("Error: Missing arguments. Usage: python predict_department.py 'book_name' 'book_summary'")
            sys.exit(1)

        # Get arguments from command line
        book_name = sys.argv[1]
        book_summary = sys.argv[2]

        # Validate inputs
        if not book_name or not book_summary:
            print("Error: Book name and summary cannot be empty")
            sys.exit(1)

        # Preprocess and predict
        full_text = clean_text(book_name + " " + book_summary)

        if not full_text:
            print("Error: No valid text after cleaning")
            sys.exit(1)

        vector = bert_model.encode([full_text])
        prediction = model.predict(vector)
        department = le.inverse_transform(prediction)[0]

        # Output the result (only the department name)
        print(department)

    except Exception as e:
        print(f"Error: {e}")
        sys.exit(1)
