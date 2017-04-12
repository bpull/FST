import sys
import re
import os

for directory in os.listdir("."):
    if os.path.isdir(directory):
        newfile = directory.strip()+"/"+directory.strip()+".csv"
        nf = open(newfile,"w")
        direct = directory
        for i in range(2007,2017,1):

            directory = direct+"/"+str(i)+"-data"
            print directory
            for j in range(1,13,1):
                filename = directory+"/"+str(j)
                try:
                    fp = open(filename,"r")
                    all_data = fp.readline()
                    all_data = re.sub('[\[\](]','',all_data)
                    all_data = all_data.replace('),','#')
                    all_data = all_data.replace(')','')
                    split_data = all_data.split("#")
                    print filename
                    counter = 1
                    for info in split_data:
                        tofile = info.strip() + ", " +str(j) +"-"+str(counter)+"-"+str(i)+"\n"
                        nf.write(tofile)
                        counter+= 1
                    fp.close()
                except:
                    pass
        nf.close()

# cots = 0
# for direct in os.listdir("."):
#     if os.path.isdir(direct):
#         cots += 1
#         print direct
#         flag = 0
#         for item in os.listdir(direct):
#             if ".csv" in item:
#                 flag = 1
#         if flag == 0:
#             print "no csv for " +direct
# print cots
