import sys,re,os

for directory in os.listdir("."):
    if os.path.isdir(directory):
        fp3 = open(directory+"/"+directory+"news.csv","w")
        fp1 = open(directory+"/"+directory+".csv","r")
        for line in fp1:
            # print line.split(",")[-1].strip()
            fp2 = open("allnews.csv","r")
            for data in fp2:
                if data.split(",")[-1].strip() == line.split(",")[-1].strip():
                    newline = line.split(",")

                    newline.insert(0," " + data.split(",")[0].strip())
                    newline.insert(0,data.split(",")[1].strip())
                    fp3.write(",".join(newline))
        fp3.close()
