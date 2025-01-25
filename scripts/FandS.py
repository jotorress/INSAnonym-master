import sys
import sqlite3
import os
from Footprint import *
from Shuffle import *
from NaiveAttack import *
from Utility import *
from Utils import *

#################################
#         Main function         #
#################################

def main(inputfile, originfile, shuffledfile, footprintfile, dbconn):
    #Managing both threads
    #Unzip original file if not already
    try:
        if not os.path.exists(originfile):
            unzip_file(originfile)
    except:
        return ((-12,-12), -1)
    #Unzip file input file
    try:
        unzip_file(inputfile)
    except:
        return ((-11,-11), -1)
    check = checking_shape(inputfile, originfile)
    if(check[0] > 0):
        thread_f = Footprint(inputfile, originfile, footprintfile, dbconn)
        thread_u = Utility(inputfile, originfile, dbconn)
        thread_s = Shuffle(inputfile, originfile, shuffledfile, dbconn)
        thread_f.start()
        thread_s.start()
        thread_u.start()
        thread_u.join()
        thread_f.join()
        # Just after the end of FootPrint Thread, start naive attack
        thread_naiveAttack = NaiveAttack(originfile, inputfile, footprintfile, dbconn)
        if type(thread_u.result()) is not tuple:
            if type(thread_f.result()) is not tuple:
                thread_naiveAttack.start()
                thread_naiveAttack.join()
            else: return (thread_f.result(), -1)
        else: return(thread_u.result(), -1)
        thread_s.join()
        
        #Finaly, zip the shuffle file
        zip_outfileShuffle(shuffledfile)
        
        #Remove the not ZIP shuffle file and not ZIP input file
        os.remove(shuffledfile)
        os.remove(inputfile)
        
        return (thread_u.result(), thread_naiveAttack.result())
    else:
        return (check, -1)


# Calling the script : "python FandS.py [input file] [shuffled file] [footprint]"

if __name__ == '__main__':
    # Connection to database
    conn = sqlite3.connect('database/tables.sqlite3', check_same_thread=False)
    
    #Inputs
    input = "uploads/" + sys.argv[1]
    origin = "files/c3465dad3864bb1e373891fdcfbfcca5f974db6a9e0b646584e07c5f554d7df7"
    shuffled = "files/" + sys.argv[2]
    footprint = "uploads/" + sys.argv[3]
    
    #Call main function - Starting processing input file -
    utility,naiveAttack = main(input, origin, shuffled, footprint, conn)
    
    #Update the result to the database
    if type(utility) is tuple:
        conn.cursor().execute(f"UPDATE anonymisation \
                                set naiveAttack='{naiveAttack}', \
                                    utility='{utility[0]}', \
                                    status='{error_messages(utility)}' \
                                where fileLink='{sys.argv[1]}'")
        conn.commit()
    else:
        conn.cursor().execute(f"UPDATE anonymisation \
                                set naiveAttack='{naiveAttack}', \
                                    utility='{utility}', \
                                    status='Terminé' \
                                where fileLink='{sys.argv[1]}'")
        conn.commit()
    conn.close()