# Python program to write JSON
# to a file

import csv
import json
from collections import defaultdict
import datetime


def metas_def(): return [0, 0, 0]  # Contient la somme, le nombre de données, et la moyenne
#def defaultdicthourmeta(): return defaultdict(metas_def)
def defaultdictseption(): return defaultdict(metas_def)


# Lecture du fichier non-anonymisé et anonymisé
fileClear = open("original_admin.csv", "r")
csv_input_clear = csv.reader(fileClear, delimiter="\t")
fileAnon = open("anonymised.csv", "r", newline='')
csv_input_anon = csv.reader(fileAnon, delimiter="\t")

# On créer un dictionnaire qui contient pour chaque individu pour chaque semaine la somme des heures pour les 2 fichiers
HoursClear = defaultdict(defaultdictseption)
HoursAnon = defaultdict(defaultdictseption)

# On parcourt le fichier initial
for line in csv_input_clear:
    # Récupération de la semaine
    parsedDate = line[1].split('-')
    parsedDay = parsedDate[2].split(' ')
    week = datetime.date(int(parsedDate[0]), int(parsedDate[1]), int(parsedDay[0])).isocalendar()[1]
    # Récupération de l'heure
    parsedTime = parsedDay[1].split(':')
    currentHour = int(parsedTime[0])  # On récupère l'heure
    try:
        tmp_sum = int((HoursClear[line[0]][parsedDate[0]+'-'+str(week)])[0])
        tmp_nb = int((HoursClear[line[0]][parsedDate[0]+'-'+str(week)])[1])
    except KeyError:
        tmp_sum = 0
        tmp_nb = 0
    (HoursClear[line[0]][parsedDate[0]+'-'+str(week)])[0] = tmp_sum + currentHour
    (HoursClear[line[0]][parsedDate[0] + '-' + str(week)])[1] = tmp_nb + 1
    # Calcul de la moyenne de heures
    (HoursClear[line[0]][parsedDate[0] + '-' + str(week)])[2] = (tmp_sum + currentHour)/(tmp_nb + 1)

# On parcourt le fichier anonymisé
for line in csv_input_anon:
    if 'DEL' in line:  # On saute les lignes supprimées et les lignes qui ne sont pas sur la semaine en cours
        continue
    parsedDate = line[1].split('-')
    parsedDay = parsedDate[2].split(' ')
    week = datetime.date(int(parsedDate[0]), int(parsedDate[1]), int(parsedDay[0])).isocalendar()[1]
    # Parsing de la date
    parsedTime = parsedDay[1].split(':')
    currentHour = int(parsedTime[0])  # On récupère l'heure
    try:
        tmp_sum = int((HoursAnon[line[0]][parsedDate[0]+'-'+str(week)])[0])
        tmp_nb = int((HoursAnon[line[0]][parsedDate[0] + '-' + str(week)])[1])
    except KeyError:
        tmp_sum = 0
        tmp_nb = 0
    (HoursAnon[line[0]][parsedDate[0]+'-'+str(week)])[0] = tmp_sum + currentHour
    (HoursAnon[line[0]][parsedDate[0] + '-' + str(week)])[1] = tmp_nb + 1
    # Calcul de la moyenne de heures
    (HoursAnon[line[0]][parsedDate[0] + '-' + str(week)])[2] = (tmp_sum + currentHour) / (tmp_nb + 1)

# Réidentification

# On créer un dictionnaire qui associe pour chaque id en clair un id anonymisé
Reid = defaultdict(dict)

for idClear in HoursClear:
    for week_number in HoursClear[idClear]:  # On parcourt chaque semaines
        diff = 9999  # Ecart entre les moyennes d'heure pour chaque id
        for idAnon in HoursAnon:
            try:
                currentDiff = abs((HoursAnon[idAnon][week_number])[2] - (HoursClear[idClear][week_number])[2])
            except KeyError:
                continue
            if currentDiff < diff:
                # Si on trouve un écart + faible, on met à jour l'association d'id
                diff = currentDiff
                Reid[idClear][week_number] = [idAnon]
        try:  # Si jamais on n'a pas trouvé de proposition s pour un id pour une semaine
            Reid[idClear][week_number]
        except KeyError:
            Reid[idClear][week_number] = ["0"]

# Serializing json
json_object = json.dumps(Reid)

# Writing to sample.json
with open("attaqueAlex.json", "w") as outfile:
    outfile.write(json_object)

fileClear.close()
fileAnon.close()
