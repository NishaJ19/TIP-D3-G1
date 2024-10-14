import pandas as pd
import json
from hybrid_malware_detector import HybridMalwareDetector
from sklearn.model_selection import train_test_split
import plotly.graph_objs as go
import plotly.utils
import seaborn as sns
import matplotlib.pyplot as plt
import base64
from io import BytesIO

def load_and_preprocess_data(file_path):
    # Read the CSV file
    df = pd.read_csv(file_path)
    
    # Create an instance of the detector
    detector = HybridMalwareDetector()
    
    # Preprocess the data
    processed_df = detector.preprocess_data(df)
    
    return processed_df, detector

def save_metrics_and_plots(metrics, feature_importance, X, y):
    # Save metrics as JSON
    with open('metrics.json', 'w') as f:
        json.dump(metrics, f)

    # Feature Importance Plot
    fig_importance = go.Figure(data=[go.Bar(
        x=feature_importance['importance'].head(10),
        y=feature_importance['feature'].head(10),
        orientation='h'
    )])
    fig_importance.update_layout(title='Top 10 Important Features', yaxis={'categoryorder': 'total ascending'})

    # Confusion Matrix Plot
    conf_matrix = metrics['confusion_matrix']
    fig_conf_matrix = go.Figure(data=go.Heatmap(
        z=conf_matrix,
        x=['Normal', 'Malicious'],
        y=['Normal', 'Malicious'],
        colorscale='RdBu'
    ))
    fig_conf_matrix.update_layout(title='Confusion Matrix')

    # Correlation Heatmap
    plt.figure(figsize=(20, 16))
    corr = X.corr()
    sns.heatmap(corr, cmap='coolwarm', annot=False)
    plt.title('Correlation Heatmap')
    buf = BytesIO()
    plt.savefig(buf, format='png', dpi=150, bbox_inches='tight')
    buf.seek(0)
    correlation_heatmap_base64 = base64.b64encode(buf.getvalue()).decode('utf-8')
    plt.close()

    # Save plots as JSON
    plots = {
        'feature_importance': json.loads(json.dumps(fig_importance, cls=plotly.utils.PlotlyJSONEncoder)),
        'confusion_matrix': json.loads(json.dumps(fig_conf_matrix, cls=plotly.utils.PlotlyJSONEncoder)),
        'correlation_heatmap': correlation_heatmap_base64
    }
    with open('plots.json', 'w') as f:
        json.dump(plots, f)

def generate_insights(X, y, attack_mapping):
    insights = {}

    # Combine X and y into a single DataFrame for easier handling
    df = X.copy()
    df['label'] = y

    # Attack Category Distribution
    if 'attack_cat' in df.columns:
        df['attack_cat_mapped'] = df['attack_cat'].map(attack_mapping)
        attack_distribution = df['attack_cat_mapped'].value_counts().to_dict()
        pie_chart = go.Figure(data=[go.Pie(
            labels=list(attack_distribution.keys()),
            values=list(attack_distribution.values()),
            hoverinfo='label+percent',
            textinfo='value'
        )])
        pie_chart.update_layout(title_text="Attack Category Distribution")
        insights['attack_category_pie_chart'] = json.loads(json.dumps(pie_chart, cls=plotly.utils.PlotlyJSONEncoder))

    # Dataset Insights
    insights['dataset_insights'] = {
        'Attack Category Distribution': df['attack_cat_mapped'].value_counts().to_dict(),
        'Normal vs. Malicious Traffic Distribution': df['label'].value_counts().to_dict(),
        'Top 5 Most Frequent Protocols': df['proto'].value_counts().head(5).to_dict(),
        'Average Duration of Malicious vs. Normal Traffic': df.groupby('label')['dur'].mean().to_dict()
    }

    # Save insights as JSON
    with open('insights.json', 'w') as f:
        json.dump(insights, f)

def main():
    # Load and preprocess the UNSW-NB15 dataset
    print("Loading and preprocessing the dataset...")
    processed_df, detector = load_and_preprocess_data('UNSW-NB15.csv')
    
    # Split features and target, keeping 'attack_cat' in X
    X = processed_df.drop(['label'], axis=1)
    y = processed_df['label']
    
    # Train the model
    print("Training the hybrid model...")
    metrics = detector.train(X, y)

    # Print training results
    print("\nTraining completed!")
    print("\nClassification Report:")
    for class_label, metrics_dict in metrics['classification_report'].items():
        if isinstance(metrics_dict, dict):
            print(f"\nClass {class_label}:")
            for metric_name, value in metrics_dict.items():
                print(f"{metric_name}: {value:.4f}")

    # Save the trained model
    print("\nSaving the model...")
    detector.save_model('model.joblib')
    print("Model saved successfully!")

    # Save metrics and visualizations as JSON files
    print("Saving metrics and visualizations for PHP...")
    feature_importance = detector.get_feature_importance()
    save_metrics_and_plots(metrics, feature_importance, X, y)

    # Get attack category mapping
    attack_mapping = detector.get_attack_cat_mapping()

    # Generate and save insights
    generate_insights(X, y, attack_mapping)
    print("All metrics, plots, and insights saved successfully!")

if __name__ == "__main__":
    main()