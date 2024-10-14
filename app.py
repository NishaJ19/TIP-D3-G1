from flask import Flask, render_template, request, jsonify
import pandas as pd
import plotly
import plotly.express as px
import plotly.graph_objs as go
import json
from werkzeug.utils import secure_filename
import os
from hybrid_malware_detector import HybridMalwareDetector
import matplotlib.pyplot as plt
import seaborn as sns
import base64
from io import BytesIO

app = Flask(__name__)
app.config['UPLOAD_FOLDER'] = 'uploads'
app.config['MAX_CONTENT_LENGTH'] = 16 * 1024 * 1024  # 16MB max-limit

# Ensure upload folder exists
os.makedirs(app.config['UPLOAD_FOLDER'], exist_ok=True)

# Global variable for the model
model = None

def generate_insights(df, attack_mapping=None):
    numeric_df = df.copy()

    # Map encoded attack categories back to original names if mapping is provided
    if attack_mapping:
        df['attack_cat_mapped'] = df['attack_cat'].map(attack_mapping)

    # 1. Distribution of Attack Categories
    attack_distribution = df['attack_cat_mapped'].value_counts().to_dict() if attack_mapping else df['attack_cat'].value_counts().to_dict()

    # 2. Proportion of Malicious vs. Normal Traffic
    label_distribution = df['label'].value_counts().to_dict()

    # 3. Top 5 Most Frequent Protocols
    protocol_distribution = df['proto'].value_counts().head(5).to_dict()

    # 4. Average Duration of Malicious vs. Normal Traffic
    mean_duration = df.groupby('label')['dur'].mean().to_dict()

    # 5. Generate Correlation Heatmap using the original numeric values
    plt.figure(figsize=(10, 8))
    corr = numeric_df.corr()  # Use the numeric version of the DataFrame for correlation
    sns.heatmap(corr, annot=False, cmap='coolwarm')
    buf = BytesIO()
    plt.savefig(buf, format="png")
    buf.seek(0)
    correlation_image = base64.b64encode(buf.getvalue()).decode('utf-8')
    buf.close()

    # 6. Create a Pie Chart for Attack Category Distribution using mapped values
    pie_chart = go.Figure(data=[go.Pie(
        labels=list(attack_distribution.keys()),
        values=list(attack_distribution.values()),
        hoverinfo='label+percent',
        textinfo='value'
    )])
    pie_chart.update_layout(title_text="Attack Category Distribution")

    # Convert the pie chart to JSON for rendering
    pie_chart_json = json.dumps(pie_chart, cls=plotly.utils.PlotlyJSONEncoder)

    return {
        'attack_distribution': attack_distribution,
        'label_distribution': label_distribution,
        'protocol_distribution': protocol_distribution,
        'mean_duration': mean_duration,
        'correlation_image': correlation_image,
        'attack_category_pie_chart': pie_chart_json
    }

@app.route('/')
def index():
    global model
    if model is None:
        return render_template('overview.html', model_loaded=False)

    # Get model metrics and feature importance
    metrics = model.get_metrics()
    feature_importance = model.get_feature_importance()

    # Create visualizations
    fig_importance = px.bar(
        feature_importance.head(10),
        x='importance',
        y='feature',
        orientation='h',
        title='Top 10 Important Features'
    )

    # Confusion Matrix Plot
    conf_matrix = metrics['confusion_matrix']
    fig_conf_matrix = go.Figure(data=go.Heatmap(
        z=conf_matrix,
        x=['Normal', 'Malicious'],
        y=['Normal', 'Malicious'],
        colorscale='RdBu'
    ))
    fig_conf_matrix.update_layout(title='Confusion Matrix')

    # Convert plots to JSON for rendering
    plots = {
        'feature_importance': json.dumps(fig_importance, cls=plotly.utils.PlotlyJSONEncoder),
        'confusion_matrix': json.dumps(fig_conf_matrix, cls=plotly.utils.PlotlyJSONEncoder)
    }

    # Load the UNSW-NB15 dataset and preprocess the data
    df = pd.read_csv('/home/nij/dashboard/UNSW-NB15.csv')
    processed_df = model.preprocess_data(df)

    # Create attack category mapping
    attack_mapping = {0: 'Fuzzers', 1: 'Exploits', 2: 'DoS', 3: 'Generic', 4: 'Reconnaissance',
                      5: 'Analysis', 6: 'Backdoor', 7: 'Shellcode', 8: 'Worms', 9: 'Normal'}

    # Generate insights with the attack mapping
    insights = generate_insights(processed_df, attack_mapping)

    return render_template(
        'overview.html',
        model_loaded=True,
        plots=plots,
        metrics=metrics,
        insights=insights
    )

@app.route('/upload', methods=['GET', 'POST'])
def upload():
    global model
    if model is None:
        return render_template('upload.html', model_loaded=False)

    if request.method == 'POST':
        if 'file' not in request.files:
            return jsonify({'error': 'No file part'})

        file = request.files['file']
        if file.filename == '':
            return jsonify({'error': 'No selected file'})

        if file:
            filename = secure_filename(file.filename)
            filepath = os.path.join(app.config['UPLOAD_FOLDER'], filename)
            file.save(filepath)

            # Load and process the new dataset
            try:
                df = pd.read_csv(filepath)
                processed_df = model.preprocess_data(df)
                X = processed_df.drop(['label'], axis=1)
                y = processed_df['label']

                # Make predictions
                predictions = model.predict(X)

                # Calculate metrics for the new dataset
                metrics = {
                    'total_samples': len(df),
                    'malicious_detected': sum(predictions),
                    'normal_samples': len(predictions) - sum(predictions),
                }

                return jsonify({
                    'success': True,
                    'metrics': metrics
                })

            except Exception as e:
                return jsonify({'error': str(e)})

    return render_template('upload.html', model_loaded=True)

if __name__ == '__main__':
    try:
        print("Attempting to load the model...")
        model = HybridMalwareDetector.load_model('model.joblib')
        print("Model loaded successfully!")
    except FileNotFoundError:
        print("Model file 'model.joblib' not found. Please check the file path.")
        model = None
    except Exception as e:
        print(f"Error loading model: {e}")
        model = None

    app.run(debug=True)
