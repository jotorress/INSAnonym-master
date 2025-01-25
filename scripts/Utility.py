from statistics import mean
from statistics import median
from threading import Thread
import sqlite3
import importlib
import os
import json

#################################
#        Utility Thread         #
#################################

class Utility(Thread):
    def __init__(self, input, origin, dbconn):
        Thread.__init__(self)
        self.input = input
        self.origin = origin
        self.errorSource = -1
        self.errorType = 0
        self.dbconn = dbconn

        #Get all the script names within the "metrics" folder
        #self.scriptList = [sc[:-3] for sc in os.listdir("scripts/metrics") if sc[-2:] == "py" and sc != "__init__.py"]
        self.scriptList = []
        self.scoreList = []

    def run(self):
        #Open DB to get the selected metrics
        cursor = self.dbconn.cursor()
        cursor.execute(f"SELECT * FROM METRIC")
        self.scriptList = ((elmt[0][:-3], elmt[1]) for elmt in cursor.fetchall())
        #Executes all the scripts in the list
        for script in self.scriptList:
            script, parameters = script
            try :
                #Try to execute the main function in each one of the scripts
                exec = importlib.import_module("metrics." + script)
                result = exec.main(self.origin, self.input, json.loads(parameters))
            except: # Error within one of the scripts
                self.errorType = -8
                self.errorSource = script + ".py"
                return
            if type(result) is not tuple:
                # Adds the result to the utility score list
                self.scoreList += [result]
            else: # Error within the submitted file
                self.errorType = -7
                self.errorSource = result[1]
                
        self.dbconn.cursor().execute(f"UPDATE anonymisation \
                                            set status='Calculs d’utilité terminés' \
                                            where fileLink='{self.input.split('/')[1]}'")
        self.dbconn.commit()



    def result(self):
        # Return the final utility score according to the aggregating function
        cursor = self.dbconn.cursor()
        cursor.execute(f"SELECT * FROM aggregation")
        agg = cursor.fetchall()[0][0]
        if self.errorType >= 0: #If there is no error
            if self.scoreList:
                if agg == "mean":
                    return mean(self.scoreList)
                if agg == "median":
                    return median(self.scoreList)
                if agg == "max":
                    return max(self.scoreList)
                if agg == "min":
                    return min(self.scoreList)
            else: return (-9, 0)
        else: return (self.errorType, self.errorSource)
