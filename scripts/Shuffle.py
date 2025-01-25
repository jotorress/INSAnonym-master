import random
from threading import Thread
from Utils import *

#################################
#         Thread class          #
#################################

# Shuffling the submitted file for attackers
class Shuffle(Thread):
    def __init__(self, input, origin, output, dbconn):
        Thread.__init__(self)
        self.input = input
        self.origin = origin
        self.output = output
        self.dbconn = dbconn
        self.chunksize = 10000000

    def run(self):
        size = csv_length(self.origin)
        chunks = 0  # Total number of chunks
        tmp = size
        while tmp > 0:
            tmp -= self.chunksize
            chunks += 1
        random_order = [i for i in range(chunks)]  # Position of the chunk in the shuffled file
        random.shuffle(random_order)
        for i in random_order:
            rows2read = self.chunksize if self.chunksize * (i + 1) < size else size - self.chunksize * i
            # Shuffles and append the csv result in the given file
            chunk_shuffler(self.input, self.chunksize * i, rows2read).to_csv(self.output, mode="a", sep=separator,
                                                                             index=False, header=None,
                                                                             line_terminator="\n")

        #Update the database
        self.dbconn.cursor().execute(f"UPDATE anonymisation \
                              set status='Génération du fichier mélangé terminée' \
                              where fileLink='{self.input.split('/')[1]}'")
        self.dbconn.commit()
