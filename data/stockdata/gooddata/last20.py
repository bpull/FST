import sys

def do_it(myfile):
    fncsv = str(myfile)+"/"+str(myfile)+".csv"
    fnnews = str(myfile)+"/"+str(myfile)+"news.csv"
    fncsvw = str(myfile)+"/short"+str(myfile)+".csv"
    fnnewsw = str(myfile)+"/short"+str(myfile)+"news.csv"

    fpcsvr = open(fncsv,"r")
    fpcsvw = open(fncsvw,"w")
    fpnewsr = open(fnnews,"r")
    fpnewsw = open(fnnewsw,"w")

    wholecsv = fpcsvr.read()
    for line in wholecsv.split("\n")[-21:-1]:
        fpcsvw.write(line+"\n")
    wholenews = fpnewsr.read()
    for line in wholenews.split("\n")[-21:-1]:
        fpnewsw.write(line+"\n")
