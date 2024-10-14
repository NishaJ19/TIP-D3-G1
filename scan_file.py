#!/usr/bin/env python3
import pandas as pd
import joblib
import json
import sys
import traceback
import logging
import os

# Set up logging
logging.basicConfig(filename='/mnt/c/xampp/htdocs/projectbu/scan_file.log', level=logging.DEBUG, 
                    format='%(asctime)s %(levelname)s: %(message)s')

def load_model():
    try:
        model_path = '/mnt/c/xampp/htdocs/projectbu/model_malware.joblib'
        logging.info(f"Attempting to load model from: {model_path}")
        if not os.path.exists(model_path):
            raise FileNotFoundError(f"Model file not found: {model_path}")
        model = joblib.load(model_path)
        logging.info("Model loaded successfully")
        return model
    except Exception as e:
        logging.error(f"Failed to load model: {str(e)}")
        logging.error(traceback.format_exc())
        raise

def preprocess_and_predict(file_path, model):
    try:
        logging.info(f"Processing file: {file_path}")
        
        if not os.path.exists(file_path):
            raise FileNotFoundError(f"File not found: {file_path}")

        # Load the uploaded file into a pandas DataFrame
        df = pd.read_csv(file_path)
        logging.info(f"File loaded, shape: {df.shape}")

        # Log column names
        logging.info(f"Columns in the dataset: {df.columns.tolist()}")

        # Preprocess the data using the model's preprocessing method
        df_processed = model.preprocess_data(df)
        logging.info("Data preprocessed")

        # Log preprocessed column names
        logging.info(f"Columns after preprocessing: {df_processed.columns.tolist()}")

        # Drop label and attack category columns if they exist (to prevent data leakage)
        X = df_processed.drop(['label', 'attack_cat'], axis=1, errors='ignore')
        logging.info(f"Features prepared, shape: {X.shape}")

        # Scale the features using the model's scaler
        X_scaled = model.scaler.transform(X)
        logging.info("Features scaled")

        # Make predictions using the hybrid model components
        rf_pred = model.rf_classifier.predict_proba(X_scaled)
        knn_pred = model.knn_classifier.predict_proba(X_scaled)
        et_pred = model.et_classifier.predict_proba(X_scaled)
        logging.info("Predictions made by individual models")

        # Compute the weighted ensemble prediction
        weighted_pred = (0.4 * rf_pred + 0.3 * knn_pred + 0.3 * et_pred)

        # Get final predictions based on the threshold (assuming binary classification)
        final_predictions = (weighted_pred[:, 1] >= 0.5).astype(int)
        logging.info("Final predictions computed")

        # Prepare a summary of results
        results = {
            "total_samples": int(len(df)),
            "malicious_detected": int(sum(final_predictions)),
            "normal_samples": int(len(df) - sum(final_predictions)),
        }

        logging.info(f"Analysis completed successfully. Results: {results}")
        return results

    except Exception as e:
        logging.error(f"An error occurred during processing: {str(e)}")
        logging.error(traceback.format_exc())
        raise

if __name__ == "__main__":
    try:
        logging.info("Script started")
        if len(sys.argv) <= 1:
            raise ValueError("No file path provided. Please upload a file.")

        file_path = sys.argv[1]
        logging.info(f"File path received: {file_path}")
        model = load_model()
        results = preprocess_and_predict(file_path, model)
        print(json.dumps(results))
        logging.info("Script completed successfully")

    except Exception as e:
        error_message = f"Error: {str(e)}\n{traceback.format_exc()}"
        logging.error(error_message)
        print(json.dumps({"error": error_message}))
        sys.exit(1)