import pandas as pd
import hashlib
import random
from datetime import datetime, timedelta

# Définition des fichiers d'entrée et de sortie
input_file = "inputUsers.csv"
output_file = "test1.csv"

# Lecture du fichier avec tabulations sans modifier la structure
df = pd.read_csv(input_file, delimiter="\t", header=None, names=["ID", "Timestamp", "Latitude", "Longitude"], dtype=str)

# Fonction pour obtenir la semaine de l'année
def get_week_key(timestamp):
    return datetime.strptime(timestamp, "%Y-%m-%d %H:%M:%S").strftime("%Y-%W")  # Année-Semaine

# Génération d'un ID pseudonymisé cohérent dans la même semaine
def pseudonymize_id(original_id, week_key):
    combined_key = f"{original_id}-{week_key}"
    hashed = hashlib.sha256(combined_key.encode()).hexdigest()[:12]
    return f"{hashed}"

# Appliquer la semaine comme clé
df['Week_Key'] = df['Timestamp'].apply(get_week_key)

# Générer des IDs anonymisés cohérents dans la même semaine
df['Anon_ID'] = df.apply(lambda row: pseudonymize_id(row['ID'], row['Week_Key']), axis=1)

# Générer une graine déterministe basée sur l'ID et la semaine
def generate_seed(original_id, week_key):
    return int(hashlib.sha256(f"{original_id}-{week_key}".encode()).hexdigest(), 16) % 5 + 1

df['Seed'] = df.apply(lambda row: generate_seed(row['ID'], row['Week_Key']), axis=1)

# Modification des coordonnées

def modify_coordinates(latitude, longitude, seed, row_index):
    def swap_end_digits(value, n):
        integer_part, decimal_part = value.split('.')
        return f"{integer_part}.{decimal_part[-n:] + decimal_part[:-n]}"

    def invert_decimal(value):
        integer_part, decimal_part = value.split('.')
        return f"{integer_part}.{decimal_part[::-1]}"

    def cycle_digits(value):
        integer_part, decimal_part = value.split('.')
        return f"{integer_part}.{decimal_part[-6:] + decimal_part[6:-6] + decimal_part[:6]}"

    def modify_integer(value, offset):
        integer_part, decimal_part = value.split('.')
        return f"{int(integer_part) + offset}.{decimal_part}"

    seed_patterns = {
        1: [invert_decimal, lambda x: swap_end_digits(x, 4), cycle_digits, lambda x: modify_integer(x, 3)],
        2: [lambda x: swap_end_digits(x, 5), invert_decimal, cycle_digits, lambda x: modify_integer(x, -4)],
        3: [cycle_digits, lambda x: swap_end_digits(x, 6), lambda x: modify_integer(x, 5), invert_decimal],
        4: [lambda x: swap_end_digits(x, 3), cycle_digits, invert_decimal, lambda x: modify_integer(x, -5)],
        5: [invert_decimal, lambda x: swap_end_digits(x, 4), cycle_digits, lambda x: modify_integer(x, 6)]
    }

    lat_pattern = seed_patterns[seed][row_index % 4]
    lon_pattern = seed_patterns[seed][(row_index + 1) % 4]

    return lat_pattern(latitude), lon_pattern(longitude)

df[['Anon_Latitude', 'Anon_Longitude']] = df.apply(lambda row: modify_coordinates(row['Latitude'], row['Longitude'], row['Seed'], row.name), axis=1, result_type="expand")

# Modification des timestamps dans la même semaine
def modify_timestamp(timestamp, seed, row_index):
    try:
        time_obj = datetime.strptime(timestamp, "%Y-%m-%d %H:%M:%S")
        minute_adjustments = {0: random.randint(-12, 12), 1: random.randint(-10, 10), 2: random.randint(-8, 8), 3: random.randint(-6, 6)}
        second_adjustments = {0: random.randint(-40, 40), 1: random.randint(-35, 35), 2: random.randint(-30, 30), 3: random.randint(-25, 25)}
        adjusted_time = time_obj + timedelta(minutes=minute_adjustments[row_index % 4], seconds=second_adjustments[row_index % 4])
        if adjusted_time.strftime("%Y-%W") != time_obj.strftime("%Y-%W"):
            adjusted_time = time_obj  # Si dépassement de semaine, garde l'original
        return adjusted_time.strftime("%Y-%m-%d %H:%M:%S")
    except ValueError:
        return timestamp

df['Anon_Timestamp'] = df.apply(lambda row: modify_timestamp(row['Timestamp'], row['Seed'], row.name), axis=1)

# Création d'une nouvelle colonne avec le format original
df['Anon_Data'] = df.apply(lambda row: f"{row['Anon_ID']}\t{row['Anon_Timestamp']}\t{row['Anon_Latitude']}\t{row['Anon_Longitude']}", axis=1)

# Sauvegarde du fichier
with open(output_file, "w", encoding="utf-8") as f:
    for row in df[['Anon_Data']].itertuples(index=False, name=None):
        f.write(f"{row[0]}\n")