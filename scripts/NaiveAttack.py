import json
import csv
from threading import Thread
from collections import defaultdict
from Utils import *
from datetime import date


#################################
#      Naive Attack Thread      #
#################################
class NaiveAttack(Thread):
    def __init__(self, original_file, anonym_file, anwser_JSON, dbconn):
        Thread.__init__(self)
        self.original_file = original_file
        self.anonym_file = anonym_file
        self.anwser_JSON = anwser_JSON
        self.dbconn = dbconn
        self.score = -1

    # Génère un dictionaire ayant pour chaque mois la somme des coordonnées GPS
    def generateSumGPS(self, file):
        dictsumGPS = defaultdict(list_struct)
        with open(file, newline='') as csvfile:
            spamreader = csv.reader(csvfile, delimiter=separator)
            for row in spamreader:
                if row[0]!="DEL":
                    # Pour chaque ligne
                    y, m, d = row[1][0:10].split("-")
                    calendar = date(int(y), int(m), int(d)).isocalendar()
                    #calendar = date.fromisoformat(row[1][0:10]).isocalendar()
                    id_date = f"{row[0]}.{calendar[0]}-{calendar[1]}"
                    dictsumGPS[id_date][0] += float(row[-2])
                    dictsumGPS[id_date][1] += float(row[-1])
        return dictsumGPS

    def run(self):
        self.anonym_dict = self.generateSumGPS(self.anonym_file)
        self.original_dict = self.generateSumGPS(self.original_file)

        # Parcours le tableau et détermine l'ID le plus probable
        sol = defaultdict(dict)
        for key in self.original_dict:
            orginal_gps = self.original_dict[key]
            minimum = float('inf')
            minimum_key = ""
            for key2 in self.anonym_dict:
                difference = abs(orginal_gps[0] - self.anonym_dict[key2][0]) + abs(
                    orginal_gps[1] - self.anonym_dict[key2][1])
                if difference < minimum:
                    minimum = difference
                    minimum_key = key2
            sol[key.split(".")[0]][key.split(".")[1]] = [minimum_key.split(".")[0]]

        # Détermine le score
        with open(self.anwser_JSON) as json_file:
            data = json.load(json_file)

            # Nombre d'ID à déterminer
            size = sum((len(data[tab]) for tab in data))
            score = 0

            for tab in data:
                for month in data[tab]:
                    if data[tab][month][0] == sol[tab][month][0]:
                        score += 1
            self.score = score / size

        #Update the database
        self.dbconn.cursor().execute(f"UPDATE anonymisation \
                                       set status='Attaque naïve terminée' \
                                       where fileLink='{self.anonym_file.split('/')[1]}'")
        self.dbconn.commit()

    def result(self):
        return self.score