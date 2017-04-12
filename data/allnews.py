fp2 = open("allnews.csv","w")
for i in range(2007,2017,1):
    for j in range(1,13,1):
        filename = str(i)+"-data"+"/"+str(j)
        fp = open(filename,"r")
        alldata = fp.readline().strip().split("], ")
        for k in range(len(alldata)):
            fp2.write(alldata[k].strip("[]")+", " + str(j)+"-"+str(k)+"-"+str(i)+"\n")
