import pandas as pd
import numpy as np
from sklearn.metrics.pairwise import cosine_similarity
from datetime import datetime
import json

# Définition des fichiers de données
original_file = "output.csv"  # Fichier contenant les données originales
anon_file = "test2.csv"  # Fichier contenant les données anonymisées

def preprocess_data(file_path):
    """
    Charge et prétraite les données d'un fichier CSV.
    
    Paramètres :
        file_path (str) : Chemin du fichier à charger.
    
    Retourne :
        pd.DataFrame : Un DataFrame contenant les données prétraitées.
    """
    df = pd.read_csv(file_path, delimiter="\t", header=None, dtype=str, encoding="utf-8")
    
    # Vérifie si les données sont dans une seule colonne et tente de les corriger
    if df.shape[1] == 1:
        df = df[0].str.split('\t', expand=True)
    
    # Renomme les colonnes
    df.columns = ['ID', 'Timestamp', 'Latitude', 'Longitude']
    
    # Conversion des types de données
    df['Latitude'] = df['Latitude'].astype(float)
    df['Longitude'] = df['Longitude'].astype(float)
    df['Timestamp'] = pd.to_datetime(df['Timestamp'], errors='coerce')
    
    # Génération de clés temporelles
    df['Week_Key'] = df['Timestamp'].dt.strftime("%G-%V")  # Clé de la semaine
    df['DayOfWeek'] = df['Timestamp'].dt.weekday  # Jour de la semaine (0 = Lundi, 6 = Dimanche)
    df['SecondsOfDay'] = df['Timestamp'].dt.hour * 3600 + df['Timestamp'].dt.minute * 60 + df['Timestamp'].dt.second  # Secondes écoulées dans la journée
    
    return df

# Chargement et traitement des données
df_original = preprocess_data(original_file)
df_anon = preprocess_data(anon_file)

def create_feature_vector(df):
    """
    Convertit chaque ligne du DataFrame en un vecteur numérique basé sur certaines caractéristiques.
    
    Paramètres :
        df (pd.DataFrame) : DataFrame contenant les données prétraitées.
    
    Retourne :
        np.array : Tableau NumPy contenant les vecteurs caractéristiques.
    """
    vectors = []
    for _, row in df.iterrows():
        vectors.append([row['DayOfWeek'], row['SecondsOfDay'], row['Latitude'], row['Longitude']])
    return np.array(vectors)

# Conversion des données en vecteurs numériques
vectors_original = create_feature_vector(df_original)
vectors_anon = create_feature_vector(df_anon)

# Calcul de la similarité cosinus entre les données anonymisées et originales
similarity_matrix = cosine_similarity(vectors_anon, vectors_original)

def get_best_matches(similarity_row, threshold=0.9):
    """
    Identifie les indices des meilleures correspondances selon un seuil de similarité donné.
    Si aucune correspondance n'est trouvée, retourne le meilleur score.
    
    Paramètres :
        similarity_row (np.array) : Ligne de la matrice de similarité.
        threshold (float) : Seuil minimal de similarité pour considérer une correspondance.
    
    Retourne :
        np.array : Tableau contenant les indices des correspondances satisfaisant le critère.
    """
    best_matches = np.where(similarity_row > threshold)[0]
    if len(best_matches) == 0:
        return [np.argmax(similarity_row)]  # Si aucune correspondance, retourne la plus proche
    return best_matches

# Génération des correspondances basées sur la similarité cosinus
attack_results = {}
for i, anon_id in enumerate(df_anon['ID']):
    best_match_indices = get_best_matches(similarity_matrix[i])
    week = df_anon.iloc[i]['Week_Key']
    
    for best_match_index in best_match_indices:
        original_id = df_original.iloc[best_match_index]['ID']
        
        if original_id not in attack_results:
            attack_results[original_id] = {}
        if week not in attack_results[original_id]:
            attack_results[original_id][week] = set()
        
        attack_results[original_id][week].add(anon_id)

# Conversion des ensembles en listes pour la sérialisation JSON
attack_results = {user: {week: list(anon_ids) for week, anon_ids in weeks.items()} for user, weeks in attack_results.items()}

# Sauvegarde des résultats dans un fichier JSON
with open("attack_test2v3.json", "w") as f:
    json.dump(attack_results, f, indent=4)
