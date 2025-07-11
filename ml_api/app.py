from flask import Flask, request, jsonify
import joblib
import re
import nltk
from nltk.corpus import stopwords
from sentence_transformers import SentenceTransformer

# Download stopwords if not already present
nltk.download('stopwords')
stop_words = set(stopwords.words('english'))

# Load saved model and label encoder
model = joblib.load('svm_book_classifier.pkl')
label_encoder = joblib.load('label_encoder.pkl')

# Load the sentence-transformer model (downloads once)
bert_model = SentenceTransformer('all-MiniLM-L6-v2')

# Create the Flask app
app = Flask(__name__)

# Text cleaning function
def clean_text(text):
    text = text.lower()
    text = re.sub(r'[^a-zA-Z\s]', '', text)
    tokens = [word for word in text.split() if word not in stop_words]
    return " ".join(tokens)

# Prediction endpoint
@app.route('/predict', methods=['POST'])
def predict():
    data = request.get_json()
    
    # Check input
    if not data or 'book_name' not in data or 'book_summary' not in data:
        return jsonify({'error': 'Please provide book_name and book_summary'}), 400

    book_name = data['book_name']
    book_summary = data['book_summary']

    # Preprocess and embed text
    combined_text = clean_text(book_name + " " + book_summary)
    embedding = bert_model.encode([combined_text])
    
    # Predict and decode
    label = model.predict(embedding)[0]
    department = label_encoder.inverse_transform([label])[0]
    
    return jsonify({'predicted_department': department})


# Run the app
if __name__ == '__main__':
    app.run(host='0.0.0.0', port=5000)
