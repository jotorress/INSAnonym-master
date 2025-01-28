import pandas as pd
import hashlib
import numpy as np
from datetime import datetime, timedelta

# Définition des fichiers d'entrée et de sortie
input_file = "inputUsers.csv"
output_file = "test2.csv"

# Lecture du fichier sans modifier la structure
df = pd.read_csv(input_file, delimiter="\t", header=None, names=["ID", "Timestamp", "Latitude", "Longitude"], dtype=str)

# Fonction pour obtenir la semaine de l'année selon l'ISO 8601
def get_week_key(timestamp):
    return datetime.strptime(timestamp, "%Y-%m-%d %H:%M:%S").strftime("%G-%V")

df['Week_Key'] = df['Timestamp'].apply(get_week_key)

# Dictionnaire pour stocker les ID anonymes
id_mapping = {}

def pseudonymize_id(original_id, week_key):
    """ Génère un ID anonyme unique par utilisateur et par semaine. """
    key = f"{original_id}-{week_key}"
    if key not in id_mapping:
        id_mapping[key] = hashlib.sha256(key.encode()).hexdigest()[:16]
    return id_mapping[key]

df['Anon_ID'] = df.apply(lambda row: pseudonymize_id(row['ID'], row['Week_Key']), axis=1)

# Génération d'une graine aléatoire déterministe
def generate_seed(original_id, week_key):
    return int(hashlib.sha256(f"{original_id}-{week_key}".encode()).hexdigest(), 16) % 10000 + 1

df['Seed'] = df.apply(lambda row: generate_seed(row['ID'], row['Week_Key']), axis=1)

# Technique de suppression des cellules géographiques
def cell_suppression(latitude, longitude, seed):
    """ Modifie légèrement les coordonnées pour anonymiser la localisation. """
    np.random.seed(seed)
    lat, lon = float(latitude), float(longitude)
    cell_size = np.random.uniform(0.005, 0.01)
    lat_shift = np.random.uniform(-cell_size, cell_size)
    lon_shift = np.random.uniform(-cell_size, cell_size)
    return round(lat + lat_shift, 6), round(lon + lon_shift, 6)

df[['Anon_Latitude', 'Anon_Longitude']] = df.apply(lambda row: cell_suppression(row['Latitude'], row['Longitude'], row['Seed']), axis=1, result_type="expand")

# Technique de déplacement des timestamps
def generalize_timestamp(timestamp, seed):
    """ Agrège et décale légèrement les timestamps. """
    try:
        time_obj = datetime.strptime(timestamp, "%Y-%m-%d %H:%M:%S")
        interval_choices = [15, 30, 45, 60]
        interval = interval_choices[seed % len(interval_choices)]
        minutes_group = (time_obj.minute // interval) * interval
        generalized_time = time_obj.replace(minute=minutes_group, second=0)
        offset_seconds = np.random.randint(-600, 600)
        generalized_time += timedelta(seconds=offset_seconds)
        if generalized_time.strftime("%G-%V") != time_obj.strftime("%G-%V"):
            generalized_time = time_obj
        return generalized_time.strftime("%Y-%m-%d %H:%M:%S")
    except ValueError:
        return timestamp

df['Anon_Timestamp'] = df.apply(lambda row: generalize_timestamp(row['Timestamp'], row['Seed']), axis=1)

# Création d'une nouvelle colonne avec le format anonymisé
df['Anon_Data'] = df.apply(lambda row: f"{row['Anon_ID']}\t{row['Anon_Timestamp']}\t{row['Anon_Latitude']}\t{row['Anon_Longitude']}", axis=1)

# Sauvegarde du fichier anonymisé
with open(output_file, "w", encoding="utf-8") as f:
    for row in df[['Anon_Data']].itertuples(index=False, name=None):
        f.write(f"{row[0]}\n")
