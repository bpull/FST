import sys
import re

fp = open(sys.argv[1],"r")

all_data = fp.readline()
all_data = re.sub('[\[\](]','',all_data)
all_data = all_data.replace('),','#')
all_data = all_data.replace(')','')
split_data = all_data.split("#")
for all_info in split_data:
    split_info = all_info.split(",")
    for info in split_info:
        printer_info = info.split(".")
        if info is split_info[-1]:
            print(printer_info[0])
        else:
            print(printer_info[0],end=",")
