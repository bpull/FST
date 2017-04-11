import sys
import re

#fp = open(sys.argv[1],"r")
company = "A"
for i in range(2007,2017,1):
    directory = company+"/"+str(i)+"-data"
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
                print info.strip() + ", " +str(counter)
                counter+= 1


            fp.close()
        except:
            pass
'''
for line in fp:
    #all_data = fp.readline()
    all_data = re.sub('[\[\](]','',line)
    all_data = all_data.replace('),','#')
    all_data = all_data.replace(')','')
    split_data = all_data.split("#")
    for all_info in split_data:
        split_info = all_info.split(",")
        for info in split_info:
            printer_info = info.split(".")
            if info is split_info[-1]:
                print(info)
            else:
                print(info,end=",")
'''
