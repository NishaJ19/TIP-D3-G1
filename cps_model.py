import pandas as pd
import numpy as np
from scipy import stats

def load_data(file_path):
    df = pd.read_csv(file_path)
    df['DATETIME'] = pd.to_datetime(df['DATETIME'], format='%d/%m/%y %H')
    # Strip leading/trailing spaces from column names
    df.columns = df.columns.str.strip()
    return df

def detect_anomalies(df, columns, window=24, threshold=3):
    anomalies = pd.DataFrame(index=df.index)
    for column in columns:
        rolling_mean = df[column].rolling(window=window).mean()
        rolling_std = df[column].rolling(window=window).std()
        z_score = np.abs((df[column] - rolling_mean) / rolling_std)
        anomalies[column] = z_score > threshold
    return anomalies

def get_latest_data(df, n_hours=24):
    return df.tail(n_hours)

if __name__ == "__main__":
    df = load_data('CPS_dataset.csv')
    print("Data loaded successfully. Shape:", df.shape)
    print("Columns:", df.columns.tolist())
    print("Sample data:\n", df.head())