import os,sys,re
fp2 = open("fornick.txt","w")
for directory in os.listdir("."):
    if os.path.isdir(directory):
        if os.path.exists(directory+"/"+directory+".csv"):
            filename = directory+"/"+directory+".csv"
            fp = open(filename,"r")
            for line in fp:
                try:
                    fp2.write(directory + " " + line.split(",")[5].strip() + " "+line.split(",")[6].strip()+"\n")
                except:
                    pass

                # tofile = directory + " " + linesplit[5].strip() + " "+linesplit[6].strip()
