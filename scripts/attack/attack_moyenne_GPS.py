import csv
import json
import datetime
separator = "\t"
from collections import defaultdict
def dictstruc(): return [float(), float(), int()]

size = 2

def main(originalFile, anonymisedFile, parameters={"size":2}):
	global size
	size = parameters['size']
    
	fd_original = open(originalFile, newline='')
	fd_anonymised = open(anonymisedFile, newline='')
	original_reader = csv.reader(fd_original, delimiter=separator)
	anonymised_reader = csv.reader(fd_anonymised, delimiter=separator)
	
	tabOri = defaultdict(dictstruc)
	tabAno = defaultdict(dictstruc)
	for lineOri, lineAno in zip(original_reader, anonymised_reader):
	
		#--- Original file
		id = lineOri[0]
		date = datetime.date.fromisoformat(lineOri[1][0:10])
		calendar = date.isocalendar()
		key = (id, calendar[0], calendar[1])
		tabOri[key][0] += float(lineOri[2])
		tabOri[key][1] += float(lineOri[3])
		tabOri[key][2] += 1
		
		#--- Anonymisation file
		if lineAno[0] != "DEL":
			id2 = lineAno[0]
			date = datetime.date.fromisoformat(lineAno[1][0:10])
			calendar = date.isocalendar()
			key2 = (id2, calendar[0], calendar[1])
			tabAno[key2][0] += float(lineAno[2])
			tabAno[key2][1] += float(lineAno[3])
			tabAno[key2][2] += 1
			
	for id in tabOri:
		tabOri[id][0] = tabOri[id][0]/tabOri[id][2]
		tabOri[id][1] = tabOri[id][1]/tabOri[id][2]
		del tabOri[id][2]
	for id in tabAno:
		tabAno[id][0] = tabAno[id][0]/tabAno[id][2]
		tabAno[id][1] = tabAno[id][1]/tabAno[id][2]
		del tabAno[id][2]
	
	def defaultdictlist(): return defaultdict(list)
	final_json = defaultdict(defaultdictlist)
	for id in tabOri:
		min = 99999999999
		min_id = ('0',0,0)
		for id2 in tabAno:
			if id[1]==id2[1] and id[2]==id2[2]:
				difference = abs(tabAno[id2][0]-tabOri[id][0])**2 + abs(tabAno[id2][1]-tabOri[id][1])**2
				if difference<min:
					#print(id, id2, difference)
					min_id = id2
					min = difference
				
		final_json[id[0]][f"{id[1]}-{id[2]}"].append(min_id[0])
	print(json.dumps(final_json, indent=4))



				
main("withisodate.txt","d3b4619b0ca49aaad78a5f33d9307c7d0a963746f96e24980f0c68dd849a0c4c")
#main("minimized_week.csv","minimized_week_anonymised.csv")