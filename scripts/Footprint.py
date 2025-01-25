import json
import csv
from threading import Thread
from statistics import mean
from statistics import median
from Utility import *
from Utils import *
from datetime import date

#################################
#        Footprint Thread       #
#################################

# Generating the submitted file footprint for assessment and computes teh utility
class Footprint(Thread):
    def __init__(self, input, origin, footprint, dbconn):
        Thread.__init__(self)
        self.input = input
        self.origin = origin
        self.footprint = footprint
        self.exception = 0
        self.dbconn = dbconn


    def run(self):
        fd_nona_file = open(self.origin, "r")
        fd_anon_file = open(self.input, "r")
        nona_reader = csv.reader(fd_nona_file, delimiter=separator)
        anon_reader = csv.reader(fd_anon_file, delimiter=separator)
        found_ids_weeks = {}  # Dictionary {key = user id : value = list of months in which the user appears}
        linktable = {}  # Dictionary {key = user id : value = dictionary {key = month : value = list of anonymized ids}}
        index = 0
        for row1, row2 in zip(nona_reader, anon_reader):
            # Reads simultaneously both origin and edited file
            index += 1
            if row2[0]:
                if row2[0] != "DEL":
                    try: #Computing both week numbers
                        y2, m2, d2 = row2[1][0:10].split("-")
                        calendar2 = date(int(y2), int(m2), int(d2)).isocalendar()
                        weeknum2 = f"{calendar2[0]}-{calendar2[1]}"
                    except:
                        self.exception = (-10, index)
                        return
                    y1, m1, d1 = row1[1][0:10].split("-")
                    calendar1 = date(int(y1), int(m1), int(d1)).isocalendar()
                    weeknum1 = f"{calendar1[0]}-{calendar1[1]}"
                    if weeknum1 == weeknum2: #Checking if the week number is the same in both files
                        if row1[0] not in found_ids_weeks.keys() and weeknum1 == weeknum2: #Filling the correction table
                            found_ids_weeks[row1[0]] = [weeknum1]
                            linktable[row1[0]] = {}
                            linktable[row1[0]][weeknum1] = [row2[0]]
                        elif weeknum1 not in found_ids_weeks[row1[0]] and weeknum1 == weeknum2:
                            found_ids_weeks[row1[0]] += [weeknum1]
                            linktable[row1[0]][weeknum1] = [row2[0]]
                        else:
                            if linktable[row1[0]][weeknum1][0] != row2[0]:
                                # 2 pseudo IDs are standing for the same user, the same week
                                self.exception = (-5, index)
                                return
                    else:
                        self.exception = (-5, index)
                        return
            else :
                self.exception = (-6, index)
                return

        # Turns the python table into a json table
        with open(self.footprint, 'w') as result:
            json.dump(linktable, result)

        #Update the database
        self.dbconn.cursor().execute(f"UPDATE anonymisation \
                                       set status='Génération de la correction terminée' \
                                       where fileLink='{self.input.split('/')[1]}'")
        self.dbconn.commit()

    def result(self):
        return self.exception