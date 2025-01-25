import csv
import json
import datetime
separator = "\t"
from collections import defaultdict
def dictstruc(): return defaultdict(int)

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
		gps1 = (round(float(lineOri[2]),size), round(float(lineOri[3]),size))
		date = datetime.date.fromisoformat(lineOri[1][0:10])
		calendar = date.isocalendar()
		key = (id, calendar[0], calendar[1])
		tabOri[key][gps1] += 1
		
		#--- Anonymisation file
		if lineAno[0] != "DEL":
			id2 = lineAno[0]
			gps2 = (round(float(lineAno[2]),size), round(float(lineAno[3]),size))
			date = datetime.date.fromisoformat(lineAno[1][0:10])
			calendar = date.isocalendar()
			key2 = (id2, calendar[0], calendar[1])
			tabAno[key2][gps2] += 1

	final_tab_original = defaultdict(int)
	final_tab_anonymised = defaultdict(int)
	for id in tabOri.copy():
		valeur = max(tabOri[id], key=tabOri[id].get)
		final_tab_original[id] = int(len(tabOri[id])*valeur[0]*valeur[1])
	for id in tabAno:
		valeur = max(tabAno[id], key=tabAno[id].get)
		final_tab_anonymised[id] = int(len(tabAno[id])*valeur[0]*valeur[1])
	
	def defaultdictlist(): return defaultdict(list)
	final_json = defaultdict(defaultdictlist)
	for id in final_tab_original:
		min = 99999999999
		min_id = ('0',0,0)
		for id2 in final_tab_anonymised:
			if id[1]==id2[1] and id[2]==id2[2]:
				#print(id, id2, abs(final_tab_original[id]-final_tab_anonymised[id2]))
				if abs(final_tab_original[id]-final_tab_anonymised[id2])<min:
				
					min_id = id2
					min = abs(final_tab_original[id]-final_tab_anonymised[id2])
				
		final_json[id[0]][f"{id[1]}-{id[2]}"].append(min_id[0])
	print(json.dumps(final_json, indent=4))



				
main("withisodate.txt","08368d9e3df0f98ed1ba2e8f38ab30fa2fdeed3de0e04bc513050b0dd65bc8c3")
#main("minimized_week.csv","minimized_week_anonymised.csv")